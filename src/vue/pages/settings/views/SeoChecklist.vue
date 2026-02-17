<template>
	<div
		class="aioseo-seo-checklist"
		:class="{ 'filter-completed': 'completed' === filter }"
	>
		<!-- Confetti animation -->
		<div
			v-if="showConfetti"
			class="confetti-container"
		>
			<div
				v-for="i in 50"
				:key="i"
				class="confetti"
				:style="{
					left: Math.random() * 100 + '%',
					animationDelay: Math.random() * 3 + 's',
					backgroundColor: ['#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3', '#03a9f4', '#00bcd4', '#009688', '#4caf50', '#8bc34a', '#cddc39', '#ffeb3b', '#ffc107', '#ff9800', '#ff5722'][Math.floor(Math.random() * 16)]
				}"
			/>
		</div>

		<core-card
			slug="seoChecklist"
			:header-text="strings.seoChecklist"
			:toggles="false"
			no-slide
		>
			<div class="seo-checklist-description">
				{{ strings.description }}
			</div>

			<seo-checklist-progress-bar
				:inline-text="false"
				max-width="400px"
			>
				<span class="progress-motivation"> — {{ progressMotivation }}</span>
			</seo-checklist-progress-bar>

			<hr class="seo-checklist-divider">

			<core-wp-table
				:id="tableId"
				:columns="columns"
				:rows="rows"
				:totals="seoChecklistStore.totals"
				:filters="seoChecklistStore.filters"
				:additional-filters="additionalFilters"
				:selected-filters="selectedFilters"
				:loading="wpTableLoading"
				:bulk-options="bulkOptions"
				:show-search="false"
				:show-pagination="true"
				:show-table-footer="true"
				:initial-page-number="pageNumber"
				:initial-items-per-page="settingsStore.settings.tablePagination?.seoChecklist || 10"
				:no-results-label="noResultsMessage"
				:key="wpTableKey"
				show-items-per-page
				@filter-table="processFilterTable"
				@paginate="processPagination"
				@process-bulk-action="processBulkAction"
				@process-change-items-per-page="processChangeItemsPerPage"
				@process-additional-filters="processAdditionalFilters"
				@search="processSearch"
				@sort-column="processSort"
			>
				<template #title="{ row }">
					<div class="checklist-title-wrapper">
						<span class="checklist-title">
							{{ row.title }}
						</span>
						<span
							v-if="row.description"
							class="checklist-description"
						>
							{{ row.description }}
						</span>
					</div>
				</template>

				<template #priority="{ row }">
					<span
						class="priority-label"
						:class="'priority-' + row.priority"
					>
						<span class="priority-dot" />
						{{ getPriorityLabel( row.priority ) }}
					</span>
				</template>

				<template #time="{ row }">
					<span class="time-estimate">
						{{ row.time?.label || strings.notApplicable }}
					</span>
				</template>

				<template #actions="{ row, editRow, index, editRowActive }">
					<div class="checklist-actions">
						<template v-if="!row.completed">
							<!-- Primary action links -->
							<div class="actions-primary">
							<template
								v-for="( action, actionIndex ) in row.actions"
								:key="actionIndex"
							>
								<a
									v-if="action.callback"
									href="#"
									class="action-link"
									:class="{ 'has-separator': actionIndex < row.actions.length - 1 }"
									@click.prevent.stop="handleAction( row, action.callback )"
								>
									{{ action.label }}
								</a>
								<a
									v-else-if="action.url"
									:href="action.url"
									target="_blank"
									class="action-link"
									:class="{ 'has-separator': actionIndex < row.actions.length - 1 }"
								>
									{{ action.label }}
								</a>
							</template>
							</div>

							<!-- Secondary links (explanation, dismiss) -->
							<div
								v-if="hasInfoPanel( row ) || row.dismissable"
								class="actions-secondary"
							>
								<a
									v-if="hasInfoPanel( row )"
									href="#"
									class="secondary-link"
									:class="{ 'has-separator': row.dismissable }"
									@click.prevent.stop="editRow( index )"
								>
									{{ editRowActive ? strings.hideExplanation : strings.viewExplanation }}
								</a>
							<a
								v-if="row.dismissable"
								href="#"
								class="secondary-link dismiss-link"
								@click.prevent.stop="editRow( null ); dismissItem( row.name )"
							>
									{{ strings.dismiss }}
								</a>
							</div>
						</template>

						<template v-else>
							<div
								v-if="!rootStore.aioseo.data?.isDev && !row.actionAfterCompletion"
								class="actions-completed"
							>
								<span class="completed-check">✓</span>
							</div>

							<div
								v-if="row.actionAfterCompletion && row.actionAfterCompletion.length"
								class="actions-primary"
							>
								<template
									v-for="( action, actionIndex ) in row.actionAfterCompletion"
									:key="actionIndex"
								>
									<a
										v-if="action.callback"
										href="#"
										class="action-link"
										:class="{ 'has-separator': actionIndex < row.actionAfterCompletion.length - 1 }"
										@click.prevent.stop="handleAction( row, action.callback )"
									>
										{{ action.label }}
									</a>
									<a
										v-else-if="action.url"
										:href="action.url"
										target="_blank"
										class="action-link"
										:class="{ 'has-separator': actionIndex < row.actionAfterCompletion.length - 1 }"
									>
										{{ action.label }}
									</a>
								</template>
							</div>

						<div
							v-if="rootStore.aioseo.data?.isDev || (hasInfoPanel( row ) && 'completed' !== filter)"
							class="actions-secondary"
						>
							<a
								v-if="hasInfoPanel( row ) && 'completed' !== filter"
								href="#"
								class="secondary-link"
								@click.prevent.stop="editRow( index )"
							>
								{{ editRowActive ? strings.hideExplanation : strings.viewExplanation }}
							</a>
								<a
									v-if="rootStore.aioseo.data?.isDev"
									href="#"
									class="secondary-link"
									@click.prevent="uncompleteItem(row.name)"
								>
									{{ strings.markIncomplete }}
								</a>
							</div>
						</template>
					</div>
				</template>

				<template #edit-row="{ row, index }">
					<div
						v-if="hasInfoPanel( row )"
						class="checklist-info-panel"
						:class="{ 'panel-odd': 0 !== index % 2, 'panel-even': 0 === index % 2 }"
					>
						<component
							v-if="infoComponents[row.name]"
							:is="infoComponents[row.name]"
							class="checklist-info"
						/>
						<div
							v-else-if="row.info"
							class="checklist-info"
							v-html="row.info"
						/>
					</div>
				</template>
			</core-wp-table>
		</core-card>
	</div>
