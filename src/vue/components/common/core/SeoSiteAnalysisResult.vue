<template>
	<div class="aioseo-seo-site-analysis-result">
		<div class="result-header">
			<div class="result-icon">
				<component
					:is="getIcon"
					:class="result.status"
				/>
			</div>

			<div class="result-content">
				{{ getTestTitle }}
			</div>

			<div
				v-if="showInstructions || getBody.code ||getBody.codeAlt"
				class="result-toggle"
				:class="{ active : active }"
				@click="active = !active"
			>
				<svg-caret />
			</div>
		</div>

		<transition-slide
			v-if="showInstructions || getBody.code ||getBody.codeAlt"
			:active="active"
		>
			<div class="result-body">
				<div
					v-if="getBody.code"
					class="result-code"
				>
					<pre><code
						v-html="getBody.code"
					/></pre>
				</div>

				<div
					v-if="getBody.codeAlt"
					class="result-code-alt"
				>
					<pre><code>{{ getBody.codeAlt }}</code></pre>
				</div>

				<div
					v-if="getBody.message && showInstructions"
					class="result-message"
					v-html="getBody.message"
				/>

				<div
					class="result-action"
					v-if="getBody.buttonLink && showInstructions"
				>
					<base-button
						:href="getBody.buttonLink"
						tag="a"
						target="_blank"
						type="blue"
						size="medium"
						:loading="loading"
					>{{ getBody.buttonText }}</base-button>
				</div>
			</div>
		</transition-slide>
	</div>
</template>

<script>
import SiteAnalysis from '@/vue/classes/SiteAnalysis'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import SvgCircleInformation from '@/vue/components/common/svg/circle/Information'
import SvgGear from '@/vue/components/common/svg/Gear'
import TransitionSlide from '@/vue/components/common/transition/Slide'
export default {
	components : {
		SvgCaret,
		SvgCircleCheck,
		SvgCircleClose,
		SvgCircleInformation,
		SvgGear,
		TransitionSlide
	},
	props : {
		test : {
			type     : String,
			required : true
		},
		result : {
			type     : Object,
			required : true
		},
		showInstructions : Boolean
	},
	data () {
		return {
			active  : false,
			loading : false
		}
	},
	computed : {
		getIcon () {
			return 'passed' === this.result.status
				? 'svg-circle-check'
				: (
					'error' === this.result.status
						? 'svg-circle-close'
						: (
							'warning' === this.result.status
								? 'svg-gear'
								: 'svg-circle-information'
						)
				)
		},
		getTestTitle () {
			SiteAnalysis.personalize = this.showInstructions
			return SiteAnalysis.head(this.test, this.result)
		},
		getBody () {
			SiteAnalysis.personalize = this.showInstructions
			return SiteAnalysis.body(this.test, this.result)
		}
	}
}
</script>

<style lang="scss">
.aioseo-seo-site-analysis-result {
	border: 1px solid $gray;
	margin-top: 8px;

	.result-header {
		height: 48px;
		padding: 0 13px;
		display: flex;
		align-items: center;

		.result-icon {
			display: flex;
			align-items: center;
			margin-right: 14px;

			svg {
				width: 24px;
				height: 24px;
				color: $placeholder-color;

				&.passed {
					color: $green;
				}

				&.error {
					color: $red;
				}

				&.warning {
					// color: $orange2;
					color: $blue;
				}
			}
		}

		.result-content {
			font-size: $font-md;
			line-height: 22px;
			font-weight: 600;
			flex: 1;
		}

		.result-toggle {
			width: 30px;
			height: 26px;
			border: 1px solid $gray;
			border-radius: 3px;
			display: flex;
			align-items: center;
			justify-content: center;
			cursor: pointer;

			&.active,
			&:hover {
				background-color: $black2;

				svg {
					color: #fff;
				}
			}

			&.active {
				svg {
					transform: rotate(-180deg);
				}
			}

			svg {
				width: 100%;
				max-width: 20px;
				height: auto;
				color: $placeholder-color;
				transform: rotate(-90deg);
				transition: transform 0.3s;
			}
		}
	}

	.result-body {
		padding: 0 40px 22px;

		.result-message {
			color: $black2;
			font-size: 14px;
		}

		.result-code,
		.result-code-alt {
			pre {
				background: $background;
				border-radius: 3px;
				max-width: 100%;
				padding: 10px;
				overflow: auto;

				code {
					padding: 0;
					background: transparent;
				}
			}
		}

		.result-code {
			pre {
				white-space: pre-wrap;
			}
		}

		.result-action {
			margin-top: 20px;
		}
	}
}
</style>