<template>
	<div
		class="aioseo-google-search-preview"
		:class="`aioseo-google-search-preview--${device}`"
	>
		<div class="aioseo-google-search-preview__main">
			<div class="aioseo-google-search-preview__favicon">
				<div class="favicon-wrapper">
					<img
						:src="parseFavicon"
						alt="Favicon"
						loading="lazy"
						decoding="async"
						height="18"
						width="18"
					/>
				</div>
			</div>

			<div class="aioseo-google-search-preview__location">
				<div class="hostname text-truncate">
					{{ hostname.replace(/^(m|www)\./, '') }}
				</div>

				<div
					class="url text-truncate"
					v-html="urlBreadcrumbs"
				/>
			</div>

			<div class="aioseo-google-search-preview__title">
				{{ title.substring(0, 70).trim() + (title.length > 70 ? ' ...' : '') }}
			</div>

			<div
				class="aioseo-google-search-preview__description"
				v-html="parseDescription"
			/>
		</div>

		<div
			v-if="Object.values(reviewSnippet).length"
			class="aioseo-google-search-preview__review-snippet"
		>
			<div class="aioseo-google-search-preview__review-snippet__stars">
				<div/>
			</div>

			<div class="aioseo-google-search-preview__review-snippet__rating">
				<span>{{ strings.rating }}:</span>

				{{ parseFloat(reviewSnippet.ratingValue).toFixed(2) }}
			</div>

			<div class="aioseo-google-search-preview__review-snippet__count bullet">
				{{ getReviewSnippetCountLabel() }}
			</div>

			<div
				v-if="null !== reviewSnippet?.price"
				class="aioseo-google-search-preview__review-snippet__price bullet"
			>
				{{ getReviewSnippetPriceLabel() }}
			</div>
		</div>

		<div
			v-if="this.richResults?.anchorLinks?.length"
			class="aioseo-google-search-preview__anchor"
		>
			<template
				v-for="(item, index) in this.richResults.anchorLinks"
				:key="`anchor-${index}`"
			>
				<span class="aioseo-google-search-preview__anchor__link">{{ truncate(item, 30) }}</span>

				<span
					class="aioseo-google-search-preview__anchor__bullet"
					v-if="index !== this.richResults.anchorLinks.length - 1"
				> &bull; </span>
			</template>
		</div>

		<div
			v-if="Object.values(faq).length"
			class="aioseo-google-search-preview__faq"
		>
			<details
				v-for="(item, index) in faq.slice(0, 3)"
				:key="`faq-${index}`"
				class="aioseo-google-search-preview__faq__container"
			>
				<summary
					class="aioseo-google-search-preview__faq__question"
					role="button"
				>
					<span
						class="text-truncate"
						v-html="truncate(stripTags(item.question), 60)"
					/>

					<svg-caret width="20"/>
				</summary>

				<span
					class="aioseo-google-search-preview__faq__answer"
					v-html="stripTags(item.answer)"
				/>
			</details>
		</div>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import { stripTags } from '@/vue/utils/strings'
import { getText, truncate } from '@/vue/utils/html'
import { CURRENCY_LIST } from '@/vue/plugins/constants'
import SvgCaret from '@/vue/components/common/svg/Caret'

