<template>
	<div class="aioseo-webmaster-tools">
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
import {
	useIndexNowStore,
	useOptionsStore,
	usePluginsStore,
	useRootStore
} from '@/vue/stores'

import { MetaTag } from '@/vue/mixins/MetaTag'
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseEditor from '@/vue/components/common/base/Editor'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import BaseTextarea from '@/vue/components/common/base/Textarea'
import CoreAlert from '@/vue/components/common/core/alert/Index'
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
export default {
	setup () {
		return {
			indexNowStore : useIndexNowStore(),
			optionsStore  : useOptionsStore(),
			pluginsStore  : usePluginsStore(),
			rootStore     : useRootStore()
		}
	},
	components : {
		BaseCheckbox,
		BaseEditor,
		BaseRadioToggle,
		BaseTextarea,
		CoreAlert,
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
	mixins : [ MetaTag ],
	data () {
		return {
			heightOkay    : false,
			activeTool    : null,
			columnsPerRow : 4,
			strings       : {
				enterVerificationCode                : this.$t.__('Enter your verification codes below to activate webmaster tools.', this.$td),
				miscellaneousVerification            : this.$t.__('Miscellaneous Verification', this.$td),
				miscellaneousVerificationDescription : this.$t.sprintf(
					// Translators: 1 - "<head></head>".
					this.$t.__('The code above will be added between the %1$s tags on every page on your website.', this.$td),
					'<em>&lt;head&gt; &lt/head&gt;</em>'
				),
				webmasterToolsVerification : this.$t.__('Webmaster Tools Verification', this.$td),
				unfilteredHtmlError        : this.$t.sprintf(
					// Translators: 1 - Learn more link.
					this.$t.__('Your user account role does not have access to edit this field. %1$s', this.$td),
					this.$links.getDocLink(this.$constants.GLOBAL_STRINGS.learnMore, 'unfilteredHtml', true)
				)
			}
		}
	},
	computed : {
		tools () {
			return [
				{
					slug      : 'googleSearchConsole',
					name      : this.$t.__('Google Search Console', this.$td),
					svg       : 'svg-logo-google',
					component : 'GoogleSearchConsoleSettings',
					settings  : [
						{
							option      : 'google',
							label       : this.$t.__('Google Verification Code', this.$td),
							description : this.$t.sprintf(
								// Translators: 1 - "Google Search Console".
								this.$t.__('Get your Google verification code in %1$s.', this.$td),
								this.$links.getDocLink(this.$t.__('Google Search Console', this.$td), 'googleSearchConsole')
							)
						}
					]
				},
				{
					slug     : 'bing',
					name     : this.$t.__('Bing Webmaster Tools', this.$td),
					svg      : 'svg-logo-microsoft-bing',
					settings : [
						{
							option      : 'bing',
							label       : this.$t.__('Bing Verification Code', this.$td),
							description : this.$t.sprintf(
								// Translators: 1 - "Bing Webmaster Tools".
								this.$t.__('Get your Bing verification code in %1$s.', this.$td),
								this.$links.getDocLink(this.$t.__('Bing Webmaster Tools', this.$td), 'bingWebmasterVerification')
							)
						}
					]
				},
				{
					slug     : 'yandex',
					name     : this.$t.__('Yandex Webmaster Tools', this.$td),
					svg      : 'svg-logo-yandex',
					settings : [
						{
							option      : 'yandex',
							label       : this.$t.__('Yandex Verification Code', this.$td),
							description : this.$t.sprintf(
								// Translators: 1 - "Yandex Webmaster Tools".
								this.$t.__('Get your Yandex verification code in %1$s.', this.$td),
								this.$links.getDocLink(this.$t.__('Yandex Webmaster Tools', this.$td), 'yandexWebmasterVerification')
							)
						}
					]
				},
				{
					slug     : 'baidu',
					name     : this.$t.__('Baidu Webmaster Tools', this.$td),
					svg      : 'svg-logo-baidu',
					settings : [
						{
							option      : 'baidu',
							label       : this.$t.__('Baidu Verification Code', this.$td),
							description : this.$t.sprintf(
								// Translators: 1 - "Baidu Webmaster Tools".
								this.$t.__('Get your Baidu verification code in %1$s.', this.$td),
								this.$links.getDocLink(this.$t.__('Baidu Webmaster Tools', this.$td), 'baiduWebmasterVerification')
							)
						}
					]
				},
				{
					slug     : 'pinterest',
					name     : this.$t.__('Pinterest Site Verification', this.$td),
					svg      : 'svg-logo-pinterest',
					settings : [
						{
							option      : 'pinterest',
							label       : this.$t.__('Pinterest Verification Code', this.$td),
							description : this.$t.sprintf(
								// Translators: 1 - "Pinterest account".
								this.$t.__('Get your Pinterest verification code in your %1$s.', this.$td),
								this.$links.getDocLink(this.$t.__('Pinterest account', this.$td), 'pinterestSiteVerification')
							)
						}
					]
				},
				{
					slug      : 'indexNow',
					name      : this.$t.__('IndexNow', this.$td),
					svg       : 'svg-logo-index-now',
					component : 'IndexNowSettings',
					settings  : [
						{
							option      : 'apiKey',
							label       : this.$t.__('IndexNow API Key', this.$td),
							description : this.$t.sprintf(
								// Translators: 1 - Learn more link.
								this.$t.__('You can manually set an API key here, but if left blank a new one will be auto-generated. %1$s', this.$td),
								this.$links.getDocLink(this.$constants.GLOBAL_STRINGS.learnMore, 'indexNow', true)
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
							label  : this.$t.sprintf(
								// Translators: 1 - "Clarity".
								this.$t.__('%1$s Project ID', this.$td),
								'Clarity'
							),
							description : this.$t.sprintf(
								// Translators: 1 - "Clarity", 2 - Learn more link.
								this.$t.__('%1$s helps you understand how users interact with your website through heatmaps and session recordings. %2$s', this.$td),
								'Clarity',
								this.$links.getDocLink(this.$constants.GLOBAL_STRINGS.learnMore, 'microsoftClarityDocumentation', true)
							)
						}
					]
				},
				{
					slug      : 'googleAnalytics',
					name      : this.$t.__('Google Analytics', this.$td),
					svg       : 'svg-logo-google-analytics',
					component : 'GoogleAnalyticsSettings',
					settings  : [
						{
							option      : 'gtmContainerId',
							label       : this.$t.__('Google Tag Manager Container ID', this.$td),
							pro         : true,
							placeholder : 'GTM-XXXXXX',
							description : this.$t.sprintf(
								// Translators: 1 - "Google Tag Manager account".
								this.$t.__('Get your Google Tag Manager ID in your %1$s.', this.$td),
								this.$links.getDocLink(this.$t.__('Google Tag Manager account', this.$td), 'gtmContainerId')
							)
						}
					]
				}
			]
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
				return this.optionsStore.options.deprecated.webmasterTools.googleAnalytics[tool.settings[0].option] && !this.pluginsStore.plugins.miLite.activated
			}

			return !!this.optionsStore.options.webmasterTools[tool.settings[0].option]
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