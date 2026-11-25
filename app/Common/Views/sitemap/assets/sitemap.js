/**
 * Renders the AIOSEO sitemaps as a readable table when they're opened in a browser.
 *
 * The sitemap XML loads it as an XHTML script, and it swaps the XML for an XHTML view built from the
 * XML plus the labels, dates and counts that the sitemap's ".xsl" URL serves as JSON. If anything fails, the XML stays as it is,
 * styled by sitemap.css.
 *
 * NOTE: Kept dependency-free and outside the build: it runs in XML documents, not admin pages.
 */
(function () {
	'use strict'

	const XHTML = 'http://www.w3.org/1999/xhtml'
	const SVG = 'http://www.w3.org/2000/svg'
	const NS = {
		sitemap : 'http://www.sitemaps.org/schemas/sitemap/0.9',
		image   : 'http://www.google.com/schemas/sitemap-image/1.1',
		video   : 'http://www.google.com/schemas/sitemap-video/1.1',
		news    : 'http://www.google.com/schemas/sitemap-news/0.9'
	}

	const script = document.currentScript
	const viewUrl = script ? script.getAttribute('data-view') : ''
	if (!viewUrl || !window.fetch || !window.Promise) {
		return
	}

	// Hides the raw XML until the view is ready, like the XSL stylesheet did. The timeout shows the CSS-only list if the data is slow.
	const show = hide()
	const timeout = window.setTimeout(show, 3000)

	const separator = -1 === viewUrl.indexOf('?') ? '?' : '&'
	const request = window.fetch(viewUrl + separator + 'url=' + encodeURIComponent(window.location.href), { credentials: 'same-origin' })
		.then((response) => {
			if (!response.ok) {
				throw new Error('HTTP ' + response.status)
			}

			return response.json()
		})

	// The script is the first child of the root element, so the rest of the XML isn't parsed yet.
	const parsed = new Promise((resolve) => {
		if ('loading' !== document.readyState) {
			resolve()

			return
		}

		document.addEventListener('DOMContentLoaded', resolve)
	})

	Promise.all([ request, parsed ])
		.then(([ data ]) => render(data))
		.catch((error) => {
			console.warn('AIOSEO: Could not render the sitemap view.', error)
		})
		.finally(() => {
			window.clearTimeout(timeout)
			show()
		})

	// Uses a constructed stylesheet, since sitemap.css may still be loading and XML elements ignore inline styles.
	function hide () {
		if (!window.CSSStyleSheet || !('adoptedStyleSheets' in document)) {
			return () => {}
		}

		try {
			const sheet = new window.CSSStyleSheet()
			sheet.replaceSync(':root { visibility: hidden }')
			document.adoptedStyleSheets = [ ...document.adoptedStyleSheets, sheet ]

			return () => {
				document.adoptedStyleSheets = document.adoptedStyleSheets.filter((adopted) => adopted !== sheet)
			}
		} catch (error) {
			return () => {}
		}
	}

	function render (data) {
		const sitemap = readSitemap(data)
		const strings = data.strings || {}
		const isIndex = 'index' === sitemap.kind

		const head = el('head', {}, [
			el('meta', { name: 'viewport', content: 'width=device-width, initial-scale=1' }),
			el('title', {}, isIndex ? data.indexTitle : data.title)
		])

		const container = el('div', { class: 'container' })
		if (!sitemap.entries.length) {
			append(container, emptyView(data, strings))
		} else {
			append(container, [
				breadcrumb(isIndex ? data.breadcrumbs.index : data.breadcrumbs.sitemap, strings),
				table(sitemap, data, strings)
			])
		}

		const body = el('body', {}, [
			header(data, sitemap),
			el('div', { class: 'content' }, container)
		])

		// The stylesheet processing instruction stays, so sitemap.css styles the new view as well.
		const html = el('html', { lang: data.lang || null }, [ head, body ])
		document.replaceChild(html, document.documentElement)

		// The browser resolved the fragment before the view existed, so a "#modal-N" link wouldn't open its modal.
		if (window.location.hash) {
			window.location.replace(window.location.href)
		}
	}

	function readSitemap (data) {
		const root = document.documentElement
		if ('sitemapindex' === root.localName) {
			return { kind: 'index', entries: sortEntries('index', children(root, NS.sitemap, 'sitemap')) }
		}

		if ('rss' === root.localName) {
			const channel = child(root, null, 'channel')

			return { kind: 'rss', entries: sortEntries('rss', channel ? children(channel, null, 'item') : []) }
		}

		const kind = 'video' === data.type || 'news' === data.type ? data.type : 'urlset'

		return { kind, entries: sortEntries(kind, children(root, NS.sitemap, 'url')) }
	}

	// Supports the "sitemap-order" and "sitemap-orderby" URL parameters.
	function sortEntries (kind, entries) {
		const params = new URLSearchParams(window.location.search)
		const orderBy = params.get('sitemap-orderby')
		const order = params.get('sitemap-order')
		if ('ascending' !== orderBy && 'descending' !== orderBy) {
			return entries
		}

		let key = null
		switch (kind) {
			case 'index':
			case 'video':
				key = (entry) => text(entry, NS.sitemap, 'lastmod')
				break
			case 'news':
				key = (entry) => {
					const news = child(entry, NS.news, 'news')

					return news ? text(news, NS.news, 'publication_date') : ''
				}
				break
			case 'rss':
				key = order ? (entry) => text(entry, null, 'pubDate') : null
				break
			default:
				if ('image' === order) {
					key = (entry) => children(entry, NS.image, 'image').length
				} else if ('date' === order) {
					key = (entry) => text(entry, NS.sitemap, 'lastmod')
				} else if (order) {
					key = (entry) => text(entry, NS.sitemap, order)
				}
		}

		if (!key) {
			return entries
		}

		const sorted = entries.slice().sort((a, b) => compare(key(a), key(b)))

		return 'descending' === orderBy ? sorted.reverse() : sorted
	}

	function compare (a, b) {
		const numberA = parseFloat(a)
		const numberB = parseFloat(b)
		if (!isNaN(numberA) && !isNaN(numberB) && String(numberA) === String(a) && String(numberB) === String(b)) {
			return numberA - numberB
		}

		return String(a).localeCompare(String(b))
	}

	function header (data, sitemap) {
		const strings = data.header || {}
		const count = sitemap.entries.length
		const head = el('div', { id: 'content-head' }, [
			el('h1', {}, data.heading),
			el('p', {}, [
				strings.generatedBy + ' ',
				link(strings.pluginUrl, strings.pluginName, { target: '_blank', rel: 'noreferrer noopener' }),
				', ' + strings.description
			]),
			el('p', {}, withLink(strings.moreInfo, strings.moreInfoUrl))
		])

		if (count) {
			const summary = 'index' === sitemap.kind
				? [ strings.indexContains, count, 1 === count ? strings.sitemap : strings.sitemaps ]
				: [ strings.contains, count, 1 === count ? strings.url : strings.urls ]

			append(head, el('p', {}, summary.concat(strings.generatedOn).join(' ')))
		}

		return head
	}

	function breadcrumb (crumbs, strings) {
		if (!crumbs || !crumbs.length) {
			return null
		}

		const trail = el('div', { class: 'breadcrumb' })
		if (1 < crumbs.length) {
			append(trail, icon('M5.274 7.56L2.22 4.5l3.054-3.06-.94-.94-4 4 4 4 .94-.94z', 6, 9, 'back'))
		}

		crumbs.forEach((crumb, index) => {
			if (0 < index) {
				append(trail, icon('M.727 7.06L3.78 4 .727.94l.94-.94 4 4-4 4-.94-.94z', 6, 8))
			}

			if (crumbs.length === index + 1) {
				append(trail, el('span', {}, crumb.title))

				return
			}

			append(trail, link(crumb.url, el('span', {}, crumb.title), { 'data-back': strings.back }))
		})

		return trail
	}

	function table (sitemap, data, strings) {
		const columns = getColumns(sitemap.kind, data, strings)
		// The addon stylesheets stripe the even rows, the general one the odd rows.
		const stripe = 'video' === data.type || 'news' === data.type ? 1 : 0
		const rows = sitemap.entries.map((entry, index) => el(
			'tr',
			{ class: stripe === index % 2 ? 'stripe' : null },
			columns.map((column) => el('td', { class: column.className || null }, column.cell(entry, index)))
		))

		return el('div', { class: 'table-wrapper' }, el('table', { cellpadding: '3' }, [
			el('thead', {}, el('tr', {}, columns.map((column, index) => el('th', { class: column.left ? 'left' : null }, headerTitle(column, index, data))))),
			el('tbody', {}, rows)
		]))
	}

	// The video stylesheet links its URL header to the sitemap without the sorting parameters or the modal fragment.
	function headerTitle (column, index, data) {
		if ('video' !== data.type || 0 !== index) {
			return column.title
		}

		return link(window.location.origin + window.location.pathname, column.title)
	}

	function getColumns (kind, data, strings) {
		const url = { title: strings.url, className: 'left', left: true, cell: (entry) => link(text(entry, NS.sitemap, 'loc')) }
		const lastUpdated = { title: strings.lastUpdated, className: 'datetime', cell: (entry) => datetime(data, text(entry, NS.sitemap, 'loc')) }

		switch (kind) {
			case 'index':
				return [
					url,
					{ title: strings.urlCount, cell: (entry) => urlCount(data, text(entry, NS.sitemap, 'loc')) },
					lastUpdated
				]
			case 'rss':
				return [
					{ title: strings.url, className: 'left', left: true, cell: (entry) => link(text(entry, null, 'link')) },
					{ title: strings.publicationDate, className: 'datetime', cell: (entry) => datetime(data, text(entry, null, 'link')) }
				]
			case 'video':
				return [
					url,
					{ title: strings.video, cell: (entry) => el('div', { class: 'item-count' }, children(entry, NS.video, 'video').length) },
					{ title: strings.videoThumbnail, left: true, cell: (entry, index) => videoThumbnails(entry, index, strings) },
					lastUpdated
				]
			case 'news':
				return [
					{
						title     : strings.title,
						className : 'left',
						left      : true,
						cell      : (entry) => {
							const news = child(entry, NS.news, 'news')

							return news ? decodeEntities(text(news, NS.news, 'title')) : ''
						}
					},
					url,
					{ title: strings.publishDate, className: 'datetime', cell: (entry) => datetime(data, text(entry, NS.sitemap, 'loc')) }
				]
			default:
				return [
					{ title: strings.url, className: 'left', left: true, cell: alternates },
					data.excludeImages ? null : { title: strings.images, cell: (entry) => el('div', { class: 'item-count' }, children(entry, NS.image, 'image').length) },
					{ title: strings.changeFrequency, cell: (entry) => capitalize(text(entry, NS.sitemap, 'changefreq')) },
					{ title: strings.priority, cell: (entry) => priority(text(entry, NS.sitemap, 'priority'), strings) },
					lastUpdated
				].filter(Boolean)
		}
	}

	// Lists the hreflang alternates instead of the URL, starting with the last one.
	function alternates (entry) {
		const links = Array.from(entry.children).filter((node) => 'alternate' === node.getAttribute('rel'))
		if (!links.length) {
			return link(text(entry, NS.sitemap, 'loc'))
		}

		const alternate = (node) => [
			el('a', { href: safeUrl(node.getAttribute('href')), class: 'localized' }, node.getAttribute('href')),
			'  → ' + (node.getAttribute('hreflang') || '')
		]

		const last = links[links.length - 1]
		const cell = alternate(last)
		links.slice(0, -1).forEach((node) => {
			cell.push(el('br'))
			cell.push(...alternate(node))
		})

		return cell
	}

	function urlCount (data, loc) {
		const counts = data.counts || {}
		const slugs = Object.keys(counts)
		if (!slugs.length) {
			return null
		}

		const slug = slugs.find((value) => -1 !== loc.indexOf(value))

		return el('div', { class: 'item-count' }, slug ? counts[slug] : data.linksPerIndex)
	}

	function datetime (data, url) {
		const value = data.datetime ? data.datetime[url] : null
		if (!value) {
			return null
		}

		return [
			el('div', { class: 'date' }, value.date),
			el('div', { class: 'time' }, value.time)
		]
	}

	function priority (value, strings) {
		const number = parseFloat(value)
		if (isNaN(number)) {
			return null
		}

		let level = ''
		if (0.5 >= number) {
			level = 'low'
		} else if (0.6 <= number && 0.8 >= number) {
			level = 'medium'
		} else if (0.9 <= number) {
			level = 'high'
		}

		return el('div', { class: 'priority priority--' + level }, level ? strings[level] : '')
	}

	function videoThumbnails (entry, index, strings) {
		const videos = children(entry, NS.video, 'video')
		const thumbnail = (video) => {
			const thumbnailUrl = safeUrl(text(video, NS.video, 'thumbnail_loc'))
			if (!thumbnailUrl) {
				return null
			}

			const playerUrl = text(video, NS.video, 'player_loc') || text(video, NS.video, 'content_loc')

			return el('a', { href: safeUrl(playerUrl), target: '_new' }, el('img', { src: thumbnailUrl, alt: '' }))
		}

		const column = el('div', { class: 'videos-column' }, el('div', { class: 'videos' }, videos.slice(0, 2).map(thumbnail)))
		if (2 >= videos.length) {
			return column
		}

		const modalId = 'modal-' + (index + 1)
		const loc = text(entry, NS.sitemap, 'loc')
		append(column, [
			el('a', { class: 'open-modal', href: '#' + modalId }, '+' + (videos.length - 2)),
			el('div', { class: 'modal-window', id: modalId }, el('div', { class: 'modal-content' }, [
				el('a', { href: '#', title: strings.close, class: 'modal-close' }, icon('M14.667 1.91L13.257.5l-5.59 5.59L2.077.5.667 1.91l5.59 5.59-5.59 5.59 1.41 1.41 5.59-5.59 5.59 5.59 1.41-1.41-5.59-5.59 5.59-5.59z', 15, 15, null, '#8C8F9A')),
				el('div', { class: 'title' }, (strings.videoCount || '').replace('%s', videos.length)),
				link(loc, loc, { class: 'link' }),
				el('div', { class: 'videos' }, videos.map(thumbnail))
			]))
		])

		return column
	}

	function emptyView (data, strings) {
		const empty = data.empty || {}

		return [
			breadcrumb(data.breadcrumbs.empty, strings),
			el('div', { class: 'empty-sitemap' }, [
				el('h2', { class: 'empty-sitemap__title' }, [ empty.title, el('br'), empty.subtitle ]),
				el('div', { class: 'empty-sitemap__buttons' }, [
					link(empty.homeUrl, empty.homeLabel, { class: 'button' }),
					empty.canManage ? link(empty.adminUrl, empty.adminLabel, { class: 'button' }) : null
				]),
				empty.canManage ? el('div', { class: 'aioseo-alert yellow' }, withLink(empty.alert, empty.learnMoreUrl)) : null
			])
		]
	}

	// Turns a translated string with %1$s/%2$s around the link text into text and a link.
	function withLink (string, url) {
		string = string || ''
		const start = string.indexOf('%1$s')
		const end = string.indexOf('%2$s')
		if (-1 === start || end < start) {
			return string.replace(/%[12]\$s/g, '')
		}

		return [
			string.slice(0, start),
			link(url, string.slice(start + 4, end), { target: '_blank', rel: 'noreferrer noopener' }),
			string.slice(end + 4)
		]
	}

	function capitalize (value) {
		return value ? value.charAt(0).toUpperCase() + value.slice(1) : ''
	}

	function safeUrl (url) {
		return /^https?:\/\//i.test(url || '') ? url : null
	}

	function link (url, content, attributes) {
		return el('a', Object.assign({ href: safeUrl(url) }, attributes), undefined === content ? url : content)
	}

	function icon (path, width, height, className, fill) {
		const svg = document.createElementNS(SVG, 'svg')
		svg.setAttribute('width', width)
		svg.setAttribute('height', height)
		svg.setAttribute('fill', 'none')
		if (className) {
			svg.setAttribute('class', className)
		}

		const shape = document.createElementNS(SVG, 'path')
		shape.setAttribute('d', path)
		shape.setAttribute('fill', fill || '#141B38')
		svg.appendChild(shape)

		return svg
	}

	function el (tag, attributes, content) {
		const node = document.createElementNS(XHTML, tag)
		Object.keys(attributes || {}).forEach((name) => {
			if (null !== attributes[name] && undefined !== attributes[name]) {
				node.setAttribute(name, attributes[name])
			}
		})

		return append(node, content)
	}

	function append (node, content) {
		[].concat(undefined === content ? [] : content).forEach((item) => {
			if (null === item || undefined === item || false === item) {
				return
			}

			if (Array.isArray(item)) {
				append(node, item)

				return
			}

			node.appendChild('object' === typeof item ? item : document.createTextNode(String(item)))
		})

		return node
	}

	function children (node, namespace, name) {
		return Array.from(node.children).filter((item) => item.localName === name && (item.namespaceURI || null) === namespace)
	}

	function child (node, namespace, name) {
		return children(node, namespace, name)[0] || null
	}

	// News titles come HTML-encoded. DOMParser documents are inert, so nothing in the title runs or loads, and only its text is kept.
	function decodeEntities (value) {
		if (!value || -1 === value.indexOf('&')) {
			return value
		}

		return new window.DOMParser().parseFromString('<!DOCTYPE html><body>' + value, 'text/html').body.textContent
	}

	function text (node, namespace, name) {
		const item = child(node, namespace, name)

		return item ? item.textContent.trim() : ''
	}
})()