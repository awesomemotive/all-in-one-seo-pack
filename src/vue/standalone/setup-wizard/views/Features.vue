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
					v-for="(feature, index) in getFeatures"
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
import { useWizard } from '@/vue/composables'
import { Wizard } from '@/vue/mixins/Wizard'
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import WizardBody from '@/vue/components/common/wizard/Body'
import WizardCloseAndExit from '@/vue/components/common/wizard/CloseAndExit'
import WizardContainer from '@/vue/components/common/wizard/Container'
import WizardHeader from '@/vue/components/common/wizard/Header'
import WizardSteps from '@/vue/components/common/wizard/Steps'
export default {
	setup () {
		const { strings } = useWizard()

		return {
			setupWizardStore  : useSetupWizardStore(),
			composableStrings : strings
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
	mixins : [ Wizard ],
	data () {
		return {
			loading : false,
			stage   : 'features',
			strings : merge(this.composableStrings, {
				whichFeatures : this.$t.__(
					'Which SEO features do you want to enable?',
					this.$td
				),
				description : this.$t.__(
					'We have already selected our recommended features based on your site category, but you can use the following features to fine-tune your site.',
					this.$td
				)
			})
		}
	},
	computed : {
		showPluginsAll () {
			return (
				this.setupWizardStore.features.includes('analytics') ||
					this.setupWizardStore.features.includes('conversion-tools')
			) &&
				(
					this.setupWizardStore.features.includes('image-seo') ||
					this.setupWizardStore.features.includes('news-sitemap') ||
					this.setupWizardStore.features.includes('video-sitemap') ||
					this.setupWizardStore.features.includes('local-seo') ||
					this.setupWizardStore.features.includes('redirects') ||
					this.setupWizardStore.features.includes('index-now') ||
					this.setupWizardStore.features.includes('link-assistant') ||
					this.setupWizardStore.features.includes('rest-api')
				)
		},
		showPluginsAddons () {
			return (
				!this.setupWizardStore.features.includes('analytics') ||
				!this.setupWizardStore.features.includes('conversion-tools')
			) &&
				(
					this.setupWizardStore.features.includes('image-seo') ||
					this.setupWizardStore.features.includes('news-sitemap') ||
					this.setupWizardStore.features.includes('video-sitemap') ||
					this.setupWizardStore.features.includes('local-seo') ||
					this.setupWizardStore.features.includes('redirects') ||
					this.setupWizardStore.features.includes('index-now') ||
					this.setupWizardStore.features.includes('link-assistant') ||
					this.setupWizardStore.features.includes('rest-api')
				)
		},
		showPluginsOnly () {
			return (
				this.setupWizardStore.features.includes('analytics') ||
				this.setupWizardStore.features.includes('conversion-tools')
			) &&
				!this.setupWizardStore.features.includes('image-seo') &&
				!this.setupWizardStore.features.includes('news-sitemap') &&
				!this.setupWizardStore.features.includes('video-sitemap') &&
				!this.setupWizardStore.features.includes('local-seo') &&
				!this.setupWizardStore.features.includes('redirects') &&
				!this.setupWizardStore.features.includes('index-now') &&
				!this.setupWizardStore.features.includes('link-assistant') &&
				!this.setupWizardStore.features.includes('rest-api')
		},
		getPluginsText () {
			if (this.showPluginsOnly) {
				return this.$t.sprintf(
					// Translators: 1 - A list of plugin names.
					this.$t.__('The following plugins will be installed: %1$s', this.$td),
					this.getPluginNames
				)
			}

			if (this.showPluginsAddons) {
				return this.$t.sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO"), 2 - A list of plugin names.
					this.$t.__('The following %1$s addons will be installed: %2$s', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					this.getPluginNames
				)
			}

			if (this.showPluginsAll) {
				return this.$t.sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO"), 2 - A list of plugin names.
					this.$t.__('The following plugins and %1$s addons will be installed: %2$s', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					this.getPluginNames
				)
			}

			return null
		},
		getPluginNames () {
			const pluginNames = []
			if (this.setupWizardStore.features.includes('analytics')) {
				pluginNames.push('MonsterInsights Free')
			}

			if (this.setupWizardStore.features.includes('conversion-tools')) {
				pluginNames.push('OptinMonster')
			}

			if (this.setupWizardStore.features.includes('image-seo')) {
				pluginNames.push('Image SEO')
			}

			if (this.setupWizardStore.features.includes('local-seo')) {
				pluginNames.push('Local Business SEO')
			}

			if (this.setupWizardStore.features.includes('video-sitemap')) {
				pluginNames.push('Video Sitemap')
			}

			if (this.setupWizardStore.features.includes('news-sitemap')) {
				pluginNames.push('News Sitemap')
			}

			if (this.setupWizardStore.features.includes('redirects')) {
				pluginNames.push('Redirects')
			}

			if (this.setupWizardStore.features.includes('index-now')) {
				pluginNames.push('Index Now')
			}

			if (this.setupWizardStore.features.includes('link-assistant')) {
				pluginNames.push('Link Assistant')
			}

			if (this.setupWizardStore.features.includes('rest-api')) {
				pluginNames.push('REST API')
			}

			return pluginNames.join(', ')
		},
		// Because we want translations for future options, we need to filter out ones we don't want showing right now.
		getFeatures () {
			return this.features
				// Hiding the following for now since we do not support them in this launch.
				.filter(f => 'breadcrumbs' !== f.value)
				.map(f => {
					f.selected = false
					if (this.setupWizardStore.features.includes(f.value)) {
						f.selected = true
					}
					return f
				})
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
		}
	}

	.plugins {
		margin-top: 16px;
		font-size: 14px;
		text-align: center;
		color: $black2;
	}

	.go-back {
		a {
			color: $black2;
			font-size: 14px;
		}
	}
}
</style>