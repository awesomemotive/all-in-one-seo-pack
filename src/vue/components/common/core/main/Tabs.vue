<template>
	<div
		class="aioseo-tabs"
		:class="{ internal: internal, skinny: skinnyTabs }"
		ref="aioseo-tabs"
	>
		<div
			class="tabs-scroller"
			:class="{ 'visually-hidden': showMobileMenu, 'is-scrolling': showScrollingTabs }"
			ref="tabs-scroller"
		>
			<var-tabs
				:active="activeTab"
				@click="maybeChangeTab"
				ref="var-tabs"
			>
				<var-tab
					v-for="(tab, index) in tabs"
					:name="tab.slug"
					:key="index"
				>
					<slot name="var-tab" :tab="tab">
						<slot name="var-tab-icon" :tab="tab" />

						<span class="tab-label">
							{{ tab.name }}
						</span>

						<slot name="after-label" :tab="tab"></slot>

						<span
							v-if="tab.warning && 'sidebar' !== $root.$data.screenContext"
							class="warning"
						>
							<svg-circle-information
								width="15"
								height="15"
							/>
						</span>

						<template
							v-if="tab.label"
						>
							<span
								v-if="'pro' === tab.label"
								class="label pro-badge"
							>
								<core-pro-badge />
							</span>

							<span
								v-if="'new' === tab.label"
								class="label new"
							>
								{{ strings.new }}
							</span>

							<span
								v-if="'beta' === tab.label"
								class="label beta"
							>
								{{ strings.beta }}
							</span>

							<span
								v-if="'coming-soon' === tab.label"
								class="label coming-soon"
							>
								{{ strings.comingSoon }}
							</span>
						</template>
					</slot>
				</var-tab>
			</var-tabs>
		</div>

		<div
			v-if="showMobileMenu"
			class="aioseo-mobile-tabs"
		>
			<div
				class="active-tab"
				@click="showMobileTabs = !showMobileTabs"
			>
				<div>
					{{ getActiveTabName() }}
					<svg-caret
						@click.stop="showMobileTabs = !showMobileTabs"
						:class="{ rotated: !showMobileTabs }"
					/>
					<span class="tab-indicator"></span>
				</div>
			</div>

			<transition-slide
				:active="showMobileTabs"
				class="tab-dropdown"
			>
				<div class="tab-links">
					<template
						v-if="!active"
					>
						<component
							:is="!active ? 'router-link' : 'a'"
							v-for="(tab, index) in tabs"
							:key="index"
							:to="tab.url"
							class="tab-link"
							:class="{ current: tab.slug === activeTab }"
							@click.native="showMobileTabs = false"
						>
							{{ tab.name }}

							<span
								v-if="'new' === tab.label"
								class="new"
							>
								{{ strings.new }}
							</span>
						</component>
					</template>

					<template
						v-if="active"
					>
						<a
							href="#"
							v-for="(tab, index) in tabs"
							:key="index"
							class="tab-link"
							:class="{ current: tab.slug === activeTab }"
							@click.prevent="clickMobileTabs(tab.slug)"
						>
							{{ tab.name }}
						</a>
					</template>
				</div>
			</transition-slide>
		</div>

		<div
			class="button-right"
			:class="{ 'has-custom-button': !!$slots.button }"
			v-if="showSaveButton"
			ref="tabs-button"
		>
			<slot name="button">
				<base-button
					type="blue"
					size="medium"
					:loading="rootStore.loading"
					@click="processSaveChanges(route.name)"
				>
					{{ strings.saveChanges }}
				</base-button>
			</slot>
		</div>

		<div
			class="tabs-extra"
			ref="tabs-extra"
		>
			<slot name="extra" />
		</div>
	</div>
</template>

<script>
import { getCurrentInstance } from 'vue'
import { useRoute } from 'vue-router'

