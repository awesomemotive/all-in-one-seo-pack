<template>
	<div class="aioseo-wizard-close-and-exit">
		<a
			v-if="$isPro || optionsStore.options.advanced.usageTracking"
			:href="rootStore.aioseo.urls.aio.dashboard"
		>
			{{ strings.closeAndExit }}
		</a>

		<a
			v-else
			href="#"
			@click.prevent="showModal = true"
		>
			{{ strings.closeAndExit }}
		</a>

		<core-modal
			v-if="showModal && !$isPro"
			@close="showModal = false"
		>
			<template #header>
				{{ strings.buildABetterAioseo }}

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
	useRootStore
} from '@/vue/stores'

import { useWizard } from '@/vue/composables'
import { WizardUsageTracking } from '@/vue/mixins'
export default {
	setup () {
		const { strings } = useWizard()

		return {
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore(),
			strings
		}
	},
	mixins : [ WizardUsageTracking ]
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