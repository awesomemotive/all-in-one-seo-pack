<template>
	<div>
		<core-wp-table
			:bulk-options="bulkOptions"
			:class="{'link-assistant-inner-table' : !postReport}"
			:columns="columns"
			:id="tableId"
			:initial-items-per-page="settingsStore.settings.tablePagination.linkAssistantPostsReport"
			:initial-page-number="pageNumber"
			:key="wpTableKey"
			:loading="wpTableLoading"
			:rows="rows"
			:show-pagination="!linksReport"
			:show-search="false"
			:show-table-footer="postReport"
			:totals="post.links.inboundInternal.totals"
			show-items-per-page
			@paginate="processPagination"
			@process-bulk-action="maybeDoBulkAction"
			@process-change-items-per-page="processChangeItemsPerPage"
		>
			<template #post_title="{ row }">
				<span>
					{{ row.context.postTitle }}
					<span v-if="row.context.permalink.replace(/\/$/, '') === rootStore.aioseo.urls.home">({{ strings.frontPage }})</span>
				</span>

				<div
					class="row-actions"
					v-if="row.context"
				>
					<span class="view">
						<a
							:href="row.context.permalink"
							target="_blank"
						>{{ maybeViewPost(row) }}</a> |
					</span>

					<span class="edit">
						<a
							:href="row.context.editLink"
							target="_blank"
						>{{ maybeEditPost(row) }}</a>
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

			<template #delete="{ index }">
				<core-tooltip
					type="action"
				>
					<svg-trash
						@click.native="maybeDoBulkAction({
							action: 'delete',
							selectedRows: index
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
					@click.native="$emit('openSuggestions')"
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
			v-if="showModal"
			:selectedRows="selectedRows"
			:strings="modalStrings"
			@doBulkAction="doBulkAction"
			@closeModal="showModal = false"
		/>
	</div>
</template>

<script>
import {
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import { merge } from 'lodash-es'
import { WpTable } from '@/vue/mixins'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import { useLinks } from '@/vue/composables/link-assistant/Links'
import LinksMixin from '@/vue/mixins/link-assistant/Links'
import PostTypesMixin from '@/vue/mixins/PostTypes'
import LinkAssistantConfirmationModal from '@/vue/components/common/link-assistant/ConfirmationModal'
import LinkAssistantPhrase from '@/vue/components/common/link-assistant/Phrase'
import SvgLinkExternal from '@/vue/components/common/svg/link/External'
import SvgLinkSuggestion from '@/vue/components/common/svg/link/Suggestion'
import SvgTrash from '@/vue/components/common/svg/Trash'
export default {
	setup () {
		const { strings, modalStrings, bulkOptions } = useLinks()

		return {
			rootStore         : useRootStore(),
			settingsStore     : useSettingsStore(),
			composableStrings : strings,
			bulkOptions,
			modalStrings
		}
	},
	emits      : [ 'openSuggestions' ],
	components : {
		CoreTooltip,
		CoreWpTable,
		LinkAssistantConfirmationModal,
		LinkAssistantPhrase,
		SvgLinkExternal,
		SvgLinkSuggestion,
		SvgTrash
	},
	mixins : [ LinksMixin, PostTypesMixin, WpTable ],
	data () {
		return {
			tableId  : 'aioseo-post-report-inbound-internal',
			linkType : 'inboundInternal',
			strings  : merge(this.composableStrings, {
				deleteAllLinks : this.$t.sprintf(
					// Translators: 1 - The type of link.
					this.$t.__('Delete All %1$s Links', this.$td), this.$t.__('Inbound Internal', this.$td)
				),
				outboundSuggestions : this.$t.sprintf(
					// Translators: 1 - The type of link.
					this.$t.__('%1$s Link Suggestions', this.$td), this.$t.__('Inbound', this.$td)
				)
			})
		}
	},
	computed : {
		columns () {
			return [
				{
					slug  : 'post_title',
					label : this.$t.__('Post Title', this.$td)
				},
				{
					slug  : 'phrase',
					label : this.$t.__('Phrase', this.$td)
				},
				{
					slug  : 'delete',
					width : '50px'
				}
			]
		},
		seeAllLinks () {
			return this.$t.sprintf(
				// Translators: 1 - The amount of links, 2 - The type of link.
				this.$t.__('See All %1$s %2$s Links', this.$td),
				this.post.links.inboundInternal.totals.total,
				this.$t.__('Inbound Internal', this.$td)
			)
		}
	},
	methods : {
		maybeViewPost (row) {
			this.viewPost(row.context?.postType?.singular || 'Post')
		},
		maybeEditPost (row) {
			this.editPost(row.context?.postType?.singular || 'Post')
		},
		processPagination (pageNumber) {
			this.pageNumber = pageNumber

			if (this.metabox) {
				return
			}

			this.wpTableLoading = true

			this.processFetchTableData()
				.then(() => (this.wpTableLoading = false))
		}
	}
}
</script>