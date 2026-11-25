<template>
	<div class="aioseo-seo-preview-standalone-view-seo-inspector">
		<div>
			<div
				class="first-half"
				v-if="isCheckEligible()"
			>
				<div class="child">
					<dl>
						<dt>{{ strings.focusKeyphrase }}</dt>
						<dd>
							<span v-if="focusKeyphrase">
								{{ focusKeyphrase }}
							</span>
							<span
								class="no-keyphrase-found"
								v-if="!focusKeyphrase"
							>
								<svg-circle-exclamation width="20"/>
								{{ strings.noKeyphraseFound }}
							</span>
						</dd>

						<dt>{{ strings.pageAnalysis }}</dt>
						<dd>
							<div
								v-if="focusKeyphrase"
								class="check"
							>
								<template v-if="groupHasData('keywords')">
									<component
										:is="getGroupIconComponent('keywords')"
										:class="getGroupIconClass('keywords')"
										class="check__icon"
									/>
								</template>
								<div>
									<span class="check__title">{{ strings.keywords }}: </span>
									<span class="check__feedback">{{ getGroupFeedback('keywords') }}</span>
								</div>
							</div>

							<div class="check">
								<template v-if="groupHasData('basic')">
									<component
										:is="getGroupIconComponent('basic')"
										:class="getGroupIconClass('basic')"
										class="check__icon"
									/>
								</template>
								<div>
									<span class="check__title">{{ strings.basicSeo }}: </span>
									<span class="check__feedback">{{ getGroupFeedback('basic') }}</span>
								</div>
							</div>

							<div
								v-if="hasReadability"
								class="check"
							>
								<template v-if="groupHasData('readability')">
									<component
										:is="getGroupIconComponent('readability')"
										:class="getGroupIconClass('readability')"
										class="check__icon"
									/>
								</template>
								<div>
									<span class="check__title">{{ strings.readability }}: </span>
									<span class="check__feedback">{{ getGroupFeedback('readability') }}</span>
								</div>
							</div>

							<div
								v-if="hasSpellingData"
								class="check"
							>
								<component
									:is="spellingHasIssues ? 'svg-circle-close' : 'svg-circle-check'"
									:class="spellingHasIssues ? 'red' : 'green'"
									class="check__icon"
								/>
								<div>
									<span class="check__title">{{ strings.spelling }}: </span>
									<span class="check__feedback">{{ spellingHasIssues ? strings.fixesNeeded : strings.noImprovementsNeeded }}</span>
								</div>
							</div>

							<div
								v-if="hasHeadlineScore"
								class="check"
							>
								<component
									:is="getHeadlineIconComponent(headlineScore)"
									:class="getHeadlineScoreColor(headlineScore)"
									class="check__icon"
								/>
								<div>
									<span class="check__title">{{ strings.headline }}: </span>
									<span class="check__feedback">{{ headlineScore }}/100</span>
								</div>
							</div>
						</dd>
					</dl>

					<core-alert
						v-if="rootStore.aioseo.editObjectBtnText && editFocusKeyphraseUrl"
						v-safe-html="`${strings.visitAdmin} <a style='display: inline-flex' href='${editFocusKeyphraseUrl}'>${rootStore.aioseo.editObjectBtnText} &rarr;</a>`"
						size="small"
					/>
				</div>
			</div>

			<div class="second-half">
				<div class="child">
					<dl>
						<dt>{{ strings.metaTags }}</dt>
						<dd>
							<view-meta-tags/>
						</dd>
					</dl>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import { merge } from 'lodash-es'

