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
import {
	useSettingsStore
} from '@/vue/stores'

import SvgAioseoLogoGear from '@/vue/components/common/svg/aioseo/LogoGear'
import SvgClose from '@/vue/components/common/svg/Close'
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
				boldText : this.$t.sprintf(
					'<strong>%1$s %2$s</strong>',
					import.meta.env.VITE_NAME,
					this.$t.__('Free', this.$td)
				),
				url      : this.$links.utmUrl('lite-upgrade-bar'),
				linkText : this.$t.sprintf(
					// Translators: 1 - "Pro".
					this.$t.__('upgrading to %1$s', this.$td),
					'Pro'
				)
			}
		}
	},
	computed : {
		link () {
			return this.$t.sprintf(
				'<strong><a href="%1$s" target="_blank" class="text-white">%2$s</a> <a href="%1$s" target="_blank" class="text-white upgrade-arrow">&rarr;</a></strong>',
				this.strings.url,
				this.strings.linkText
			)
		},
		upgradeText () {
			return this.$t.sprintf(
				// Translators: 1 - The plugin name ("All in One SEO"), 2 - "upgrading to Pro".
				this.$t.__('You\'re using %1$s. To unlock more features, consider %2$s', this.$td),
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