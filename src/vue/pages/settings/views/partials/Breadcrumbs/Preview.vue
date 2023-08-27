<template>
	<div class="aioseo-preview-box">
		<span class="label" v-if="label">
			{{ label }}:
		</span>

		<template
			v-for="(item,index) in this.getPreviewData()"
			:key="index"
		>
			<span
				class="aioseo-breadcrumb-separator"
				v-if="1 < previewLength && index > 0 && index < previewLength"
			>
				{{ decodeHTMLEntities(optionsStore.options.breadcrumbs.separator) }}
			</span>

			<span
				:class="{ 'aioseo-breadcrumb' : !item.match(/aioseo-breadcrumb/),  link : item !== optionsStore.options.breadcrumbs.breadcrumbPrefix && !item.match(/<a /) }"
				v-if="index < (previewLength -1)"
				v-html="item"
			/>

			<span
				v-if="index === (previewLength -1)"
				:class="{ last : true, link: optionsStore.options.breadcrumbs.linkCurrentItem && useDefaultTemplate && !item.match(/<a /), noLink : !optionsStore.options.breadcrumbs.linkCurrentItem && useDefaultTemplate, 'aioseo-breadcrumb' : !item.match(/aioseo-breadcrumb/) }"
				v-html="item"
			/>
		</template>
	</div>
</template>

<script>
import {
	useOptionsStore
} from '@/vue/stores'

import tags from '@/vue/utils/tags'
import { decodeHTMLEntities } from '@/vue/utils/helpers'
import { softSanitizeHtml } from '@/vue/utils/strings'

export default {
	setup () {
		return {
			optionsStore : useOptionsStore()
		}
	},
	props : {
		previewData : {
			type    : Array,
			default : null
		},
		useDefaultTemplate : {
			type    : Boolean,
			default : true
		},
		label : String
	},
	computed : {
		previewLength () {
			return this.getPreviewData() ? this.getPreviewData().length : 0
		}
	},
	methods : {
		getPreviewData () {
			let crumbs = this.previewData.filter(item => !!item).map(item => tags.decodeHTMLEntities(softSanitizeHtml(item)).replace(/#breadcrumb_separator/g, '<span class="aioseo-breadcrumb-separator">' + this.optionsStore.options.breadcrumbs.separator + '</span>').replace(/#breadcrumb_link/g, 'Permalink'))
			if (this.useDefaultTemplate && !this.optionsStore.options.breadcrumbs.showCurrentItem) {
				crumbs = crumbs.slice(0, crumbs.length - 1)
			}
			return crumbs
		},
		decodeHTMLEntities
	}
}
</script>

<style lang="scss">

</style>