<template>
	<div class="aioseo-keyphrase-tag">
		<span class="aioseo-add-keyphrase-tag" :class="edit ? 'hidden' : ''">
			<span class="keyphrase-name" @click="toggleKeyphrasesPanel(index)">
				{{ keyphrase }}
			</span>
			<span class="keyphrase-edit" @click="editKeyphraseEv(index)">
				<svg-pencil />
			</span>
			<a
				href="#"
				@click.prevent.exact="keywordRankTrackerStore.toggleModal({modal:'modalOpenPostEdit', open: true})"
				class="btn-keyword-rank-tracker"
			>
				<svg-statistics width="20"/>
			</a>
			<span class="keyphrase-score" :class="scoreClass" @click="toggleKeyphrasesPanel(index)">{{ score }}/100</span>
		</span>
		<span class="aioseo-edit-keyphrase-tag" v-if="edit">
			<input
				:value="keyphrase"
				@blur="closeEdit"
				@keydown.enter="pressEnter"
			/>
			<span class="keyphrase-delete" @click="deleteKeyphraseEv(index)">
				<core-tooltip
					type="action"
				>
					<svg-trash />

					<template #tooltip>
						{{ strings.delete }}
					</template>
				</core-tooltip>
			</span>
		</span>
	</div>
</template>

<script>
import {
	useKeywordRankTrackerStore
} from '@/vue/stores'

import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgPencil from '@/vue/components/common/svg/Pencil'
import SvgStatistics from '@/vue/components/common/svg/Statistics'
import SvgTrash from '@/vue/components/common/svg/Trash'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'selectedKeyphrase', 'deleted', 'saved' ],
	setup () {
		return {
			keywordRankTrackerStore : useKeywordRankTrackerStore()
		}
	},
	components : {
		CoreTooltip,
		SvgPencil,
		SvgStatistics,
		SvgTrash
	},
	props : {
		index : {
			type : Number
		},
		keyphrase : {
			type : String
		},
		score : {
			type : Number
		}
	},
	data () {
		return {
			edit    : false,
			strings : {
				delete : __('Delete', td)
			}
		}
	},
	computed : {
		scoreClass () {
			return 80 < this.score ? 'score-green' : 50 < this.score ? 'score-orange' : 1 < this.score ? 'score-red' : 'score-none'
		}
	},
	methods : {
		editKeyphraseEv (index) {
			this.edit = true
			this.$emit('selectedKeyphrase', index)
		},
		deleteKeyphraseEv (index) {
			this.edit = false
			this.$emit('deleted', index)
		},
		toggleKeyphrasesPanel (panel) {
			this.$emit('selectedKeyphrase', panel)
		},
		closeEdit (event) {
			const value = event.target?.value.trim()
			const index = this.index

			if (value) {
				this.edit = false
				this.$emit('saved', { index, value })
			} else {
				this.deleteKeyphraseEv(index)
			}
		},
		pressEnter (event) {
			event.preventDefault()
			event.target.blur()
			this.edit = false
		}
	}
}
</script>

<style lang="scss">
.aioseo-keyphrase-tag {
	.keyphrase-delete {
		.aioseo-tooltip {
			display: flex;
			margin: 0;
		}

		svg {
			width: 16px;
			height: 16px;
		}
	}

	.btn-keyword-rank-tracker {
		--gutter: 16px;

		display: inline-flex;
		margin: 0 calc(var(--gutter) * 2) 0 var(--gutter);
		position: relative;
		vertical-align: middle;

		&:after {
			background: $input-border;
			content: '';
			height: 20px;
			position: absolute;
			right: calc(var(--gutter) * -1);
			top: 50%;
			transform: translateY(-50%);
			width: 1px;
		}

		&:hover {
			opacity: 0.8;
		}
	}
}

</style>