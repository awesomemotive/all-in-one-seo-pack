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
					:href="links.getUpsellUrl('header-logo', rootStore.aioseo.page, 'liteUpgrade')"
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
							v-if="activeScan && null !== percentage"
							:percentage="percentage"
							:done="scanCounts.done"
							:total="scanCounts.total"
							:label="scanLabel"
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
							:done="scanCounts.done"
							:total="scanCounts.total"
							:unit="scanUnit"
							:phases="scanPhases"
							:phase="scanPhase"
							@close="toggleCirclePopup"
						/>
					</transition>

					<span
						class="round"
						:title="strings.openNotifications"
						@click.stop="notificationsStore.toggleNotifications"
					>
						<span
							v-if="notificationsStore.activeNotificationsCount"
							class="round dot"
						/>

						<svg-bell
							@click.stop="notificationsStore.toggleNotifications"
						/>
					</span>

					<span
						v-if="newsroomItems.length"
						class="round"
						:title="strings.openNewsroom"
						@click.stop="openNewsroom"
					>
						<span
							v-if="newsroomUnread"
							class="round dot"
						/>

						<svg-megaphone />
					</span>

					<span
						class="round"
						:title="strings.openHelp"
						@click.stop="toggleModal"
						v-if="helpPanelStore.docs && Object.keys(helpPanelStore.docs).length"
					>
						<svg-circle-question-mark />
					</span>
				</div>
			</div>
		</grid-container>

		<core-newsroom-drawer
			:show="showNewsroom"
			:newsroom="rootStore.aioseo.newsroom"
			@close="showNewsroom = false"
		/>
	</div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'

import {
	useAnalyzerStore,
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
import SvgBell from '@/vue/components/common/svg/Bell'
import CoreNewsroomDrawer from '@/vue/components/common/core/NewsroomDrawer'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import SvgMegaphone from '@/vue/components/common/svg/Megaphone'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const SEEN_KEY = 'aioseoNewsroomSeen'

const showNewsroom  = ref(false)
const newsroomUnread = ref(false)

const newsroomItems = computed(() => rootStore.aioseo.newsroom?.items || [])

const newestNewsroomStamp = () => newsroomItems.value.reduce((newest, item) => {
	const stamp = Date.parse(item.date)

	return isNaN(stamp) ? newest : Math.max(newest, stamp)
}, 0)

const openNewsroom = () => {
	showNewsroom.value = true

	const newest = newestNewsroomStamp()
	if (newest) {
		window.localStorage.setItem(SEEN_KEY, String(newest))
	}

	newsroomUnread.value = false
}

onMounted(() => {
	const newest = newestNewsroomStamp()
	const seen   = parseInt(window.localStorage.getItem(SEEN_KEY) || '0', 10)

	newsroomUnread.value = Boolean(newest) && newest > seen
})

// We just need to call this to make sure the composable is used.
useScrollAndHighlight()

const analyzerStore      = useAnalyzerStore()
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
	openNotifications : __('Open Notification Center', td),
	openNewsroom      : __('Open Newsroom', td),
	openHelp          : __('Open Help Center', td),
	laPhases          : {
		links       : __('Finding links', td),
		suggestions : __('Suggesting links', td)
	},
	laUnit             : __('posts scanned', td),
	scanDone           : __('Up to date', td),
	linkAssistantPopup : {
		header      : __('Link suggestions are being processed.', td),
		headerDone  : __('Your link suggestions are up to date.', td),
		description : __('Depending on the number of posts being scanned, this process can take some time. You can safely leave this page and check back later.', td)
	},
	searchStatisticsPopup : {
		header      : __('Search statistics are being fetched.', td),
		description : __('Depending on the amount of content on your site, this process can take some time. You can safely leave this page and check back later.', td)
	},
	seoAnalyzerPopup : {
		header      : __('Site Audit is being processed.', td),
		headerDone  : __('Your Site Audit is up to date.', td),
		description : __('Depending on the amount of content on your site, this process can take some time. You can safely leave this page and check back later.', td)
	}
}

// Link Assistant runs its links scan before its suggestions scan, and the second has nothing to work
// from until the first finishes - so they are one process shown as two steps.
const linkAssistantPhase = computed(() => 100 === linkAssistantStore.linksScan.percent ? 'suggestions' : 'links')

