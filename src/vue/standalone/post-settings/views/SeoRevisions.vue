<template>
	<div class="aioseo-seo-revisions aioseo-tab-content">
		<seo-revisions
			v-if="!licenseStore.isUnlicensed && license.hasCoreFeature('seo-revisions')"
			:parentComponentContext="parentComponentContext"
		/>

		<seo-revisions-lite
			v-else
			:parentComponentContext="parentComponentContext"
		/>
	</div>
</template>

<script>
import {
	useLicenseStore
} from '@/vue/stores'

import license from '@/vue/utils/license'
import SeoRevisions from './AIOSEO_VERSION/SeoRevisions'
import SeoRevisionsLite from './lite/SeoRevisions'

export default {
	setup () {
		return {
			licenseStore : useLicenseStore()
		}
	},
	data () {
		return {
			license
		}
	},
	components : {
		SeoRevisions,
		SeoRevisionsLite
	},
	props : {
		parentComponentContext : String
	}
}
</script>

<style lang="scss" scoped>
.aioseo-seo-revisions {
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Helvetica Neue", sans-serif;
	letter-spacing: normal;

	#aioseo-post-settings-sidebar & {
		padding: 12px;
	}

	:deep() {
		.text-truncate {
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}
	}
}
</style>