</template>

<script>
import { markRaw, ref } from 'vue'

import {
	useRootStore,
	useSeoChecklistStore,
	useSettingsStore
} from '@/vue/stores'

import { useWpTable } from '@/vue/composables/WpTable'

import CoreCard from '@/vue/components/common/core/Card'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import SeoChecklistProgressBar from '@/vue/components/common/core/SeoChecklistProgressBar'
import ConnectBrokenLinkCheckerInfo from './seo-checklist/info/ConnectBrokenLinkChecker'
import ConnectGoogleSearchConsoleInfo from './seo-checklist/info/ConnectGoogleSearchConsole'
import EnableIndexingInfo from './seo-checklist/info/EnableIndexing'
import EnableLlmsTxtInfo from './seo-checklist/info/EnableLlmsTxt'
import FillKnowledgeGraphInfo from './seo-checklist/info/FillKnowledgeGraph'
import FillSocialProfilesInfo from './seo-checklist/info/FillSocialProfiles'
import InstallBrokenLinkCheckerInfo from './seo-checklist/info/InstallBrokenLinkChecker'
import ReviewArchivesInfo from './seo-checklist/info/ReviewArchives'
import ReviewContentTypesInfo from './seo-checklist/info/ReviewContentTypes'
import ReviewTaxonomiesInfo from './seo-checklist/info/ReviewTaxonomies'
import RunHomepageAuditInfo from './seo-checklist/info/RunHomepageAudit'
import EnableIndexNowInfo from './seo-checklist/info/AIOSEO_VERSION/EnableIndexNow'
import FillLocalBusinessSchemaInfo from './seo-checklist/info/AIOSEO_VERSION/FillLocalBusinessSchema'
import FixOrphanedPostsInfo from './seo-checklist/info/AIOSEO_VERSION/FixOrphanedPosts'
import LicenseActivatedInfo from './seo-checklist/info/AIOSEO_VERSION/LicenseActivated'
import Review404LogsInfo from './seo-checklist/info/AIOSEO_VERSION/Review404Logs'
import ReviewImageSeoSettingsInfo from './seo-checklist/info/AIOSEO_VERSION/ReviewImageSeoSettings'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const rootStore            = useRootStore()
		const seoChecklistStore    = useSeoChecklistStore()
		const settingsStore        = useSettingsStore()
		const tableId              = 'aioseo-seo-checklist-table'
		const defaultSelectedFilters = ref({ priority: 'all' })

		// Info components registry (using markRaw to prevent reactivity)
		const infoComponents = {
			connectBrokenLinkChecker   : markRaw(ConnectBrokenLinkCheckerInfo),
			connectGoogleSearchConsole : markRaw(ConnectGoogleSearchConsoleInfo),
			enableIndexing             : markRaw(EnableIndexingInfo),
			enableIndexNow             : markRaw(EnableIndexNowInfo),
			enableLlmsTxt              : markRaw(EnableLlmsTxtInfo),
			fillKnowledgeGraph         : markRaw(FillKnowledgeGraphInfo),
			fillLocalBusinessSchema    : markRaw(FillLocalBusinessSchemaInfo),
			fillSocialProfiles         : markRaw(FillSocialProfilesInfo),
			fixOrphanedPosts           : markRaw(FixOrphanedPostsInfo),
			installBrokenLinkChecker   : markRaw(InstallBrokenLinkCheckerInfo),
			licenseActivated           : markRaw(LicenseActivatedInfo),
			review404Logs              : markRaw(Review404LogsInfo),
			reviewArchives             : markRaw(ReviewArchivesInfo),
			reviewContentTypes         : markRaw(ReviewContentTypesInfo),
			reviewImageSeoSettings     : markRaw(ReviewImageSeoSettingsInfo),
			reviewTaxonomies           : markRaw(ReviewTaxonomiesInfo),
			runHomepageAudit           : markRaw(RunHomepageAuditInfo)
		}

		const {
			filter,
			orderBy,
			orderDir,
			pageNumber,
			processAdditionalFilters,
			processChangeItemsPerPage,
			processFetchTableData,
			processFilterTable,
			processPagination,
			processSearch,
			processSort,
			refreshTable,
			searchTerm,
			selectedFilters,
			wpTableKey,
			wpTableLoading
		} = useWpTable({
			changeItemsPerPageSlug : 'seoChecklist',
			fetchData              : seoChecklistStore.fetchChecks,
			selectedFilters        : defaultSelectedFilters,
			tableId
		})

		return {
			filter,
			infoComponents,
			orderBy,
			orderDir,
			pageNumber,
			processAdditionalFilters,
			processChangeItemsPerPage,
			processFetchTableData,
			processFilterTable,
			processPagination,
			processSearch,
			processSort,
			refreshTable,
			rootStore,
			searchTerm,
			selectedFilters,
			seoChecklistStore,
			settingsStore,
			tableId,
			wpTableKey,
			wpTableLoading
		}
	},
	components : {
		CoreCard,
		CoreWpTable,
		SeoChecklistProgressBar
	},
	data () {
		return {
			pollingInterval   : null,
			confettiTriggered : false,
			showConfetti      : false,
			strings           : {
				seoChecklist       : __('SEO Checklist', td),
				description        : __('Our SEO Checklist helps you set up your site correctly, identify and resolve critical SEO issues, and introduces you to important AIOSEO features. Complete prioritized tasks and track your progress as you optimize your site for better search engine rankings.', td),
				dismiss            : __('Dismiss', td),
				markIncomplete     : __('Mark Incomplete', td),
				notApplicable      : __('N/A', td),
				high               : __('High', td),
				medium             : __('Medium', td),
				low                : __('Low', td),
				optional           : __('Optional', td),
				filterByPriority   : __('Filter by Priority', td),
				allPriorities      : __('All Priorities', td),
				viewExplanation    : __('View Explanation', td),
				hideExplanation    : __('Hide Explanation', td),
				allTasksComplete   : __('You\'ve completed all tasks. Well done!', td),
				motivationRed      : __('You\'ve still got work to do!', td),
				motivationOrange   : __('You\'re making progress!', td),
				motivationYellow   : __('More than halfway there!', td),
				motivationGreen    : __('Almost there!', td),
				motivationComplete : __('All done!', td)
			}
		}
	},
	watch : {
		allTasksCompleted (newVal, oldVal) {
			// Trigger confetti only when transitioning from incomplete to complete
			if (newVal && !oldVal && !this.confettiTriggered) {
				this.triggerConfetti()
			}
		}
	},
	computed : {
		progressPercent () {
			const total     = this.seoChecklistStore.overallTotalCount
			const completed = this.seoChecklistStore.overallCompletedCount

			if (0 === total) {
				return 0
			}

			return Math.round((completed / total) * 100)
		},
		progressMotivation () {
			const p = this.progressPercent

			if (100 === p) {
				return this.strings.motivationComplete + ' 🎉'
			}

			if (75 <= p) {
				return this.strings.motivationGreen
			}

			if (50 <= p) {
				return this.strings.motivationYellow
			}

			if (25 <= p) {
				return this.strings.motivationOrange
			}

			return this.strings.motivationRed
		},
		noResultsMessage () {
			if ('incomplete' === this.filter) {
				return this.strings.allTasksComplete + ' 🚀'
			}

			return null
		},
		bulkOptions () {
			const options = []

			// Dismiss is only available on the Incomplete tab.
			if ('incomplete' === this.filter) {
				options.push({ label: this.strings.dismiss, value: 'dismiss' })
			}

			// Mark Incomplete is only available in dev mode on the Completed tab.
			if (this.rootStore.aioseo.data?.isDev && 'completed' === this.filter) {
				options.push({ label: this.strings.markIncomplete, value: 'uncomplete' })
			}

			return options
		},
		additionalFilters () {
			return [
				{
					label   : this.strings.filterByPriority,
					name    : 'priority',
					options : [
						{ label: this.strings.allPriorities, value: 'all' },
						{ label: this.strings.high, value: 'high' },
						{ label: this.strings.medium, value: 'medium' },
						{ label: this.strings.low, value: 'low' },
						{ label: this.strings.optional, value: 'optional' }
					]
				}
			]
		},
		columns () {
			return [
				{
					slug  : 'title',
					label : __('Title', td)
				},
				{
					slug     : 'priority',
					label    : __('Priority', td),
					width    : '120px',
					sortable : true,
					sortDir  : 'priority' === this.orderBy ? this.orderDir : 'asc',
					sorted   : 'priority' === this.orderBy
				},
				{
					slug     : 'time',
					label    : __('Time Estimate', td),
					width    : '130px',
					sortable : true,
					sortDir  : 'time' === this.orderBy ? this.orderDir : 'asc',
					sorted   : 'time' === this.orderBy
				},
				{
					slug  : 'actions',
					label : 'completed' === this.filter ? __('Completed', td) : __('Actions', td),
					width : 'completed' === this.filter ? '100px' : '250px'
				}
			]
		},
		rows () {
			return this.seoChecklistStore.rows.map(item => ({
				id                    : item.name,
				name                  : item.name,
				title                 : item.title || item.name,
				description           : item.description || '',
				priority              : item.priority || 'medium',
				time                  : item.time || '',
				completed             : item.completed || false,
				actions               : item.actions || [],
				actionAfterCompletion : item.actionAfterCompletion || null,
				dismissable           : false !== item.dismissable,
				preventBulkAction     : false === item.dismissable,
				info                  : item.info || null
			}))
		},
		allTasksCompleted () {
			const total     = this.seoChecklistStore.overallTotalCount
			const completed = this.seoChecklistStore.overallCompletedCount

			return 0 < total && completed === total
		}
	},
	methods : {
		getPriorityLabel (priority) {
			const labels = {
				high     : this.strings.high,
				medium   : this.strings.medium,
				low      : this.strings.low,
				optional : this.strings.optional
			}
			return labels[priority] || priority
		},
		hasInfoPanel (row) {
			// Check if there's a Vue component registered for this check
			if (this.infoComponents[row.name]) {
				return true
			}

			return false
		},
		processBulkAction ({ action, selectedRows }) {
			if (!selectedRows.length) {
				return
			}

			if ('dismiss' === action) {
				// Filter out non-dismissable rows
				const dismissableRows = selectedRows.filter(rowId => {
					const row = this.rows.find(r => r.id === rowId)
					return row && row.dismissable
				})

				if (!dismissableRows.length) {
					return
				}

				this.wpTableLoading = true
				this.seoChecklistStore.bulkCompleteItems(dismissableRows)
					.then(() => this.processFetchTableData())
					.then(() => (this.wpTableLoading = false))
			}

			if ('uncomplete' === action) {
				this.wpTableLoading = true
				this.seoChecklistStore.bulkUncompleteItems(selectedRows)
					.then(() => this.processFetchTableData())
					.then(() => (this.wpTableLoading = false))
			}
		},
		handleAction (row, actionName) {
			if (row.completed) {
				return
			}

			this.wpTableLoading = true
			this.seoChecklistStore.doAction(row.name, actionName)
				.then(() => this.processFetchTableData())
				.then(() => (this.wpTableLoading = false))
		},
		dismissItem (itemName) {
			this.wpTableLoading = true
			this.seoChecklistStore.dismissItem(itemName)
				.then(() => this.processFetchTableData())
				.then(() => (this.wpTableLoading = false))
		},
		uncompleteItem (itemName) {
			this.wpTableLoading = true
			this.seoChecklistStore.uncompleteItem(itemName)
				.then(() => this.processFetchTableData())
				.then(() => (this.wpTableLoading = false))
		},

		/**
		 * Start polling for completed check changes.
		 *
		 * @returns {void}
		 */
		startPolling () {
			this.pollingInterval = setInterval(async () => {
				// Don't poll while table is loading (e.g. bulk action in progress)
				if (this.wpTableLoading) {
					return
				}

				const apiCounts = await this.seoChecklistStore.fetchCompletedChecks()

				// Check again after fetch - a bulk action may have started while we were fetching
				if (this.wpTableLoading) {
					return
				}

				if (this.seoChecklistStore.hasCompletedChecksChanged(apiCounts)) {
					// Update the stored counts first to prevent double-reload
					this.seoChecklistStore.updatePollingCounts(apiCounts)

					this.wpTableLoading = true
					await this.processFetchTableData()
					this.wpTableLoading = false
				}
			}, 10000) // Poll every 10 seconds
		},

		/**
		 * Stop polling for completed check changes.
		 *
		 * @returns {void}
		 */
		stopPolling () {
			if (this.pollingInterval) {
				clearInterval(this.pollingInterval)
				this.pollingInterval = null
			}
		},

		/**
		 * Trigger confetti animation.
		 *
		 * @returns {void}
		 */
		triggerConfetti () {
			this.confettiTriggered = true
			this.showConfetti      = true

			// Hide confetti after animation completes
			setTimeout(() => {
				this.showConfetti = false
			}, 10000)
		}
	},
	mounted () {
		// Set default filter - incomplete if there are incomplete items, otherwise completed.
		const hasIncomplete = 0 < this.seoChecklistStore.overallTotalCount - this.seoChecklistStore.overallCompletedCount
		this.filter = hasIncomplete ? 'incomplete' : 'completed'

		this.wpTableLoading = true
		this.processFetchTableData()
			.then(() => (this.wpTableLoading = false))
		this.startPolling()
	},
	beforeUnmount () {
		this.stopPolling()
	}
}
</script>