import {
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import { useSaveChanges } from '@/vue/composables/SaveChanges'

import BaseButton from '@/vue/components/common/base/Button'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgCircleInformation from '@/vue/components/common/svg/circle/Information'
import TransitionSlide from '@/vue/components/common/transition/Slide'
import VarTab from '@varlet/ui/es/tab'
import VarTabs from '@varlet/ui/es/tabs'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'changed' ],
	setup () {
		const app = getCurrentInstance()

		let route = { name: '' }
		if (!app?.root?.data?.screenContext) {
			route = useRoute()
		}

		const { processSaveChanges } = useSaveChanges()

		return {
			postEditorStore : usePostEditorStore(),
			processSaveChanges,
			rootStore       : useRootStore(),
			route
		}
	},
	components : {
		BaseButton,
		CoreProBadge,
		SvgCaret,
		SvgCircleInformation,
		TransitionSlide,
		VarTab,
		VarTabs
	},
	props : {
		tabs : {
			type     : Array,
			required : true
		},
		skinnyTabs     : Boolean,
		active         : String,
		showSaveButton : {
			type : Boolean,
			default () {
				return true
			}
		},
		internal      : Boolean,
		disableMobile : Boolean,
		disableTabs   : Boolean
	},
	data () {
		return {
			buttonLoading     : false,
			showMobileMenu    : false,
			showScrollingTabs : false,
			showMobileTabs    : false,
			strings           : {
				saveChanges : __('Save Changes', td),
				new         : __('NEW!', td),
				beta        : __('BETA!', td),
				comingSoon  : __('COMING SOON!', td)
			}
		}
	},
	watch : {
		activeTab () {
			this.$nextTick(() => this.maybeShowMobileMenu())
		}
	},
	computed : {
		activeTab () {
			if (this.active) {
				return this.active
			}

			if (this.route && this.route.name) {
				return this.route.name
			}

			return this.tabs[0]?.slug
		}
	},
	methods : {
		clickMobileTabs (slug) {
			this.$emit('changed', slug)
			this.showMobileTabs = false
		},
		maybeChangeTab (id) {
			if (this.disableTabs) {
				return
			}

			if (this.active) {
				this.$emit('changed', id)

				return
			}

			const tab = this.tabs.find(t => t.slug === id)
			if (tab) {
				this.$router.push(tab.url)
			}
		},
		getActiveTabName () {
			const tab = this.tabs.find(t => t.slug === (this.active ? this.active : (this.route && this.route.name ? this.route.name : '')))
			if (tab) {
				return tab.name
			}

			return ''
		},
		maybeShowMobileMenu () {
			if (this.disableMobile) {
				return
			}

			if (window.matchMedia('(max-width: 782px)').matches) {
				this.showMobileMenu    = true
				this.showScrollingTabs = false

				return
			}

			const tabsContainer = this.$refs['aioseo-tabs']
			const tabsScroller  = this.$refs['tabs-scroller']
			if (!tabsContainer || !tabsScroller) {
				return
			}

			const tabsWidth = this.measureTabsWidth(tabsScroller)

			let buttonWidth = 0,
				extraWidth  = 0

			// Get button width.
			const buttonElement = this.$refs['tabs-button']
			if (buttonElement) {
				const button = buttonElement.querySelector('.aioseo-button')
				buttonWidth  = button ? button.offsetWidth : 0
			}

			// Get extra slot width.
			const extraElement = this.$refs['tabs-extra']
			if (extraElement) {
				extraWidth = extraElement.offsetWidth
			}

			// Check if content fits in container.
			const totalNeeded    = tabsWidth + buttonWidth + extraWidth
			const availableWidth = tabsContainer.offsetWidth

			// Tabs that do not fit are scrolled rather than hidden. Above the mobile breakpoint there is a
			// pointer to scroll with, and putting eight tabs behind a dropdown on a laptop is a downgrade
			// from tabs; below it, the list is the only thing that works, which the branch above handles.
			this.showMobileMenu    = false
			this.showScrollingTabs = totalNeeded > availableWidth
		},
		measureTabsWidth (scroller) {
			// The scroller itself is overflow: clip with min-width: 0, so its own scrollWidth can never
			// exceed its clientWidth - which is why the old comparison against its parent never fired.
			const wrap = scroller.querySelector('.var-tabs__tab-wrap')
			if (wrap && wrap.scrollWidth) {
				return wrap.scrollWidth
			}

			const tabs = scroller.querySelectorAll('.var-tab')
			if (!tabs.length) {
				return scroller.scrollWidth
			}

			let sum = 0
			tabs.forEach(tab => (sum += tab.offsetWidth))

			return sum
		},
		createRipple (event) {
			const button   = event.currentTarget
			const circle   = document.createElement('span')
			const diameter = Math.max(button.clientWidth, button.clientHeight)
			const radius   = diameter / 2
			const offset   = button.getBoundingClientRect()

			circle.style.width = circle.style.height = `${diameter}px`
			circle.style.left = `${event.clientX - (offset.left + radius)}px`
			circle.style.top = `${event.clientY - (offset.top + radius)}px`

			circle.classList.add('ripple')

			const ripple = button.getElementsByClassName('ripple')[0]

			if (ripple) {
				ripple.remove()
			}

			button.appendChild(circle)
		}
	},
	beforeMount () {
		window.addEventListener('resize', this.maybeShowMobileMenu)
	},
	async mounted () {
		// Occasionally the tab indicator doesn't show up on the first load. This is a hack to fix that.
		this.$nextTick(() => {
			this.maybeShowMobileMenu()

			const tabs      = this.$refs['aioseo-tabs']
			const activeTab = tabs.querySelector('.var-tab--active')
			const indicator = tabs.querySelector('.var-tabs__indicator')
			if (!activeTab || !indicator) {
				return
			}

			// Set initial indicator width.
			setTimeout(() => {
				if ('0px' !== indicator.style.width) {
					return
				}

				indicator.style.width     = `${activeTab.offsetWidth}px`
				indicator.style.transform = `translateX(${activeTab.offsetLeft}px)`
			}, 300)

			// Prevent stupid magic from resetting the indicator width to 0.
			const mutationObserver = new MutationObserver(() => {
				if ('0px' !== indicator.style.width) {
					return
				}

				indicator.style.width     = `${activeTab.offsetWidth}px`
				indicator.style.transform = `translateX(${activeTab.offsetLeft}px)`
			})

			mutationObserver.observe(indicator, {
				attributes : true
			})

			const rippleTabs = tabs.querySelectorAll('.var-tab')
			if (rippleTabs.length) {
				rippleTabs.forEach(tab => {
					tab.addEventListener('click', this.createRipple)
				})
			}
		})
	},
	beforeUnmount () {
		window.removeEventListener('resize', this.maybeShowMobileMenu)
	}
}
</script>

