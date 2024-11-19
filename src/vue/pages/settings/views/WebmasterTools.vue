<template>
	<div class="aioseo-webmaster-tools">
		<core-alert-actionable
			v-if="!searchStatisticsStore.isConnected && !settingsStore.settings.dismissedAlerts?.searchConsoleNotConnected"
			:title="strings.gsc.haveYouConnected"
			:text="strings.gsc.aioseoCanNowVerify"
			:button="strings.gsc.connectTo"
			type="yellow"
			size="small"
			button-type="link"
			show-close
			@click="openSearchConsoleSettings"
			@close-alert="() => settingsStore.dismissAlert('searchConsoleNotConnected')"
		/>

		<core-alert-actionable
			v-if="showGscSiteDisconnectedAlert && !settingsStore.settings.dismissedAlerts?.searchConsoleNotConnected"
			:title="strings.gsc.siteRemoved"
			:text="strings.gsc.weDetected"
			:button="strings.gsc.reconnect"
			type="yellow"
			size="small"
			button-type="button"
			show-close
			@click="openSearchConsoleSettings"
			@close-alert="() => settingsStore.dismissAlert('searchConsoleNotConnected')"
		/>

		<core-card
			slug="webmasterTools"
			:header-text="strings.webmasterToolsVerification"
		>
			<div class="webmaster-tools-description">{{strings.enterVerificationCode}}</div>

			<grid-row class="webmaster-tools-toggles">
				<grid-column
					class="tool-toggle"
					v-for="(tool, index) in tools"
					:key="`toggle_${index}`"
					sm="6"
					md="4"
					lg="3"
					:style="{ order: getOrder(tool) }"
				>
					<div
						@click="toggleActiveTool(tool.slug)"
						:class="[
							{ active: tool.slug === activeTool },
							{ connected: isConnected(tool) }
						]"
					>
						<div class="logo">
							<component
								class="logo-svg"
								:is="tool.svg"
							/>
						</div>

						<div>{{ tool.name }}</div>

						<svg-circle-check-solid
							v-if="isConnected(tool)"
						/>
					</div>
				</grid-column>

				<transition-slide
					class="tool-settings-slide"
					:style="{ order: getOrder(tool, true) }"
					v-for="(tool, index) in tools"
					:key="`content_${index}`"
					:active="tool.slug === activeTool"
					@open-start="heightOkay = activeTool"
					@close-end="heightOkay = activeTool"
				>
					<component
						:is="tool.component ? tool.component : 'ToolSettings'"
						:tool="tool"
						:isConnected="isConnected(tool)"
						v-if="heightOkay === tool.slug"
					/>
				</transition-slide>
			</grid-row>

			<grid-row class="webmaster-tools-spacer">
				<grid-column>
					<div />
				</grid-column>
			</grid-row>

			<core-settings-row
				:name="strings.miscellaneousVerification"
			>
				<template #content>
					<core-alert-unfiltered-html />
					<base-editor
						:disabled="!rootStore.aioseo.user.unfilteredHtml"
						v-model="optionsStore.options.webmasterTools.miscellaneousVerification"
						line-numbers
						monospace
						preserve-whitespace
					/>

					<p
						class="aioseo-description"
						v-html="strings.miscellaneousVerificationDescription"
					/>
				</template>
			</core-settings-row>
		</core-card>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useIndexNowStore,
	useOptionsStore,
	usePluginsStore,
	useRootStore,
	useSettingsStore,
	useSearchStatisticsStore
} from '@/vue/stores'

