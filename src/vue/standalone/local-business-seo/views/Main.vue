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
} from '@/vue/stores/'

import { debounce } from '@/vue/utils/debounce'
import BusinessInfo from './BusinessInfo'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import OpeningHours from './OpeningHours'
import Maps from './Maps'
import SvgSettings from '@/vue/components/common/svg/Settings'

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
	watch : {
		'postEditorStore.currentPost' : {
			deep : true,
			handler () {
				debounce(this.postEditorStore.savePostState, 250)
			}
		}
	},
	data () {
		return {
			tab  : 'business-info',
			tabs : [
				{
					slug : 'business-info',
					icon : 'svg-settings',
					name : this.$t.__('Business Info', this.$td)
				},
				{
					slug : 'opening-hours',
					icon : 'svg-settings',
					name : this.$t.__('Opening Hours', this.$td)
				},
				{
					slug : 'maps',
					icon : 'svg-settings',
					name : this.$t.__('Maps', this.$td)
				}
			]
		}
	},
	methods : {
		processChangeTab (newTabValue) {
			this.tab = newTabValue
		}
	}
}
</script>