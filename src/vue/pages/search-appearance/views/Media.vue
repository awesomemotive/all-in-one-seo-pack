<template>
	<div class="aioseo-search-appearance-content-types">
		<core-card
			:slug="`${postType.name}SA`"
		>
			<template #header>
				<div
					class="icon dashicons"
					:class="getPostIconClass(postType.icon)"
				/>

				<div>{{ postType.label }}</div>
			</template>

			<template #before-tabs>
				<core-settings-row
					:name="strings.redirectAttachmentUrls"
					align
				>
					<template #content>
						<base-radio-toggle
							v-model="optionsStore.dynamicOptions.searchAppearance.postTypes.attachment.redirectAttachmentUrls"
							name="redirectAttachmentUrls"
							:options="[
								{ label: GLOBAL_STRINGS.disabled, value: 'disabled', activeClass: 'dark' },
								{ label: strings.attachment, value: 'attachment' },
								{ label: strings.attachmentParent, value: 'attachment_parent' }
							]"
						/>

						<div class="aioseo-description">
							{{ strings.attachmentUrlsDescription }}
						</div>
					</template>
				</core-settings-row>
			</template>

			<template
				#tabs
				v-if="'disabled' === optionsStore.dynamicOptions.searchAppearance.postTypes.attachment.redirectAttachmentUrls"
			>
				<core-main-tabs
					:tabs="tabs.attachments"
					:showSaveButton="false"
					:active="settingsStore.settings.internalTabs[`${postType.name}SA`]"
					internal
					@changed="value => processChangeTab(postType.name, value)"
				/>
			</template>

			<transition
				name="route-fade" mode="out-in"
				v-if="'disabled' === optionsStore.dynamicOptions.searchAppearance.postTypes.attachment.redirectAttachmentUrls"
			>
				<component
					:is="settingsStore.settings.internalTabs[`${postType.name}SA`]"
					:object="postType"
					:separator="optionsStore.options.searchAppearance.global.separator"
					:options="optionsStore.dynamicOptions.searchAppearance.postTypes[postType.name]"
					type="postTypes"
				/>
			</transition>
		</core-card>

		<core-card
			slug="imageSeo"
			:noSlide="!shouldShowMain"
		>
			<template #header>
				<span>{{ strings.imageSeo }}</span>
				<core-pro-badge />
			</template>

			<template #tabs>
				<core-main-tabs
					:tabs="tabs.imageSeo"
					:showSaveButton="false"
					:active="imageSeoActiveTab.slug"
					internal
					@changed="value => setImageSeoActiveTab(value)"
				/>
			</template>

			<image-seo
				v-if="shouldShowMain"
				:activeTab="imageSeoActiveTab"
				:key="imageSeoKey"
			/>

			<cta
				v-if="shouldShowUpdate || shouldShowActivate"
			/>

			<lite
				v-if="shouldShowLite"
			/>

		</core-card>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import {
	useOptionsStore,
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import { useAddonConditions } from '@/vue/composables/AddonConditions'
import { usePostTypes } from '@/vue/composables/PostTypes'

import Advanced from './partials/Advanced'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import Cta from './AIOSEO_VERSION/image-seo/Cta'
import CustomFields from './partials/AIOSEO_VERSION/CustomFields'
import ImageSeo from './AIOSEO_VERSION/image-seo/ImageSeo'
import Lite from './lite/image-seo/ImageSeo'
import Schema from './partials/Schema'
import TitleDescription from './partials/TitleDescription'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			shouldShowActivate,
			shouldShowLite,
			shouldShowMain,
			shouldShowUpdate
		} = useAddonConditions({
			addonSlug : 'aioseo-image-seo'
		})

		const {
			getPostIconClass
		} = usePostTypes()

		return {
			GLOBAL_STRINGS,
			getPostIconClass,
			optionsStore  : useOptionsStore(),
			rootStore     : useRootStore(),
			settingsStore : useSettingsStore(),
			shouldShowActivate,
			shouldShowLite,
			shouldShowMain,
			shouldShowUpdate
		}
	},
	components : {
		Advanced,
		BaseRadioToggle,
		CoreCard,
		CoreMainTabs,
		CoreProBadge,
		CoreSettingsRow,
		Cta,
		CustomFields,
		ImageSeo,
		Lite,
		Schema,
		TitleDescription
	},
	data () {
		return {
			imageSeoKey       : 0,
			internalDebounce  : false,
			imageSeoActiveTab : {
				slug : 'title',
				name : __('Title', td),
				pro  : true
			},
			strings : {
				redirectAttachmentUrls    : __('Redirect Attachment URLs', td),
				attachment                : __('Attachment', td),
				attachmentParent          : __('Attachment Parent', td),
				attachmentUrlsDescription : __('We recommended redirecting attachment URLs back to the attachment since the default WordPress attachment pages have little SEO value.', td),
				imageSeo                  : __('Image SEO', td),
				advancedSettings          : __('Advanced Settings', td)
			},
			tabs : {
				attachments : [
					{
						slug   : 'title-description',
						name   : __('Title & Description', td),
						access : 'aioseo_search_appearance_settings',
						pro    : false
					},
					{
						slug   : 'Schema',
						name   : __('Schema Markup', td),
						access : 'aioseo_search_appearance_settings',
						pro    : true
					},
					{
						slug   : 'advanced',
						name   : __('Advanced', td),
						access : 'aioseo_search_appearance_settings',
						pro    : false
					}
				],
				imageSeo : [
					{
						slug : 'title',
						name : __('Title', td),
						pro  : true
					},
					{
						slug : 'altTag',
						name : __('Alt Tag', td),
						pro  : true
					},
					{
						slug : 'caption',
						name : __('Caption', td),
						pro  : true
					},
					{
						slug : 'description',
						name : __('Description', td),
						pro  : true
					},
					{
						slug : 'filename',
						name : __('Filename', td),
						pro  : true
					}
				]
			}
		}
	},
	computed : {
		postType () {
			return this.rootStore.aioseo.postData.postTypes
				.filter(pt => 'attachment' === pt.name)[0]
		}
	},
	methods : {
		processChangeTab (postType, value) {
			if (this.internalDebounce) {
				return
			}

			this.internalDebounce = true
			this.settingsStore.changeTab({ slug: `${postType}SA`, value })

			// Debouncing a little here to save extra API calls.
			setTimeout(() => {
				this.internalDebounce = false
			}, 50)
		},
		setImageSeoActiveTab (tabSlug) {
			this.imageSeoActiveTab = this.tabs.imageSeo.find(tab => tab.slug === tabSlug)
			this.imageSeoKey++
		}
	}
}
</script>

<style lang="scss">
.aioseo-search-appearance-content-types {
	.icon {
		display: flex;
		align-items: center;
		margin-right: 16px;
	}

	.content:empty {
		display: none;
	}
}
</style>