<style lang="scss">
.aioseo-seo-checklist {
	position: relative;

	.confetti-container {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		pointer-events: none;
		overflow: hidden;
		z-index: 9999;
	}

	.confetti {
		position: absolute;
		width: 10px;
		height: 10px;
		top: -10px;
		opacity: 0;
		animation: confetti-fall 8s ease-in-out forwards;
	}

	@keyframes confetti-fall {
		0% {
			transform: translateY(0) rotate(0deg);
			opacity: 1;
		}

		100% {
			transform: translateY(100vh) rotate(720deg);
			opacity: 0;
		}
	}

	.seo-checklist-description {
		font-size: 14px;
		line-height: 1.6;
		color: $black2;
		margin-bottom: 16px;
	}

	.aioseo-seo-checklist-progress-bar {
		margin-bottom: 16px;
	}

	.progress-motivation {
		color: $black2;
	}

	.seo-checklist-divider {
		border: none;
		border-top: 1px solid #E8E8EB;
		margin: 0 0 16px 0;
	}

	.checklist-title-wrapper {
		display: flex;
		flex-direction: column;
		gap: 4px;
	}

	.checklist-title {
		font-weight: 600;
	}

	.checklist-description {
		font-weight: 400;
		font-size: 13px;
	}

	.priority-label {
		display: inline-flex;
		align-items: center;
		gap: 10px;
		font-size: 13px;
		line-height: 1;
		color: $black2;

		.priority-dot {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			flex-shrink: 0;
			vertical-align: middle;
		}

		&.priority-high .priority-dot {
			background-color: #dc2626;
		}

		&.priority-medium .priority-dot {
			background-color: #d97706;
		}

		&.priority-low .priority-dot {
			background-color: #2563eb;
		}

		&.priority-optional .priority-dot {
			background-color: #9ca3af;
		}
	}

	.time-estimate {
		font-size: 13px;
	}

	// Right-align the actions column by default.
	th.actions,
	td.actions {
		text-align: right;
	}

	// Center-align when viewing completed items.
	&.filter-completed {
		th.actions,
		td.actions {
			text-align: center;
		}

		.checklist-actions {
			align-items: center;
		}
	}

	.checklist-actions {
		display: flex;
		flex-direction: column;
		align-items: flex-end;
		gap: 6px;

		.actions-primary {
			display: flex;
			gap: 8px;

			.action-link {
				display: inline-flex;
				align-items: center;
				gap: 4px;
				color: $blue;
				font-size: 13px;

				&.has-separator {
					padding-right: 8px;
					border-right: 1px solid $placeholder-color;
				}
				text-decoration: none;
				cursor: pointer;

				&:hover {
					text-decoration: underline;
				}

				svg.aioseo-external {
					width: 11px;
					height: 11px;
					flex-shrink: 0;
				}
			}
		}

		.actions-secondary {
			display: flex;
			align-items: center;
			font-size: 12px;

			.secondary-link {
				color: $placeholder-color;
				text-decoration: none;
				cursor: pointer;

				&:hover {
					text-decoration: underline;
				}

				&.has-separator {
					padding-right: 8px;
					margin-right: 8px;
					border-right: 1px solid $placeholder-color;
				}
			}
		}

		.actions-completed {
			.completed-check {
				font-size: 18px;
				font-weight: 600;
				color: $blue;
			}
		}
	}

	.checklist-info-panel {
		padding: 8px 16px;
		border-radius: 4px;

		background-color: $box-background;
	}

	// Common styles for all info panels
	.checklist-info {
		font-size: 14px;
		line-height: 1.6;
		color: $black2;

		h4 {
			margin: 0 0 8px 0;
			font-size: 14px;
			font-weight: 600;
			color: $black;
		}

		p {
			margin: 0 0 12px 0;

			&:last-child {
				margin-bottom: 0;
			}
		}

		ul {
			margin: 0 0 12px 0;
			padding-left: 20px;
			list-style: disc;

			li {
				margin-bottom: 4px;
			}
		}

		a {
			color: $blue;

			&:hover {
				text-decoration: underline;
			}

			&.no-underline {
				&:hover {
					text-decoration: none;
				}
			}
		}
	}
}
</style>