export default {
	components : {
		SvgCaret
	},
	computed : {
		faq () {
			const faq = this.richResults?.faq || []

			return Array.isArray(faq) && faq.length ? faq : []
		},
		reviewSnippet () {
			const defaultReviewSnippet = {
				bestRating    : null,
				ratingValue   : null,
				reviewCount   : null,
				ratingCount   : null,
				priceCurrency : null,
				price         : null,
				priceFrom     : null,
				priceTo       : null
			}

			const reviewSnippet = {
				...defaultReviewSnippet,
				...(this.richResults?.reviewSnippet || {})
			}

			if (
				Object.values(reviewSnippet).every(value => null === value) ||
				(!reviewSnippet.reviewCount && !reviewSnippet.ratingCount)
			) {
				return {}
			}

			for (const [ key, value ] of Object.entries(reviewSnippet)) {
				if (
					[ 'bestRating', 'ratingValue' ].includes(key) &&
					(5 < value || null === value)
				) {
					return {}
				}
			}

			reviewSnippet.price     = parseFloat(reviewSnippet.price) ? parseFloat(reviewSnippet.price).toFixed(2) : null
			reviewSnippet.priceFrom = parseFloat(reviewSnippet.priceFrom) ? parseFloat(reviewSnippet.priceFrom).toFixed(2) : null
			reviewSnippet.priceTo   = parseFloat(reviewSnippet.priceTo) ? parseFloat(reviewSnippet.priceTo).toFixed(2) : null

			return reviewSnippet
		},
		yellowStarsWidth () {
			return `${(this.reviewSnippet.ratingValue * 100) / 5}%`
		},
		urlBreadcrumbs () {
			try {
				const url = new URL(this.url)

				let out = url.hostname + url.pathname.replace(/\/$/, '')
				out = out.substring(0, 50).trim() + (50 < out.length ? '...' : '')

				return `${url.protocol}//` + out.replaceAll('/', ' &rsaquo; ')
			} catch (_e) {
				return ''
			}
		},
		parseFavicon () {
			const rootStore = useRootStore()
			let faviconUrl = `https://www.google.com/s2/favicons?sz=64&domain=${rootStore.aioseo.urls.domain}`

			try {
				const url = new URL(this.favicon || '')

				faviconUrl = url.href
			} catch (_e) {
				if (rootStore.aioseo.data?.isDev) {
					faviconUrl = `${rootStore.aioseo.urls.home}/favicon.ico`
				}
			}

			return faviconUrl
		},
		parseDescription () {
			const output = getText(this.description.substring(0, 160).trim() + (160 < this.description.length ? ' ...' : ''))
			if (!this.focusKeyphrase) {
				return output
			}

			const words = this.focusKeyphrase.split(' ')
			const regex = new RegExp('\\b' + words.join('\\b|\\b') + '\\b', 'gi')
			return output.replace(regex, '<strong>$&</strong>')
		}
	},
	methods : {
		stripTags,
		getReviewSnippetPriceLabel () {
			if (
				0 === parseFloat(this.reviewSnippet.price) &&
				!this.reviewSnippet.priceTo
			) {
				return this.strings.free
			}

			if (this.reviewSnippet.priceCurrency) {
				const currency = CURRENCY_LIST.find(h => h.value === this.reviewSnippet.priceCurrency) || {}
				return this.reviewSnippet.priceFrom && this.reviewSnippet.priceTo
					? `${currency?.symbol}${this.reviewSnippet.priceFrom} - ${currency?.symbol}${this.reviewSnippet.priceTo}`
					: `${currency?.symbol}${this.reviewSnippet.price}`
			}

			// Fall back to just the price with $ symbol.
			return `$${this.reviewSnippet.price}`
		},
		getReviewSnippetCountLabel () {
			if ('desktop' === this.device) {
				const count = this.reviewSnippet.ratingCount || this.reviewSnippet.reviewCount
				const suffix = this.reviewSnippet.ratingCount
					? this.$t._n('vote', 'votes', count, this.$td)
					: this.$t._n('review', 'reviews', count, this.$td)

				// Translators: 1 - Amount of reviews, 2 - "vote(s)" or "review(s)".
				return this.$t.sprintf(this.$t.__('%1$s %2$s', this.$td), count, suffix)
			}

			return `(${this.reviewSnippet.ratingCount || this.reviewSnippet.reviewCount})`
		},
		truncate
	},
	props : {
		focusKeyphrase : String,
		device         : {
			type    : String,
			default : 'desktop'
		},
		favicon  : String,
		hostname : {
			type : String,
			default () {
				const rootStore = useRootStore()
				return rootStore.aioseo.data.siteName || rootStore.aioseo.urls.domain
			}
		},
		url : {
			type : String,
			default () {
				const rootStore = useRootStore()
				return rootStore.aioseo.urls.mainSiteUrl
			}
		},
		title       : String,
		description : String,
		richResults : Object
	},
	data () {
		return {
			strings : {
				free   : this.$t.__('Free', this.$td),
				rating : this.$t.__('Rating', this.$td)
			}
		}
	}
}
</script>

