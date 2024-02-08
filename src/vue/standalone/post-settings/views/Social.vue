<template>
	<div class="aioseo-tab-content aioseo-post-social">
		<core-settings-row
			v-if="'metabox' === $root._data.screenContext || 'modal' === parentComponentContext"
			no-border
		>
			<template #content>
				<core-main-tabs
					:tabs="tabs"
					:showSaveButton="false"
					:active="initTab"
					internal
					@changed="value => processChangeTab(value)"
					disableMobile
				/>
			</template>
		</core-settings-row>

		<transition
			v-if="'metabox' === $root._data.screenContext || 'modal' === parentComponentContext"
			name="route-fade"
			mode="out-in"
		>
			<component :is="initTab" />
		</transition>
		<social-side-bar v-if="'modal' !== this.parentComponentContext" />
	</div>
</template>

<script>
import {
	usePostEditorStore,
	useSettingsStore
} from '@/vue/stores'

import { getParams, removeParam } from '@/vue/utils/params'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import Facebook from './Facebook'
import Twitter from './Twitter'
import SocialSideBar from './SocialSideBar'

export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore(),
			settingsStore   : useSettingsStore()
		}
	},
	components : {
		BaseRadioToggle,
		CoreMainTabs,
		CoreSettingsRow,
		Facebook,
		Twitter,
		SocialSideBar
	},
	props : {
		parentComponentContext : String
	},
	data () {
		return {
			strings : {
				pageName : this.$t.__('Social', this.$td)
			},
			tabs : [
				{
					slug : 'facebook',
					name : this.$t.__('Facebook', this.$td)
				},
				{
					slug : 'twitter',
					name : this.$t.__('Twitter', this.$td)
				}
			]
		}
	},
	computed : {
		initTab : function () {
			let initTab = this.settingsStore.metaBoxTabs.social
			if ('modal' === this.parentComponentContext) {
				initTab = this.settingsStore.metaBoxTabs.socialModal
			}

			return initTab
		}
	},
	mounted () {
		if (getParams()['social-tab']) {
			this.processChangeTab(getParams()['social-tab'])

			setTimeout(() => {
				removeParam('social-tab')
			}, 500)
		}
	},
	methods : {
		processChangeTab (newTabValue) {
			if ('modal' === this.parentComponentContext) {
				this.settingsStore.changeTabSettings({ setting: 'socialModal', value: newTabValue })
			} else {
				this.settingsStore.changeTabSettings({ setting: 'social', value: newTabValue })
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-post-social {

	.aioseo-row {
		&:has(.no-name) {
			row-gap: 0;
		}
	}

	.aioseo-col.col-md-9 {
		position: relative;
	}

	.aioseo-tabs {
		background: #fff!important;
		border: none!important;
		border-bottom: 2px solid $border !important;
	}

	svg.aioseo-pencil {
		width: 12px;
		height: 12px;
		color: $black2;
	}

	.aioseo-select .multiselect__tags {
		padding: 9px 40px 9px 16px;
	}
}

.aioseo-modal-content {

	.tab-facebook,
	.tab-twitter {

		.aioseo-html-tags-editor .aioseo-description {
			display: none;
		}
	}
}
</style>