<template>
	<div class="aioseo-search-appearance-archives">
		<core-card
			v-for="(archive, index) in getArchives"
			:key="index"
			:slug="`${archive.name}Archives`"
			:card-id="`${archive.name}Archives`"
		>
			<template #header>
				<div
					class="icon dashicons"
					:class="getPostIconClass(archive.icon)"
				/>
				<div>
					{{ archive.label }}
				</div>
			</template>

			<template #tabs>
				<core-main-tabs
					:tabs="tabs"
					:showSaveButton="false"
					:active="settingsStore.settings.internalTabs[`${archive.name}Archives`]"
					internal
					@changed="value => processChangeTab(archive.name, value)"
				/>
			</template>

			<transition name="route-fade" mode="out-in">
				<component
					:is="settingsStore.settings.internalTabs[`${archive.name}Archives`]"
					:object="archive"
					:separator="optionsStore.options.searchAppearance.global.separator"
					:options="getOptions(archive)"
					type="archives"
					:show-bulk="false"
					no-meta-box
					include-keywords
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
		TitleDescription
	},
	data () {
		return {
			internalDebounce : null,
			tabs             : [
				{
					slug   : 'title-description',
					name   : __('Title & Description', td),
					access : 'aioseo_search_appearance_settings',
					pro    : false
				},
				{
					slug   : 'advanced',
					name   : __('Advanced', td),
					access : 'aioseo_search_appearance_settings',
					pro    : false
				}
			],
			archives : [
				{
					label    : 'Author Archives',
					name     : 'author',
					singular : 'Author',
					icon     : 'dashicons-admin-users'
				},
				{
					label    : 'Date Archives',
					name     : 'date',
					singular : 'Date',
					icon     : 'dashicons-calendar-alt'
				},
				{
					label    : 'Search Page',
					name     : 'search',
					singular : 'Search Page',
					icon     : 'dashicons-search'
				}
			]
		}
	},
	computed : {
		getArchives () {
			return this.archives.concat(this.rootStore.aioseo.postData.archives.map(a => ({
				label    : `${a.label} Archives`,
				name     : `${a.name}Archive`,
				icon     : a?.icon || 'dashicons-category',
				singular : a.singular,
				dynamic  : true
			})))
		}
	},
	methods : {
		processChangeTab (archive, value) {
			if (this.internalDebounce) {
				return
			}

			this.internalDebounce = true
			this.settingsStore.changeTab({ slug: `${archive}Archives`, value })

			// Debouncing a little here to save extra API calls.
			setTimeout(() => {
				this.internalDebounce = false
			}, 50)
		},
		getOptions (archive) {
			if (archive.dynamic) {
				return this.optionsStore.dynamicOptions.searchAppearance.archives[archive.name.replace('Archive', '')]
			}

			return this.optionsStore.options.searchAppearance.archives[archive.name]
		}
	}
}
</script>

<style lang="scss">
.aioseo-search-appearance-archives {
	.icon {
		display: flex;
		align-items: center;
		margin-right: 16px;
	}
}
</style>