<style lang="scss" scoped>
.aioseo-google-search-preview {
	&--mobile {
		.aioseo-google-search-preview {
			&__review-snippet {
				&__stars {
					order: 2;
				}

				&__rating {
					span {
						display: none;
					}

					order: 1;
				}

				&__count.bullet::before {
					display: none;
				}
			}

			&__anchor {
				&__link {
					border: 1px solid #e4e4e4;
					border-radius: 3px;
					padding: 4px 6px;
				}

				&__bullet {
					display: none;
				}
			}

			&__faq {
				display: none;
			}
		}
	}

	&__main {
		background-color: #fff;
		display: grid;
		grid-template-areas:
			"favicon location"
			"title title"
			"description description";
		grid-template-columns: 38px minmax(0, 1fr);
		grid-template-rows: auto auto auto;
		width: 100%;
	}

	&__favicon {
		align-items: center;
		display: flex;
		grid-area: favicon;

		.favicon-wrapper {
			align-items: center;
			background-color: #F1F3F4;
			border-radius: 50%;
			color: #0060f0;
			display: flex;
			height: 28px;
			justify-content: center;
			width: 28px;

			img {
				height: 18px;
				width: 18px;
			}
		}
	}

	&__location {
		grid-area: location;
		line-height: 1.4;

		.hostname {
			color: $black3;
			font-size: 14px;
		}

		.url {
			color: #5F6368;
			font-size: 12px;
		}
	}

	&__title,
	&__description {
		overflow-wrap: break-word;
		word-break: break-word;
	}

	&__title {
		color: #180EA4;
		font-size: 20px;
		grid-area: title;
		margin-top: 4px;
	}

	&__description {
		color: #4E5156;
		font-size: 14px;
		grid-area: description;
		line-height: 1.4;
		margin-top: 4px;
		width: 100%;

		&:empty {
			display: none;
		}

		:deep(strong) {
			font-weight: 600;
		}
	}

	&__review-snippet {
		align-items: center;
		color: #70757a;
		display: flex;
		font-size: 13px;
		gap: 5px;
		line-height: normal;
		margin-top: 3px;

		&__stars {
			background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 23.44 19'><polygon fill='%23dadce0' points='10,15.27 16.18,19 14.54,11.97 20,7.24 12.81,6.63 10,0 7.19,6.63 0,7.24 5.46,11.97 3.82,19'/></svg>");
			background-repeat: repeat-x;
			height: 11px;
			order: 1;
			overflow: hidden;
			position: relative;
			width: 66px;

			div {
				background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 23.44 19'><polygon fill='%23fbbc04' points='10,15.27 16.18,19 14.54,11.97 20,7.24 12.81,6.63 10,0 7.19,6.63 0,7.24 5.46,11.97 3.82,19'/></svg>");
				height: 11px;
				width: v-bind(yellowStarsWidth)
			}
		}

		&__rating {
			order: 2;
		}

		&__count {
			order: 3;
		}

		&__price {
			order: 4;
		}

		.bullet {
			&::before {
				content: '•';
				margin: 0 5px 0 0;
				font-size: 10px;
			}
		}
	}

	&__anchor {
		display: flex;
		gap: 8px;
		line-height: normal;
		margin-top: 6px;
		overflow: hidden;

		&__link {
			color: #1a0dab;
			cursor: pointer;
			display: inline-block;
			font-size: 14px;
			white-space: nowrap;
		}

		&__bullet {
			color: #70757a;
			display: inline-block;
			font-size: 12px;
		}
	}

	&__faq {
		margin-top: 12px;

		&__container {
			appearance: none;
			border-top: 1px solid #dadce0;
			color: #4E5156;
			font-size: 14px;
			line-height: normal;
			list-style: none;
			padding: 8px 0;

			.aioseo-caret {
				transform: rotate(0);
				transition: transform 0.2s ease-in-out;
			}

			&[open] {
				.aioseo-caret {
					transform: rotate(180deg);
				}
			}
		}

		&__question {
			align-items: center;
			appearance: none;
			cursor: pointer;
			display: flex;
			justify-content: space-between;
		}

		&__answer {
			display: block;
			margin-top: 8px;
		}
	}

	.text-truncate {
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}
}
</style>