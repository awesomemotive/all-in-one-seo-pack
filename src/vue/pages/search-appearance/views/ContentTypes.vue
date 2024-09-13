<template>
	<div class="aioseo-search-appearance-content-types">
		<core-card
			v-for="(postType, index) in postTypes"
			:key="index"
			:slug="`${postType.name}SA`"
		>
			<template #header>
				<div
					class="icon dashicons"
					:class="getPostIconClass(postType.icon)"
				/>

				{{ postType.label }}

				<core-tooltip
					z-index="99999"
				>
					<svg-circle-question-mark />

					<template #tooltip>
						<div class="aioseo-description">
							{{ strings.label }} <strong>{{ postType.label }}</strong><br>
							{{ strings.name }} <strong>{{ postType.name }}</strong><br>
						</div>
					</template>
				</core-tooltip>
			</template>

			<template #tabs>
				<core-main-tabs
					:tabs="tabs"
					:showSaveButton="false"
					:active="settingsStore.settings.internalTabs[`${postType.name}SA`]"
					internal
					@changed="value => processChangeTab(postType.name, value)"
				/>
			</template>

			<transition name="route-fade" mode="out-in">
				<component
					:is="settingsStore.settings.internalTabs[`${postType.name}SA`]"
					:object="postType"
					:separator="optionsStore.options.searchAppearance.global.separator"
					:options="optionsStore.dynamicOptions.searchAppearance.postTypes[postType.name]"
					type="postTypes"
				/>
			</transition>
		</core-card>
	</div>
</template>

<script>
import {
	useOptionsStore,
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import { usePostTypes } from '@/vue/composables/PostTypes'

import Advanced from './partials/Advanced'
import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CustomFields from './partials/CustomFields'
import Schema from './partials/Schema'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import TitleDescription from './partials/TitleDescription'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			getPostIconClass
		} = usePostTypes()

		return {
			getPostIconClass,
			optionsStore  : useOptionsStore(),
			rootStore     : useRootStore(),
			settingsStore : useSettingsStore()
		}
	},
	components : {
		Advanced,
		CoreCard,
		CoreMainTabs,
		CoreTooltip,
		CustomFields,
		Schema,
		SvgCircleQuestionMark,
		TitleDescription
	},
	data () {
		return {
			internalDebounce : null,
			strings          : {
				label : __('Label:', td),
				name  : __('Slug:', td)
			},
			tabs : [
				{
					slug   : 'title-description',
					name   : __('Title & Description', td),
					access : 'aioseo_search_appearance_settings',
					pro    : false
				},
				{
					slug   : 'schema',
					name   : __('Schema Markup', td),
					access : 'aioseo_search_appearance_settings',
					pro    : true
				},
				{
					slug   : 'custom-fields',
					name   : __('Custom Fields', td),
					access : 'aioseo_search_appearance_settings',
					pro    : true
				},
				{
					slug   : 'advanced',
					name   : __('Advanced', td),
					access : 'aioseo_search_appearance_settings',
					pro    : false
				}
			]
		}
	},
	computed : {
		postTypes () {
			return this.rootStore.aioseo.postData.postTypes
				.filter(pt => 'attachment' !== pt.name)
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
}
</style>