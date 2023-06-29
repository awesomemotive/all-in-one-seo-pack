<template>
	<div class="aioseo-site-score-dashboard">
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
			class="aioseo-seo-site-score-recommendations"
		>
			<div class="critical">
				<span class="round red">{{ summary.critical || 0 }}</span>
				{{ strings.criticalIssues }}
			</div>
			<div class="recommended">
				<span class="round blue">{{ summary.recommended || 0 }}</span>
				{{ strings.recommendedImprovements }}
			</div>
			<div class="good">
				<span class="round green">{{ summary.good || 0 }}</span>
				{{ strings.goodResults }}
			</div>

			<div class="links" v-if="allowed('aioseo_seo_analysis_settings')">
				<a :href="rootStore.aioseo.urls.aio.seoAnalysis">{{ strings.completeSiteAuditChecklist }}</a>
				<a :href="rootStore.aioseo.urls.aio.seoAnalysis" class="no-underline">&rarr;</a>
			</div>
		</div>

		<div
			class="analyze-errors"
			v-if="analyzerStore.analyzeError"
		>
			<strong>{{ strings.anErrorOccurred }}</strong><br/>
			<p>{{ getError }}</p>
		</div>
	</div>
</template>

<script>
import {
	useAnalyzerStore,
	useRootStore
} from '@/vue/stores'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { merge } from 'lodash-es'
import { useSeoSiteScore } from '@/vue/composables'
import { SeoSiteScore } from '@/vue/mixins'
import CoreSiteScore from '@/vue/components/common/core/site-score/Index'
export default {
	setup () {
		const { strings } = useSeoSiteScore()

		return {
			analyzerStore     : useAnalyzerStore(),
			rootStore         : useRootStore(),
			composableStrings : strings
		}
	},
	components : {
		CoreSiteScore
	},
	mixins : [ SeoSiteScore ],
	props  : {
		score   : Number,
		loading : Boolean,
		summary : {
			type : Object,
			default () {
				return {}
			}
		}
	},
	data () {
		return {
			allowed,
			strings : merge(this.composableStrings, {
				anErrorOccurred            : this.$t.__('An error occurred while analyzing your site.', this.$td),
				criticalIssues             : this.$t.__('Important Issues', this.$td),
				warnings                   : this.$t.__('Warnings', this.$td),
				recommendedImprovements    : this.$t.__('Recommended Improvements', this.$td),
				goodResults                : this.$t.__('Good Results', this.$td),
				completeSiteAuditChecklist : this.$t.__('Complete Site Audit Checklist', this.$td)
			})
		}
	},
	computed : {
		getError () {
			switch (this.analyzerStore.analyzeError) {
				case 'invalid-url':
					return this.$t.__('The URL provided is invalid.', this.$td)
				case 'missing-content':
					return this.$t.__('We were unable to parse the content for this site.', this.$td)
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
.aioseo-site-score-dashboard {
	position: relative;
	display: flex;
	align-items: center;
	justify-content: flex-start;

	.analyze-errors {
		align-items: center;
		justify-content: center;
		margin-left: auto;
		margin-right: auto;
		text-align: center;
	}

	.aioseo-seo-site-score-score {
		position: relative;
		width: 100%;
		max-width: 200px;
		margin-right: 1rem;

		svg {
			width: 100%;
			height: auto;
		}
	}

	.aioseo-seo-site-score-recommendations {
		> div:not(.links) {
			display: flex;
			align-items: center;
			font-size: 14px;
			color: $black;
			font-weight: 600;
			margin-bottom: 10px;

			.round {
				position: relative;
				border-radius: 50%;
				width: 24px;
				min-width: 24px;
				max-width: 24px;
				height: 24px;
				line-height: 24px;
				display: flex;
				align-items: center;
				justify-content: center;
				margin-right: 10px;
				font-size: 12px;
				color: #fff;
				font-weight: 600;

				&.red {
					background-color: $red;
				}

				&.blue {
					background-color: $blue;
				}

				&.orange {
					background-color: $orange;
				}

				&.green {
					background-color: $green;
				}
			}
		}

		.links {
			margin-top: 18px;
			font-size: 14px;
			font-weight: 600;

			.no-underline {
				padding-left: 5px;
			}
		}
	}
}
</style>