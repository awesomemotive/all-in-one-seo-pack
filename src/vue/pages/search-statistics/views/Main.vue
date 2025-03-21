<template>
	<core-main
		:page-name="strings.pageName"
		:exclude-tabs="excludeTabs"
		:showTabs="!excludeTabs.includes($route.name)"
		:containerClasses="containerClasses"
	>
		<template #extra>
			<base-date-picker
				v-if="showDatePicker"
				@change="onDateChange"
				@updated="rolling => highlightShortcut(rolling)"
				:clearable="false"
				:defaultValue="defaultRange"
				:defaultRolling="searchStatisticsStore.rolling"
				:isDisabledDate="isDisabledDate"
				:shortcuts="datepickerShortcuts"
				size="small"
			/>
		</template>

		<div>
			<authentication-alert />

			<template
				v-if="searchStatisticsStore.shouldShowSampleReports"
			>
				<core-alert
					class="description sample-data-alert"
					type="yellow"
					@close-alert="() => {}"
				>
					{{ strings.sampleDataAlert }}

					<br />
					<br />

					<base-button
						v-if="showSampleDataUnlockCta"
						type="green"
						size="small"
						@click="connect"
						:loading="loading"
					>
						{{ strings.ctaButtonText }}
					</base-button>

					<base-button
						v-if="!showSampleDataUnlockCta"
						tag="a"
						:href="links.getPricingUrl('search-statistics', 'search-statistics-demo-upsell', $route.name)"
						target="_blank"
						type="green"
						size="small"
						@click="searchStatisticsStore.showSampleReports"
						:loading="loading"
					>
						{{ strings.ctaUnlockButtonText }}
					</base-button>
				</core-alert>

				<component :is="$route.name" />
			</template>

			<div
				v-if="showConnectCta"
				class="connect-cta"
			>
				<core-blur
					v-if="!searchStatisticsStore.shouldShowSampleReports"
				>
					<component :is="$route.name" />
				</core-blur>

				<connect-cta v-if="!searchStatisticsStore.shouldShowSampleReports" />
			</div>

			<component
				v-if="!showConnectCta && !searchStatisticsStore.shouldShowSampleReports"
				:is="$route.name"
			/>
		</div>
	</core-main>
</template>

<script>
import {
	useLicenseStore,
	useSearchStatisticsStore
} from '@/vue/stores'

import license from '@/vue/utils/license'
import { DateTime } from 'luxon'

import { useGoogleSearchConsole } from '@/vue/composables/GoogleSearchConsole'

import links from '@/vue/utils/links'

