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

import Advanced from './partials/Advanced'
import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CustomFields from './partials/CustomFields'
import PostTypesMixin from '@/vue/mixins/PostTypes'
import Schema from './partials/Schema'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import TitleDescription from './partials/TitleDescription'
export default {
	setup () {
		return {
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
	mixins : [ PostTypesMixin ],
	data () {
		return {
			internalDebounce : null,
			strings          : {
				label : this.$t.__('Label:', this.$td),
				name  : this.$t.__('Slug:', this.$td)
			},
			tabs : [
				{
					slug   : 'title-description',
					name   : this.$t.__('Title & Description', this.$td),
					access : 'aioseo_search_appearance_settings',
					pro    : false
				},
				{
					slug   : 'schema',
					name   : this.$t.__('Schema Markup', this.$td),
					access : 'aioseo_search_appearance_settings',
					pro    : true
				},
				{
					slug   : 'custom-fields',
					name   : this.$t.__('Custom Fields', this.$td),
					access : 'aioseo_search_appearance_settings',
					pro    : true
				},
				{
					slug   : 'advanced',
					name   : this.$t.__('Advanced', this.$td),
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
		},
		getPostIconClass (icon) {
			const defaultIcon = 'dashicons-admin-post'

			// If the icon's name starts with 'dashicons-awb-', then it's a custom icon for Avada that
			// we are not able to import.
			if (icon?.startsWith('dashicons-awb-')) {
				return defaultIcon
			}

			return icon || defaultIcon
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