<template>
	<div class="aioseo-advanced">
		<core-card
			slug="advanced"
			:header-text="strings.advanced"
		>
			<core-settings-row
				:name="strings.truSeo"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.advanced.truSeo"
					/>

					<div class="aioseo-description">
						{{ strings.truSeoDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.headlineAnalyzer"
			>
				<template #content>
					<base-toggle v-model="optionsStore.options.advanced.headlineAnalyzer"/>

					<div class="aioseo-description">
						{{ strings.headlineAnalyzerDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.postTypeColumns"
			>
				<template #content>
					<base-checkbox
						size="medium"
						v-model="optionsStore.options.advanced.postTypes.all"
					>
						{{ strings.includeAllPostTypes }}
					</base-checkbox>

					<core-post-type-options
						v-if="!optionsStore.options.advanced.postTypes.all"
						:options="optionsStore.options.advanced"
						type="postTypes"
					/>

					<div class="aioseo-description">
						{{ strings.selectPostTypes }}

						<span
							v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'selectPostTypesColumns', true)"
						/>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row>
				<template #name>
					{{ strings.taxonomyColumns }}
					<core-pro-badge
						v-if="licenseStore.isUnlicensed"
					/>
				</template>
				<template #content>
					<base-checkbox
						v-if="licenseStore.isUnlicensed"
						disabled
						size="medium"
						:modelValue="true"
					>
						{{ strings.includeAllTaxonomies }}
					</base-checkbox>

					<base-checkbox
						v-if="!licenseStore.isUnlicensed"
						size="medium"
						v-model="optionsStore.options.advanced.taxonomies.all"
					>
						{{ strings.includeAllTaxonomies }}
					</base-checkbox>

					<core-post-type-options
						v-if="!optionsStore.options.advanced.taxonomies.all && !licenseStore.isUnlicensed"
						:options="optionsStore.options.advanced"
						type="taxonomies"
					/>

					<div class="aioseo-description">
						{{ strings.selectTaxonomies }}

						<span
							v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'selectTaxonomiesColumns', true)"
						/>
					</div>

					<core-alert
						class="inline-upsell"
						v-if="licenseStore.isUnlicensed"
						type="blue"
					>
						<div v-html="strings.taxonomyColumnsUpsell" />
					</core-alert>
				</template>
			</core-settings-row>

			<core-settings-row>
				<template #name>
					{{ strings.adminBarMenu }}
					<core-pro-badge
						v-if="licenseStore.isUnlicensed"
					/>
				</template>

				<template #content>
					<base-radio-toggle
						:disabled="licenseStore.isUnlicensed"
						v-model="adminBarMenu"
						name="adminBarMenu"
						:options="[
							{ label: GLOBAL_STRINGS.hide, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.show, value: true }
						]"
					/>

					<div class="aioseo-description">
						{{ strings.adminBarMenuDescription }}
					</div>

					<core-alert
						class="inline-upsell"
						v-if="licenseStore.isUnlicensed"
						type="blue"
					>
						<div v-html="strings.adminBarMenuUpsell" />
					</core-alert>
				</template>
			</core-settings-row>

			<core-settings-row>
				<template #name>
					{{ strings.dashboardWidgets }}
					<core-pro-badge
						v-if="licenseStore.isUnlicensed"
					/>
				</template>
				<template #content>
					<grid-row>
						<grid-column
							v-for="(widget, index) in widgets"
							:key="index"
						>
							<base-checkbox
								size="medium"
								:disabled="licenseStore.isUnlicensed"
								:modelValue="isDashboardWidgetChecked(widget)"
								@update:modelValue="value => updateDashboardWidgets(value, widget)"
							>
								{{ widget.label }}
								<core-tooltip>
									<svg-circle-question-mark />

									<template #tooltip>
										{{ widget.tooltip }}
									</template>
								</core-tooltip>
							</base-checkbox>
						</grid-column>
					</grid-row>

					<div class="aioseo-description">
						{{ strings.dashboardWidgetsDescription }}
					</div>

					<core-alert
						class="inline-upsell"
						v-if="licenseStore.isUnlicensed"
						type="blue"
					>
						<div v-html="strings.dashboardWidgetsUpsell" />
					</core-alert>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.emailSummaries"
				id="aioseo-email-summary-row"
			>
				<template #content>
					<email-summary />
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.announcements"
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.advanced.announcements"
						name="announcements"
						:options="[
							{ label: GLOBAL_STRINGS.hide, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.show, value: true }
						]"
					/>

					<div class="aioseo-description">
						{{ strings.announcementsDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="rootStore.isPro"
			>
				<template #name>
					{{ strings.automaticUpdates }}
				</template>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.advanced.autoUpdates"
						name="autoUpdates"
						:options="[
							{ label: strings.all, value: 'all' },
							{ label: strings.minor, value: 'minor' },
							{ label: strings.none, value: 'none', activeClass: 'dark' }
						]"
					/>

					<div class="aioseo-description">
						<span v-if="'all' === optionsStore.options.advanced.autoUpdates">{{ strings.allDescription }}</span>
						<span v-if="'minor' === optionsStore.options.advanced.autoUpdates">{{ strings.minorDescription }}</span>
						<span v-if="'none' === optionsStore.options.advanced.autoUpdates">{{ strings.noneDescription }}</span>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!rootStore.isPro"
			>
				<template #name>
					{{ strings.usageTracking }}
					<core-tooltip>
						<svg-circle-question-mark />

						<template #tooltip>
							<div v-html="strings.usageTrackingTooltip" />
						</template>
					</core-tooltip>
				</template>

				<template #content>
					<base-toggle
						v-model="optionsStore.options.advanced.usageTracking"
					/>

					<div class="aioseo-description">
						{{ strings.usageTrackingDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				id="aioseo-open-ai-api-key"
				:name="strings.openAiKey"
			>
				<template #name>
					{{ strings.openAiKey }}
					<core-pro-badge
						v-if="licenseStore.isUnlicensed"
					/>
				</template>

				<template #content>
					<base-input
						class="openAiKey"
						type="text"
						size="medium"
						v-model="optionsStore.options.advanced.openAiKey"
						:disabled="licenseStore.isUnlicensed"
						@blur="validateOpenAiKey"
					/>

					<div
						class="aioseo-description"
						v-html="strings.openAiKeyDescription"
					/>

					<core-alert
						class="inline-upsell"
						v-if="!licenseStore.isUnlicensed && optionsStore.options.advanced.openAiKey && openAiKeyInvalid"
						type="red"
					>
						<div>{{strings.openAiKeyInvalid}}</div>
					</core-alert>

					<core-alert
						class="inline-upsell"
						v-if="licenseStore.isUnlicensed"
						type="blue"
					>
						<div v-html="strings.openAiKeyUpsell" />
					</core-alert>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.uninstallAioseo"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.advanced.uninstall"
					/>

					<div class="aioseo-description">
						{{ strings.uninstallAioseoDescription }}
					</div>
				</template>
			</core-settings-row>
		</core-card>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { versionCompare } from '@/vue/utils/helpers'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CorePostTypeOptions from '@/vue/components/common/core/PostTypeOptions'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import EmailSummary from './partials/Advanced/EmailSummary'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			licenseStore : useLicenseStore(),
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore(),
			GLOBAL_STRINGS,
			links
		}
	},
	components : {
		BaseCheckbox,
		BaseRadioToggle,
		CoreAlert,
		CoreCard,
		CorePostTypeOptions,
		CoreProBadge,
		CoreSettingsRow,
		CoreTooltip,
		EmailSummary,
		GridColumn,
		GridRow,
		SvgCircleQuestionMark
	},
	data () {
		return {
			openAiKeyInvalid : false,
			strings          : {
				advanced                    : __('Advanced Settings', td),
				truSeo                      : __('TruSEO Score & Content', td),
				truSeoDescription           : __('Enable our TruSEO score to help you optimize your content for maximum traffic.', td),
				headlineAnalyzer            : __('Headline Analyzer', td),
				headlineAnalyzerDescription : __('Enable our Headline Analyzer to help you write irresistible headlines and rank better in search results.', td),
				seoAnalysis                 : __('SEO Analysis', td),
				postTypeColumns             : __('Post Type Columns', td),
				includeAllPostTypes         : __('Include All Post Types', td),
				selectPostTypes             : sprintf(
					// Translators: 1 - Plugin Short Name ("AIOSEO").
					__('Select which Post Types you want to use the %1$s columns with.', td),
					import.meta.env.VITE_SHORT_NAME
				),
				usageTracking           : __('Usage Tracking', td),
				adminBarMenu            : __('Admin Bar Menu', td),
				adminBarMenuDescription : sprintf(
					// Translators: 1 - Plugin Short Name ("AIOSEO").
					__('This adds %1$s to the admin toolbar for easy access to your SEO settings.', td),
					import.meta.env.VITE_SHORT_NAME
				),
				dashboardWidgets            : __('Dashboard Widgets', td),
				dashboardWidgetsDescription : sprintf(
					// Translators: 1 - Plugin Short Name ("AIOSEO").
					__('Select which %1$s widgets to display on the dashboard.', td),
					import.meta.env.VITE_SHORT_NAME
				),
				announcements            : __('Announcements', td),
				announcementsDescription : __('This allows you to hide plugin announcements and update details in the Notification Center.', td),
				automaticUpdates         : __('Automatic Updates', td),
				all                      : __('All (recommended)', td),
				allDescription           : __('You are getting the latest features, bugfixes, and security updates as they are released.', td),
				minor                    : __('Minor Only', td),
				minorDescription         : __('You are getting bugfixes and security updates, but not major features.', td),
				none                     : __('None', td),
				noneDescription          : __('You will need to manually update everything.', td),
				usageTrackingDescription : __('By allowing us to track usage data we can better help you as we will know which WordPress configurations, themes and plugins we should test.', td),
				usageTrackingTooltip     : sprintf(
					// Translators: 1 - Opening HTML link and bold tag, 2 - Closing HTML link and bold tag.
					__('Complete documentation on usage tracking is available %1$shere%2$s.', td),
					sprintf(
						'<strong><a href="%1$s" target="_blank">',
						links.getDocUrl('usageTracking')
					),
					'</a></strong>'
				),
				adminBarMenuUpsell : sprintf(
					// Translators: 1 - "PRO", 2 - "Learn more".
					__('Admin Bar Menu is a %1$s feature. %2$s', td),
					'PRO',
					links.getUpsellLink('general-settings-advanced', GLOBAL_STRINGS.learnMore, 'admin-bar-menu', true)
				),
				dashboardWidgetsUpsell : sprintf(
					// Translators: 1 - "PRO", 2 - "Learn more".
					__('Dashboard Widgets is a %1$s feature. %2$s', td),
					'PRO',
					links.getUpsellLink('general-settings-advanced', GLOBAL_STRINGS.learnMore, 'dashboard-widget', true)
				),
				taxonomyColumns      : __('Taxonomy Columns', td),
				includeAllTaxonomies : __('Include All Taxonomies', td),
				selectTaxonomies     : sprintf(
					// Translators: 1 - Plugin Short Name ("AIOSEO").
					__('Select which Taxonomies you want to use the %1$s columns with.', td),
					import.meta.env.VITE_SHORT_NAME
				),
				taxonomyColumnsUpsell : sprintf(
					// Translators: 1 - "PRO", 2 - "Learn more".
					__('Taxonomy Columns is a %1$s feature. %2$s', td),
					'PRO',
					links.getUpsellLink('general-settings-advanced', GLOBAL_STRINGS.learnMore, 'taxonomy-columns', true)
				),
				uninstallAioseo : sprintf(
					// Translators: 1 - Plugin Short Name ("AIOSEO").
					__('Uninstall %1$s', td),
					import.meta.env.VITE_SHORT_NAME
				),
				uninstallAioseoDescription : sprintf(
					// Translators: 1 - Plugin Short Name ("AIOSEO").
					__('Check this if you would like to remove ALL %1$s data upon plugin deletion. All settings and SEO data will be unrecoverable.', td),
					import.meta.env.VITE_SHORT_NAME
				),
				openAiKey            : __('OpenAI API Key', td),
				openAiKeyDescription : sprintf(
					// Translators: 1 - "Learn More" link.
					__('Enter an OpenAI API key in order to automatically generate SEO titles and meta descriptions for your pages. %1$s', td),
					links.getDocLink(GLOBAL_STRINGS.learnMore, 'openAi', true)
				),
				openAiKeyUpsell : sprintf(
					// Translators: 1 - "PRO", 2 - "Learn more".
					__('OpenAI Integration is a %1$s feature. %2$s', td),
					'PRO',
					links.getUpsellLink('general-settings-advanced', GLOBAL_STRINGS.learnMore, 'open-ai', true)
				),
				openAiKeyInvalid : __('The API key you have entered is invalid. Please check your API key and try again.', td),
				emailSummaries   : __('Email Reports', td)
			}
		}
	},
	computed : {
		adminBarMenu : {
			get () {
				return !this.licenseStore.isUnlicensed ? this.optionsStore.options.advanced.adminBarMenu : true
			},
			set (newValue) {
				this.optionsStore.options.advanced.adminBarMenu = newValue
			}
		},
		widgets () {
			return [
				{
					key     : 'seoSetup',
					label   : __('SEO Setup Wizard', td),
					tooltip : __('Our SEO Setup Wizard dashboard widget helps you remember to finish setting up some initial crucial settings for your site to help you rank higher in search results. Once the setup wizard is completed this widget will automatically disappear.', td)
				},
				{
					key     : 'seoOverview',
					label   : __('SEO Overview', td),
					tooltip : __('Our SEO Overview widget helps you determine which posts or pages you should focus on for content updates to help you rank higher in search results.', td)
				},
				{
					key     : 'seoNews',
					label   : __('SEO News', td),
					tooltip : __('Our SEO News widget provides helpful links that enable you to get the most out of your SEO and help you continue to rank higher than your competitors in search results.', td)
				}
			]
		}
	},
	methods : {
		versionCompare : versionCompare,
		updateDashboardWidgets (checked, widget) {
			if (checked) {
				const included = this.optionsStore.options.advanced.dashboardWidgets
				included.push(widget.key)
				this.optionsStore.options.advanced.dashboardWidgets = included
				return
			}

			const index = this.optionsStore.options.advanced.dashboardWidgets.findIndex(t => t === widget.key)
			if (-1 !== index) {
				this.optionsStore.options.advanced.dashboardWidgets.splice(index, 1)
			}
		},
		isDashboardWidgetChecked (widget) {
			return !this.licenseStore.isUnlicensed ? this.optionsStore.options.advanced.dashboardWidgets.includes(widget.key) : true
		},
		validateOpenAiKey () {
			if (this.optionsStore.options.advanced.openAiKey && null === this.optionsStore.options.advanced.openAiKey.match(/^sk-.*/)) {
				this.openAiKeyInvalid = true
			} else {
				this.openAiKeyInvalid = false
			}
		}
	},
	beforeMount () {
		this.validateOpenAiKey()
	}
}
</script>

<style lang="scss">
.aioseo-advanced {
	.inline-upsell,
	.warning {
		display: inline-flex;
		margin-top: 12px;
	}

	.aioseo-input-container {
		max-width: 500px;
	}
}
</style>