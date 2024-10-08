<template>
	<div class="aioseo-analytics">
		<div id="aioseo-analytics" class="aioseo-wrap aioseo-plugin-landing">
			<core-mi-intro
				:prefers-em="prefersEm"
			/>

			<section
				:class="justInstalled || gaActivated ? 'aioseo-card step step--completed' : 'aioseo-card step step--current'"
			>
				<div class="step-count">
					<span class="step-count__num">1</span>
				</div>
				<div class="content">
					<h2 class="step-title">
						<template v-if="!gaInstalled">{{ strings.miemInstallH }} &amp;&nbsp;</template>
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

import CoreMiIntro from '@/vue/components/common/core/MiIntro'
import SvgExternal from '@/vue/components/common/svg/External'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			pluginsStore : usePluginsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		CoreMiIntro,
		SvgExternal
	},
	data () {
		return {
			installingPlugin : false,
			justInstalled    : false,
			strings          : {
				miLink : sprintf(
					'<strong>%1$s</strong>',
					__('Click here', td)
				),
				installMi : sprintf(
					// Translators: 1 - A plugin name (e.g. "MonsterInsights", "Broken Link Checker", etc.).
					__('Install %1$s', td),
					'MonsterInsights'
				),
				activateMi : sprintf(
					// Translators: 1 - A plugin name (e.g. "MonsterInsights", "Broken Link Checker", etc.).
					__('Activate %1$s', td),
					'MonsterInsights'
				),
				activateEm : sprintf(
					// Translators: 1 - A plugin name (e.g. "MonsterInsights", "Broken Link Checker", etc.).
					__('Activate %1$s', td),
					'ExactMetrics'
				),
				miInstalled : sprintf(
					// Translators: 1 - A plugin name (e.g. "MonsterInsights", "Broken Link Checker", etc.).
					__('%1$s is Installed & Active', td),
					'MonsterInsights'
				),
				emInstalled : sprintf(
					// Translators: 1 - A plugin name (e.g. "MonsterInsights", "Broken Link Checker", etc.).
					__('%1$s is Installed & Active', td),
					'ExactMetrics'),
				setupGA : __('Launch Setup Wizard', td
				),
				emInstallH : sprintf(
					// Translators: 1 - A plugin name (e.g. "MonsterInsights", "Broken Link Checker", etc.).
					__('Activate %1$s', td),
					'ExactMetrics'
				),
				emInstallP : sprintf(
					// Translators: 1 - A plugin name (e.g. "MonsterInsights", "Broken Link Checker", etc.).
					__('%1$s shows you exactly which content gets the most visits, so you can analyze and optimize it for higher conversions.', td),
					'ExactMetrics'
				),
				miemInstallH : __('Install', td),
				miInstallH   : sprintf(
					// Translators: 1 - A plugin name (e.g. "MonsterInsights", "Broken Link Checker", etc.).
					__('Activate %1$s', td),
					'MonsterInsights'
				),
				miInstallP : sprintf(
					// Translators: 1 - A plugin name (e.g. "MonsterInsights", "Broken Link Checker", etc.).
					__('%1$s shows you exactly which content gets the most visits, so you can analyze and optimize it for higher conversions.', td),
					'MonsterInsights'
				),
				emWizardH : sprintf(
					// Translators: 1 - A plugin name (e.g. "MonsterInsights", "Broken Link Checker", etc.).
					__('Setup %1$s', td),
					'ExactMetrics'
				),
				miWizardH : sprintf(
					// Translators: 1 - A plugin name (e.g. "MonsterInsights", "Broken Link Checker", etc.).
					__('Setup %1$s', td),
					'MonsterInsights'
				),
				emWizardP : sprintf(
					// Translators: 1 - A plugin name (e.g. "MonsterInsights", "Broken Link Checker", etc.).
					__('%1$s has an intuitive setup wizard to guide you through the setup process.', td),
					'ExactMetrics'
				),
				miWizardP : sprintf(
					// Translators: 1 - A plugin name (e.g. "MonsterInsights", "Broken Link Checker", etc.).
					__('%1$s has an intuitive setup wizard to guide you through the setup process.', td),
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