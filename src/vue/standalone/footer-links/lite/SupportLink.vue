<template>
	<a
		:href="getUtmLink('https://aioseo.com/plugin/lite-support', 'support')"
		target="_blank"
		rel="noreferrer noopener"
	>
		{{strings.support}}
	</a>
</template>

<script setup>
import { __ } from '@/vue/plugins/translations'
import { getUtmLink } from '../composables/utmLink'

const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	support : __('Support', td)
}
</script>