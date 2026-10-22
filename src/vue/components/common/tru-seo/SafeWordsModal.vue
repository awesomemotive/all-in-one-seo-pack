<template>
	<core-modal
		modal-name="aioseo-safe-words"
		:show="show"
		@close="handleClose"
	>
		<template #headerTitle>
			<span class="aioseo-safe-words-modal__title">
				{{ strings.title }}
			</span>
		</template>

		<template #body>
			<div class="aioseo-safe-words-modal">
				<p class="aioseo-safe-words-modal__intro">
					{{ strings.intro }}
				</p>

				<div class="aioseo-safe-words-modal__add">
					<div class="aioseo-safe-words-modal__search">
						<base-input
							v-model="searchInput"
							type="text"
							size="small"
							:placeholder="strings.searchPlaceholder"
						/>
					</div>

					<div class="aioseo-safe-words-modal__add-content">
						<base-input
							v-model="addInput"
							type="text"
							size="small"
							:placeholder="strings.addPlaceholder"
							:disabled="addLoading"
							@keyup.enter="handleAdd"
							@input="addError = ''"
						/>

						<base-button
							type="blue"
							size="small"
							:loading="addLoading"
							:disabled="!addInput.trim() || addLoading"
							@click="handleAdd"
						>
							{{ strings.add }}
						</base-button>
					</div>
				</div>

				<div
					v-if="addError"
					class="aioseo-safe-words-modal__error"
					role="alert"
				>
					{{ addError }}
				</div>

				<div
					v-if="removeError"
					class="aioseo-safe-words-modal__error"
					role="alert"
				>
					{{ removeError }}
				</div>

				<div
					v-if="matchCaseError"
					class="aioseo-safe-words-modal__error"
					role="alert"
				>
					{{ matchCaseError }}
				</div>

				<core-wp-table
					ref="tableRef"
					:columns="columns"
					:rows="rows"
					:loading="wpTableLoading"
					:totals="totals"
					:initial-items-per-page="resultsPerPage"
					:initial-page-number="pageNumber"
					:initial-search-term="searchTerm || ''"
					:show-bulk-actions="false"
					:show-items-per-page="true"
					:show-header="false"
					:no-results-label="strings.noResults"
					@search="processSearch"
					@paginate="processPagination"
					@process-change-items-per-page="processChangeItemsPerPage"
				>
					<template #word="{ row }">
						<span class="aioseo-safe-words-modal__word">{{ row.word }}</span>
					</template>

					<template #matchCase="{ row }">
						<div class="aioseo-safe-words-modal__match-case-cell">
							<base-toggle
								:model-value="row.matchCase"
								:disabled="matchCaseLoading === row.word"
								@update:model-value="handleToggleMatchCase(row)"
							/>
						</div>
					</template>

					<template #matchCaseHeaderFooter="{ area }">
						<span class="aioseo-safe-words-modal__match-case-header">
							{{ strings.matchCaseColumn }}

							<core-tooltip v-if="'header' === area">
								<svg-info class="aioseo-safe-words-modal__match-case-info" />

								<template #tooltip>
									{{ strings.matchCaseTooltip }}
								</template>
							</core-tooltip>
						</span>
					</template>

					<template #actions="{ row }">
						<div
							class="aioseo-safe-words-modal__actions-cell"
							:class="{ 'aioseo-safe-words-modal__actions-cell--disabled': !!removeLoading }"
						>
							<core-tooltip type="action">
								<svg-trash @click.native="handleRemove(row.word)" />

								<template #tooltip>
									{{ strings.remove }}
								</template>
							</core-tooltip>
						</div>
					</template>
				</core-wp-table>
			</div>
		</template>
	</core-modal>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue'

import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import { useWpTable } from '@/vue/composables/WpTable'
import { createDebounce } from '@/vue/utils/debounce'

import {
	requestAddSafeWord,
	requestRemoveSafeWord,
	requestSetSafeWordMatchCase
} from '@/vue/plugins/tru-seo/spellingSuggestions'

import BaseToggle from '@/vue/components/common/base/Toggle'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import SvgInfo from '@/vue/components/common/svg/Info'
import SvgTrash from '@/vue/components/common/svg/Trash'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	show : {
		type    : Boolean,
		default : false
	}
})

const emit = defineEmits([ 'close' ])

const strings = {
	title             : __('Manage Dictionary', td),
	intro             : __('Words in this list are ignored by the spelling checker. Use it for brand names, technical terms, or proper nouns that are spelled correctly but flagged as misspelled.', td),
	addPlaceholder    : __('Add a word…', td),
	searchPlaceholder : __('Search words…', td),
	add               : __('Add', td),
	remove            : __('Remove', td),
	noResults         : __('No words in your dictionary yet.', td),
	wordColumn        : __('Word', td),
	matchCaseColumn   : __('Match Exact Case', td),
	matchCaseTooltip  : __('When enabled, only this exact capitalization is accepted. Other casings are still flagged as misspelled.', td),
	addFailed         : __('Failed to add word to your dictionary.', td),
	removeFailed      : __('Failed to remove word from your dictionary.', td),
	matchCaseFailed   : __('Failed to update the word in your dictionary.', td)
}

