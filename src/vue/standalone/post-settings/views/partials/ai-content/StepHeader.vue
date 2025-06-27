<template>
	<div class="aioseo-ai-content-step-header">
		<div class="step-header">
			<span
				v-if="step"
				class="step-header-number"
			>
				{{ step }}
			</span>

			<span class="step-header-title">{{ header }}</span>
		</div>
	</div>
</template>

<script>
export default {
	props : {
		header : {
			type     : String,
			required : true
		},
		step : {
			type     : [ Number, String ],
			required : false
		}
	}
}
</script>

<style lang="scss">
.aioseo-ai-content-step-header {
	.step-header {
		display: flex;
		align-items: center;
		margin-bottom: 20px;

		.step-header-number {
			position: absolute;

			display: flex;
			align-items: center;
			justify-content: center;
			width: 24px;
			height: 24px;

			margin-right: 10px;

			background-color: #f0f1f2;
			border-radius: 50%;

			font-weight: bold;
		}

		.step-header-title {
			font-size: 16px;
			font-weight: bold;
			margin-left: 40px;
		}
	}
}
</style>