import { useGoogleSearchConsole } from '@/vue/composables/GoogleSearchConsole'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseEditor from '@/vue/components/common/base/Editor'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import BaseTextarea from '@/vue/components/common/base/Textarea'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreAlertActionable from '@/vue/components/common/core/alert/Actionable'
import CoreCard from '@/vue/components/common/core/Card'
import CoreAlertUnfilteredHtml from '@/vue/components/common/core/alert/UnfilteredHtml'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import GoogleAnalyticsSettings from './partials/WebmasterTools/GoogleAnalyticsSettings'
import GoogleSearchConsoleSettings from './partials/WebmasterTools/GoogleSearchConsoleSettings'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import IndexNowSettings from './partials/WebmasterTools/IndexNowSettings'
import MicrosoftClaritySettings from './partials/WebmasterTools/MicrosoftClaritySettings'
import SvgCircleCheckSolid from '@/vue/components/common/svg/circle/CheckSolid'
import SvgExternal from '@/vue/components/common/svg/External'
import SvgLogoBaidu from '@/vue/components/common/svg/logo/Baidu'
import SvgLogoGoogle from '@/vue/components/common/svg/logo/Google'
import SvgLogoGoogleAnalytics from '@/vue/components/common/svg/logo/GoogleAnalytics'
import SvgLogoIndexNow from '@/vue/components/common/svg/logo/IndexNow'
import SvgLogoMicrosoftBing from '@/vue/components/common/svg/logo/MicrosoftBing'
import SvgLogoMicrosoftClarity from '@/vue/components/common/svg/logo/MicrosoftClarity'
import SvgLogoPinterest from '@/vue/components/common/svg/logo/Pinterest'
import SvgLogoYandex from '@/vue/components/common/svg/logo/Yandex'
import ToolSettings from './partials/WebmasterTools/ToolSettings'
import TransitionSlide from '@/vue/components/common/transition/Slide'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			connect
		} = useGoogleSearchConsole()

		return {
			connect,
			indexNowStore         : useIndexNowStore(),
			optionsStore          : useOptionsStore(),
			pluginsStore          : usePluginsStore(),
			rootStore             : useRootStore(),
			searchStatisticsStore : useSearchStatisticsStore(),
			settingsStore         : useSettingsStore()
		}
	},
	components : {
		BaseCheckbox,
		BaseEditor,
		BaseRadioToggle,
		BaseTextarea,
		CoreAlert,
		CoreAlertActionable,
		CoreAlertUnfilteredHtml,
		CoreCard,
		CoreSettingsRow,
		GoogleAnalyticsSettings,
		GoogleSearchConsoleSettings,
		GridColumn,
		GridRow,
		IndexNowSettings,
		MicrosoftClaritySettings,
		SvgCircleCheckSolid,
		SvgExternal,
		SvgLogoBaidu,
		SvgLogoGoogle,
		SvgLogoGoogleAnalytics,
		SvgLogoIndexNow,
		SvgLogoMicrosoftBing,
		SvgLogoMicrosoftClarity,
		SvgLogoPinterest,
		SvgLogoYandex,
		ToolSettings,
		TransitionSlide
	},
	data () {
		return {
			heightOkay    : false,
			activeTool    : null,
			columnsPerRow : 4,
			strings       : {
				enterVerificationCode                : __('Enter your verification codes below to activate webmaster tools.', td),
				miscellaneousVerification            : __('Miscellaneous Verification', td),
				miscellaneousVerificationDescription : sprintf(
					// Translators: 1 - "<head></head>".
					__('The code above will be added between the %1$s tags on every page on your website.', td),
					'<em>&lt;head&gt; &lt/head&gt;</em>'
				),
				webmasterToolsVerification : __('Webmaster Tools Verification', td),
				unfilteredHtmlError        : sprintf(
					// Translators: 1 - Learn more link.
					__('Your user account role does not have access to edit this field. %1$s', td),
					links.getDocLink(GLOBAL_STRINGS.learnMore, 'unfilteredHtml', true)
				),
				gsc : {
					haveYouConnected   : __('Have you connected your site to Google Search Console?', td),
					aioseoCanNowVerify : sprintf(
						// Translators: 1 - The plugin short name ("AIOSEO").
						__('%1$s can now verify whether your site is correctly verified with Google Search Console and that your sitemaps have been submitted correctly. Connect with Google Search Console now to ensure your content is being added to Google as soon as possible for increased rankings.', td),
						import.meta.env.VITE_SHORT_NAME
					),
					connectTo   : __('Connect to Google Search Console', td),
					siteRemoved : __('Your site was removed from Google Search Console.', td),
					weDetected  : __('We detected that your site has been removed from Google Search Console. If this was done in error, click below to re-sync and resolve this issue.', td),
					reconnect   : __('Reconnect Google Search Console', td)
				}
			}
		}
	},
	computed : {
		tools () {
			return [
				{
					slug      : 'googleSearchConsole',
					name      : __('Google Search Console', td),
					svg       : 'svg-logo-google',
					component : 'GoogleSearchConsoleSettings',
					settings  : [
						{
							option      : 'google',
							label       : __('Google Verification Code', td),
							description : sprintf(
								// Translators: 1 - "Google Search Console".
								__('Get your Google verification code in %1$s.', td),
								links.getDocLink(__('Google Search Console', td), 'googleSearchConsole')
							)
						}
					]
				},
				{
					slug     : 'bing',
					name     : __('Bing Webmaster Tools', td),
					svg      : 'svg-logo-microsoft-bing',
					settings : [
						{
							option      : 'bing',
							label       : __('Bing Verification Code', td),
							description : sprintf(
								// Translators: 1 - "Bing Webmaster Tools".
								__('Get your Bing verification code in %1$s.', td),
								links.getDocLink(__('Bing Webmaster Tools', td), 'bingWebmasterVerification')
							)
						}
					]
				},
				{
					slug     : 'yandex',
					name     : __('Yandex Webmaster Tools', td),
					svg      : 'svg-logo-yandex',
					settings : [
						{
							option      : 'yandex',
							label       : __('Yandex Verification Code', td),
							description : sprintf(
								// Translators: 1 - "Yandex Webmaster Tools".
								__('Get your Yandex verification code in %1$s.', td),
								links.getDocLink(__('Yandex Webmaster Tools', td), 'yandexWebmasterVerification')
							)
						}
					]
				},
				{
					slug     : 'baidu',
					name     : __('Baidu Webmaster Tools', td),
					svg      : 'svg-logo-baidu',
					settings : [
						{
							option      : 'baidu',
							label       : __('Baidu Verification Code', td),
							description : sprintf(
								// Translators: 1 - "Baidu Webmaster Tools".
								__('Get your Baidu verification code in %1$s.', td),
								links.getDocLink(__('Baidu Webmaster Tools', td), 'baiduWebmasterVerification')
							)
						}
					]
				},
				{
					slug     : 'pinterest',
					name     : __('Pinterest Site Verification', td),
					svg      : 'svg-logo-pinterest',
					settings : [
						{
							option      : 'pinterest',
							label       : __('Pinterest Verification Code', td),
							description : sprintf(
								// Translators: 1 - "Pinterest account".
								__('Get your Pinterest verification code in your %1$s.', td),
								links.getDocLink(__('Pinterest account', td), 'pinterestSiteVerification')
							)
						}
					]
				},
				{
					slug      : 'indexNow',
					name      : __('IndexNow', td),
					svg       : 'svg-logo-index-now',
					component : 'IndexNowSettings',
					settings  : [
						{
							option      : 'apiKey',
							label       : __('IndexNow API Key', td),
							description : sprintf(
								// Translators: 1 - Learn more link.
								__('You can manually set an API key here, but if left blank a new one will be auto-generated. %1$s', td),
								links.getDocLink(GLOBAL_STRINGS.learnMore, 'indexNow', true)
							)
						}
					]
				},
				{
					slug      : 'microsoftClarity',
					name      : 'Microsoft Clarity',
					svg       : 'svg-logo-microsoft-clarity',
					component : 'MicrosoftClaritySettings',
					settings  : [
						{
							option : 'microsoftClarityProjectId',
							label  : sprintf(
								// Translators: 1 - "Clarity".
								__('%1$s Project ID', td),
								'Clarity'
							),
							description : sprintf(
								// Translators: 1 - "Clarity", 2 - Learn more link.
								__('%1$s helps you understand how users interact with your website through heatmaps and session recordings. %2$s', td),
								'Clarity',
								links.getDocLink(GLOBAL_STRINGS.learnMore, 'microsoftClarityDocumentation', true)
							)
						}
					]
				},
				{
					slug      : 'googleAnalytics',
					name      : __('Google Analytics', td),
					svg       : 'svg-logo-google-analytics',
					component : 'GoogleAnalyticsSettings',
					settings  : [
						{
							option      : 'gtmContainerId',
							label       : __('Google Tag Manager Container ID', td),
							pro         : true,
							placeholder : 'GTM-XXXXXX',
							description : sprintf(
								// Translators: 1 - "Google Tag Manager account".
								__('Get your Google Tag Manager ID in your %1$s.', td),
								links.getDocLink(__('Google Tag Manager account', td), 'gtmContainerId')
							)
						}
					]
				}
			]
		},
		showGscSiteDisconnectedAlert () {
			// Early bail if the site was not checked yet.
			if (0 === this.optionsStore.internalOptions.internal.searchStatistics.site.lastFetch) {
				return false
			}

			return this.searchStatisticsStore.isConnected && !this.optionsStore.internalOptions.internal.searchStatistics.site.verified
		}
	},
	methods : {
		getOrder (tool, setting = false) {
			const index = this.tools.findIndex(t => t.slug === tool.slug)
			if (!setting) {
				return index
			}

			return this.getRow(this.columnsPerRow - 1, index)
		},
		getRow (row, index) {
			if ((index - 1) < row) {
				return row
			}

			return this.getRow(row + this.columnsPerRow, index)
		},
		toggleActiveTool (slug) {
			if (this.activeTool === slug) {
				this.activeTool = null
				return
			}

			this.activeTool = slug
		},
		maybeChangeColumnsPerRow () {
			// XS
			if (window.matchMedia('(max-width: 600px)').matches) {
				this.columnsPerRow = 1
			}

			// SM
			if (window.matchMedia('(min-width: 600px) and (max-width: 912px)').matches) {
				this.columnsPerRow = 2
			}

			// MD
			if (window.matchMedia('(min-width: 912px) and (max-width: 1042px)').matches) {
				this.columnsPerRow = 3
			}

			// LG
			if (window.matchMedia('(min-width: 1042px)').matches) {
				this.columnsPerRow = 4
			}
		},
		isConnected (tool) {
			if ('indexNow' === tool.slug) {
				return !!this.indexNowStore.options.indexNow.apiKey
			}

			if ('googleAnalytics' === tool.slug) {
				return this.optionsStore.options.deprecated?.webmasterTools?.googleAnalytics[tool.settings[0].option] && !this.pluginsStore.plugins.miLite.activated
			}

			if ('googleSearchConsole' === tool.slug) {
				return this.searchStatisticsStore.isConnected
			}

			return !!this.optionsStore.options.webmasterTools[tool.settings[0].option]
		},
		openSearchConsoleSettings () {
			if ('googleSearchConsole' !== this.activeTool) {
				this.activeTool = 'googleSearchConsole'
				return
			}

			this.connect()
		}
	},
	beforeUnmount () {
		window.removeEventListener('resize', this.maybeChangeColumnsPerRow)
	},
	mounted () {
		this.maybeChangeColumnsPerRow()

		this.$nextTick(() => this.tools.forEach(tool => {
			this.getOrder(tool)
			this.getOrder(tool, true)
		}))

		if (this.$route.query.activetool) {
			this.activeTool = this.$route.query.activetool
		}

		window.addEventListener('resize', this.maybeChangeColumnsPerRow)
	}
}
</script>

