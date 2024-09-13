<template>
	<div class="aioseo-wizard-features">
		<wizard-header />
		<wizard-container>
			<wizard-body>
				<wizard-steps />

				<div class="header">
					{{ strings.whichFeatures }}
				</div>

				<div class="description">
					{{ strings.description }}
				</div>

				<div
					v-for="(feature, index) in features"
					:key="index"
					class="feature-grid small-padding medium-margin"
				>
					<grid-row>
						<grid-column xs="11">
							<div class="settings-name">
								<div class="name small-margin">
									{{ feature.name }}
									<core-pro-badge v-if="needsUpsell(feature)" />
								</div>
								<div class="aioseo-description-text">
									{{ feature.description }}
								</div>
								<div
									v-if="feature.installs && getValue(feature)"
									class="aioseo-installs-text"
								>
									{{ feature.installs }}
								</div>
							</div>
						</grid-column>

						<grid-column xs="1">
							<base-checkbox
								round
								:class="{ 'no-clicks': feature.required }"
								:type="feature.required ? 'green' : 'blue'"
								:modelValue="feature.required ? true : getValue(feature)"
								@update:modelValue="checked => updateValue(checked, feature)"
								@click.native="event => preventUncheck(event, feature)"
							/>
						</grid-column>
					</grid-row>
				</div>

				<template #footer>
					<div class="go-back">
						<router-link :to="setupWizardStore.getPrevLink" class="no-underline">&larr;</router-link>
						&nbsp;
						<router-link :to="setupWizardStore.getPrevLink">{{ strings.goBack }}</router-link>
					</div>
					<div class="spacer"></div>
					<base-button
						type="blue"
						:loading="loading"
						@click="saveAndContinue"
					>{{ strings.saveAndContinue }} &rarr;</base-button>
				</template>
			</wizard-body>
			<div
				v-if="getPluginsText"
				class="plugins"
			>
				{{ getPluginsText }}
			</div>

			<wizard-close-and-exit />
		</wizard-container>
	</div>
</template>

<script>
import {
	useSetupWizardStore
} from '@/vue/stores'

import { merge } from 'lodash-es'