const linkAssistantScan = computed(() => 'links' === linkAssistantPhase.value
	? linkAssistantStore.linksScan
	: linkAssistantStore.suggestionsScan)

const percentage = computed(() => {
	switch (activeScan.value) {
		case 'linkAssistant':
			return linkAssistantScan.value.percent
		case 'seoAnalyzer':
			return analyzerStore.objectsScan.percent
		default:
			return null
	}
})

const showPopup = computed(() => {
	switch (activeScan.value) {
		case 'linkAssistant':
			return linkAssistantStore.suggestionsScan.showProcessingPopup
		case 'seoAnalyzer':
			return analyzerStore.objectsScan.showProcessingPopup
		default:
			return null
	}
})

const scanCounts = computed(() => {
	if ('linkAssistant' === activeScan.value) {
		return { done: linkAssistantScan.value.done, total: linkAssistantScan.value.total }
	}

	return { done: null, total: null }
})

const scanPhases = computed(() => {
	if ('linkAssistant' !== activeScan.value) {
		return []
	}

	return [
		{ key: 'links', label: strings.laPhases.links },
		{ key: 'suggestions', label: strings.laPhases.suggestions }
	]
})

const scanPhase = computed(() => 'linkAssistant' === activeScan.value ? linkAssistantPhase.value : '')

const scanUnit = computed(() => 'linkAssistant' === activeScan.value ? strings.laUnit : '')

const scanLabel = computed(() => {
	if (100 === percentage.value) {
		return strings.scanDone
	}

	if ('linkAssistant' !== activeScan.value) {
		return ''
	}

	return 'links' === linkAssistantPhase.value ? strings.laPhases.links : strings.laPhases.suggestions
})

const popupStrings = computed(() => {
	switch (activeScan.value) {
		case 'linkAssistant':
			return strings.linkAssistantPopup
		case 'seoAnalyzer':
			return strings.seoAnalyzerPopup
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
// Eligibility only: the control stays in the bar once a scan is done, so the percentage does not decide
// whether there is a scan to show.
const checkForActiveScan = () => {
	if (
		'link-assistant' === rootStore.aioseo.page &&
		addons.isActive('aioseo-link-assistant') &&
		!addons.requiresUpgrade('aioseo-link-assistant') &&
		addons.hasMinimumVersion('aioseo-link-assistant') &&
		('links-report' === route.name || 'overview' === route.name)
	) {
		activeScan.value = 'linkAssistant'
	}

	if (
		'seo-analysis' === rootStore.aioseo.page &&
		rootStore.isPro &&
		!licenseStore.isUnlicensed
	) {
		activeScan.value = 'seoAnalyzer'
	}
}

const toggleCirclePopup = () => {
	switch (activeScan.value) {
		case 'linkAssistant':
			return linkAssistantStore.toggleProcessingPopup()
		case 'seoAnalyzer':
			return analyzerStore.toggleProcessingPopup()
		default:
			return null
	}
}

// Watched rather than checked once on mount: the router resolves its first route asynchronously, so at
// mount the name the Link Assistant branch reads is still unset.
watch(() => route.name, () => checkForActiveScan(), { immediate: true })

onMounted(() => {
	storeScroll()
	document.addEventListener('scroll', debounce(storeScroll), { passive: true })
})
</script>

<style lang="scss">
@use 'sass:color';

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
		// The panel hangs off this, so it needs to be the offset parent.
		position: relative;
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

				// The megaphone sits lower and wider in its viewBox than the bell, so at a
				// matched 20px it reads noticeably smaller.
				svg.aioseo-megaphone {
					width: 23px;
					height: 23px;
				}

				&:hover {
					background-color: color.adjust($background, $lightness: -5%);
				}
			}

			.dot {
				position: absolute;
				top: -3px;
				left: 50%;
				width: 9px;
				height: 9px;
				min-width: 0;
				margin: 0;
				border: 2px solid #fff;
				border-radius: 50%;
				background-color: $red;
				transform: translate(-50%, 0);
				animation: bounce 2s 5;
				will-change: transform;

				&:hover {
					background-color: $red;
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