<style lang="scss">
.aioseo-webmaster-tools {
	.webmaster-tools-description {
		font-size: $font-md;
		margin-bottom: 12px;
	}

	.webmaster-tools-spacer {
		.aioseo-col {
			padding-block: 0;

			div {
				margin: 1px 0 var(--aioseo-gutter);
				border-bottom: 1px solid $border;
			}
		}
	}

	.webmaster-tools-toggles.aioseo-row {
		--aioseo-gutter: 16px;

		display: flex;
		row-gap: 0;
	}

	.tool-toggle {
		font-size: 16px;
		user-select: none;
		margin-bottom: var(--aioseo-gutter);

		> div {
			cursor: pointer;
			height: 165px;
			border: 1px solid $input-border;
			border-radius: 3px;
			padding: 12px;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: space-between;
			color: $black;
			text-align: center;
			position: relative;

			.logo {
				flex: 1;
				padding: 0;
				display: flex;
				align-items: center;
				max-height: calc(100% - 20px);

				.logo-svg {
					max-width: 100%;
					max-height: 60px;
					width: 100%;
					height: 100%;
				}
			}

			&.connected {
				svg.aioseo-circle-check-solid {
					width: 21px;
					height: 21px;
					color: $green;
					position: absolute;
					top: 10px;
					right: 10px;
				}

				&.active {
					svg.aioseo-circle-check-solid {
						top: 9px;
						right: 9px;
					}
				}
			}

			&.active {
				padding: 11px;
				font-weight: 600;
				border: 2px solid $blue;
				box-shadow: 0px 5px 10px rgba(0, 90, 224, 0.1);

				&.connected {
					border-color: $green;
				}
			}
		}
	}

	.tool-settings-slide {
		width: 100%;

		&[aria-expanded="true"] {
			margin-bottom: var(--aioseo-gutter);
		}

		.tool-settings {

			> div {
				padding: $gutter-sm;
				background-color: $box-background;
				border-radius: 3px;
				border-bottom: 1px solid $border;

				&:last-child {
					border-bottom: none;
				}
			}

			.aioseo-input {
				max-width: 440px;
				margin-right: 10px;
			}

			.aioseo-textarea-autosize {
				max-width: 400px;
			}

			.aioseo-button {
				svg.aioseo-external {
					width: 14px;
					height: 14px;
					margin-right: 10px;
				}
			}
		}

		.aioseo-input-container .aioseo-input input {
			height: 40px;
			padding: 10px;
			font-size: 14px;
		}

		.inline-upsell {
			display: inline-flex;
			align-items: center;
			justify-content: space-between;
			margin-top: 16px;
			padding: 8px 12px;
			font-weight: 400;
		}

		.aioseo-alert {
			padding: 8px 12px;
			font-weight: 400;
			margin-top: 16px;
		}

		.tool-settings-microsoft-clarity {

			.aioseo-description + br {
				display: none;
			}
		}
	}

	.aioseo-settings-row .settings-name .name.align {
		line-height: 22px;
		margin-bottom: 12px;
	}
}
</style>