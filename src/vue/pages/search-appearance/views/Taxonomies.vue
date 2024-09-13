<template>
	<div class="aioseo-search-appearance-taxonomies">
		<core-card
			v-for="(taxonomy, index) in taxonomies"
			:key="index"
			:slug="`${taxonomy.name}SA`"
		>
			<template #header>
				<div
					class="icon dashicons"
					:class="getPostIconClass(taxonomy.icon)"
				/>

				{{ taxonomy.label }}

				<core-tooltip
					z-index="99999"
				>
					<svg-circle-question-mark />

					<template #tooltip>
						<div class="aioseo-description">
							{{ strings.label }} <strong>{{ taxonomy.label }}</strong><br>
							{{ strings.name }} <strong>{{ taxonomy.name }}</strong><br>
							{{ strings.postTypes }}<br>

							<ul>
								<template
									v-for="(postType, index) in taxonomy.postTypes"
									:key="index"
								>
									<li>
										<strong>{{ postType }}</strong>
									</li>
								</template>
							</ul>
						</div>
					</template>
				</core-tooltip>
			</template>

			<template
				#tabs
			>
				<core-main-tabs
					:tabs="tabs"
					:showSaveButton="false"
					:active="settingsStore.settings.internalTabs[`${taxonomy.name}SA`]"
					internal
					@changed="value => processChangeTab(taxonomy.name, value)"
				/>
			</template>

			<transition
				name="route-fade" mode="out-in"
			>
				<component
					:is="settingsStore.settings.internalTabs[`${taxonomy.name}SA`]"
					:object="taxonomy"
					:separator="optionsStore.options.searchAppearance.global.separator"
					:options="optionsStore.dynamicOptions.searchAppearance.taxonomies[taxonomy.name]"
					type="taxonomies"
					:show-bulk="false"
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
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import TitleDescription from './partials/TitleDescription'

import { __, sprintf } from '@/vue/plugins/translations'

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
		SvgCircleQuestionMark,
		TitleDescription
	},
	data () {
		return {
			internalDebounce : null,
			strings          : {
				label          : __('Label:', td),
				name           : __('Slug:', td),
				postTypes      : __('Post Types:', td),
				ctaButtonText  : __('Unlock Custom Taxonomies', td),
				ctaDescription : sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO"), 2 - "Pro".
					__('%1$s %2$s lets you set the SEO title and description for custom taxonomies. You can also control all of the robots meta and other options just like the default category and tags taxonomies.', td),
					import.meta.env.VITE_SHORT_NAME,
					'Pro'
				),
				ctaHeader : sprintf(
					// Translators: 1 - "PRO".
					__('Custom Taxonomy Support is a %1$s Feature', td),
					'PRO'
				)
			},
			tabs : [
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
			]
		}
	},
	computed : {
		taxonomies () {
			return this.rootStore.aioseo.postData.taxonomies
		}
	},
	methods : {
		processChangeTab (taxonomy, value) {
			if (this.internalDebounce) {
				return
			}

			this.internalDebounce = true
			this.settingsStore.changeTab({ slug: `${taxonomy}SA`, value })

			// Debouncing a little here to save extra API calls.
			setTimeout(() => {
				this.internalDebounce = false
			}, 50)
		}
	}
}
</script>

<style lang="scss">
.aioseo-search-appearance-taxonomies {

	.icon {
		display: flex;
		align-items: center;
		margin-right: 16px;
	}

	.aioseo-description {

		ul:not([role="listbox"]) {
			list-style: initial;
			list-style-position: inside;
			margin: 0;

			li {
				padding-left: 5px;
			}
		}
	}
}
</style>