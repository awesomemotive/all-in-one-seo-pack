<template>
	<div
		class="aioseo-tabs"
		:class="{ internal: internal, skinny: skinnyTabs }"
		ref="aioseo-tabs"
	>
		<div
			class="tabs-scroller"
			ref="tabs-scroller"
			v-show="!showMobileMenu || calculateWidth"
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

						<span class="label">{{ tab.name }}</span>

						<span
							v-if="tab.errorCount >= 0"
							class="tab-score"
							:class="getErrorClass(postEditorStore.currentPost.page_analysis.analysis[tab.slug].errors)"
						>
							<svg-ellipse
								v-if="0 < postEditorStore.currentPost.page_analysis.analysis[tab.slug].errors"
								width="8"
							/>

							<svg-circle-check
								v-if="0 === postEditorStore.currentPost.page_analysis.analysis[tab.slug].errors"
								width="12"
							/>

							<span
								v-text="getErrorDisplay(postEditorStore.currentPost.page_analysis.analysis[tab.slug].errors)"
							/>
						</span>

						<span
							v-if="tab.warning && 'sidebar' !== $root._data.screenContext"
							class="warning"
						>
							<svg-circle-information
								width="15"
								height="15"
							/>
						</span>
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
						<router-link
							v-for="(tab, index) in filteredTabs"
							:key="index"
							:to="tab.url"
							@click.native="showMobileTabs = false"
						>
							{{ tab.name }}
						</router-link>
					</template>

					<template
						v-if="active"
					>
						<a
							href="#"
							v-for="(tab, index) in filteredTabs"
							:key="index"
							@click.prevent="$emit('changed', tab.slug) && (showMobileTabs = false)"
						>
							{{ tab.name }}
						</a>
					</template>
				</div>
			</transition-slide>
		</div>

		<div
			class="button-right"
			v-if="showSaveButton"
			ref="tabs-button"
		>
			<slot name="button">
				<base-button
					type="blue"
					size="medium"
					:loading="rootStore.loading"
					@click="processSaveChanges"
				>
					{{ strings.saveChanges }}
				</base-button>
			</slot>
		</div>

		<div class="tabs-extra">
			<slot name="extra" />
		</div>
	</div>
</template>

<script>
import {
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import { merge } from 'lodash-es'
import { useTruSeoScore } from '@/vue/composables'
import { SaveChanges } from '@/vue/mixins/SaveChanges'
import { TruSeoScore } from '@/vue/mixins/TruSeoScore'

import BaseButton from '@/vue/components/common/base/Button'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleInformation from '@/vue/components/common/svg/circle/Information'
import SvgEllipse from '@/vue/components/common/svg/Ellipse'
import TransitionSlide from '@/vue/components/common/transition/Slide'
import { Tab as VarTab, Tabs as VarTabs } from '@varlet/ui'

export default {
	setup () {
		const { strings } = useTruSeoScore()

		return {
			postEditorStore   : usePostEditorStore(),
			rootStore         : useRootStore(),
			composableStrings : strings
		}
	},
	emits      : [ 'changed' ],
	components : {
		BaseButton,
		SvgCaret,
		SvgCircleCheck,
		SvgCircleInformation,
		SvgEllipse,
		TransitionSlide,
		VarTab,
		VarTabs
	},
	mixins : [ SaveChanges, TruSeoScore ],
	props  : {
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
		disableMobile : Boolean
	},
	data () {
		return {
			buttonLoading  : false,
			showMobileMenu : true,
			calculateWidth : false,
			showMobileTabs : false,
			strings        : merge(this.composableStrings, {
				saveChanges : this.$t.__('Save Changes', this.$td)
			})
		}
	},
	computed : {
		activeTab () {
			if (this.active) {
				return this.active
			}

			if (this.$route && this.$route.name) {
				return this.$route.name
			}

			return this.tabs[0]?.slug
		},
		filteredTabs () {
			return this.tabs.filter(t => t.slug !== (this.active ? this.active : (this.$route && this.$route.name ? this.$route.name : '')))
		}
	},
	methods : {
		maybeChangeTab (id) {
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
			const tab = this.tabs.find(t => t.slug === (this.active ? this.active : (this.$route && this.$route.name ? this.$route.name : '')))
			if (tab) {
				return tab.name
			}

			return ''
		},
		maybeShowMobileMenu () {
			if (window.matchMedia('(max-width: 782px)').matches && !this.disableMobile) {
				this.showMobileMenu = true
				return
			}

			let width           = 0
			this.calculateWidth = true
			this.$nextTick(() => {
				width                   = this.$refs['tabs-scroller'].offsetWidth
				this.calculateWidth     = false
				let tabsButtonWidth     = 0
				const tabsButtonElement = this.$refs['tabs-button']
				if (tabsButtonElement) {
					const buttonElement = tabsButtonElement.querySelector('.aioseo-button')
					tabsButtonWidth = buttonElement ? buttonElement.scrollWidth : 0
				}

				if ((width + tabsButtonWidth) > this.$refs['aioseo-tabs'].offsetWidth) {
					this.showMobileMenu = true
					return
				}

				this.showMobileMenu = false
			})
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
.aioseo-app {
	@import '@/vue/assets/scss/tabs';

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
			white-space: pre;
			position: relative;
			overflow: hidden;

			span.ripple {
				position: absolute;
				border-radius: 50%;
				transform: scale(0);
				animation: aioseo-ripple 600ms linear;
				background-color: rgba(0, 0, 0, 0.3);
			}

			@keyframes aioseo-ripple {
				to {
					transform: scale(3);
					opacity: 0;
				}
			}
		}
	}

	.aioseo-tabs {
		display: flex;
		border-bottom: 2px solid $border;
		position: relative;
		margin-bottom:var(--aioseo-gutter);

		.button-right,
		.tabs-extra {
			position: absolute;
			right: 0;
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

		height: 40px;
		margin-top: 20px;
		position: relative;
		user-select: none;
		width: 100%;

		.active-tab {
			--spacing-x: 20px;

			align-items: center;
			color: $blue;
			cursor: pointer;
			display: inline-flex;
			min-height: 100%;
			padding-left: var(--spacing-x);
			position: relative;

			div {
				font-size: var(--mobile-font-size);

				span {
					background-color: $blue;
					bottom: -2px;
					height: 2px;
					left: 0;
					position: absolute;
					right: calc(var(--spacing-x) * -2);
					z-index: 10;
				}
			}

			svg.aioseo-caret {
				--caret-size: 24px;

				height: var(--caret-size);
				left: 100%;
				position: absolute;
				top: calc(50% - var(--caret-size) / 2);
				transform: rotate(180deg);
				transition: transform 0.3s;
				width: var(--caret-size);

				&.rotated {
					transform: rotate(0);
				}
			}
		}

		.tab-dropdown {
			border: 1px solid $border;
			border-top: none;
		}

		.tab-links {
			background: #fff;
			position: relative;
			z-index: 3;
			padding: 8px;
			width: 100%;
			max-width: 300px;

			@media screen and (max-width: 782px) {
				max-width: 100%;
			}

			a {
				font-size: var(--mobile-font-size);
				padding: 10px;
				display: block;
				color: $black;
				text-decoration: none;

				&:hover {
					color: $blue;
				}
			}
		}
	}
}
</style>