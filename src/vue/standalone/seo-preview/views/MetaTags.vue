<template>
	<div class="aioseo-seo-preview-standalone-view-meta-tags">
		<dl>
			<template
				v-for="(item,index) in metaTags"
				:key="index"
			>
				<dt>
					{{ item.label }}
				</dt>

				<dd>
					<a v-if="isUrl(item.value)"
						:href="item.value"
						target="_blank"
					>
						{{ decodeUrl(item.value) }}
					</a>
					<template v-else>{{ item.value }}</template>
				</dd>
			</template>
		</dl>
	</div>
</template>

<script>
import { isUrl } from '@/vue/utils/helpers'
import { useUrl } from '@/vue/composables/Url'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			decodeUrl
		} = useUrl()

		return {
			decodeUrl
		}
	},
	computed : {
		metaTags () {
			const output = []
			const basicTags = [
				{
					label : __('Title', td),
					value : document.title || ''
				},
				{
					label : __('Description', td),
					value : document.querySelector('meta[name="description"]')?.content || ''
				},
				{
					label : __('Canonical URL', td),
					value : document.querySelector('link[rel="canonical"]')?.href || ''
				}
			]
			const socialMetaTags = document.querySelectorAll(
				'meta[property^="og:"][content],' +
				'meta[name^="twitter:"][content]'
			)

			// Add meta keywords if any
			let keywordsMetaContent = document.querySelector('meta[name="keywords"]')?.content || ''
			if (keywordsMetaContent) {
				// Add space after each comma
				keywordsMetaContent = keywordsMetaContent.split(',').join(', ')

				// Add it to the third position in the basicTags array
				basicTags.splice(2, 0, {
					label : __('Meta Keywords', td),
					value : keywordsMetaContent
				})
			}

			basicTags.forEach((tag) => {
				if (tag.value) {
					output.push({
						label : tag.label,
						value : tag.value
					})
				}
			})

			if (0 < socialMetaTags?.length) {
				socialMetaTags.forEach((tag) => {
					if (tag.content) {
						output.push({
							label : tag.getAttribute('name') ? tag.getAttribute('name') : tag.getAttribute('property'),
							value : tag.content
						})
					}
				})
			}

			return output
		}
	},
	methods : {
		isUrl
	}
}
</script>

<style lang="scss">
.aioseo-seo-preview-standalone-view-meta-tags {
	dl {
		margin: 0;
		padding: 0 0 0 10px;

		dt,
		dd {
			font-family: $font-family;
			margin: 0;
			padding: 0;
		}

		dt {
			color: $black;
			font-size: 14px;
			font-weight: 700;

			+ dd {
				margin-top: 4px;
				padding-left: 10px;
			}
		}

		dd {
			color: $black2;
			font-size: 13px;
			font-weight: 400;

			+ dt {
				margin-top: 8px;
			}

			:deep(a) {
				border-bottom: 1px dotted $blue;
				color: $blue;
				text-decoration: none;
			}
		}
	}
}
</style>