import AuthenticationAlert from './partials/AuthenticationAlert'
import BaseButton from '@/vue/components/common/base/Button'
import BaseDatePicker from '@/vue/components/common/base/DatePicker'
import ConnectCta from './partials/pro/ConnectCta'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreMain from '@/vue/components/common/core/main/Index'
import ContentRankings from './ContentRankings'
import Cta from '@/vue/components/common/cta/Index'
import Dashboard from './Dashboard'
import KeywordRankTracker from './KeywordRankTracker'
import PostDetail from './AIOSEO_VERSION/PostDetail'
import SeoStatistics from './SeoStatistics'
import Settings from './AIOSEO_VERSION/Settings'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'rolling' ],
	setup () {
		const {
			connect,
			loading
		} = useGoogleSearchConsole()

		return {
			connect,
			licenseStore          : useLicenseStore(),
			links,
			loading,
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		AuthenticationAlert,
		BaseButton,
		BaseDatePicker,
		ConnectCta,
		CoreAlert,
		CoreBlur,
		CoreMain,
		ContentRankings,
		Cta,
		Dashboard,
		KeywordRankTracker,
		PostDetail,
		Settings,
		SeoStatistics
	},
	data () {
		return {
			maxDate : null,
			minDate : null,
			strings : {
				pageName            : __('Search Statistics', td),
				sampleDataAlert     : __('Sample data is available for you to explore. Connect your site to Google Search Console to receive insights on how content is being discovered. Identify areas for improvement and drive traffic to your website.', td),
				ctaButtonText       : __('Connect to Google Search Console', td),
				ctaUnlockButtonText : __('Unlock Search Statistics', td)
			}
		}
	},
	computed : {
		defaultRange () {
			const start = new Date(`${this.searchStatisticsStore.range.start} 00:00:00`)
			const end   = new Date(`${this.searchStatisticsStore.range.end} 00:00:00`)

			return [ start, end ]
		},
		excludeTabs () {
			const exclude = [ 'post-detail' ]

			if (this.licenseStore.isUnlicensed || !license.hasCoreFeature('search-statistics')) {
				exclude.push('settings')
			}

			return exclude
		},
		isSettings () {
			return 'settings' === this.$route.name
		},
		showSampleDataUnlockCta () {
			return (license.hasCoreFeature('search-statistics') && !this.searchStatisticsStore.isConnected) || this.searchStatisticsStore.unverifiedSite
		},
		showConnectCta () {
			return ((license.hasCoreFeature('search-statistics') && !this.searchStatisticsStore.isConnected) || this.searchStatisticsStore.unverifiedSite) && !this.isSettings
		},
		showDatePicker () {
			const isConnected  = this.searchStatisticsStore.isConnected && !this.searchStatisticsStore.unverifiedSite
			const hasDateRange = this.searchStatisticsStore.range.start && this.searchStatisticsStore.range.end

			return ![ 'settings', 'content-rankings' ].includes(this.$route.name) && isConnected && hasDateRange
		},
		containerClasses () {
			const classes = []

			// Add the blur to the main container if we are fetching data.
			if (this.searchStatisticsStore.fetching) {
				classes.push('aioseo-blur')
			}

			return classes
		},
		getOriginalMaxDate () {
			if (!this.searchStatisticsStore.latestAvailableDate) {
				return DateTime.local().plus({ days: -2 })
			}

			return DateTime.fromFormat(this.searchStatisticsStore.latestAvailableDate, 'yyyy-MM-dd').setZone(DateTime.zone) ||
				DateTime.local().plus({ days: -2 })
		},
		datepickerShortcuts () {
			return [
				{
					text  : __('Last 7 Days', td),
					value : () => {
						window.aioseoBus.$emit('rolling', 'last7Days')
						return [ this.getOriginalMaxDate.plus({ days: -6 }).toJSDate(), this.getOriginalMaxDate.toJSDate() ]
					}
				},
				{
					text  : __('Last 28 Days', td),
					value : () => {
						window.aioseoBus.$emit('rolling', 'last28Days')
						return [ this.getOriginalMaxDate.plus({ days: -27 }).toJSDate(), this.getOriginalMaxDate.toJSDate() ]
					}
				},
				{
					text  : __('Last 3 Months', td),
					value : () => {
						window.aioseoBus.$emit('rolling', 'last3Months')
						return [ this.getOriginalMaxDate.plus({ days: -89 }).toJSDate(), this.getOriginalMaxDate.toJSDate() ]
					}
				}
			]
		}
	},
	methods : {
		isDisabledDate (date) {
			if (null === this.minDate) {
				return true
			}

			return date.getTime() < this.minDate.getTime() || date.getTime() > this.maxDate.getTime()
		},
		onDateChange (dateRange, rolling) {
			this.searchStatisticsStore.setDateRange({
				dateRange,
				rolling
			})
		},
		highlightShortcut (rolling) {
			if (!rolling) {
				return
			}

			const shortcutButtons = document.querySelectorAll('.el-picker-panel__shortcut')
			shortcutButtons.forEach((button) => {
				switch (button.innerText) {
					case __('Last 7 Days', td) :
						if ('last7Days' === rolling) {
							button.classList.add('active')
						} else {
							button.classList.remove('active')
						}
						break
					case __('Last 28 Days', td) :
						if ('last28Days' === rolling) {
							button.classList.add('active')
						} else {
							button.classList.remove('active')
						}
						break
					case __('Last 3 Months', td) :
						if ('last3Months' === rolling) {
							button.classList.add('active')
						} else {
							button.classList.remove('active')
						}
						break
					case __('Last 6 Months', td) :
						if ('last6Months' === rolling) {
							button.classList.add('active')
						} else {
							button.classList.remove('active')
						}
						break
					default:
						button.classList.remove('active')
				}
			})
		}
	},
	mounted () {
		// GSC only gives us data for a max of 16 months.
		// This means that we can't allow the user to select a date range that is more than 16 months.
		this.minDate = DateTime.now().plus({ months: -16 }).toJSDate()
		this.maxDate = this.getOriginalMaxDate.toJSDate()
	}
}
</script>

<style lang="scss">
.aioseo-app {
	.sample-data-alert {
		margin-bottom: 20px;
	}

	.aioseo-card {
		margin: 0 0 20px;

		// Styles for the CardFooter be aligned on bottom.
		&:has(.aioseo-card-footer) {
			position: relative;
			padding-bottom: 44px;

			.content {
				position: static;
			}
		}
	}

	.aioseo-datepicker-picker {
		font-weight: 700;
	}

	.aioseo-wp-table {
		tbody {
			td {
				font-size: 14px;
			}

			.object-title {
				a {
					font-weight: bold;
					color: $black;

					&:hover {
						color: $blue;
					}
				}
			}

			.no-results {
				font-size: 16px;
			}
		}
	}

	.connect-cta {
		position: relative;
	}
}
</style>