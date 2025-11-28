<template>
	<div class="focus-keyphrase-display">
		<core-keyphrase
			:index="0"
			:keyphrase="keyphrase"
			:score="score"
			@saved="handleSaved"
			@deleted="handleDeleted"
			class="aioseo-keyphrase-tag"
		/>

		<core-loader
			class="analysis-loading"
			v-if="loading"
			dark
		/>

		<metabox-analysis-detail
			v-if="!loading && analysisItems"
			:analysisItems="analysisItems"
		/>
	</div>
</template>

<script>
import CoreKeyphrase from '@/vue/components/common/core/Keyphrase'
import CoreLoader from '@/vue/components/common/core/Loader'
import MetaboxAnalysisDetail from '../MetaboxAnalysisDetail'

export default {
	emits      : [ 'saved', 'deleted' ],
	components : {
		CoreKeyphrase,
		CoreLoader,
		MetaboxAnalysisDetail
	},
	props : {
		keyphrase : {
			type     : String,
			required : true
		},
		score : {
			type    : Number,
			default : 0
		},
		loading : {
			type    : Boolean,
			default : false
		},
		analysisItems : {
			type    : Object,
			default : () => ({})
		}
	},
	methods : {
		handleSaved (payload) {
			this.$emit('saved', payload)
		},
		handleDeleted () {
			this.$emit('deleted')
		}
	}
}
</script>

<style lang="scss" scoped>
.focus-keyphrase-display {
	.analysis-loading {
		margin-top: 16px;
	}
}
</style>