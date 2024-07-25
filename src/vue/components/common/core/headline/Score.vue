<template>
	<div class="aioseo-headline-score">
		<div class="aioseo-headline-score-score">
			<core-site-score
				:loading="isAnalyzing"
				:score="score"
				:description="description"
				:strokeWidth="1.75"
			/>
		</div>
		<div
			class="aioseo-headline-score-overall"
			v-html="strings.overallScore"
		/>
		<div
			class="aioseo-headline-score-recommendations"
			v-html="`${strings.veryGoodScore} ${strings.forBestResults}`"
		/>
	</div>
</template>

<script>
import CoreSiteScore from '@/vue/components/common/core/site-score/Index'
export default {
	components : {
		CoreSiteScore
	},
	props : {
		score   : Number,
		loading : Boolean
	},
	data () {
		return {
			isAnalyzing : false,
			strings     : {
				overallScore  : this.$t.__('Overall Score', this.$td),
				veryGoodScore : this.$t.sprintf(
					// Translators: 1 - Opening bold HTML tag, 2 - Closing bold HTML tag, 3 - Initial score range, 4 - Final score range.
					this.$t.__('A very good score is between %1$s%3$d and %4$d%2$s.', this.$td),
					'<strong>',
					'</strong>',
					70,
					100
				),
				forBestResults : this.$t.sprintf(
					// Translators: 1 - Opening bold HTML tag, 2 - Closing bold HTML tag, 3 - Score.
					this.$t.__('For best results, you should strive for %1$s%3$d and above%2$s.', this.$td),
					'<strong>',
					'</strong>',
					70
				),
				weveGotWorkToDo : this.$t.sprintf(
					// Translators: 1 - HTML Line break tag.
					this.$t.__('We\'ve got some%1$swork to do!', this.$td),
					'<br>'
				),
				needsImprovement : this.$t.sprintf(
					// Translators: 1 - HTML Line break tag.
					this.$t.__('Needs%1$sImprovement!', this.$td),
					'<br>'
				),
				veryGood  : this.$t.__('Very Good!', this.$td),
				excellent : this.$t.__('Excellent!', this.$td)
			}
		}
	},
	computed : {
		description () {
			switch (true) {
				case 45 >= this.score:
					return this.strings.weveGotWorkToDo
				case 70 >= this.score:
					return this.strings.needsImprovement
				case 90 >= this.score:
					return this.strings.veryGood
				default:
					return this.strings.excellent
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-headline-score {
	position: relative;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-direction: column;
	padding: 0 30px;
	max-width: 25%;

	@media (max-width: 1280px) {
		padding: 0;
		max-width: 100%;
	}

	&-score {
		position: relative;
		min-width: 175px;
		max-width: 200px;
		margin-bottom: 16px;
		margin-right: auto;
		margin-left: auto;

		svg {
			width: 100%;
			height: auto;
		}
	}

	&-overall {
		font-size: 16px;
		font-weight: bold;
		text-align: center;
		margin-bottom: 12px;
	}

	&-recommendations {
		font-size: 14px;
		line-height: 150%;
		text-align: center;
		max-width: 220px;
	}
}
</style>