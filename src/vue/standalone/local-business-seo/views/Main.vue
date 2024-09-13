<template>
	<div class="aioseo-app aioseo-post-settings">
		<core-main-tabs
			:tabs="tabs"
			:showSaveButton="false"
			:active="tab"
			internal
			disableMobile
			@changed="value => processChangeTab(value)"
		/>
		<transition name="route-fade" mode="out-in">
			<component
				:is="tab"
			/>
		</transition>
	</div>
</template>

<script>
import {
	usePostEditorStore
} from '@/vue/stores'

import { debounce } from '@/vue/utils/debounce'
import BusinessInfo from './BusinessInfo'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import OpeningHours from './OpeningHours'
import Maps from './Maps'
import SvgSettings from '@/vue/components/common/svg/Settings'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore()
		}
	},
	components : {
		BusinessInfo,
		CoreMainTabs,
		OpeningHours,
		Maps,
		SvgSettings
	},
	data () {
		return {
			tab  : 'business-info',
			tabs : [
				{
					slug : 'business-info',
					icon : 'svg-settings',
					name : __('Business Info', td)
				},
				{
					slug : 'opening-hours',
					icon : 'svg-settings',
					name : __('Opening Hours', td)
				},
				{
					slug : 'maps',
					icon : 'svg-settings',
					name : __('Maps', td)
				}
			]
		}
	},
	watch : {
		'postEditorStore.currentPost' : {
			deep : true,
			handler () {
				debounce(this.postEditorStore.savePostState, 250)
			}
		}
	},
	methods : {
		processChangeTab (newTabValue) {
			this.tab = newTabValue
		}
	}
}
</script>