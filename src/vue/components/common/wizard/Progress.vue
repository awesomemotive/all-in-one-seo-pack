<template>
	<div class="aioseo-wizard-progress">
		<div
			v-for="(step, index) in getSteps"
			:key="index"
			:class="[
				{ circle: !step.spacer },
				{ spacer: step.spacer },
				{ active: getActiveClass(step, index) }
			]"
		/>
	</div>
</template>

<script>
export default {
	props : {
		steps : {
			type     : Array,
			required : true
		}
	},
	computed : {
		getSteps () {
			const spacer = { spacer: true }
			return [ ...this.steps ]
				.map((e, i) => i < this.steps.length - 1 ? [ e, { ...spacer } ] : [ e ])
				.reduce((a, b) => a.concat(b))
		}
	},
	methods : {
		getActiveClass (step, index) {
			if (step.spacer) {
				if (this.getSteps[index + 1].active) {
					return true
				}
				return false
			}

			return step.active
		}
	}
}
</script>

<style lang="scss">
.aioseo-wizard-progress {
	display: flex;
	align-items: center;
	justify-content: center;

	@media screen and (max-width: 782px) {
		display: none;
	}

	.circle {
		background-color: $gray;
		width: 16px;
		height: 16px;
		border-radius: 50%;

		&.active {
			background-color: $blue;
		}
	}

	.spacer {
		width: 59px;
		border: 1px solid $gray;
		margin: 0 12px;

		&.active {
			border-color: $blue;
		}
	}
}
</style>