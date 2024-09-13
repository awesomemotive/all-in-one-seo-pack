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
								v-if="checkErrorsExists('basic')"
								class="check"
							>
								<component
									:is="getCheckIconComponent('basic')"
									:class="getErrorClass(getCheckErrors('basic'))"
									class="check__icon"
								/>
								<div>
									<span class="check__title">{{ strings.basicSeo }}: </span>
									<span class="check__feedback">{{ getErrorDisplay(getCheckErrors('basic')) }}</span>
								</div>
							</div>
							<div
								v-else
								class="check"
							>
								<span class="check__title">{{ strings.basicSeo }}: </span>
								<span class="check__feedback">{{ strings.noDataYet }}</span>
							</div>

							<div
								v-if="checkErrorsExists('title')"
								class="check"
							>
								<component
									:is="getCheckIconComponent('title')"
									:class="getErrorClass(getCheckErrors('title'))"
									class="check__icon"
								/>
								<div>
									<span class="check__title">{{ strings.title }}: </span>
									<span class="check__feedback">{{ getErrorDisplay(getCheckErrors('title')) }}</span>
								</div>
							</div>
							<div
								v-else
								class="check"
							>
								<span class="check__title">{{ strings.title }}: </span>
								<span class="check__feedback">{{ strings.noDataYet }}</span>
							</div>

							<div
								v-if="checkErrorsExists('readability')"
								class="check"
							>
								<component
									:is="getCheckIconComponent('readability')"
									:class="getErrorClass(getCheckErrors('readability'))"
									class="check__icon"
								/>
								<div>
									<span class="check__title">{{ strings.readability }}: </span>
									<span class="check__feedback">{{ getErrorDisplay(getCheckErrors('readability')) }}</span>
								</div>
							</div>
							<div
								v-else
								class="check"
							>
								<span class="check__title">{{ strings.readability }}: </span>
								<span class="check__feedback">{{ strings.noDataYet }}</span>
							</div>
						</dd>
					</dl>

					<core-alert
						v-if="rootStore.aioseo.editObjectBtnText && editFocusKeyphraseUrl"
						v-html="`${strings.visitAdmin} <a style='display: inline-flex' href='${editFocusKeyphraseUrl}'>${rootStore.aioseo.editObjectBtnText} &rarr;</a>`"
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

import CoreAlert from '@/vue/components/common/core/alert/Index'
import SvgIconPencil from '@/vue/components/common/svg/Pencil'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import ViewMetaTags from './MetaTags'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			getErrorClass,
			getErrorDisplay,
			strings
		} = useTruSeoScore()

		return {
			getErrorClass,
			getErrorDisplay,
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
				focusKeyphrase   : __('Focus Keyphrase', td),
				pageAnalysis     : __('Page Analysis', td),
				basicSeo         : __('Basic SEO', td),
				readability      : __('Readability', td),
				title            : __('Title', td),
				metaTags         : __('Meta Tags', td),
				noKeyphraseFound : __('No keyphrase found', td),
				noDataYet        : __('No data yet', td),
				visitAdmin       : __('You can edit the "Focus Keyphrase" and view information about "Page Analysis" on the admin side.', td)
			})
		}
	},
	computed : {
		focusKeyphrase () {
			return this.rootStore.aioseo?.keyphrases?.focus?.keyphrase || false
		},
		editFocusKeyphraseUrl () {
			try {
				if (!this.rootStore.aioseo?.editObjectUrl) {
					return '#'
				}

				const scrollTo = 'aioseo-post-settings-snippet-focus-keyphrase-row'
				const url = new URL(this.rootStore.aioseo.editObjectUrl)

				return `${url.href}&aioseo-scroll=${scrollTo}&aioseo-highlight=${scrollTo}`
			} catch (e) {
				return '#'
			}
		}
	},
	methods : {
		getCheckObject (which) {
			return this.rootStore.aioseo?.page_analysis?.analysis[which]
		},
		getCheckErrors (which) {
			return this.getCheckObject(which)?.errors || 0
		},
		getCheckIconComponent (which) {
			const errorClass = this.getErrorClass(this.getCheckErrors(which))

			if ('red' === errorClass) {
				return 'svg-circle-close'
			} else if ('orange' === errorClass) {
				return 'svg-circle-exclamation'
			}

			return 'svg-circle-check'
		},
		checkErrorsExists (which) {
			return 'undefined' !== typeof this.rootStore.aioseo.page_analysis.analysis?.[which]?.errors
		},
		isCheckEligible () {
			return 'undefined' !== typeof this.rootStore.aioseo.page_analysis?.analysis
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
				color: $orange;
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