const columns = computed(() => [
	{ slug: 'word', label: strings.wordColumn },
	{ slug: 'matchCase', label: strings.matchCaseColumn, width: '180px' },
	{ slug: 'actions', label: '', width: '120px' }
])

const tableRef       = ref(null)
const rows           = ref([])
const total          = ref(0)
const addInput       = ref('')
const addLoading     = ref(false)
const addError         = ref('')
const removeError      = ref('')
const removeLoading    = ref('')
const matchCaseError   = ref('')
const matchCaseLoading = ref('')
const dirty            = ref(false)

const totals = computed(() => ({
	total : total.value,
	pages : Math.max(1, Math.ceil(total.value / (resultsPerPage.value || 20)))
}))

const fetchData = async ({ limit, offset, searchTerm: term }) => {
	wpTableLoading.value = true
	try {
		const response = await http
			.get(links.restUrl('spell-checker/safe-words'))
			.query({
				page    : 1 + Math.floor((offset || 0) / (limit || 20)),
				perPage : limit || 20,
				search  : term || ''
			})

		if (response.body?.success) {
			rows.value  = (response.body.words || []).map(entry => ({
				word      : entry.word,
				matchCase : !!entry.matchCase
			}))
			total.value = response.body.total || 0
		}
	} catch (error) {
		// Read failure is rare; surface in the remove-error slot for visibility.
		removeError.value = error?.response?.body?.message || strings.removeFailed
	} finally {
		wpTableLoading.value = false
	}
}

const {
	pageNumber,
	processChangeItemsPerPage,
	processPagination,
	processSearch,
	resultsPerPage,
	searchTerm,
	wpTableLoading
} = useWpTable({
	changeItemsPerPageSlug : 'safeWords',
	fetchData,
	tableId                : 'aioseo-safe-words-table',
	tableRef
})

const searchInput     = ref('')
const debouncedSearch = createDebounce((term) => processSearch(term), 300)

watch(searchInput, (term) => debouncedSearch(term))

const handleAdd = async () => {
	const word = addInput.value.trim()
	if (!word || addLoading.value) {
		return
	}

	addLoading.value = true
	addError.value   = ''

	try {
		const response = await http.post(links.restUrl('spell-checker/safe-words/add')).send({ word })

		if (!response.body?.success) {
			addError.value = response.body?.message || strings.addFailed

			return
		}

		const persistedWord = response.body.word || word
		await requestAddSafeWord(persistedWord)
		dirty.value    = true
		addInput.value = ''

		await fetchData({ limit: resultsPerPage.value, offset: 0, searchTerm: searchTerm.value })
	} catch (error) {
		addError.value = error?.response?.body?.message || strings.addFailed
	} finally {
		addLoading.value = false
	}
}

const handleRemove = async (word) => {
	if (removeLoading.value) {
		return
	}

	removeLoading.value = word
	removeError.value   = ''

	try {
		const response = await http.post(links.restUrl('spell-checker/safe-words/remove')).send({ word })

		if (!response.body?.success) {
			removeError.value = response.body?.message || strings.removeFailed

			return
		}

		await requestRemoveSafeWord(response.body.word || word)
		dirty.value = true

		// If the current page becomes empty, useWpTable's onLoaded handler in
		// core-wp-table will fall back to the previous page automatically.
		const offset = (pageNumber.value - 1) * (resultsPerPage.value || 20)
		await fetchData({ limit: resultsPerPage.value, offset, searchTerm: searchTerm.value })
	} catch (error) {
		removeError.value = error?.response?.body?.message || strings.removeFailed
	} finally {
		removeLoading.value = ''
	}
}

const handleToggleMatchCase = async (row) => {
	if (matchCaseLoading.value) {
		return
	}

	const nextMatchCase = !row.matchCase

	matchCaseLoading.value = row.word
	matchCaseError.value   = ''

	try {
		const response = await http
			.post(links.restUrl('spell-checker/safe-words/match-case'))
			.send({ word: row.word, matchCase: nextMatchCase })

		if (!response.body?.success) {
			matchCaseError.value = response.body?.message || strings.matchCaseFailed

			return
		}

		row.matchCase = nextMatchCase
		await requestSetSafeWordMatchCase(response.body.word || row.word, nextMatchCase)
		dirty.value = true
	} catch (error) {
		matchCaseError.value = error?.response?.body?.message || strings.matchCaseFailed
	} finally {
		matchCaseLoading.value = ''
	}
}

