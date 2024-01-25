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

import Advanced from './partials/Advanced'
import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import PostTypesMixin from '@/vue/mixins/PostTypes'
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
		SvgCircleQuestionMark,
		TitleDescription
	},
	mixins : [ PostTypesMixin ],
	data () {
		return {
			internalDebounce : null,
			strings          : {
				label          : this.$t.__('Label:', this.$td),
				name           : this.$t.__('Slug:', this.$td),
				postTypes      : this.$t.__('Post Types:', this.$td),
				ctaButtonText  : this.$t.__('Unlock Custom Taxonomies', this.$td),
				ctaDescription : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO"), 2 - "Pro".
					this.$t.__('%1$s %2$s lets you set the SEO title and description for custom taxonomies. You can also control all of the robots meta and other options just like the default category and tags taxonomies.', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					'Pro'
				),
				ctaHeader : this.$t.sprintf(
					// Translators: 1 - "PRO".
					this.$t.__('Custom Taxonomy Support is a %1$s Feature', this.$td),
					'PRO'
				)
			},
			tabs : [
				{
					slug   : 'title-description',
					name   : this.$t.__('Title & Description', this.$td),
					access : 'aioseo_search_appearance_settings',
					pro    : false
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