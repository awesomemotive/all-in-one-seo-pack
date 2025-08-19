<template>
	<div class="aioseo-seo-analysis-settings">
		<core-card
			slug="settingsTab"
			hide-header
			no-slide
			:toggles="false"
			:cardClass="{
				'aioseo-card--simple': true,
				'aioseo-card--no-padding': true
			}"
		>
			<div class="aioseo-settings-row">
				{{ strings.headerDescription }}

				<span
					v-html="links.getDocLink(globalStrings.learnMore, 'seoAnalyzer', true)"
				/>
			</div>

			<core-settings-row
				:name="strings.postTypes"
			>
				<template #content>
					<base-checkbox
						size="medium"
						v-model="optionsStore.dynamicOptions.seoAnalysis.postTypes.all"
					>
						{{ strings.includeAllPostTypes }}
					</base-checkbox>

					<core-post-type-options
						v-if="!optionsStore.dynamicOptions.seoAnalysis.postTypes.all"
						:options="optionsStore.dynamicOptions.seoAnalysis"
						:excluded="[ 'attachment' ]"
						type="postTypes"
					/>

					<div class="aioseo-description">
						{{ strings.selectPostTypes }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.postStatuses"
			>
				<template #content>
					<base-checkbox
						size="medium"
						v-model="optionsStore.dynamicOptions.seoAnalysis.postStatuses.all"
					>
						{{ strings.includeAllPostStatuses }}
					</base-checkbox>

					<core-post-status-options
						v-if="!optionsStore.dynamicOptions.seoAnalysis.postStatuses.all"
						:options="optionsStore.dynamicOptions.seoAnalysis"
						type="postStatuses"
					/>

					<div class="aioseo-description">
						{{ strings.selectPostStatuses }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.taxonomies"
			>
				<template #content>
					<base-checkbox
						size="medium"
						v-model="optionsStore.dynamicOptions.seoAnalysis.taxonomies.all"
					>
						{{ strings.includeAllTaxonomies }}
					</base-checkbox>

					<core-post-type-options
						v-if="!optionsStore.dynamicOptions.seoAnalysis.taxonomies.all"
						:options="optionsStore.dynamicOptions.seoAnalysis"
						:excluded="[ 'product_attributes' ]"
						type="taxonomies"
					/>

					<div class="aioseo-description">
						{{ strings.selectTaxonomies }}
					</div>
				</template>
			</core-settings-row>
		</core-card>
	</div>
</template>

<script setup>
import {
	useOptionsStore
} from '@/vue/stores'

import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import CoreCard from '@/vue/components/common/core/Card'
import CorePostStatusOptions from '@/vue/components/common/core/PostStatusOptions'
import CorePostTypeOptions from '@/vue/components/common/core/PostTypeOptions'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const globalStrings = GLOBAL_STRINGS
const optionsStore  = useOptionsStore()
const strings = {
	headerDescription      : __('Customize your SEO Audit by selecting which Post Types, Post Statuses or Taxonomies you want to include. You can also individually exclude posts, page or taxonomies for complete control. Your homepage will always be included in the audit regardless of which settings are enabled or disabled below.', td),
	postTypes              : __('Post Types', td),
	includeAllPostTypes    : __('Include All Post Types', td),
	selectPostTypes        : __('Select which Post Types appear in your audit.', td),
	postStatuses           : __('Post Statuses', td),
	includeAllPostStatuses : __('Include All Post Statuses', td),
	selectPostStatuses     : __('Select which Post Statuses appear in your audit.', td),
	taxonomies             : __('Taxonomies', td),
	includeAllTaxonomies   : __('Include All Taxonomies', td),
	selectTaxonomies       : __('Select which Taxonomies appear in your audit.', td)
}
</script>

<style lang="scss">
.aioseo-seo-analysis-settings {
	padding-top: 20px;

	.aioseo-card.aioseo-card {
		&--simple {
			box-shadow: none;
			border: none;
			margin: 0;
		}

		&--no-padding {
			.content {
				padding: 0;
			}
		}
	}

	.aioseo-highlight-toggle.active {
		border-color: #E8E8EB;
		box-shadow: none;
	}
}
</style>