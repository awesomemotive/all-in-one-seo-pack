<template>
	<div
		v-if="hasKeyphrases"
		class="additional-keyphrases-display"
	>
		<core-keyphrase
			v-for="(keyphrase, index) in keyphrases"
			:key="index"
			:index="index"
			:keyphrase="keyphrase.keyphrase"
			:score="keyphrase.score"
			@saved="handleSaved"
			@deleted="handleDeleted"
			@selectedKeyphrase="handleSelectedKeyphrase"
			class="aioseo-keyphrase-tag additional-keyphrase"
			:class="(selectedKeyphrase === index) ? 'selected' : null"
		/>

		<div class="analysis-wrapper">
			<core-loader
				v-if="isLoading"
				class="analysis-loading"
				dark
			/>

			<metabox-analysis-detail
				v-if="!isLoading && hasAnalysis"
				:analysisItems="analysisItems"
			/>
		</div>
	</div>
</template>

<script>
import CoreKeyphrase from '@/vue/components/common/core/Keyphrase'
import CoreLoader from '@/vue/components/common/core/Loader'
import MetaboxAnalysisDetail from '../MetaboxAnalysisDetail'

export default {
	emits      : [ 'saved', 'deleted', 'selected' ],
	components : {
		CoreKeyphrase,
		CoreLoader,
		MetaboxAnalysisDetail
	},
	props : {
		keyphrases : {
			type     : Array,
			required : true
		},
		selectedKeyphrase : {
			type    : Number,
			default : 0
		},
		loading : {
			type    : Array,
			default : () => []
		}
	},
	computed : {
		hasKeyphrases () {
			return this.keyphrases && 0 < this.keyphrases.length
		},
		isLoading () {
			return this.loading[this.selectedKeyphrase] &&
				this.keyphrases[this.selectedKeyphrase] &&
				this.keyphrases[this.selectedKeyphrase].keyphrase
		},
		hasAnalysis () {
			return this.keyphrases[this.selectedKeyphrase] &&
				this.keyphrases[this.selectedKeyphrase].keyphrase
		},
		analysisItems () {
			return this.keyphrases[this.selectedKeyphrase]?.analysis || {}
		}
	},
	methods : {
		handleSaved (payload) {
			this.$emit('saved', payload)
		},
		handleDeleted (index) {
			this.$emit('deleted', index)
		},
		handleSelectedKeyphrase (index) {
			this.$emit('selected', index)
		}
	}
}
</script>

<style lang="scss" scoped>
.additional-keyphrases-display {
	.analysis-wrapper {
		margin-top: 16px;
	}

	.analysis-loading {
		margin-top: 16px;
	}
}
</style>