<template>
	<div class="aioseo-upgrade-bar">
		<div class="upgrade-text">
			<svg-aioseo-logo-gear />

			<div v-html="upgradeText" />
		</div>

		<svg-close
			@click="processHideUpgradeBar"
		/>
	</div>
</template>

<script>
import links from '@/vue/utils/links'
import {
	useSettingsStore
} from '@/vue/stores'

import SvgAioseoLogoGear from '@/vue/components/common/svg/aioseo/LogoGear'
import SvgClose from '@/vue/components/common/svg/Close'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			settingsStore : useSettingsStore()
		}
	},
	components : {
		SvgAioseoLogoGear,
		SvgClose
	},
	data () {
		return {
			strings : {
				boldText : sprintf(
					'<strong>%1$s %2$s</strong>',
					import.meta.env.VITE_NAME,
					__('Free', td)
				),
				url      : links.getUpsellUrl('lite-upgrade-bar', null, 'liteUpgrade'),
				linkText : sprintf(
					// Translators: 1 - "Pro".
					__('upgrading to %1$s', td),
					'Pro'
				)
			}
		}
	},
	computed : {
		link () {
			return sprintf(
				'<strong><a href="%1$s" target="_blank" class="text-white">%2$s</a> <a href="%1$s" target="_blank" class="text-white upgrade-arrow">&rarr;</a></strong>',
				this.strings.url,
				this.strings.linkText
			)
		},
		upgradeText () {
			return sprintf(
				// Translators: 1 - The plugin name ("All in One SEO"), 2 - "upgrading to Pro".
				__('You\'re using %1$s. To unlock more features, consider %2$s', td),
				this.strings.boldText,
				this.link
			)
		}
	},
	methods : {
		processHideUpgradeBar () {
			document.body.classList.remove('aioseo-has-bar')
			this.settingsStore.hideUpgradeBar()
		}
	},
	mounted () {
		document.body.classList.add('aioseo-has-bar')
	}
}
</script>

<style lang="scss">
.aioseo-app {
	.aioseo-upgrade-bar {
		display: flex;
		align-items: center;
		height: 40px;
		background-color: $green;
		justify-content: center;
		color: #fff;
		font-size: 14px;
		padding: 0 20px;

		.upgrade-text {
			display: flex;
			align-items: center;
			flex: 1;
			justify-content: center;
		}

		.upgrade-arrow {
			font-size: 15px;
			text-decoration: none;
			&:hover {
				text-decoration: none;
			}
		}

		strong {
			font-weight: 600;
		}

		svg.aioseo-logo-gear {
			width: 20px;
			height: 20px;
			min-width: 20px;
			margin-right: 14px;
		}

		svg.aioseo-close {
			cursor: pointer;
			width: 12px;
			height: 12px;
		}

		a {
			color: #fff;
			text-decoration: underline;

			&:hover {
				text-decoration: none;
			}
		}

		@media screen and (max-width: 782px) {
			padding: 0 10px;
			height: 60px;
		}
	}
}
</style>