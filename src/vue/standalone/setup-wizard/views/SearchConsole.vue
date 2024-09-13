<template>
	<div class="aioseo-wizard-search-console">
		<wizard-header />
		<wizard-container>
			<wizard-body>
				<wizard-steps />

				<div class="header">
					{{ strings.connectToGoogleSearchConsole }}
				</div>

				<div class="aioseo-wizard-search-console__panel">
					<div class="aioseo-wizard-search-console__image">
						<svg-connect-google-search-console />
					</div>

					<div class="aioseo-wizard-search-console__content">
						<div class="aioseo-wizard-search-console__content-text">{{ strings.syncYourSiteWithGsc }}</div>

						<ul class="aioseo-wizard-search-console__content-list">
							<li>
								<svg-circle-check />
								{{ strings.gscFeature1 }}
							</li>
							<li>
								<svg-circle-check />
								{{ strings.gscFeature2 }}
							</li>
							<li>
								<svg-circle-check />
								{{ strings.gscFeature3 }}
							</li>
							<li>
								<svg-circle-check />
								{{ strings.gscFeature4 }}
							</li>
						</ul>
					</div>
				</div>

				<template #footer>
					<div class="go-back">
						<router-link :to="setupWizardStore.getPrevLink" class="no-underline">&larr;</router-link>
						&nbsp;
						<router-link :to="setupWizardStore.getPrevLink">{{ strings.goBack }}</router-link>
					</div>
					<div class="spacer"></div>
					<base-button
						type="gray"
						@click="skipStep"
					>{{ strings.skipThisStep }}</base-button>
					<base-button
						type="blue"
						:loading="loading"
						@click="saveAndConnect"
					>{{ strings.connectToGoogleSearchConsole }} &rarr;</base-button>
				</template>
			</wizard-body>

			<wizard-close-and-exit />
		</wizard-container>
	</div>
</template>

<script>
import {
	useOptionsStore,
	useSetupWizardStore
} from '@/vue/stores'

import { merge } from 'lodash-es'

import { useGoogleSearchConsole } from '@/vue/composables/GoogleSearchConsole'
import { useWizard } from '@/vue/composables/Wizard'

import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgConnectGoogleSearchConsole from '@/vue/components/common/svg/ConnectGoogleSearchConsole'
import WizardBody from '@/vue/components/common/wizard/Body'
import WizardCloseAndExit from '@/vue/components/common/wizard/CloseAndExit'
import WizardContainer from '@/vue/components/common/wizard/Container'
import WizardHeader from '@/vue/components/common/wizard/Header'
import WizardSteps from '@/vue/components/common/wizard/Steps'
export default {
	setup () {
		const { strings: wizardStrings } = useWizard({
			stage : 'search-console'
		})
		const {
			connect,
			loading,
			strings: searchConsoleStrings
		} = useGoogleSearchConsole({
			returnTo : 'setup-wizard'
		})

		return {
			composableStrings : merge(wizardStrings, searchConsoleStrings),
			connect,
			loading,
			optionsStore      : useOptionsStore(),
			setupWizardStore  : useSetupWizardStore()
		}
	},
	components : {
		SvgCircleCheck,
		SvgConnectGoogleSearchConsole,
		WizardBody,
		WizardCloseAndExit,
		WizardContainer,
		WizardHeader,
		WizardSteps
	},
	data () {
		return {
			strings : merge(this.composableStrings, {})
		}
	},
	methods : {
		saveAndConnect () {
			this.loading = true
			this.setupWizardStore.saveWizard('searchConsole')
				.then(() => {
					this.connect()
				})
		},
		skipStep () {
			this.setupWizardStore.saveWizard()
			this.$router.push(this.setupWizardStore.getNextLink)
		}
	}
}
</script>

<style lang="scss">
.aioseo-wizard-search-console {
	color: $black;

	&__panel {
		background-color: $blue4;
		padding: 40px;
		border-radius: 4px;

		@media (max-width: 768px) {
			flex-direction: column;
			gap: 20px;
		}
	}

	&__image,
	&__content {
		max-width: 450px;
		margin: 0 auto;

		svg {
			width: 100%;
		}
	}

	&__image {
		margin-bottom: 20px;
	}

	&__content {
		&-text {
			margin-bottom: 16px;
			font-size: 14px;
			font-weight: 400;
			line-height: 22px;
		}

		&-list {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 10px;
			margin: 0;

			li {
				display: flex;
				align-items: flex-start;
			}

			.aioseo-circle-check {
				color: $green;
				max-width: 24px;
				margin-right: 8px;
			}
		}
	}

	.go-back {
		a {
			color: $black2;
			font-size: 14px;
		}
	}
}
</style>