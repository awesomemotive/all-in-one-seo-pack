<template>
	<core-main
		:page-name="strings.pageName"
	>
		<component :is="$route.name" />
	</core-main>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import AccessControl from './AccessControl'
import Advanced from './Advanced'
import Breadcrumbs from './Breadcrumbs'
import CoreMain from '@/vue/components/common/core/main/Index'
import GeneralSettings from './GeneralSettings'
import RssContent from './RssContent'
import WebmasterTools from './WebmasterTools'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		AccessControl,
		Advanced,
		Breadcrumbs,
		CoreMain,
		GeneralSettings,
		RssContent,
		WebmasterTools
	},
	data () {
		return {
			strings : {
				pageName : this.rootStore.aioseo.data.isNetworkAdmin
					? __('Network Settings', td)
					: __('General Settings', td)
			}
		}
	}
}
</script>