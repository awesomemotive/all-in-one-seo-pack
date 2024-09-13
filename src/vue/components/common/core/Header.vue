<template>
	<div class="aioseo-header">
		<core-upgrade-bar
			v-if="!rootStore.isPro && settingsStore.settings.showUpgradeBar && upgradeBar && rootStore.pong"
		/>

		<core-license-key-bar
			v-if="rootStore.isPro && licenseStore.isUnlicensed && rootStore.pong"
		/>

		<core-api-bar
			v-if="!rootStore.pong"
		/>

		<grid-container
			:full-width="fullWidth"
			:small="small"
		>
			<div class="aioseo-header-content">
				<a
					v-if="licenseStore.isUnlicensed"
					:href="links.utmUrl('header-logo')"
					target="_blank"

				>
					<svg-aioseo-logo />
				</a>
				<svg-aioseo-logo
					v-if="!licenseStore.isUnlicensed"
				/>
				<span
					v-if="pageName"
					class="spacer"
				/>
				<span
					v-if="pageName"
					class="page-name"
				>{{ pageName }}</span>

				<div
					v-if="actions"
					class="header-actions"
			>
					<transition name="fade-percent-circle">
						<core-percent-circle
							v-if="activeScan && 100 !== percentage"
							:percentage="percentage"
							@click.native="toggleCirclePopup"
						/>
					</transition>

					<transition
						name="fade-processing-popup"
					>
						<core-processing-popup
							v-if="showPopup"
							:strings="popupStrings"
							:percentage="percentage"
							@close="toggleCirclePopup"
						/>
					</transition>

					<span
						class="round"
						@click.stop="notificationsStore.toggleNotifications"
					>
						<span class="round number"
							v-if="notificationsStore.activeNotificationsCount"
						>
							{{ notificationsStore.activeNotificationsCount > 9 ? '!' : notificationsStore.activeNotificationsCount }}
						</span>

						<svg-notifications
							@click.stop="notificationsStore.toggleNotifications"
						/>
					</span>

					<span
						class="round"
						@click.stop="toggleModal"
						v-if="helpPanelStore.docs && Object.keys(helpPanelStore.docs).length"
					>
						<svg-circle-question-mark />
					</span>
				</div>
			</div>
		</grid-container>
	</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'

