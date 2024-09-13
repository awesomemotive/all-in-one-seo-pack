<template>
	<div class="analyze-main">
		<div
			class="analyze-header"
			v-html="header"
		/>

		<div
			class="analyze-description"
			v-html="description"
		/>

		<div class="analyze-input">
			<base-input
				v-model="input"
				:placeholder="placeholder"
				:class="{ 'aioseo-error' : inputError }"
				@keyup.enter="$emit('startAnalyzing', input)"
			/>

			<base-button
				type="green"
				@click="$emit('startAnalyzing', input)"
				:loading="isAnalyzing"
				:disabled="!input"
			>
				{{ strings.analyze }}
			</base-button>
		</div>

		<slot name="errors"></slot>

		<div
			v-if="isAnalyzing"
			class="analyze-progress"
		>
			<div
				class="analyze-progress-value"
				:style="{
					animationDuration : `${analyzeTime}s`
				}"
			></div>
		</div>
	</div>
</template>

<script>
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'startAnalyzing' ],
	props : {
		header      : String,
		description : String,
		isAnalyzing : Boolean,
		analyzeTime : {
			type : Number,
			default () {
				return 8
			}
		},
		placeholder : {
			type : String,
			default () {
				return ''
			}
		},
		inputError : {
			type : Boolean,
			default () {
				return false
			}
		}
	},
	data () {
		return {
			input   : null,
			strings : {
				analyze : __('Analyze', td)
			}
		}
	},
	watch : {
		isAnalyzing (newValue) {
			if (!newValue) {
				this.input = null
			}
		}
	}
}
</script>

<style lang="scss">
.analyze-main {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-self: center;
	font-size: 16px;

	.analyze-header {
		font-size: 24px;
		line-height: 30px;
		font-weight: 600;
		margin-bottom: 12px;
	}

	.analyze-description {
		text-align: center;
		font-size: 16px;
		line-height: 24px;
	}

	.analyze-input {
		margin-top: 12px;
		display: flex;
		align-items: center;
		gap: 8px;

		.aioseo-input {
			min-width: 430px;
			margin-right: 10px;
		}
	}
}

.analyze-progress {
	min-width: 548px;
	margin-top: 30px;
	background: $gray;
	justify-content: flex-start;
	border-radius: 100px;
	align-items: center;
	position: relative;
	padding: 0;
	display: flex;
	height: 10px;
	width: 500px;

	.analyze-progress-value {
		animation: analyze-load 2s normal forwards;
		border-radius: 100px;
		background: $blue;
		height: 10px;
		width: 0;
	}

	@keyframes analyze-load {
		0% { width: 0; }
		100% { width: 100%; }
	}
}
</style>