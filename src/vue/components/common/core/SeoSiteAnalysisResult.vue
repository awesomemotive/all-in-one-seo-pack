<template>
	<div class="aioseo-seo-site-analysis-result">
		<div class="result-header">
			<span
				class="result-status"
				:class="result.status"
			></span>

			<div class="result-content">
				{{ getTestTitle }}
			</div>

			<div
				v-if="showInstructions || getBody.code ||getBody.codeAlt"
				class="result-toggle"
				:class="{ active : activeRow }"
				@click="toggleActive"
			>
				<svg-caret />
			</div>
		</div>

		<transition-slide
			v-if="showInstructions || getBody.code ||getBody.codeAlt"
			:active="activeRow"
		>
			<div class="result-body">
				<div
					v-if="getBody.code"
					class="result-code"
				>
					<pre>
						<code
							v-html="softSanitizeHtml(getBody.code.trim())"
						/>
					</pre>
				</div>

				<div
					v-if="getBody.codeAlt"
					class="result-code-alt"
				>
					<pre>
						<code>{{ softSanitizeHtml(getBody.codeAlt.trim()) }}</code>
					</pre>
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
					>
						{{ getBody.buttonText }}
					</base-button>
				</div>
			</div>
		</transition-slide>
	</div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { softSanitizeHtml } from '@/vue/utils/strings'

import SiteAnalysis from '@/vue/classes/SiteAnalysis'
import SvgCaret from '@/vue/components/common/svg/Caret'
import TransitionSlide from '@/vue/components/common/transition/Slide'

const emit = defineEmits([ 'toggleActive' ])
const props = defineProps({
	test : {
		type     : String,
		required : true
	},
	result : {
		type     : Object,
		required : true
	},
	showInstructions : {
		type     : Boolean,
		required : false
	},
	activeRow : {
		type     : Boolean,
		required : false
	}
})

const loading = ref(false)

const getTestTitle = computed(() => SiteAnalysis.head(props.test, props.result))
const getBody = computed(() => SiteAnalysis.body(props.test, props.result))

function toggleActive () {
	emit('toggleActive')
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

		.result-status {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			margin-right: 14px;

			&.passed {
				background-color: $green;
			}

			&.error {
				background-color: $red;
			}

			&.warning {
				background-color: $orange;
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
				background-color: $blue;

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
				color: $black;
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
				display: flex;
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
				white-space: pre-line;
			}
		}

		.result-action {
			margin-top: 20px;
		}
	}
}
</style>