<style lang="scss">
@use '@/vue/assets/scss/tabs';

.aioseo-app {
	.aioseo-tabs {
		display: flex;
		align-items: center;

		--tabs-item-horizontal-height: 52px;
		--tabs-item-vertical-height: 66px;
		--tabs-radius: 2px;
		--tabs-padding: 0;
		--tabs-indicator-size: 2px;
		--tabs-indicator-background: #{$blue};
		--tabs-background: none;
		--tabs-indicator-inner-size: 100%;
		--color-text-disabled: #8c8f9a;
		--tab-padding: 18px;
		--tab-active-color: #{$blue};
		--tab-inactive-color: #{$black};
		--tab-disabled-color: var(--color-text-disabled);
		--tab-font-size: 14px;
		--tab-line-height: 22px;

		&.internal {
			--tab-padding: 25px;
			margin-bottom: 0;
		}

		&.skinny {
			--tab-padding: 16px;
		}
	}

	.var-tabs {
		margin-bottom: -2px;

		.var-tab {
			font-weight: $font-bold;
			position: relative;
			overflow: hidden;

			.tab-label {
				flex-shrink: 0;
			}

			span.ripple {
				position: absolute;
				border-radius: 50%;
				transform: scale(0);
				animation: aioseo-ripple 600ms linear;
				background-color: rgba(0, 0, 0, 0.3);
			}

			span.label {
				padding-left: 5px;

				&.new,
				&.beta,
				&.coming-soon {
					vertical-align: super;
					font-size: 10px;
					display: inline-block;
					margin-top: -5px;
				}

				&.new {
					color: #df2a4a;
				}

				&.beta {
					color: #d57201;
				}

				&.coming-soon {
					color: $blue;
				}
			}

			@keyframes aioseo-ripple {
				to {
					transform: scale(3);
					opacity: 0;
				}
			}
		}

		.var-tabs--layout-horizontal-indicator {
			/*rtl:ignore*/
			left: 0 !important;
			/*rtl:ignore*/
			right: initial !important;
		}
	}

	.aioseo-tabs {
		display: flex;
		border-bottom: 2px solid $border;
		position: relative;
		margin-bottom:var(--aioseo-gutter);

		.tabs-scroller {
			flex-grow: 0;
			min-width: 0;
			overflow-clip-margin: 2px;
			overflow: clip;

			&.visually-hidden {
				visibility: hidden;
				position: absolute;
				pointer-events: none;
			}

			// The state between "they all fit" and "hide them in a menu". The tabs could always be
			// scrolled here - Varlet's own wrapper handles that - but nothing said so, which is the
			// actual bug. A slim scrollbar is both the signal and the control.
			&.is-scrolling {
				overflow: visible;

				.var-tabs__tab-wrap {
					scrollbar-width: thin;
					scrollbar-color: $gray2 transparent;
					padding-bottom: 3px;

					&::-webkit-scrollbar {
						height: 3px;
					}

					&::-webkit-scrollbar-thumb {
						border-radius: 3px;
						background: $gray2;
					}

					&::-webkit-scrollbar-track {
						background: transparent;
					}
				}
			}
		}

		// Fades the trailing edge so a cut-off tab reads as "there is more" rather than as the end of the
		// list. Takes no pointer events, so scrolling still works through it.
		&:has(.tabs-scroller.is-scrolling)::after {
			content: '';
			position: absolute;
			top: 0;
			right: 0;
			bottom: 1px;
			width: 40px;
			background: linear-gradient(to right, rgba(255, 255, 255, 0), #fff);
			pointer-events: none;
		}

		.button-right,
		.tabs-extra {
			position: absolute;
			right: 0;
		}

		// The mobile tab picker spans the full width, and these sit on top of it. The save button at the
		// foot of the page is unaffected, so nothing becomes unreachable. A consumer that fills the slot
		// is left alone: its buttons have no equivalent anywhere else on the screen.
		@media screen and (max-width: 782px) {
			.button-right:not(.has-custom-button) {
				display: none;
			}
		}

		.tab-score {
			display: inline-flex;
			font-weight: 700;
			line-height: normal;
			padding-left: 12px;
			font-size: 12px;
			&.green {
				color: $green;
			}
			&.orange {
				color: $orange;
			}
			&.red {
				color: $red;
			}
			svg {
				display: inline;
				margin-right: 4px;
			}
		}

		.warning {
			color: $orange !important;
			svg {
				position: relative;
				top: 2px;
				left: 5px;
				display: inline;
				color: $orange !important;
			}
		}
	}

	.aioseo-mobile-tabs {
		--mobile-font-size: 14px;

		position: relative;
		user-select: none;
		width: 100%;

		.active-tab {
			--spacing-x: 14px;

			align-items: center;
			border: 1px solid $input-border;
			border-radius: 4px;
			background: #fff;
			color: $black;
			cursor: pointer;
			display: flex;
			justify-content: space-between;
			min-height: 46px;
			padding: 0 var(--spacing-x);
			position: relative;

			div {
				display: flex;
				align-items: center;
				justify-content: space-between;
				width: 100%;
				font-size: var(--mobile-font-size);
				font-weight: 600;

				// The underline belonged to the old tab-shaped trigger; a field does not carry one.
				span.tab-indicator {
					display: none;
				}
			}

			svg.aioseo-caret {
				--caret-size: 20px;

				flex: 0 0 auto;
				height: var(--caret-size);
				position: static;
				transform: rotate(180deg);
				transition: transform 0.3s;
				width: var(--caret-size);
				color: $placeholder-color;

				&.rotated {
					transform: rotate(0);
				}
			}
		}

		.tab-dropdown {
			position: relative;
			z-index: 3;
			margin-top: 6px;
		}

		.tab-links {
			background: #fff;
			border: 1px solid $input-border;
			border-top: none;
			position: relative;
			z-index: 3;
			border-radius: 4px;
			box-shadow: 0 10px 26px -14px rgba(20, 27, 56, .3);
			overflow: hidden;
			width: 100%;
			max-width: 300px;

			@media screen and (max-width: 782px) {
				max-width: 100%;
			}

			a {
				font-size: var(--mobile-font-size);
				display: flex;
				align-items: center;
				justify-content: space-between;
				min-height: 46px;
				padding: 10px 14px;
				border-top: 1px solid $gray2;
				color: $black;
				text-decoration: none;

				&:first-child {
					border-top: 0;
				}

				&:hover {
					background: $blue4;
					color: $blue;
				}

				// Where you are, said in the list rather than only in the trigger.
				&.current {
					background: $blue4;
					font-weight: 700;
					color: $blue;
				}

				.new {
					color: $red;
					vertical-align: super;
					font-size: 10px;
					display: inline-block;
					margin-top: -5px;
				}
			}
		}
	}
}
</style>