import {
	useHelpPanelStore,
	useLicenseStore,
	useLinkAssistantStore,
	useNotificationsStore,
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import links from '@/vue/utils/links'

import { useScrollAndHighlight } from '@/vue/composables/ScrollAndHighlight'

import CoreApiBar from '@/vue/components/common/core/ApiBar'
import CoreLicenseKeyBar from '@/vue/components/AIOSEO_VERSION/core/LicenseKeyBar'
import CorePercentCircle from '@/vue/components/common/core/PercentCircle'
import CoreProcessingPopup from '@/vue/components/common/core/ProcessingPopup'
import CoreUpgradeBar from '@/vue/components/AIOSEO_VERSION/core/UpgradeBar'
import GridContainer from '@/vue/components/common/grid/Container'
import SvgAioseoLogo from '@/vue/components/common/svg/aioseo/Logo'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import SvgNotifications from '@/vue/components/common/svg/Notifications'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

// We just need to call this to make sure the composable is used.
useScrollAndHighlight()

const helpPanelStore     = useHelpPanelStore()
const licenseStore       = useLicenseStore()
const linkAssistantStore = useLinkAssistantStore()
const notificationsStore = useNotificationsStore()
const rootStore          = useRootStore()
const settingsStore      = useSettingsStore()

defineProps({
	fullWidth : Boolean,
	small     : Boolean,
	pageName  : String,
	actions   : {
		type : Boolean,
		default () {
			return true
		}
	},
	upgradeBar : {
		type : Boolean,
		default () {
			return true
		}
	}
})

const activeScan = ref(null)

const strings = {
	linkAssistantPopup : {
		header      : __('Link suggestions are being processed.', td),
		description : __('Depending on the number of posts being scanned, this process can take some time. You can safely leave this page and check back later.', td)
	},
	searchStatisticsPopup : {
		header      : __('Search statistics are being fetched.', td),
		description : __('Depending on the amount of content on your site, this process can take some time. You can safely leave this page and check back later.', td)
	}
}

const percentage = computed(() => {
	switch (activeScan.value) {
		case 'linkAssistant':
			return linkAssistantStore.suggestionsScan.percent
		default:
			return null
	}
})

const showPopup = computed(() => {
	switch (activeScan.value) {
		case 'linkAssistant':
			return linkAssistantStore.suggestionsScan.showProcessingPopup && 100 !== linkAssistantStore.suggestionsScan.percent
		default:
			return null
	}
})

const popupStrings = computed(() => {
	switch (activeScan.value) {
		case 'linkAssistant':
			return strings.linkAssistantPopup
		default:
			return null
	}
})

const debounce = (fn) => {
	let frame
	return (...params) => {
		if (frame) {
			cancelAnimationFrame(frame)
		}
		frame = requestAnimationFrame(() => {
			fn(...params)
		})
	}
}

const storeScroll = () => {
	document.documentElement.dataset.scroll = window.scrollY
}

const toggleModal = () => {
	const modal = document.getElementById('aioseo-help-modal')
	modal.classList.toggle('visible')
	document.body.classList.toggle('modal-open')
}

const route = useRoute()
const checkForActiveScan = () => {
	if (
		'link-assistant' === rootStore.aioseo.page &&
		addons.isActive('aioseo-link-assistant') &&
		!addons.requiresUpgrade('aioseo-link-assistant') &&
		addons.hasMinimumVersion('aioseo-link-assistant') &&
		('links-report' === route.name || 'overview' === route.name) &&
		100 !== linkAssistantStore.suggestionsScan.percent
	) {
		activeScan.value = 'linkAssistant'
	}
}

const toggleCirclePopup = () => {
	switch (activeScan.value) {
		case 'linkAssistant':
			return linkAssistantStore.toggleProcessingPopup()
		default:
			return null
	}
}

onMounted(() => {
	storeScroll()
	document.addEventListener('scroll', debounce(storeScroll), { passive: true })

	checkForActiveScan()
})
</script>

<style lang="scss">
html:not([data-scroll='0']) {
	.aioseo-header {
		box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);
		transition: box-shadow 0.6s;
	}
}

.aioseo-header {
	position: fixed;
	z-index: 1051;
	top: 0;
	right: 0;
	left: 0;
	background-color: #fff;
	height: var(--aioseo-header-height, 72px);
	color: $black;

	.mascot {
		width: 35px;
		height: auto;
		margin-right: 10px;
	}

	.aioseo-header-content {
		padding: 0;
		display: flex;
		height: 72px;
		align-items: center;
		gap: 4px;

		a:focus {
			box-shadow: none;
		}

		svg.aioseo-logo {
			height: 20px;
		}

		.spacer {
			display: inline-flex;
			width: 20px;
			height: 2px;
			background: $input-border;
			transform: rotate(-70deg);
		}

		.page-name {
			display: inline-flex;
			font-size: 16px;
			font-weight: normal;
			flex: 1 0 auto;
		}

		.header-actions {
			display: flex;

			.round {
				position: relative;
				background-color: $background;
				border-radius: 50%;
				width: 40px;
				height: 40px;
				display: flex;
				align-items: center;
				justify-content: center;
				margin-left: 10px;
				cursor: pointer;
				transition: background-color 0.2s ease;

				svg {
					width: 20px;
					height: 20px;
				}

				&:hover {
					background-color: darken($background, 5%);
				}
			}

			.number {
				position: absolute;
				background-color: $red;
				width: 16px;
				height: 16px;
				font-weight: 600;
				font-size: 10px;
				color: #fff;
				top: -8px;
				left: 50%;
				transform: translate(-50%, 0);
				margin: 0;
				animation: bounce 2s 5;
				will-change: transform;

				&:hover {
					background-color: $red;
				}

				@keyframes bounce {
					0%, 25%, 50%, 75%, 100% {
						transform: translate(-50%, 0);
					}
					40% {
						transform: translate(-50%, -8px);
					}
					60% {
						transform: translate(-50%, -4px);
					}
				}
			}
		}
	}

	.fade-percent-circle-enter-active, .fade-percent-circle-leave-active {
		transition: opacity .5s;
	}
	.fade-percent-circle-enter-from, .fade-percent-circle-leave-to {
		opacity: 0;
	}
}
</style>