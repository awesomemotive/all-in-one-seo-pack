<template>
	<core-main
		:page-name="strings.pageName"
		:show-tabs="false"
		:show-save-button="false"
	>
		<feature-manager />
	</core-main>
</template>

<script>
import CoreMain from '@/vue/components/common/core/main/Index'
import FeatureManager from './FeatureManager'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreMain,
		FeatureManager
	},
	data () {
		return {
			strings : {
				pageName : __('Feature Manager', td)
			}
		}
	}
}
</script>