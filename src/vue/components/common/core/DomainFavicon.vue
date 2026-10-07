<template>
	<span class="aioseo-domain-favicon">
		<base-img
			v-show="faviconUrl && canShow"
			:src="faviconUrl"
			:debounce="false"
			:alt="strings.imgAltText"
			@can-show="value => canShow = value"
		/>
		<svg-globe v-if="!faviconUrl || !canShow" />
	</span>
</template>

<script setup>
import { ref, computed } from 'vue'
import { getFaviconUrl } from '@/vue/utils/favicon'
import BaseImg from '@/vue/components/common/base/Img'
import SvgGlobe from '@/vue/components/common/svg/Globe'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	domain : {
		type    : String,
		default : ''
	},
	size : {
		type    : Number,
		default : 32
	}
})

// Remote favicons 404 for domains Google can't resolve; base-img only paints
// once the image loads, and the Globe stands in when it never does.
const canShow    = ref(false)
const faviconUrl = computed(() => getFaviconUrl(props.domain, props.size))

const strings = {
	imgAltText : __('Domain Favicon', td)
}
</script>

<style lang="scss">
.aioseo-domain-favicon {
	img,
	svg {
		// A broad legacy `.aioseo-wp-table svg` rule adds margin and lets the icon
		// shrink inside the flex wrapper (globe collapsed to ~4.78px); keep the
		// favicon square and gap-free for every consumer.
		flex: 0 0 auto;
		margin: 0 !important;
	}
}
</style>