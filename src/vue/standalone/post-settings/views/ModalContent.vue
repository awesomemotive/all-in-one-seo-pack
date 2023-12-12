<template>
	<div class="aioseo-modal-content">
		<div class="mobile-radio-buttons">
			<core-main-tabs
				:tabs="getTabs"
				:showSaveButton="false"
				:active="settingsStore.metaBoxTabs.modal || 'general'"
				internal
				@changed="value => processChangeTab(value)"
			/>
		</div>

		<div class="component-wrapper">
			<transition name="route-fade" mode="out-in">
				<component
					:is="settingsStore.metaBoxTabs.modal || 'general'"
					parentComponentContext="modal"
				/>
			</transition>
		</div>
	</div>
</template>

<script>
import {
	useSettingsStore
} from '@/vue/stores'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'

import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import General from './General'
import Social from './Social'
import SvgSettings from '@/vue/components/common/svg/Settings'
import SvgShare from '@/vue/components/common/svg/Share'
export default {
	setup () {
		return {
			settingsStore : useSettingsStore()
		}
	},
	components : {
		CoreMainTabs,
		CoreSettingsRow,
		General,
		Social,
		SvgSettings,
		SvgShare
	},
	data () {
		return {
			strings : {
				pageName : this.$t.__('Modal Content', this.$td)
			},
			tabs : [
				{
					slug : 'general',
					icon : 'svg-settings',
					name : this.$t.__('General', this.$td)
				},
				{
					slug : 'social',
					icon : 'svg-share',
					name : this.$t.__('Social', this.$td)
				}
			]
		}
	},
	computed : {
		getTabs () {
			return this.tabs.filter(tab => allowed(`aioseo_page_${tab.slug}_settings`))
		}
	},
	methods : {
		processChangeTab (newTabValue) {
			this.settingsStore.changeTabSettings({ setting: 'modal', value: newTabValue })
		}
	}
}
</script>
<style lang="scss">
.aioseo-modal-content {
	.mobile-radio-buttons {
		display: block!important;

		> .col-md-3 {
			display: none;
		}

		> .col-md-9 {
			padding: 0;
			flex-basis: 100%!important;
			max-width: 100%!important;
		}
	}

	.aioseo-settings-row {
		> .aioseo-col.col-md-3 {
			padding-bottom: 0;
		}

		> .aioseo-col.col-md-3,
		> .aioseo-col.col-md-9 {
			flex-basis: 100%;
			max-width: 100%;
		}
	}

	> .aioseo-settings-row {
		.aioseo-tabs {
			&.internal {
				padding-left: 40px;
			}

			.var-tab {
				display: flex;
				align-items: center;

				svg {
					display: inline;
					width: 16px;
					height: 16px;
				}

				&:before {
					height: auto!important;
					border-radius: 0!important;
				}

				.label {
					display: inline!important;
					margin-left: 10px;
				}
			}
		}
	}

	> .aioseo-settings-row.mobile-radio-buttons {
		.aioseo-tabs {
			--tabs-item-horizontal-height: 46px;
		}
	}

	.aioseo-tab-content {
		position: relative;
		padding: 16px;
	}
}
</style>