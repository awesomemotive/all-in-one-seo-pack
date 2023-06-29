<template>
	<div class="aioseo-analytics">
		<div id="aioseo-analytics" class="aioseo-wrap aioseo-plugin-landing">
			<core-card
				slug="monsterinsights-intro"
				:hideHeader="true"
				:noSlide="true"
				:cardClass="{
					'aioseo-card--intro': true
				}"
			>
				<div class="aioseo-analytics__intro">
					<template v-if="prefersEm">
						<div class="intro-image">
							<svg-aioseo-logo-gear />
							<span>♥</span>
							<img
								:src="getAssetUrl(emLogoImg)"
								height="90"
								alt="ExactMetrics"
							/>
						</div>
						<h2 class="intro-heading">
							{{ strings.emIntroH }}
						</h2>
						<p>
							{{ strings.emIntroP }}
						</p>
						<div class="preview-list">
							<img
								:src="getAssetUrl(emGraphImg)"
								height="200"
								alt="mi-graph-preview"
							/>
							<ul>
								<li>
									<svg-circle-check />
									{{ strings.emIntroLi1 }}
								</li>
								<li>
									<svg-circle-check />
									{{ strings.emIntroLi2 }}
								</li>
								<li>
									<svg-circle-check />
									{{ strings.emIntroLi3 }}
								</li>
							</ul>
						</div>
					</template>
					<template v-else>
						<div class="intro-image">
							<svg-aioseo-logo-gear />
							<span>♥</span>
							<img
								:src="getAssetUrl(miLogoImg)"
								height="90"
								alt="MonsterInsights"
							/>
						</div>
						<h2 class="intro-heading">
							{{ strings.miIntroH }}
						</h2>
						<p>
							{{ strings.miIntroP }}
						</p>
						<div class="preview-list">
							<img
								:src="getAssetUrl(miGraphImg)"
								height="200"
								alt="mi-graph-preview"
							/>
							<ul>
								<li>
									<svg-circle-check />
									{{ strings.miIntroLi1 }}
								</li>
								<li>
									<svg-circle-check />
									{{ strings.miIntroLi2 }}
								</li>
								<li>
									<svg-circle-check />
									{{ strings.miIntroLi3 }}
								</li>
								<li>
									<svg-circle-check />
									{{ strings.miIntroLi4 }}
								</li>
							</ul>
						</div>
					</template>
				</div>
			</core-card>
			<section
				:class="justInstalled || gaActivated ? 'aioseo-card step step--completed' : 'aioseo-card step step--current'"
			>
				<div class="step-count">
					<span class="step-count__num">1</span>
				</div>
				<div class="content">
					<h2 class="step-title">
						<template v-if="!gaInstalled">{{ strings.miemInstallH }}</template>
						<template v-if="prefersEm">{{ strings.emInstallH }}</template>
						<template v-else>{{ strings.miInstallH }}</template>
					</h2>
					<p>
						<template v-if="prefersEm">{{ strings.emInstallP }}</template>
						<template v-else>{{ strings.miInstallP }}</template>
					</p>
					<base-button
						v-if="!pluginsStore.plugins.miLite.canInstall"
						type="blue"
						size="medium"
						tag="a"
						target="_blank"
						:href="pluginsStore.plugins.miLite.wpLink"
					>
						<svg-external /> {{ strings.installMi }}
					</base-button>
					<base-button
						v-if="pluginsStore.plugins.miLite.canInstall"
						:loading="installingPlugin"
						:type="justInstalled || gaActivated ? 'disabled' : 'blue'"
						size="medium"
						@click="installMi"
					>
						<span
							v-if="!justInstalled && !gaInstalled"
						>{{ strings.installMi }}</span>
						<span
							v-if="(justInstalled || gaActivated) && !prefersEm"
						>{{ strings.miInstalled }}</span>
						<span
							v-if="(justInstalled || gaActivated) && prefersEm"
						>{{ strings.emInstalled }}</span>
						<span
							v-if="!justInstalled && gaInstalled && !gaActivated && !prefersEm"
						>{{ strings.activateMi }}</span>
						<span
							v-if="!justInstalled && gaInstalled && !gaActivated && prefersEm"
						>{{ strings.activateEm }}</span>
					</base-button>
				</div>
			</section>
			<section
				:class="justInstalled || gaActivated ? 'aioseo-card step step--current' : 'aioseo-card step step--pending'"
			>
				<div class="step-count">
					<span class="step-count__num">2</span>
				</div>
				<div class="content">
					<template v-if="prefersEm">
						<h2 class="step-title">{{ strings.emWizardH }}</h2>
						<p class="step-body">{{ strings.emWizardP }}</p>
					</template>
					<template v-else>
						<h2 class="step-title">{{ strings.miWizardH }}</h2>
						<p class="step-body">{{ strings.miWizardP }}</p>
					</template>
					<base-button
						:disabled="!justInstalled && !gaActivated"
						type="blue"
						size="medium"
						tag="a"
						:href="miOnboardingUrl"
					>
						{{ strings.setupGA }}
					</base-button>
				</div>
			</section>
		</div>
	</div>
