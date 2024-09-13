<template>
	<div>
		<import
			v-if="shouldShowMain"
		/>

		<cta
			v-if="shouldShowUpdate || shouldShowActivate"
			card-slug="localBusinessImport"
			:header-text="strings.importLocalSeoFromOtherPlugins"
		/>

		<lite
			v-if="shouldShowLite"
		/>
	</div>
</template>

<script setup>
import Import from './AIOSEO_VERSION/import/Import'
import Lite from './lite/import/Import'
import Cta from './AIOSEO_VERSION/partials/Cta'
import { useAddonConditions } from '@/vue/composables/AddonConditions'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const {
	shouldShowActivate,
	shouldShowLite,
	shouldShowMain,
	shouldShowUpdate
} = useAddonConditions({
	addonSlug : 'aioseo-local-business'
})

const strings = {
	importLocalSeoFromOtherPlugins : __('Import Local SEO From Other Plugins', td)
}
</script>