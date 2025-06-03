<template>
  <core-alert
    class="inline-upsell"
    v-if="licenseStore.isUnlicensed"
    type="blue"
  >
    <div v-html="strings.breadcrumbsUpsell" />
  </core-alert>
</template>

<script setup>
import {
	useLicenseStore
} from '@/vue/stores'

import {
	GLOBAL_STRINGS
} from '@/vue/plugins/constants'
import links from '@/vue/utils/links'

import CoreAlert from '@/vue/components/common/core/alert/Index'

import { __, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const licenseStore = useLicenseStore()

const strings = {
	breadcrumbsUpsell : sprintf(
		// Translators: 1 - "PRO", 2 - "Learn more".
		__('Breadcrumbs is a %1$s feature. %2$s', td),
		'PRO',
		links.getUpsellLink('post-advanced', GLOBAL_STRINGS.learnMore, 'breadcrumbs', true)
	)
}
</script>

<style scoped>
.inline-upsell {
  margin-top: 0 !important;
}
</style>