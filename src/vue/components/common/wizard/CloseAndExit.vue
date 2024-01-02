<template>
	<div class="aioseo-wizard-close-and-exit">
		<slot name="links">
			<a
				v-if="$isPro || optionsStore.options.advanced.usageTracking"
				:href="rootStore.aioseo.urls.aio.dashboard"
			>
				{{ strings.closeAndExit }}
			</a>

			<a
				v-else
				href="#"
				@click.prevent="setupWizardStore.showUsageTrackingModal = true"
			>
				{{ strings.closeAndExit }}
			</a>
		</slot>

		<core-modal
			:show="setupWizardStore.showUsageTrackingModal && !$isPro"
			@close="setupWizardStore.showUsageTrackingModal = false"
			:classes="[ 'aioseo-close-and-exit-modal' ]"
		>
			<template #header>
				{{ strings.buildABetterAioseo }}

				<button
					class="close"
					@click.stop="setupWizardStore.showUsageTrackingModal = false"
				>
					<svg-close @click="setupWizardStore.showUsageTrackingModal = false" />
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
							@click.stop="processOptIn"
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

import { useWizard } from '@/vue/composables'

import CoreModal from '@/vue/components/common/core/modal/Index'
import SvgClose from '@/vue/components/common/svg/Close'

export default {
	setup () {
		const { strings } = useWizard()

		return {
			optionsStore     : useOptionsStore(),
			rootStore        : useRootStore(),
			setupWizardStore : useSetupWizardStore(),
			strings
		}
	},
	components : {
		CoreModal,
		SvgClose
	},
	data () {
		return {
			loading : false
		}
	},
	methods : {
		processOptIn () {
			this.setupWizardStore.smartRecommendations.usageTracking = true
			this.loading                                             = true

			this.setupWizardStore.saveWizard('smartRecommendations')
				.then(() => {
					window.location.href = this.rootStore.aioseo.urls.aio.dashboard
				})
		}
	}
}
</script>

<style lang="scss">
.aioseo-wizard-close-and-exit {
	margin: 40px 0;
	min-height: 40px;
	text-align: center;
	font-size: 14px;
	line-height: 22px;

	a {
		color: $placeholder-color !important;
	}
}

.aioseo-close-and-exit-modal {
	.modal-wrapper .modal-container {
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