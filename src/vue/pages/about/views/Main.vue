<template>
	<core-main
		:page-name="strings.pageName"
		:showSaveButton="false"
	>
		<component :is="$route.name" />
	</core-main>
</template>

<script>
import AboutUs from './AboutUs'
import CoreMain from '@/vue/components/common/core/main/Index'
import GettingStarted from './GettingStarted'
import LiteVsPro from './AIOSEO_VERSION/LiteVsPro'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		AboutUs,
		CoreMain,
		GettingStarted,
		LiteVsPro
	},
	data () {
		return {
			strings : {
				pageName : __('About Us', td)
			}
		}
	}
}
</script>