import { useTruSeoScore } from '@/vue/composables/TruSeoScore'
import { isBadResult } from '@/app/tru-seo/scoring/interpreters'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import SvgIconPencil from '@/vue/components/common/svg/Pencil'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import ViewMetaTags from './MetaTags'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const { strings } = useTruSeoScore()

		return {
			rootStore         : useRootStore(),
			composableStrings : strings
		}
	},
	components : {
		CoreAlert,
		SvgIconPencil,
		SvgCircleCheck,
		SvgCircleExclamation,
		SvgCircleClose,
		ViewMetaTags
	},
	data () {
		return {
			strings : merge(this.composableStrings, {
				focusKeyphrase       : __('Focus Keyword', td),
				pageAnalysis         : __('Page Analysis', td),
				keywords             : __('Keywords', td),
				basicSeo             : __('Basic SEO', td),
				readability          : __('Readability', td),
				spelling             : __('Spelling', td),
				headline             : __('Headline Score', td),
				metaTags             : __('Meta Tags', td),
				noKeyphraseFound     : __('No focus keyword set', td),
				fixesNeeded          : __('Fixes needed', td),
				noImprovementsNeeded : __('No improvements needed', td),
				noDataYet            : __('No data yet', td),
				visitAdmin           : __('You can edit the "Focus Keyword" and view information about "Page Analysis" on the admin side.', td)
			})
		}
	},
	computed : {
		focusKeyphrase () {
			return this.rootStore.truseoData?.focusKeyword || false
		},
		// False on a term, whose editor hides the Readability tab because its description is a
		// single short block. Without this the row would read "No data yet" forever.
		// NOTE: wp_localize_script stringifies PHP booleans, so `false` arrives as ''. Never
		// strict-compare against false. An absent key defaults to true so a post keeps its row.
		hasReadability () {
			const value = this.rootStore.aioseo?.hasReadability

			return undefined === value ? true : !!value
		},
		hasHeadlineScore () {
			return null !== this.headlineScore
		},
		headlineScore () {
			// The data is localized as strings, so coerce; '' / null means the row is hidden.
			const raw = this.rootStore.aioseo?.headlineScore

			if (null === raw || undefined === raw || '' === raw) {
				return null
			}

			const score = Number(raw)

			return Number.isFinite(score) ? score : null
		},
		spellingScore () {
			const score = this.rootStore.truseoData?.truseo?.general?.spelling?.spellingChecker?.score

			return Number.isFinite(score) ? score : null
		},
		hasSpellingData () {
			// Score 0 is the neutral result when the dictionary isn't loaded or the
			// language is unsupported, so hide the row rather than flag an issue.
			return null !== this.spellingScore && 0 < this.spellingScore
		},
		spellingHasIssues () {
			return this.hasSpellingData && isBadResult(this.spellingScore)
		},
		editFocusKeyphraseUrl () {
			try {
				if (!this.rootStore.aioseo?.editObjectUrl) {
					return '#'
				}

				const scrollTo = 'aioseo-post-content-analysis'
				const url = new URL(this.rootStore.aioseo.editObjectUrl)

				return `${url.href}&aioseo-tab=analysis&aioseo-scroll=${scrollTo}&aioseo-highlight=${scrollTo}`
			} catch (e) {
				return '#'
			}
		}
	},
	methods : {
		countIssues (items) {
			return Object.values(items || {}).filter(item => isBadResult(item?.score) && '' !== item?.title).length
		},
		hasItems (items) {
			return 0 < Object.keys(items || {}).length
		},
		getGroupCount (which) {
			// The "Keywords" line covers every keyword, so sum the focus keyword and each additional one.
			if ('keywords' === which) {
				let count = this.countIssues(this.rootStore.truseoData?.truseo?.focus_keyword?.items)

				this.rootStore.truseoData?.additionalKeywords?.forEach(keyword => {
					count += this.countIssues(keyword?.items)
				})

				return count
			}

			return this.countIssues(this.rootStore.truseoData?.truseo?.general?.[which])
		},
		groupHasData (which) {
			if ('keywords' === which) {
				return this.hasItems(this.rootStore.truseoData?.truseo?.focus_keyword?.items) ||
					!!this.rootStore.truseoData?.additionalKeywords?.some(keyword => this.hasItems(keyword?.items))
			}

			return this.hasItems(this.rootStore.truseoData?.truseo?.general?.[which])
		},
		getGroupIconClass (which) {
			return this.getGroupCount(which) ? 'orange' : 'green'
		},
		getGroupIconComponent (which) {
			return 'orange' === this.getGroupIconClass(which) ? 'svg-circle-exclamation' : 'svg-circle-check'
		},
		getGroupFeedback (which) {
			if (!this.groupHasData(which)) {
				return this.strings.noDataYet
			}

			const count = this.getGroupCount(which)

			return count ? this.improvementsLabel(count) : this.strings.noImprovementsNeeded
		},
		improvementsLabel (count) {
			return sprintf(
				// Translators: %1$d - The number of items still to improve.
				__('%1$d to improve', td),
				count
			)
		},
		getHeadlineScoreColor (score) {
			if (40 > score) {
				return 'red'
			}

			if (70 > score) {
				return 'orange'
			}

			return 'green'
		},
		getHeadlineIconComponent (score) {
			const color = this.getHeadlineScoreColor(score)

			if ('red' === color) {
				return 'svg-circle-close'
			}

			if ('orange' === color) {
				return 'svg-circle-exclamation'
			}

			return 'svg-circle-check'
		},
		isCheckEligible () {
			return !!this.rootStore.truseoData?.truseo?.general &&
				this.rootStore.aioseo.aioseoPageAnalysis
		}
	}
}
</script>

<style lang="scss" scoped>
.aioseo-seo-preview-standalone-view-seo-inspector {
	> div {
		display: flex;
		margin: 0 -20px;
	}

	dl {
		margin: 0;
		padding: 0;

		dt,
		dd {
			font-family: $font-family;
			margin: 0;
			padding: 0;
		}

		dt {
			color: $black;
			font-size: 16px;
			font-weight: 700;

			+ dd {
				margin-top: 8px;
			}
		}

		dd {
			color: $black2;
			font-size: 14px;
			font-weight: 400;
			overflow-wrap: break-word;
			word-break: break-word;

			+ dt {
				margin-top: 20px;
			}
		}
	}

	.check {
		align-items: center;
		display: flex;
		flex-wrap: nowrap;
		gap: 3px;

		+ .check {
			margin-top: 10px;
		}

		&__icon {
			flex: 0 1 20px;
			min-width: 20px;

			&.red {
				color: $red;
			}

			&.orange {
				color: #e8730c;
			}

			&.green {
				color: $green;
			}
		}

		&__title {
			font-weight: 700;
		}
	}

	.first-half,
	.second-half {
		padding: 0 20px;
		position: relative;
	}

	.first-half {
		flex: 0 1 auto;
		min-width: 40%;
		width: 100%;

		+ .second-half {
			&:before {
				background-color: $gray;
				content: '';
				height: 100%;
				left: 0;
				position: absolute;
				top: 0;
				width: 1px;
			}
		}
	}

	.second-half {
		flex: 1 1 60%;
		min-width: 60%;
		width: 100%;
	}

	.no-keyphrase-found {
		align-items: center;
		color: $orange;
		display: flex;
		gap: 2px;
	}

	.aioseo-alert {
		margin-top: 30px;

		:deep(a) {
			color: $blue;
			text-decoration: none;
		}
	}
}
</style>