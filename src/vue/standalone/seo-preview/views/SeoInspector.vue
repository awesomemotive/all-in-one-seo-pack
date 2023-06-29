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

						<dt>{{ strings.checks }}</dt>
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
import { useTruSeoScore } from '@/vue/composables'
import { TruSeoScore } from '@/vue/mixins/TruSeoScore'
import SvgIconPencil from '@/vue/components/common/svg/Pencil'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import ViewMetaTags from './MetaTags'

export default {
	setup () {
		const { strings } = useTruSeoScore()

		return {
			rootStore         : useRootStore(),
			composableStrings : strings
		}
	},
	components : {
		SvgIconPencil,
		SvgCircleCheck,
		SvgCircleExclamation,
		SvgCircleClose,
		ViewMetaTags
	},
	computed : {
		focusKeyphrase () {
			return this.rootStore.aioseo?.keyphrases?.focus?.keyphrase || false
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
	},
	mixins : [ TruSeoScore ],
	data () {
		return {
			strings : merge(this.composableStrings, {
				focusKeyphrase   : this.$t.__('Focus Keyphrase', this.$td),
				checks           : this.$t.__('Checks', this.$td),
				basicSeo         : this.$t.__('Basic SEO', this.$td),
				readability      : this.$t.__('Readability', this.$td),
				title            : this.$t.__('Title', this.$td),
				metaTags         : this.$t.__('Meta Tags', this.$td),
				noKeyphraseFound : this.$t.__('No keyphrase found', this.$td),
				noDataYet        : this.$t.__('No data yet', this.$td)
			})
		}
	}
}
</script>

<style lang="scss" scoped>
.aioseo-seo-preview-standalone-view-seo-inspector {
	padding: 20px;

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
			line-height: 1.4;
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

			+ dt {
				margin-top: 20px;
			}
		}
	}

	.check {
		align-items: center;
		display: flex;
		flex-wrap: nowrap;
		gap: 2px;
		line-height: 1.2;

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
		line-height: 1;
	}
}
</style>