<template>
	<div class="aioseo-tab-content aioseo-post-social">
		<core-settings-row
			class="mobile-radio-buttons"
		>
			<template #content>
				<core-main-tabs
					:tabs="tabs"
					:showSaveButton="false"
					:active="initTab"
					internal
					@changed="value => processChangeTab(value)"
				/>
				<base-radio-toggle
					v-if="'metabox' === $root._data.screenContext || 'modal' === parentComponentContext"
					:modelValue="postEditorStore.currentPost.socialMobilePreview"
					@update:modelValue="isMobilePreviewEv"
					name="previewSocialIsMobile"
					class="circle"
					:options="[
						{ label: 'desktop', value: false, activeClass: 'dark', slot: 'desktop' },
						{ label: 'mobile', value: true, slot: 'mobile' }
					]"
				>
					<template #desktop>
						<svg-desktop />
					</template>
					<template #mobile>
						<svg-mobile />
					</template>
				</base-radio-toggle>
			</template>
		</core-settings-row>

		<transition name="route-fade" mode="out-in">
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
import SvgDesktop from '@/vue/components/common/svg/Desktop'
import SvgMobile from '@/vue/components/common/svg/Mobile'
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
		SocialSideBar,
		SvgDesktop,
		SvgMobile
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

			if (getParams()['social-tab']) {
				initTab = getParams()['social-tab']
			}
			if ('modal' === this.parentComponentContext) {
				initTab = this.settingsStore.metaBoxTabs.socialModal
			}

			return initTab
		}
	},
	mounted () {
		setTimeout(() => {
			if (getParams()['social-tab']) {
				removeParam('social-tab')
			}
		}, 500)
	},
	methods : {
		isMobilePreviewEv (ev) {
			this.postEditorStore.changeSocialPreview(ev)
		},
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

	.aioseo-col.col-md-9 {
		position: relative;
	}

	.mobile-radio-buttons {
		margin: 0;
		padding: 0;
		border: 0;

		.aioseo-radio-toggle {
			justify-content: flex-end;
			position: absolute;
			right: 20px;
			top: 12px;
			&> div {
				margin-left: 10px;
			}
		}

		.col-md-9 {
			padding-left: 4px;
			padding-right: 0;
		}
	}

	.ismobile {
		max-width: 375px;
	}

	.ismobilecard {
		.facebook-post,
		.twitter-post {
			max-width: 375px;
		}
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

.edit-post-sidebar {

	.tab-facebook,
	.tab-twitter,
	.mobile-radio-buttons {
		display: none;
	}
}

.aioseo-modal-content {

	.mobile-radio-buttons {
		padding-bottom: 0 !important;

		.aioseo-radio-toggle {
			right: 0 !important;
			top: 8px !important;

			@media screen and (max-width: 520px) {
				display: none;
			}
		}
	}

	.tab-facebook,
	.tab-twitter {
		display: block;

		> .aioseo-settings-row:first-of-type {
			border-bottom: 0;
			padding-bottom: 0;

			.aioseo-col:first-of-type {
				display: none;
			}
		}

		.aioseo-html-tags-editor {
			margin-top: 0;

			.aioseo-description {
				display: none;
			}
		}
	}
}
</style>