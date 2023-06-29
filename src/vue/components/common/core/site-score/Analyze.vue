<template>
	<div class="aioseo-site-score-analyze">
		<div
			v-if="!analyzerStore.analyzeError"
			class="aioseo-seo-site-score-score"
		>
			<core-site-score
				:loading="loading"
				:score="score"
				:description="description"
				:strokeWidth="1.75"
			/>
		</div>

		<div
			v-if="!analyzerStore.analyzeError"
			class="aioseo-seo-site-score-description"
		>
			<h2>{{ strings.yourOverallSiteScore }}</h2>

			<div v-html="strings.goodResult" />

			<div v-html="strings.forBestResults" />

			<div class="d-flex">
				<svg-book />

				<a
					:href="$links.getDocUrl('ultimateGuide')"
					target="_blank"
				>{{ strings.readUltimateSeoGuide }}</a>
			</div>
		</div>

		<div
			class="analyze-errors"
			v-if="analyzerStore.analyzeError"
		>
			<h3>{{ strings.anErrorOccurred }}</h3>

			<span v-html="getError"/>
		</div>
	</div>
</template>

<script>
import {
	useAnalyzerStore
} from '@/vue/stores'

import CoreSiteScore from '@/vue/components/common/core/site-score/Index'
import SvgBook from '@/vue/components/common/svg/Book'
export default {
	setup () {
		return {
			analyzerStore : useAnalyzerStore()
		}
	},
	components : {
		CoreSiteScore,
		SvgBook
	},
	props : {
		score       : Number,
		loading     : Boolean,
		description : String,
		summary     : {
			type : Object,
			default () {
				return {}
			}
		}
	},
	data () {
		return {
			strings : {
				yourOverallSiteScore : this.$t.__('Your Overall Site Score', this.$td),
				goodResult           : this.$t.sprintf(
					// Translators: 1 - Opening bold HTML tag, 2 - Closing bold HTML tag, 3 - Initial score range, 4 - Final score range.
					this.$t.__('A very good score is between %1$s%3$d and %4$d%2$s.', this.$td),
					'<strong>',
					'</strong>',
					50,
					75
				),
				forBestResults : this.$t.sprintf(
					// Translators: 1 - Opening bold HTML tag, 2 - Closing bold HTML tag, 3 - Score.
					this.$t.__('For best results, you should strive for %1$s%3$d and above%2$s.', this.$td),
					'<strong>',
					'</strong>',
					70
				),
				anErrorOccurred            : this.$t.__('An error occurred while analyzing your site.', this.$td),
				criticalIssues             : this.$t.__('Important Issues', this.$td),
				warnings                   : this.$t.__('Warnings', this.$td),
				recommendedImprovements    : this.$t.__('Recommended Improvements', this.$td),
				goodResults                : this.$t.__('Good Results', this.$td),
				completeSiteAuditChecklist : this.$t.__('Complete Site Audit Checklist', this.$td),
				readUltimateSeoGuide       : this.$t.__('Read the Ultimate WordPress SEO Guide', this.$td)
			}
		}
	},
	computed : {
		getError () {
			switch (this.analyzerStore.analyzeError) {
				case 'invalid-url':
					return this.$t.__('The URL provided is invalid.', this.$td)
				case 'missing-content':
					return this.$t.sprintf(
						'%1$s %2$s',
						this.$t.__('We were unable to parse the content for this site.', this.$td),
						this.$links.getDocLink(this.$constants.GLOBAL_STRINGS.learnMore, 'seoAnalyzerIssues', true)
					)
				case 'invalid-token':
					return this.$t.sprintf(
						// Translators: 1 - The plugin short name ('AIOSEO').
						this.$t.__('Your site is not connected. Please connect to %1$s, then try again.', this.$td),
						import.meta.env.VITE_SHORT_NAME
					)
			}

			return this.analyzerStore.analyzeError
		}
	}
}
</script>

<style lang="scss">
.aioseo-site-score-analyze {
	position: relative;
	display: flex;
	align-items: center;
	justify-content: center;
	flex: 1;

	.analyze-errors {
		text-align: center;
		margin-bottom: 1em;
	}

	.aioseo-seo-site-score-score {
		position: relative;
		width: 100%;
		max-width: 160px;
		margin-right: 32px;

		svg {
			width: 100%;
			height: auto;
		}

		.aioseo-site-score {
			display: flex;
		}

		.aioseo-score-amount {

			.score {
				font-size: 40px;
				line-height: 1.2;
			}

			.total {
				font-size: 14px;
			}
		}

		.score-description {
			font-size: 13px;
		}
	}

	.aioseo-seo-site-score-description {

		h2 {
			font-size: 24px;
			line-height: 30px;
			margin-bottom: 12px;
		}

		div[class] {
			margin-top: 12px;
		}

		svg.aioseo-book {
			width: 20px;
			height: 20px;
			margin: 0 10px 0 0;
			color: $blue;
		}

		.links {
			margin-top: 30px;
			font-size: 14px;
			font-weight: 600;

			.no-underline {
				padding-left: 5px;
			}
		}
	}
}
</style>