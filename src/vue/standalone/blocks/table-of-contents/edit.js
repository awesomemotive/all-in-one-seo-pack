import '@/vue/utils/vue2.js'
import { createApp } from 'vue'

import {
	loadPiniaStores,
	useTableOfContentsStore
} from '@/vue/stores'

import App from './vue/App.vue'
import { formatHeadingList, flattenHeadings } from './helpers'
import { sidebarControls } from './sidebar-controls'
import { html, isEqual, deepCopy, cleanHtml } from '@/vue/standalone/blocks/utils'
import { observeElement } from '@/vue/utils/helpers'
import { cleanForSlug } from '@/vue/utils/cleanForSlug'
import { extraHeadingProperties } from './constants'
import translate from '@/vue/plugins/translations'

const { useSelect }    = window.wp.data
const blockEditorStore = window.wp.blockEditor.store
const { isTyping }     = window.wp.data.select(blockEditorStore) || { isTyping: () => null }

const hasInitialized = []
let latestHeadings   = []
export default function edit (props) {
	const { setAttributes, attributes, clientId, className, isSelected } = props

	// If we have headings, mount our Vue menu.
	const targetElementId = `aioseo-${clientId}`

	// The hasInitialized value is unique to this instance. Check if it's new.
	// When a page is loaded with an existing block, check that the DOM has loaded the editors block HTML.
	// When a block is first added, the DOM check would have already come back false. But isSelected will be true.
	if (!hasInitialized.includes(clientId) && (isSelected || document.querySelector(`[data-block="${clientId}"]`))) {
		hasInitialized.push(clientId)

		observeElement({
			id      : targetElementId,
			parent  : document.querySelector('.block-editor'),
			subtree : true,
			done    : function (el) {
				const app = createApp({ ...App, name: 'Blocks/TableOfContents' })

				app.$t  = app.config.globalProperties.$t  = translate
				app.$td = app.config.globalProperties.$td = import.meta.env.VITE_TEXTDOMAIN

				// Use the pinia store.
				loadPiniaStores(app)

				app.mount(el)

				// Initialize the block state with any existing attributes that were saved previously or simply the defaults.
				// NOTE: We need to populate the state before we create our Vue instance because the client ID otherwise will not be available yet when mounted() runs.
				const tableOfContentsStore         = useTableOfContentsStore()
				tableOfContentsStore.blockClientId = clientId
				tableOfContentsStore.headings      = attributes.headings
				tableOfContentsStore.listStyle     = attributes.listStyle
				tableOfContentsStore.reOrdered     = attributes.reOrdered

				// If headings were already saved previously, we need to sync up the new block client IDs.
				if (tableOfContentsStore.headings?.length) {
					const syncedHeadings = flattenHeadings(deepCopy(tableOfContentsStore.headings))
					syncedHeadings.forEach((heading) => {
						const matchingHeading = latestHeadings.find((x) => {
							return x.content === heading.content &&
								   x.anchor  === heading.anchor  &&
								   x.id      === heading.id      &&
								   x.level   === heading.level
						})

						if (matchingHeading) {
							heading.blockClientId = matchingHeading.blockClientId
						}
					})

					tableOfContentsStore.headings = formatHeadingList(syncedHeadings)
					setAttributes(tableOfContentsStore)
				}

				window.aioseoBus.$on('setAttributes' + clientId, () => {
					setAttributes(tableOfContentsStore.$state)
				})
			}
		})
	}

	// First, let's grab all the headings and watch them.
	latestHeadings = useSelect(
		select => {
			const {
				getBlockAttributes,
				getBlockIndex,
				getBlockName,
				getClientIdsWithDescendants
			} = select(blockEditorStore)
			const tableOfContentsIndex = getBlockIndex(clientId)

			// First, we get all the headings in the editor.
			const headings          = []
			const allBlockClientIds = getClientIdsWithDescendants()
			allBlockClientIds.forEach((blockClientId) => {
				// We just want core heading blocks.
				if ('core/heading' !== getBlockName(blockClientId) && 'aioseo/faq' !== getBlockName(blockClientId)) {
					return
				}

				const headingAttributes = getBlockAttributes(blockClientId)
				const headingIndex      = getBlockIndex(blockClientId)
				const hasAnchor         = 'string' === typeof headingAttributes?.anchor && '' !== headingAttributes.anchor
				const headingLevel      = headingAttributes.level || headingAttributes.tagName.replace('h', '')

				// Don't include div FAQ blocks
				if ('div' === headingLevel) {
					return
				}

				let headingContent = headingAttributes.question || headingAttributes.content || ''

				// Don't add a heading until it has content & is below the TOC.
				if (
					'string' !== typeof headingContent ||
					'' === headingContent ||
					tableOfContentsIndex > headingIndex
				) {
					return
				}

				// Sanitize and strip HTML from the content headings. Convert <br> tags to a space.
				headingContent = cleanHtml(headingContent.replace(/(<br *\/?>)+/g, ' '), true)

				// If the heading already has an anchor, we don't want to overwrite it.
				// If the user is typing, let them finish so the anchor isn't a partial word.
				if (!hasAnchor && !isTyping()) {
					headingAttributes.anchor = 'aioseo-' + cleanForSlug(headingContent)
				}

				headings.push({
					id      : headingIndex,
					blockClientId,
					content : headingContent,
					level   : Number(headingLevel),
					anchor  : hasAnchor ? headingAttributes.anchor : ''
				})
			})

			headings.forEach((heading, index) => {
				heading.order = index
			})

			// Next, get our existing headings from the state. It's important that we clone them because we're also flattening them and we don't want to flatten by reference.
			// Then, delete all the dynamic properties; otherwise the headings from the editor will always be different from the headings from the state.
			const tableOfContentsStore = useTableOfContentsStore()
			const existingHeadings     = flattenHeadings(deepCopy(tableOfContentsStore.headings))?.map((existingHeading) => {
				Object.keys(extraHeadingProperties).forEach((propName) => {
					delete existingHeading[propName]
				})

				return existingHeading
			})

			// Now, check if the headings from the editor differ from the state ones.
			// If they do, return them so that we'll force an update.
			// If they do not, return null so that we do not keep recomputing this.
			// See https://github.com/WordPress/gutenberg/blob/trunk/packages/block-library/src/table-of-contents/edit.js#L81-L84 for more info on this.
			if (!isEqual(
				[ ...headings ].sort((a, b) => a.order - b.order),
				[ ...existingHeadings ].sort((a, b) => a.order - b.order)
			)) {
				return headings
			}

			// To ensure that our state & block attributes are synchronised, we also should do a quick array length check here.
			// getBlockAttributes(clientId) is more reliable than using the attributes prop here.
			if (flattenHeadings([ ...getBlockAttributes(clientId)?.headings ])?.length !== [ ...headings ]?.length) {
				return headings
			}

			// If reordered, we need to look at our editedOrder value, which content headings don't have.
			// We'll order by editedOrder and compare our attributes to our store.
			if (true === getBlockAttributes(clientId)?.reOrdered) {
				if (!isEqual(
					flattenHeadings([ ...tableOfContentsStore.headings ]).sort((a, b) => a.editedOrder - b.editedOrder),
					flattenHeadings([ ...getBlockAttributes(clientId)?.headings ]).sort((a, b) => a.editedOrder - b.editedOrder)
				)) {
					return headings
				}
			}

			return null
		},
		[ clientId ]
	)

	// If the headings changed, we need to update the state as soon as the user stops typing.
	if (null !== latestHeadings) {
		const sorted = latestHeadings.sort((a, b) => a.editedOrder - b.editedOrder)
		window.aioseoBus.$emit('updateHeadings' + clientId, sorted)
	}

	return html`
		<div className="${className}">
			<div id="aioseo-${clientId}"></div>
			${sidebarControls(props)}
		</div>
	`
}