const handleClose = () => {
	emit('close', { dirty: dirty.value })
	// Reset transient state so re-opening starts fresh.
	dirty.value          = false
	addError.value       = ''
	removeError.value    = ''
	matchCaseError.value = ''
	addInput.value       = ''
}

watch(() => props.show, (isOpen) => {
	if (!isOpen) {
		return
	}

	// The modal stays mounted between opens, so its search + pagination state
	// persists. A search term left from a prior open would re-filter pagination
	// while the fetch below stays unfiltered (page 1 unfiltered, later pages
	// filtered), so reset the input and the table state first. Clearing
	// searchInput queues its debounced search; cancel it so it can't refetch.
	searchInput.value = ''
	searchTerm.value  = null
	pageNumber.value  = 1
	nextTick(() => debouncedSearch.cancel())

	// Fresh fetch every time the modal opens — picks up edits made by other
	// users between sessions, no polling needed.
	fetchData({ limit: resultsPerPage.value, offset: 0, searchTerm: '' })
}, { immediate: true })
</script>

<style lang="scss">
.aioseo-safe-words-modal {
	padding: 24px;

	&__title {
		font-weight: $font-bold;
	}

	&__add-content {
		.aioseo-input > input {
			min-height: 32px !important;
		}
	}

	&__intro {
		color: $black2;
		font-size: 13px;
		line-height: 1.6;
		margin: 0 0 20px;
	}

	&__add {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		margin-bottom: 14px;

		&-content {
			display: flex;
			align-items: center;
			gap: 8px;
			width: 100%;
			max-width: 270px;
		}

		.aioseo-input {
			flex: 1;
			margin: 0;
		}
	}

	&__search {
		display: flex;
		align-items: center;
		width: 100%;
		max-width: 270px;

		.aioseo-input {
			flex: 1;
			margin: 0;

			> input {
				min-height: 32px !important;
			}
		}
	}

	&__error {
		background-color: rgba(217, 53, 65, 0.08);
		border-radius: 4px;
		color: $red;
		font-size: $font-sm;
		margin-bottom: 12px;
		padding: 8px 12px;
	}

	&__word {
		word-break: break-word;
	}

	&__match-case-cell {
		align-items: center;
		display: flex;
		gap: 6px;
	}

	&__match-case-info {
		width: 15px;
		height: 15px;
		color: $placeholder-color;
		cursor: pointer;
	}

	&__match-case-header {
		align-items: center;
		display: inline-flex;
		gap: 4px;
		white-space: nowrap;

		.aioseo-tooltip {
			margin-left: 0;
		}
	}

	&__actions-cell {
		align-items: center;
		display: flex;
		gap: 6px;
		justify-content: flex-end;

		svg.aioseo-trash {
			width: 20px;
			height: 20px;
			color: $placeholder-color;
			cursor: pointer;
			transition: color 0.1s ease;

			&:hover {
				color: $red;
			}
		}

		&--disabled {
			opacity: 0.5;
			pointer-events: none;
		}
	}

	// Page builders like Elementor and Divi don't load WP core's list-table, form and button styles,
	// so the footer controls can't rely on them for layout.
	.aioseo-wp-table .tablenav.bottom {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		gap: 8px;
		height: auto;

		.alignleft.actions:empty,
		br.clear {
			display: none;
		}

		.aioseo-wp-items-per-page {
			float: none;

			label {
				gap: 8px;
			}

			select {
				width: auto;
				min-height: 32px;
				margin: 0;
				padding: 0 24px 0 8px;
				border: 1px solid $input-border;
				border-radius: 3px;
				background-color: #fff;
				font-size: 13px;
			}
		}

		.tablenav-pages {
			display: flex;
			align-items: center;
			gap: 8px;
			float: none;
			margin: 0 0 0 auto;
		}

		.pagination-links,
		.paging-input {
			display: inline-flex;
			align-items: center;
			gap: 4px;
		}

		// Restated here because the shared table rule only ties with WP core's `list-tables.css`
		// `inline-block`, and where core loads later (WPBakery's frontend editor) the arrow glyph
		// sat at the top of the 32px button.
		.button {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			box-sizing: border-box;
			min-width: 32px;
			min-height: 32px;
			margin: 0;
			padding: 0 4px;
			border: 1px solid $blue;
			border-radius: 3px;
			background: #fff;
			color: $blue;
			font-size: 16px;
			line-height: 1;
			text-decoration: none;

			&.disabled {
				border-color: $gray;
				color: $placeholder-color;
				cursor: default;
			}
		}

		.current-page {
			box-sizing: border-box;
			width: 48px;
			min-height: 32px;
			margin: 0;
			padding: 0 0 0 4px;
			border: 1px solid $input-border;
			border-radius: 3px;
			font-size: 13px;
			text-align: center;
		}
	}
}
</style>