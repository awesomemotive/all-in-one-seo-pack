<template>
	<div
		class="aioseo-seo-setup"
		:class="isWpDashboard ? 'aioseo-seo-setup--wp-styles' : ''"
	>
		<div class="aioseo-seo-setup-content">
			<div class="progress">
				<svg-progress-circle :percent="percent" />

				<span v-html="steps" />
			</div>

			<p class="description">{{ strings.description }}</p>

			<base-button
				v-if="!isWpDashboard"
				type="blue"
				size="medium"
				tag="a"
				:href="wizardUrl"
			>
				<svg-rocket /> {{ strings.improveSeo }}
			</base-button>

			<a
				v-if="isWpDashboard"
				:href="wizardUrl"
				class="button button-primary"
			>
				<svg-rocket /> {{ strings.improveSeo }}
			</a>
		</div>

		<svg-seo />
	</div>
</template>

<script>
import {
	useLicenseStore,
	useOptionsStore,
	useRootStore,
	useSetupWizardStore
} from '@/vue/stores'

import SvgProgressCircle from '@/vue/components/common/svg/ProgressCircle'
import SvgRocket from '@/vue/components/common/svg/Rocket'
import SvgSeo from '@/vue/components/common/svg/Seo'
export default {
	setup () {
		return {
			licenseStore     : useLicenseStore(),
			optionsStore     : useOptionsStore(),
			rootStore        : useRootStore(),
			setupWizardStore : useSetupWizardStore()
		}
	},
	components : {
		SvgProgressCircle,
		SvgRocket,
		SvgSeo
	},
	props : {
		isWpDashboard : {
			type : Boolean,
			default () {
				return false
			}
		}
	},
	data () {
		return {
			strings : {
				description : this.$t.__('You\'re almost there! Once you complete the SEO setup your site will be optimized to rank in search engine results!', this.$td),
				improveSeo  : this.$t.__('Improve SEO Rankings', this.$td)
			}
		}
	},
	computed : {
		steps () {
			const currentHtml = `<strong>${this.setupWizardStore.getCurrentStageCount}</strong>`
			const totalHtml   = `<strong>${this.setupWizardStore.getTotalStageCount}</strong>`

			return this.$t.sprintf(
				// Translators: 1 - The current step count. 2 - The total step count.
				this.$t.__('Step %1$s of %2$s', this.$td),
				currentHtml,
				totalHtml
			)
		},
		percent () {
			return Math.ceil((100 * this.setupWizardStore.getCurrentStageCount) / this.setupWizardStore.getTotalStageCount)
		},
		wizardUrl () {
			return `${this.rootStore.aioseo.urls.aio.wizard}#/${this.setupWizardStore.getNextLink.name}`
		}
	},
	methods : {
		deleteStage (stage) {
			const index = this.setupWizardStore.stages.findIndex(s => stage === s)
			if (-1 !== index) {
				// Delete the stage from stages.
				this.setupWizardStore.stages.splice(index, 1)
			}
		}
	},
	mounted () {
		if (this.optionsStore.internalOptions.internal.wizard) {
			const wizard = JSON.parse(this.optionsStore.internalOptions.internal.wizard)
			this.setupWizardStore.loadState(wizard)
		}

		if (!this.setupWizardStore.shouldShowImportStep) {
			this.deleteStage('import')
		}

		if (!this.licenseStore.isUnlicensed) {
			this.deleteStage('license-key')
		}

		if (this.$isPro) {
			this.deleteStage('smart-recommendations')
		}
	}
}
</script>

<style lang="scss">
.aioseo-seo-setup {
	display: flex;
	flex-direction: row;
	align-items: center;

	.progress {
		display: inline-flex;
		align-items: center;
		line-height: 1;
		padding: 8px 14px 8px 8px;
		border: 1px solid #C3C4C7;
		border-radius: 100px;
		margin-bottom: 20px;
		color: $black;

		.aioseo-progress-circle {
			width: 18px;
			margin-right: 8px;
		}
	}

	.aioseo-button {
		font-size: $font-sm;
		height: 32px;

		svg {
			width: 14px;
			height: 14px;
			margin-right: 10px;
		}
	}

	.description {
		font-size: 14px;
		margin-bottom: 20px;
		color: $black2;
	}

	.aioseo-seo {
		max-width: 300px;
		min-width: 275px;
		width: 100%;
		height: auto;
		@media screen and (max-width: 1280px) {
			min-width: 0;
		}
	}

	@media screen and (max-width: 912px) {
		flex-direction: column;
	}

	&--wp-styles {
		.progress {
			margin-bottom: 12px;
		}

		.description {
			color: #3C434A;
			margin-bottom: 12px;
		}

		.button {
			svg {
				vertical-align: text-bottom;
				width: 14px;
				height: 14px;
				margin-right: 10px;
			}
		}

		.aioseo-seo {
			max-width: 225px;
			min-width: 0;
		}

		@media screen and (max-width: 520px) {
			flex-direction: column;
		}
	}
}
</style>