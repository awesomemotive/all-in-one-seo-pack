<template>
	<div class="aioseo-setup-wizard-welcome">
		<svg-giant-gear />

		<div class="aioseo-welcome-container">
			<div class="aioseo-welcome-content">
				<span class="header">{{ strings.welcome }}</span>

				<span class="description">
					{{ strings.description }}
				</span>

				<base-button
					type="green"
					inline
					tag="router-link"
					:to="setupWizardStore.getNextLink"
				>
					{{ strings.letsGetStarted }} &rarr;
				</base-button>

				<div class="aioseo-go-back">
					<a
						v-if="$isPro || optionsStore.options.advanced.usageTracking"
						:href="rootStore.aioseo.urls.aio.dashboard"
						class="no-underline"
					>&larr;</a>
					<a
						v-else
						class="no-underline"
						href="#"
						@click.prevent="showModal = true"
					>&larr;</a>
					&nbsp;
					<a
						v-if="$isPro || optionsStore.options.advanced.usageTracking"
						:href="rootStore.aioseo.urls.aio.dashboard"
					>
						{{ strings.goBack }}
					</a>

					<a
						v-else
						href="#"
						@click.prevent="showModal = true"
					>
						{{ strings.goBack }}
					</a>
				</div>
			</div>
		</div>

		<core-modal
			v-if="showModal && !$isPro"
			@close="showModal = false"
		>
			<template #header>
				<span>{{ strings.buildABetterAioseo }}</span>

				<button
					class="close"
					@click.stop="showModal = false"
				>
					<svg-close @click="showModal = false" />
				</button>
			</template>
			<template #body >
				<div class="aioseo-modal-body">
					<div class="reset-description"
						v-html="strings.getImprovedFeatures"
					/>

					<div class="actions">
						<base-button
							tag="a"
							:href="rootStore.aioseo.urls.aio.dashboard"
							type="gray"
							size="medium"
						>
							{{ strings.noThanks }}
						</base-button>

						<base-button
							type="blue"
							size="medium"
							:loading="loading"
							@click="processOptIn"
						>
							{{ strings.yesCountMeIn }}
						</base-button>
					</div>
				</div>
			</template>
		</core-modal>
	</div>
</template>

<script>
import {
	useOptionsStore,
	useRootStore,
	useSetupWizardStore
} from '@/vue/stores'
import { merge } from 'lodash-es'
import { useWizard } from '@/vue/composables'
import { Wizard, WizardUsageTracking } from '@/vue/mixins'
import SvgGiantGear from '@/vue/components/common/svg/GiantGear'
export default {
	setup () {
		const { strings } = useWizard()
		return {
			optionsStore      : useOptionsStore(),
			rootStore         : useRootStore(),
			setupWizardStore  : useSetupWizardStore(),
			composableStrings : strings
		}
	},
	components : {
		SvgGiantGear
	},
	mixins : [ Wizard, WizardUsageTracking ],
	data () {
		return {
			stage   : 'welcome',
			strings : merge(this.composableStrings, {
				welcome : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					this.$t.__('Welcome to the %1$s Setup Wizard!', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				description : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					this.$t.__('%1$s makes it easy to configure your site\'s SEO settings without the need to hire an expert. And it takes less than 10 minutes too!', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				letsGetStarted : this.$t.__('Let\'s Get Started', this.$td),
				goBack         : this.$t.__('Go back to the Dashboard', this.$td)
			})
		}
	}
}
</script>

<style lang="scss">
.aioseo-setup-wizard-welcome {
	padding: 25px;
	height: 100vh;
	display: flex;
	align-items: center;
	justify-content: center;
	overflow: hidden;

	svg.aioseo-giant-gear {
		width: 904px;
		min-width: 904px;
		height: auto;
		color: #DDDEE2;
		opacity: 0.3;
	}

	.aioseo-welcome-container {
		position: absolute;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		display: flex;
		align-items: center;
		justify-content: center;

		.aioseo-welcome-content {
			position: relative;
			width: 100%;
			max-width: 640px;
			background-color: #fff;
			border: 1px solid $border;
			box-shadow: 0px 2px 10px rgba(0, 90, 224, 0.2);
			padding: 40px;
			display: flex;
			justify-content: center;
			flex-direction: column;
			text-align: center;

			.aioseo-go-back {
				position: absolute;
				bottom: -80px;
				align-self: center;

				a {
					color: $placeholder-color;
				}
			}

			.header {
				font-size: 24px;
				color: $black;
				font-weight: 600;
				margin-bottom: 30px;
			}

			.description {
				line-height: 1.4;
				font-size: 16px;
				color: $black2;
			}

			.aioseo-button {
				margin-top: 20px;
				max-width: 265px;
				align-self: center;
			}
		}
	}

	@media screen and (max-width: 782px) {
		.aioseo-welcome-container {
			.aioseo-welcome-content {
				padding: 30px;
			}
		}
	}

	.modal-mask .modal-wrapper .modal-container {
		max-width: 600px;

		.modal-header {
			border-bottom: none;
			font-size: 18px;
		}
	}

	.aioseo-modal-body {
		padding: 0 30px 30px;
		display: flex;
		flex-direction: column;
		position: relative;

		h3 {
			font-size: 20px;
			margin-bottom: 16px;
		}

		.reset-description {
			font-size: 16px;
			color: $black;
			margin-bottom: 16px;
			text-align: left;
			max-width: 515px;
			line-height: 24px;
		}

		button.close {
			position: absolute;
			right: 11px;
			top: 11px;
			width: 24px;
			height: 24px;
			background-color: #fff;
			border: none;
			display: flex;
			align-items: center;

			svg.aioseo-close {
				cursor: pointer;
				width: 14px;
				height: 14px;
			}
		}

		.aioseo-description {
			max-width: 510px;
			text-align: center;
		}

		.aioseo-button:not(.close) {
			margin-top: 16px;
		}

		div.actions {
			display: flex;
			justify-content: flex-end;

			a {
				margin-right: 10px;
			}
		}
	}
}
</style>