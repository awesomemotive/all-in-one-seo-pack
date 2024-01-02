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

<script>
import Import from './AIOSEO_VERSION/import/Import'
import Lite from './lite/import/Import'
import Cta from './AIOSEO_VERSION/partials/Cta'
import { AddonConditions } from '@/vue/mixins/AddonConditions'
export default {
	mixins     : [ AddonConditions ],
	components : {
		Import,
		Lite,
		Cta
	},
	data () {
		return {
			addonSlug : 'aioseo-local-business',
			strings   : {
				importLocalSeoFromOtherPlugins : this.$t.__('Import Local SEO From Other Plugins', this.$td)
			}
		}
	}
}
</script>