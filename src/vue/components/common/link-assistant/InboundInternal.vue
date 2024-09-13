<template>
	<div>
		<core-wp-table
			:id="tableId"
			:key="wpTableKey"
			:class="{'link-assistant-inner-table' : !postReport}"
			:columns="columns"
			:loading="wpTableLoading"
			:rows="rows"
			:totals="post.links.inboundInternal.totals"
			:bulk-options="bulkOptions"
			:initial-items-per-page="settingsStore.settings.tablePagination.linkAssistantPostsReport"
			:initial-page-number="pageNumber"
			:show-pagination="!linksReport"
			:show-search="false"
			:show-table-footer="postReport"
			show-items-per-page
			@paginate="processPagination"
			@process-bulk-action="maybeDoBulkAction"
			@process-change-items-per-page="processChangeItemsPerPage"
		>
			<template #post_title="{ row }">
				<span>
					{{ row.context.postTitle }}
					<span v-if="row.context?.permalink?.replace(/\/$/, '') === rootStore.aioseo.urls.home">({{ strings.frontPage }})</span>
				</span>

				<div
					class="row-actions"
					v-if="row.context"
				>
					<span class="view">
						<a
							:href="row.context.permalink"
							target="_blank"
						>{{ viewPost(row.context.postType.singular) }}</a> |
					</span>

					<span class="edit">
						<a
							:href="row.context.editLink"
							target="_blank"
						>{{ editPost(row.context.postType.singular) }}</a>
					</span>
				</div>
			</template>

			<template #phrase="{ row }">
				<link-assistant-phrase
					:phrase="row.phrase"
					:phraseHtml="row.phrase_html || ''"
					:anchor="row.anchor"
					:url="row.url"
					:clickableAnchor="true"
				/>
			</template>

			<template #delete="{ row }">
				<core-tooltip
					type="action"
				>
					<svg-trash
						@click.native="maybeDoBulkAction({
							action       : 'delete',
							selectedRows : [ row.id ]
						})"
					/>
					<template #tooltip>
						{{ strings.deleteLink }}
					</template>
				</core-tooltip>
			</template>
		</core-wp-table>

		<div
			class="links-bottom"
			v-if="!postReport"
		>
			<div class="links-bottom-left">
				<base-button
					v-if="post.links.inboundInternal.rows.length"
					type="blue"
					tag="button"
					@click.native="emit('openSuggestions')"
				>
					<svg-link-suggestion />
					{{ strings.outboundSuggestions }}
				</base-button>
				<div
					v-if="post.links.inboundInternal.totals.total > 5 && linksReport"
				>
					<svg-link-external />
					<a
						class="link-view"
						href="#"
						@click.prevent="openPostReport('inbound-internal')"
					>
						{{ seeAllLinks }}
					</a>
				</div>
			</div>

			<div class="links-bottom-right">
				<a
					v-if="post.links.inboundInternal.rows.length"
					class="link-delete"
					@click.prevent="maybeDoBulkAction({
						action: 'delete',
						selectedRows : 'all'
					})"
				>
					{{ strings.deleteAllLinks }}
				</a>
			</div>
		</div>

		<link-assistant-confirmation-modal
			:show="showModal"
			:selectedRows="selectedRows"
			:strings="modalStrings"
			@doBulkAction="doBulkAction"
			@closeModal="showModal = false"
		/>
	</div>
</template>

<script setup>
import { ref, computed } from 'vue'

import {
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import { merge } from 'lodash-es'

import { useLinks } from '@/vue/composables/link-assistant/Links'
import { usePostTypes } from '@/vue/composables/PostTypes'
import { useWpTable } from '@/vue/composables/WpTable'

import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import LinkAssistantConfirmationModal from '@/vue/components/common/link-assistant/ConfirmationModal'
import LinkAssistantPhrase from '@/vue/components/common/link-assistant/Phrase'
import SvgLinkExternal from '@/vue/components/common/svg/link/External'
import SvgLinkSuggestion from '@/vue/components/common/svg/link/Suggestion'
import SvgTrash from '@/vue/components/common/svg/Trash'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const rootStore     = useRootStore()
const settingsStore = useSettingsStore()

const props = defineProps({
	post : {
		type     : Object,
		required : true
	},
	postIndex : {
		type     : Number,
		required : false
	},
	postId : {
		type     : Number,
		required : false
	},
	linksReport : {
		type : Boolean,
		default () {
			return false
		}
	},
	postReport : {
		type : Boolean,
		default () {
			return false
		}
	},
	metabox : {
		type : Boolean,
		default () {
			return false
		}
	}
})

const emit = defineEmits([ 'linksUpdated', 'openSuggestions' ])

const pageNumber     = ref(1)
const wpTableLoading = ref(false)
const linkType       = 'inboundInternal'
const {
	bulkOptions,
	changeItemsPerPageSlug,
	doBulkAction,
	fetchData,
	maybeDoBulkAction,
	modalStrings,
	openPostReport,
	rows,
	selectedRows,
	showModal,
	strings: composableStrings
} = useLinks({
	emit,
	linkType,
	linksReport  : props.linksReport,
	metabox      : props.metabox,
	pageNumber,
	post         : props.post,
	postIndex    : props.postIndex,
	postReport   : props.postReport,
	refreshTable : () => refreshTable(),
	wpTableLoading
})

const {
	editPost,
	viewPost
} = usePostTypes()

const tableId = 'aioseo-post-report-inbound-internal'
const {
	processChangeItemsPerPage,
	processFetchTableData,
	refreshTable,
	wpTableKey
} = useWpTable({
	changeItemsPerPageSlug,
	fetchData,
	pageNumber,
	tableId,
	wpTableLoading
})

const strings = merge(composableStrings, {
	deleteAllLinks : sprintf(
		// Translators: 1 - The type of link.
		__('Delete All %1$s Links', td),
		__('Inbound Internal', td)
	),
	outboundSuggestions : sprintf(
		// Translators: 1 - The type of link.
		__('%1$s Link Suggestions', td),
		__('Inbound', td)
	)
})

const columns = computed(() => [
	{
		slug  : 'post_title',
		label : __('Post Title', td)
	},
	{
		slug  : 'phrase',
		label : __('Phrase', td)
	},
	{
		slug  : 'delete',
		width : '50px'
	}
])

const seeAllLinks = computed(() => {
	return sprintf(
		// Translators: 1 - The amount of links, 2 - The type of link.
		__('See All %1$s %2$s Links', td),
		props.post.links.inboundInternal.totals.total,
		__('Inbound Internal', td)
	)
})

const processPagination = (number) => {
	pageNumber.value = number

	if (props.metabox) {
		return
	}

	wpTableLoading.value = true

	processFetchTableData()
		.then(() => (wpTableLoading.value = false))
}
</script>