import { useWizard } from '@/vue/composables/Wizard'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import WizardBody from '@/vue/components/common/wizard/Body'
import WizardCloseAndExit from '@/vue/components/common/wizard/CloseAndExit'
import WizardContainer from '@/vue/components/common/wizard/Container'
import WizardHeader from '@/vue/components/common/wizard/Header'
import WizardSteps from '@/vue/components/common/wizard/Steps'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			features,
			needsUpsell,
			strings
		} = useWizard({
			stage : 'features'
		})

		return {
			composableStrings : strings,
			features,
			needsUpsell,
			setupWizardStore  : useSetupWizardStore()
		}
	},
	components : {
		BaseCheckbox,
		CoreProBadge,
		GridColumn,
		GridRow,
		WizardBody,
		WizardCloseAndExit,
		WizardContainer,
		WizardHeader,
		WizardSteps
	},
	data () {
		return {
			loading : false,
			strings : merge(this.composableStrings, {
				whichFeatures : __(
					'Which SEO features do you want to enable?',
					td
				),
				description : __(
					'We have already selected our recommended features based on your site category, but you can use the following features to fine-tune your site.',
					td
				)
			})
		}
	},
	computed : {
		showPluginsAll () {
			return (
				this.setupWizardStore.features.includes('analytics') ||
				this.setupWizardStore.features.includes('broken-link-checker') ||
				this.setupWizardStore.features.includes('conversion-tools')
			) &&
			(
				this.setupWizardStore.features.includes('aioseo-eeat') ||
				this.setupWizardStore.features.includes('aioseo-image-seo') ||
				this.setupWizardStore.features.includes('aioseo-index-now') ||
				this.setupWizardStore.features.includes('aioseo-link-assistant') ||
				this.setupWizardStore.features.includes('aioseo-local-business') ||
				this.setupWizardStore.features.includes('aioseo-news-sitemap') ||
				this.setupWizardStore.features.includes('aioseo-redirects') ||
				this.setupWizardStore.features.includes('aioseo-rest-api') ||
				this.setupWizardStore.features.includes('aioseo-video-sitemap')
			)
		},
		showPluginsAddons () {
			return (
				!this.setupWizardStore.features.includes('analytics') ||
				!this.setupWizardStore.features.includes('broken-link-checker') ||
				!this.setupWizardStore.features.includes('conversion-tools')
			) &&
			(
				this.setupWizardStore.features.includes('aioseo-eeat') ||
				this.setupWizardStore.features.includes('aioseo-image-seo') ||
				this.setupWizardStore.features.includes('aioseo-index-now') ||
				this.setupWizardStore.features.includes('aioseo-link-assistant') ||
				this.setupWizardStore.features.includes('aioseo-local-business') ||
				this.setupWizardStore.features.includes('aioseo-news-sitemap') ||
				this.setupWizardStore.features.includes('aioseo-redirects') ||
				this.setupWizardStore.features.includes('aioseo-rest-api') ||
				this.setupWizardStore.features.includes('aioseo-video-sitemap')
			)
		},
		showPluginsOnly () {
			return (
				this.setupWizardStore.features.includes('analytics') ||
				this.setupWizardStore.features.includes('broken-link-checker') ||
				this.setupWizardStore.features.includes('conversion-tools')
			) &&
			(
				!this.setupWizardStore.features.includes('aioseo-eeat') &&
				!this.setupWizardStore.features.includes('aioseo-image-seo') &&
				!this.setupWizardStore.features.includes('aioseo-index-now') &&
				!this.setupWizardStore.features.includes('aioseo-link-assistant') &&
				!this.setupWizardStore.features.includes('aioseo-local-business') &&
				!this.setupWizardStore.features.includes('aioseo-news-sitemap') &&
				!this.setupWizardStore.features.includes('aioseo-redirects') &&
				!this.setupWizardStore.features.includes('aioseo-rest-api') &&
				!this.setupWizardStore.features.includes('aioseo-video-sitemap')
			)
		},
		getPluginsText () {
			if (this.showPluginsOnly) {
				return sprintf(
					// Translators: 1 - A list of plugin names.
					__('The following plugins will be installed: %1$s', td),
					this.getPluginNames
				)
			}

			if (this.showPluginsAddons) {
				return sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO"), 2 - A list of plugin names.
					__('The following %1$s addons will be installed: %2$s', td),
					import.meta.env.VITE_SHORT_NAME,
					this.getPluginNames
				)
			}

			if (this.showPluginsAll) {
				return sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO"), 2 - A list of plugin names.
					__('The following plugins and %1$s addons will be installed: %2$s', td),
					import.meta.env.VITE_SHORT_NAME,
					this.getPluginNames
				)
			}

			return null
		},
		getPluginNames () {
			const pluginNames = []
			this.features.forEach(feature => {
				if (this.setupWizardStore.features.includes(feature.value) && feature.pluginName) {
					pluginNames.push(feature.pluginName)
				}
			})

			// Separate the last plugin with an "and" if there is more than one.
			let lastPlugin = ''
			if (1 < pluginNames.length) {
				lastPlugin = sprintf(
					// Translators: 1 - A plugin's name (e.g. "OptinMonster", "Broken Link Checker").
					__(' and %1$s', td),
					pluginNames[pluginNames.length - 1]
				)
			} else {
				lastPlugin = pluginNames[pluginNames.length - 1]
			}

			delete pluginNames[pluginNames.length - 1]

			// Implode the list but trim leading and trailing commas and spaces.
			const pluginNamesString = pluginNames.join(', ').replace(/(^[,\s]+)|([,\s]+$)/g, '')

			// Add the last plugin to the list.
			return pluginNamesString + lastPlugin
		}
	},
	methods : {
		preventUncheck (event, feature) {
			if (feature.required) {
				event.preventDefault()
				event.stopPropagation()
			}
		},
		getValue (feature) {
			return this.setupWizardStore.features.includes(feature.value)
		},
		updateValue (checked, feature) {
			const features = [ ...this.setupWizardStore.features ]

			if (checked) {
				features.push(feature.value)
				this.setupWizardStore.features = features
				return
			}

			const index = features.findIndex(f => f === feature.value)
			if (-1 !== index) {
				features.splice(index, 1)
			}

			this.setupWizardStore.features = features
		},
		saveAndContinue () {
			this.loading = true
			this.setupWizardStore.saveWizard('features')
				.then(() => {
					this.$router.push(this.setupWizardStore.getNextLink)
				})
		}
	}
}
</script>

<style lang="scss">
.aioseo-wizard-features {

	.feature-grid {
		border-bottom: 1px solid $border;
		padding-bottom: 16px;
		margin-bottom: 16px;

		&:last-child {
			border-bottom: none;
			padding-bottom: 0;
			margin-bottom: 0;
		}

		.settings-name {
			color: $black;

			.name {
				display: flex;
				align-items: center;
				font-weight: $font-bold;

				.aioseo-pro-badge {
					margin-left: 12px;
				}

				+ .aioseo-description-text {
					margin-top: 8px;
				}
			}

			.aioseo-description-text {
				max-width: 650px;
				color: $black2;
			}

			.aioseo-installs-text {
				font-style: italic;
				margin-top: 5px;
				font-size: 12px;
				color: $black2;
			}
		}
	}

	.plugins {
		font-style: italic;
		max-width: 650px;
		margin: 16px auto 0;
		font-size: 12px;
		text-align: center;
		color: $placeholder-color;
		line-height: 1.5;
	}

	.go-back {
		a {
			color: $black2;
			font-size: 14px;
		}
	}
}
</style>