</template>
<script>
import {
	usePluginsStore,
	useRootStore
} from '@/vue/stores'

import { getAssetUrl } from '@/vue/utils/helpers'
import emLogoImg from '@/vue/assets/images/about/plugins/em.png'
import emGraphImg from '@/vue/assets/images/plugins/em-graph-preview.png'
import miLogoImg from '@/vue/assets/images/plugins/mi-logo.png'
import miGraphImg from '@/vue/assets/images/plugins/mi-graph-preview.png'
import CoreCard from '@/vue/components/common/core/Card'
import SvgAioseoLogoGear from '@/vue/components/common/svg/aioseo/LogoGear'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgExternal from '@/vue/components/common/svg/External'
export default {
	setup () {
		return {
			pluginsStore : usePluginsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		CoreCard,
		SvgAioseoLogoGear,
		SvgCircleCheck,
		SvgExternal
	},
	data () {
		return {
			emLogoImg,
			emGraphImg,
			miLogoImg,
			miGraphImg,
			installingPlugin : false,
			justInstalled    : false,
			strings          : {
				miLink : this.$t.sprintf(
					'<strong>%1$s</strong>',
					this.$t.__('Click here', this.$td)
				),
				installMi : this.$t.sprintf(
					// Translators: 1 - The addon or plugin name.
					this.$t.__('Install %1$s', this.$td),
					'MonsterInsights'
				),
				activateMi : this.$t.sprintf(
					// Translators: 1 - The name of one of our partner plugins.
					this.$t.__('Activate %1$s', this.$td),
					'MonsterInsights'
				),
				activateEm : this.$t.sprintf(
					// Translators: 1 - The name of one of our partner plugins.
					this.$t.__('Activate %1$s', this.$td),
					'ExactMetrics'
				),
				miInstalled : this.$t.sprintf(
					// Translators: 1 - The name of one of our partner plugins.
					this.$t.__('%1$s is Installed & Active', this.$td),
					'MonsterInsights'
				),
				emInstalled : this.$t.sprintf(
					// Translators: 1 - The name of one of our partner plugins.
					this.$t.__('%1$s is Installed & Active', this.$td),
					'ExactMetrics'),
				setupGA : this.$t.__('Launch Setup Wizard', this.$td
				),
				emIntroH : this.$t.__('The Best Google Analytics Plugin for WordPress', this.$td),
				emIntroP : this.$t.sprintf(
					// Translators: 1 - The name of one of our partner plugins, 2 - The name of one of our partner plugins.
					this.$t.__('%1$s connects AIOSEO to Google Analytics, providing a powerful integration. %2$s is a sister company of AIOSEO.', this.$td),
					'ExactMetrics',
					'ExactMetrics'
				),
				emIntroLi1 : this.$t.__('Quick & Easy Google Analytics Setup', this.$td),
				emIntroLi2 : this.$t.__('Google Analytics Dashboard + Real Time Stats', this.$td),
				emIntroLi3 : this.$t.__('Google Analytics Enhanced Ecommerce Tracking', this.$td),
				emInstallH : this.$t.sprintf(
					// Translators: 1 - The name of one of our partner plugins.
					this.$t.__('Activate %1$s', this.$td),
					'ExactMetrics'
				),
				emInstallP : this.$t.sprintf(
					// Translators: 1 - The name of one of our partner plugins.
					this.$t.__('%1$s shows you exactly which content gets the most visits, so you can analyze and optimize it for higher conversions.', this.$td),
					'ExactMetrics'
				),
				miIntroH : this.$t.__('The Best Google Analytics Plugin for WordPress', this.$td),
				miIntroP : this.$t.sprintf(
					// Translators: 1 - The name of one of our partner plugins, 2 - The name of one of our partner plugins.
					this.$t.__('%1$s connects AIOSEO to Google Analytics, providing a powerful integration. %2$s is a sister company of AIOSEO.', this.$td),
					'MonsterInsights',
					'MonsterInsights'
				),
				miIntroLi1   : this.$t.__('Quick & Easy Google Analytics Setup', this.$td),
				miIntroLi2   : this.$t.__('Google Analytics Dashboard + Real Time Stats', this.$td),
				miIntroLi3   : this.$t.__('Google Analytics Enhanced Ecommerce Tracking', this.$td),
				miIntroLi4   : this.$t.__('Universal Tracking for AMP and Instant Articles', this.$td),
				miemInstallH : this.$t.__('Install &', this.$td),
				miInstallH   : this.$t.sprintf(
					// Translators: 1 - The name of one of our partner plugins.
					this.$t.__('Activate %1$s', this.$td),
					'MonsterInsights'
				),
				miInstallP : this.$t.sprintf(
					// Translators: 1 - The name of one of our partner plugins.
					this.$t.__('%1$s shows you exactly which content gets the most visits, so you can analyze and optimize it for higher conversions.', this.$td),
					'MonsterInsights'
				),
				emWizardH : this.$t.sprintf(
					// Translators: 1 - The name of one of our partner plugins.
					this.$t.__('Setup %1$s', this.$td),
					'ExactMetrics'
				),
				miWizardH : this.$t.sprintf(
					// Translators: 1 - The name of one of our partner plugins.
					this.$t.__('Setup %1$s', this.$td),
					'MonsterInsights'
				),
				emWizardP : this.$t.sprintf(
					// Translators: 1 - The name of one of our partner plugins.
					this.$t.__('%1$s has an intuitive setup wizard to guide you through the setup process.', this.$td),
					'ExactMetrics'
				),
				miWizardP : this.$t.sprintf(
					// Translators: 1 - The name of one of our partner plugins.
					this.$t.__('%1$s has an intuitive setup wizard to guide you through the setup process.', this.$td),
					'MonsterInsights'
				)
			}
		}
	},
	computed : {
		gaActivated () {
			return this.pluginsStore.plugins.miLite.activated ||
				this.pluginsStore.plugins.emLite.activated ||
				this.pluginsStore.plugins.miPro.activated ||
				this.pluginsStore.plugins.emPro.activated
		},
		gaInstalled () {
			return this.pluginsStore.plugins.miLite.installed ||
				this.pluginsStore.plugins.emLite.installed ||
				this.pluginsStore.plugins.miPro.installed ||
				this.pluginsStore.plugins.emPro.installed
		},
		miOnboardingUrl () {
			return this.prefersEm
				? `${this.rootStore.aioseo.urls.home}/wp-admin/admin.php?page=exactmetrics-onboarding`
				: `${this.rootStore.aioseo.urls.home}/wp-admin/admin.php?page=monsterinsights-onboarding`
		},
		prefersEm () {
			return (this.pluginsStore.plugins.emLite.installed ||
				this.pluginsStore.plugins.emPro.installed) &&
				(!this.pluginsStore.plugins.miLite.installed &&
				!this.pluginsStore.plugins.miPro.installed)
		}
	},
	methods : {
		getAssetUrl,
		installMi () {
			this.installingPlugin = true
			this.pluginsStore.installPlugins([
				{
					plugin : this.prefersEm ? 'emLite' : 'miLite',
					type   : 'plugin'
				}
			])
				.then(() => {
					this.installingPlugin                      = false
					this.justInstalled                         = true
					this.pluginsStore.plugins.miLite.activated = true
				})
				.catch(error => {
					console.error(error)
				})
		}
	}
}
</script>
<style lang="scss">
.aioseo-app .aioseo-analytics {
	padding-top: 1px;
	line-height: 1.5;

	p {
		max-width: 600px;
		margin-bottom: 24px;
		line-height: 1.5;
	}

	section.aioseo-card {
		padding: 18px;
		position: relative;

		&.step {
			display: flex;
			align-items: center;
			margin-left: auto;
			margin-right: auto;
		}
	}

	.aioseo-card.aioseo-card--intro {
		background-color: $box-background2;
		box-shadow: none;
	}

	.step-count {
		max-width: 170px;
		width: 100%;
	}

	.step-count__num {
		font-size: 32px;
		bottom: 50%;
		line-height: 1;
		width: 48px;
		height: 48px;
		text-align: center;
		border-radius: 50%;
		color: $input-border;
		border: 2px solid $input-border;
		display: flex;
		align-items: center;
		justify-content: center;
		margin: 0 auto;
		transition: all .2s ease;
	}

	.step--completed .step-count__num {
		background-color: $background;
		border-color: $background;
		color: #fff;
	}

	.step--current .step-count__num {
		background-color: $input-border;
		color: #fff;
	}

	.aioseo-button.disabled:not(.loading) {
		color: $placeholder-color !important;
		background-color: $background !important;
		border-width: 0;
		pointer-events: none;
		cursor: default;
		box-shadow: none !important;
		transform: none !important;
	}

	.aioseo-analytics__intro {
		text-align: center;

		.intro-image {
			max-width: 300px;
			margin: 0 auto;
			display: flex;
			justify-content: space-between;
			font-size: 45px;
			align-items: center;

			.aioseo-logo-gear {
				width: 100%;
				max-width: 90px;
				color: $blue;
			}

			span {
				color: $red;
			}
		}

		.intro-heading {
			margin: 24px auto;
			font-size: 36px;
			line-height: 1.25;
		}

		p {
			margin: 0 auto;
		}
	}

	.preview-list {
		display: flex;
		text-align: left;
		align-items: center;
		max-width: 800px;
		margin: 24px auto 0;

		ul {
			margin-left: 24px;
		}

		li {
			display: flex;
			align-items: flex-start;
			font-size: 16px;
		}
	}

	.aioseo-circle-check {
		color: $green;
		max-width: 18px;
		margin-right: 18px;
		margin-top: 3px;
	}

	.step--pending {
		opacity: .5;
		filter: grayscale(1);
	}

	.step-title {
		margin-bottom: 24px;
		font-size: 32px;
		line-height: 1.25;
	}
}
</style>