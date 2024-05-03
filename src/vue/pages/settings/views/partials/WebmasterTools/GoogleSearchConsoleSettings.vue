<template>
	<grid-column class="tool-settings tool-settings-google-search-console">
		<div>
			<div class="google-search-console-settings" id="google-search-console-settings">
				<core-alert
					v-if="showAlert"
					type="red"
				>
					{{ alertMessage }}
				</core-alert>

				<div v-if="!isConnected" class="google-search-console-settings__title">
					{{ strings.connectYourSite }}
				</div>

				<div class="google-search-console-settings__panel">
					<div class="google-search-console-settings__image">
						<svg-connect-google-search-console />
					</div>

					<div class="google-search-console-settings__content">
						<template v-if="isConnected">
							<div class="google-search-console-settings__content-title">{{ strings.connected }}</div>

							<div class="google-search-console-settings__content-text">{{ strings.syncYourSiteWithGsc }}</div>

							<base-button
								size="medium"
								type="blue"
								tag="button"
								@click.stop="reconnect()"
								:loading="loading"
							>
								{{ strings.reconnect }}&nbsp;&rarr;
							</base-button>

							<base-button
								size="medium"
								type="gray"
								tag="button"
								@click="showModal = true"
							>
								{{ strings.disconnect }}
							</base-button>
						</template>

						<template v-if="!isConnected">
							<div v-if="showUpgradeCta" class="google-search-console-settings__content-text">{{ strings.connectText }}</div>

							<base-button
								v-if="showUpgradeCta"
								size="medium"
								type="blue"
								tag="button"
								@click.stop="connect()"
								:loading="loading"
							>
								{{ strings.connectToGoogleSearchConsole }}&nbsp;&rarr;
							</base-button>

							<div class="google-search-console-settings__content-text">{{ strings.syncYourSiteWithGsc }}</div>

							<ul class="google-search-console-settings__content-list">
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

							<base-button
								v-if="!showUpgradeCta"
								size="medium"
								type="blue"
								tag="button"
								@click.stop="connect()"
								:loading="loading"
							>
								{{ strings.connectToGoogleSearchConsole }}&nbsp;&rarr;
							</base-button>

							<base-button
								v-if="showUpgradeCta"
								type="green"
								tag="a"
								size="medium"
								:href="$links.utmUrl('general-settings', 'google-search-console')"
								target="_blank"
							>
								{{ strings.upgradeButton }}
							</base-button>
						</template>
					</div>
				</div>
			</div>

			<disconnect-modal
				:show-modal="showModal"
				:loading="loading"
				@continue="disconnect()"
				@cancel="showModal = false"
			/>
		</div>
	</grid-column>
</template>

<script>
import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import license from '@/vue/utils/license'
import { merge } from 'lodash-es'
import { useSearchConsole } from '@/vue/composables'
import { WebmasterTools } from '@/vue/pages/settings/mixins'
import { GoogleSearchConsole } from '@/vue/mixins/GoogleSearchConsole'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import GridColumn from '@/vue/components/common/grid/Column'
import DisconnectModal from '@/vue/pages/search-statistics/views/partials/DisconnectModal'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgConnectGoogleSearchConsole from '@/vue/components/common/svg/ConnectGoogleSearchConsole'
export default {
	setup () {
		const { strings } = useSearchConsole()

		return {
			optionsStore      : useOptionsStore(),
			rootStore         : useRootStore(),
			composableStrings : strings
		}
	},
	components : {
		CoreAlert,
		DisconnectModal,
		GridColumn,
		SvgCircleCheck,
		SvgConnectGoogleSearchConsole
	},
	mixins : [ WebmasterTools, GoogleSearchConsole ],
	data () {
		return {
			returnTo : 'webmaster-tools',
			strings  : merge(this.composableStrings, {
				connectYourSite : this.$t.__('Connect Your Site to Google Search Console', this.$td),
				connected       : this.$t.__('Google Search Console is Connected.', this.$td),
				reconnect       : this.$t.__('Reconnect', this.$td),
				disconnect      : this.$t.__('Disconnect', this.$td),
				connectText     : this.$t.__('Quickly verify ownership in Google Search Console and automatically submit sitemaps with one click. Speed up indexing, increase visibility and optimize your site\'s performance to effortlessly attract more organic traffic.', this.$td),
				upgradeButton   : this.$t.__('Unlock Search Statistics', this.$td)
			}),
			license
		}
	},
	computed : {
		showUpgradeCta () {
			return !this.license.hasCoreFeature('search-statistics') && !this.isConnected
		}
	}
}
</script>

<style lang="scss">
.tool-settings-google-search-console {
	> div {
		padding: 0 !important;
	}
}

.google-search-console-settings {
	background-color: $blue4;
	border: 1px solid #CCE0FF;
	padding: 40px;
	border-radius: 3px;

	.aioseo-alert {
		margin-bottom: 30px;
	}

	&__title {
		font-size: 24px;
		font-weight: 700;
		line-height: 30px;
		color: $black;
		margin-bottom: 24px;
		text-align: center;
	}

	&__panel {
		display: flex;
		max-width: 900px;
		margin: 0 auto 0;
		font-size: 14px;
		line-height: 22px;
		gap: 40px;

		@media (max-width: 768px) {
			flex-direction: column;
			gap: 20px;
		}
	}

	&__image {
		svg {
			width: 100%;
		}
	}

	&__content {
		flex: 1;

		&-title {
			@extend .google-search-console-settings__title;
			text-align: left;
			margin-bottom: 12px;
		}

		&-text {
			margin-bottom: 16px;
		}

		&-list {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 10px;
			margin-bottom: 24px;

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

		.aioseo-button {
			display: inline-flex;
			margin-right: 16px;
			margin-bottom: 16px;
			align-items: center;

			svg {
				&.aioseo-chevron {
					width: 5px;
					height: 8px;
					color: white;
					margin-left: 13.5px;
					margin-top: 2px;
				}
			}
		}
	}
}
</style>