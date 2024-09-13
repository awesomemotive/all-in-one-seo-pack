<template>
	<div
		class="aioseo-flyout-menu"
		:class="[
			{ 'is-open': isOpen },
			{ 'will-open': willOpen }
		]"
		ref="aioseo-flyout-menu"
	>
		<div class="aioseo-flyout-menu-items">
			<a
				v-for="(item, index) in items"
				:key="index"
				:href="item.url"
				target="_blank"
				:style="{ transitionDelay: `${items.length - index}00ms` }"
				@mouseover="hovering = index"
				@mouseleave="hovering = null"
			>
				<span class="aioseo-flyout-menu-label">{{ item.label }}</span>
				<div class="aioseo-flyout-menu-label icon">
					<component :is="item.icon" :active="index === hovering"/>
				</div>
			</a>
		</div>
		<div
			class="aioseo-flyout-menu-button"
			@click="isOpen = !isOpen"
			@mouseover="willOpen = true"
			@mouseleave="willOpen = false"
		>
			<span class="aioseo-flyout-menu-label">{{ strings.quickLinks }}</span>
			<svg-flyout-dannie-up v-show="isOpen" />
			<svg-flyout-dannie v-show="!isOpen" />
		</div>
	</div>
</template>

<script>
import links from '@/vue/utils/links'
import {
	useRootStore
} from '@/vue/stores'

import SvgFlyoutDannie from '@/vue/components/common/svg/flyout-dannie/Index'
import SvgFlyoutDannieUp from '@/vue/components/common/svg/flyout-dannie/Up'
import SvgLightBulb from '@/vue/components/common/svg/LightBulb'
import SvgMessage from '@/vue/components/common/svg/Message'
import SvgStar from '@/vue/components/common/svg/Star'
import SvgSupport from '@/vue/components/common/svg/Support'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		SvgFlyoutDannie,
		SvgFlyoutDannieUp,
		SvgLightBulb,
		SvgMessage,
		SvgStar,
		SvgSupport
	},
	data () {
		return {
			isOpen   : false,
			willOpen : false,
			hovering : null,
			strings  : {
				quickLinks : __('Quick Links', td)
			}
		}
	},
	computed : {
		items () {
			const items = [
				{
					label : __('Support & Docs', td),
					url   : links.utmUrl(
						'flyout-menu',
						'support-docs',
						links.docLinks.home
					),
					icon : 'svg-support'
				},
				{
					label : __('Join Our Community', td),
					url   : links.utmUrl(
						'flyout-menu',
						'join-our-community',
						'plugin/facebook/'
					),
					icon : 'svg-message'
				},
				{
					label : __('Suggest a Feature', td),
					url   : links.utmUrl(
						'flyout-menu',
						'suggest-a-feature',
						'suggest-a-feature/'
					),
					icon : 'svg-light-bulb'
				}
			]

			if (!this.rootStore.isPro && this.rootStore.pong) {
				items.unshift({
					label : sprintf(
						// Translators: 1 - The plugin short name ("AIOSEO"), 2 - "Pro" string.
						__('Upgrade to %1$s %2$s', td),
						import.meta.env.VITE_SHORT_NAME,
						'Pro'
					),
					url : links.utmUrl(
						'flyout-menu',
						'upgrade-to-pro'
					),
					icon : 'svg-star'
				})
			}

			return items
		}
	},
	methods : {
		documentClick (event) {
			if (!this.isOpen) {
				return
			}

			const element = this.$refs['aioseo-flyout-menu']
			if (element && (element === event.target || element.contains(event.target))) {
				return
			}

			this.isOpen = false
		}
	},
	mounted () {
		this.$nextTick(function () {
			document.addEventListener('click', this.documentClick)
		})
	},
	beforeUnmount () {
		document.removeEventListener('click', this.documentClick)
	}
}
</script>

<style lang="scss">
.aioseo-flyout-menu {
	position: fixed;
	z-index: 1000;
	right: 20px;
	bottom: 40px;

	&-label {
		font-weight: 600;
		font-size: 12px;
		line-height: 15px;

		color: $black;
		background: $white;

		border: 1px solid $gray;
		box-sizing: border-box;
		box-shadow: 0.5px 0.5px 10px $placeholder-color;
		border-radius: 80px;

		transition: all 0.05s ease;
	}

	&-button {
		display: flex;
		align-items: center;
		justify-content: flex-end;
		cursor: pointer;

		> span {
			position: relative;
			z-index: 0;
			padding: 8px 16px;
			margin-right: -100px;

			transition: all 0.2s ease;
			opacity: 0;
			transform: scale(0.5)
		}
		svg {
			position: relative;
			z-index: 1;

			background: $white;
			border: 2px solid #004F9D;
			box-sizing: border-box;
			box-shadow: 0.5px 0.5px 10px $placeholder-color;
			border-radius: 70px;
		}
		&:hover {
			> span {
				opacity: 1;
				transform: scale(1);
				margin-right: 12px;
			}
		}
	}

	&-items {
		display: none;
		a {
			display: flex;
			align-items: center;
			justify-content: flex-end;
			margin-bottom: 12px;

			text-decoration: none;
			transition: all 0.2s ease;
			opacity: 0;
			transform: translateX(100%);

			span {
				flex-shrink: 0;
				padding: 8px 16px;
			}

			.icon {
				width: 40px;
				height: 40px;
				margin-left: 8px;
				display: flex;
				justify-content: center;
				align-items: center;

				svg {
					max-width: 60%;
					max-height: 60%;
					transition: all 0.2s ease;

					@supports (-webkit-appearance: none) {
						min-height: 20px;
						min-width: 20px;
					}

					path,
					g {
						will-change: transform, opacity;
						transition-property: transform, opacity;
						transition-duration: 0.5s;
						transition-timing-function: ease;
					}
				}
			}

			&:hover {
				.aioseo-flyout-menu-label {
					border-color: $blue3;
					color: $blue3;
					box-shadow: 0.5px 0.5px 10px $placeholder-color, inset 0 0 0 1px $blue3;
				}

				.icon {
					svg {
						max-width: 70%;
						max-height: 70%;
					}
				}
			}
		}
	}

	&.is-open &-items {
		display: block;

		a {
			opacity: 1;
			transform: translateX(0%)
		}
	}

	&.is-open &-items,
	&.will-open &-items {
		margin-bottom: 20px;
		margin-right: 8px;
		display: block;
	}
}
</style>