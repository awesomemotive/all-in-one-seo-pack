<template>
	<div class="results">
		<table
			aria-label="Additional Keywords"
			class="additional-keyphrases-table"
			cellpadding="0"
			cellspacing="0"
		>
			<thead>
				<tr class="keyphrases-header">
					<th class="keyphrase">{{ strings.keyphrase }}</th>
					<th class="keyphrase-volume">{{ strings.volume }}</th>
					<th class="keyphrase-trend">{{ strings.trend }}</th>
					<th
						v-if="!isUnlicensed"
						class="keyphrase-actions"
					/>
				</tr>
			</thead>
			<tbody class="keyphrases-rows">
				<template v-if="keywords.length && !loading">
					<semrush-keyword-table-row
						v-for="(keyword, index) in keywords"
						:key="index"
						:keyword="keyword"
						:index="index"
						:focus-keyphrase="focusKeyphrase"
						:has-additional="hasAdditionalKeyphrase(keyword[0])"
						:additional-score="getAdditionalKeyphraseScore(keyword[0])"
						:is-adding="addingIndex === index"
						:is-removing="removingIndex === index"
						:max-reached="maxReached"
						:is-unlicensed="isUnlicensed"
						:max-additional-keyphrases="maxAdditionalKeyphrases"
						@add="handleAdd"
						@remove="handleRemove"
						@navigate="handleNavigate"
					/>
				</template>

				<template v-if="!keywords.length || loading">
					<tr class="keyphrase-row">
						<td
							:colspan="isUnlicensed ? 3 : 4"
							class="no-results"
						>
							<div>
								<core-loader
									v-if="loading"
									dark
								/>

								<span v-if="!loading && !error">
									{{ strings.noResults }}
								</span>

								<core-alert
									type="red"
									v-if="error && !error.includes('TOTAL LIMIT EXCEEDED')"
								>
									{{ formattedError }}
								</core-alert>

								<template v-if="error && error.includes('TOTAL LIMIT EXCEEDED')">
									<div class="semrush-logo">
										<svg-logo-semrush />
									</div>
									<div class="semrush-upsell">
										<span>
											<strong v-html="strings.youHaveExceededSemrush" />
										</span>

										{{ ' ' }}

										<span v-html="strings.inOrderToUpgradeSemrush" />
									</div>
								</template>
							</div>
						</td>
					</tr>
				</template>
			</tbody>
		</table>
	</div>
</template>

<script>
import links from '@/vue/utils/links'
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreLoader from '@/vue/components/common/core/Loader'
import SvgLogoSemrush from '@/vue/components/common/svg/logo/Semrush'
import SemrushKeywordTableRow from './SemrushKeywordTableRow'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits      : [ 'add', 'remove', 'navigate' ],
	components : {
		CoreAlert,
		CoreLoader,
		SvgLogoSemrush,
		SemrushKeywordTableRow
	},
	props : {
		keywords : {
			type    : Array,
			default : () => []
		},
		loading : {
			type    : Boolean,
			default : false
		},
		error : {
			type    : String,
			default : ''
		},
		focusKeyphrase : {
			type     : String,
			required : true
		},
		additionalKeyphrases : {
			type    : Array,
			default : () => []
		},
		addingIndex : {
			type    : [ Number, Boolean ],
			default : false
		},
		removingIndex : {
			type    : [ Number, Boolean ],
			default : false
		},
		maxAdditionalKeyphrases : {
			type     : Number,
			required : true
		},
		isUnlicensed : {
			type    : Boolean,
			default : false
		}
	},
	computed : {
		maxReached () {
			return this.maxAdditionalKeyphrases <= (this.additionalKeyphrases?.length || 0)
		},
		formattedError () {
			if (this.error && this.error.includes('TOTAL LIMIT EXCEEDED')) {
				return __('You have exceeded the limit for requests. Please try again later.', td)
			}

			return this.error || __('An error occurred while fetching keywords. Please try again later.', td)
		},
		strings () {
			return {
				keyphrase              : __('Keyword', td),
				volume                 : __('Volume', td),
				trend                  : __('Trend', td),
				noResults              : __('No results', td),
				youHaveExceededSemrush : sprintf(
					// Translators: 1 - Semrush.
					__('You have exceeded the number of requests allowed by your %1$s plan.', td),
					'Semrush'
				),
				inOrderToUpgradeSemrush : sprintf(
					// Translators: 1 - Link to learn more.
					__('In order to continue searching for additional keywords, you\'ll need to upgrade. %1$s', td),
					links.getUpsellLink('post-settings', 'semrush-pricing', GLOBAL_STRINGS.learnMore, 'semrushPricing', true)
				)
			}
		}
	},
	methods : {
		hasAdditionalKeyphrase (keyphrase) {
			if (!this.additionalKeyphrases || !Array.isArray(this.additionalKeyphrases)) {
				return false
			}
			return 0 < this.additionalKeyphrases.filter(k => k.keyphrase.toLowerCase() === keyphrase.toLowerCase()).length
		},
		getAdditionalKeyphraseScore (keyphrase) {
			if (!this.additionalKeyphrases || !Array.isArray(this.additionalKeyphrases)) {
				return 0
			}
			const found = this.additionalKeyphrases.find(k => k.keyphrase.toLowerCase() === keyphrase.toLowerCase())
			return found ? found.score : 0
		},
		handleAdd (keyphrase, index) {
			this.$emit('add', keyphrase, index)
		},
		handleRemove (keyphrase, index) {
			this.$emit('remove', keyphrase, index)
		},
		handleNavigate (keyphrase) {
			this.$emit('navigate', keyphrase)
		}
	}
}
</script>

<style lang="scss" scoped>
.results {
	margin-top: 20px;

	.additional-keyphrases-table {
		width: 100%;
		border: 1px solid $input-border;
		border-radius: 3px;

		.keyphrase-volume,
		.keyphrase-trend {
			text-align: center;
		}

		.keyphrases-header {
			height: 50px;
			font-size: 14px;

			th {
				border-bottom: 1px solid $input-border;
				padding: 15px;

				&:first-of-type {
					padding-left: 30px;
				}

				&:last-of-type {
					padding-right: 30px;
				}
			}
		}

		.keyphrases-rows {
			font-size: 14px;

			.no-results {
				> div {
					display: flex;
					align-items: center;
					justify-content: center;
					min-height: 200px;
				}

				.semrush-logo {
					padding: 0 30px 0 0;
					min-width: 150px;
				}

				.semrush-upsell {
					text-align: left;
				}
			}
		}
	}
}
</style>