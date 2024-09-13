<template>
	<svg
		class="aioseo-progress-circle"
		:viewBox="`0 0 ${vb} ${vb}`"
		xmlns="http://www.w3.org/2000/svg"
	>
		<circle
			class="aioseo-progress-circle__background"
			:stroke-width="strokeWidth"
			fill="none"
			:cx="cx"
			:cy="cx"
			:r="r"
		/>
		<circle
			class="aioseo-progress-circle__circle"
			:stroke-width="strokeWidth"
			:stroke-dasharray="`${dashArray} 100`"
			fill="none"
			:cx="cx"
			:cy="cx"
			:r="r"
		/>
	</svg>
</template>

<script>
export default {
	props : {
		scoreColor : String,
		percent    : {
			type     : Number,
			required : true
		},
		strokeWidth : {
			type : Number,
			default () {
				return 8
			}
		}
	},
	data () {
		return {
			pi : Math.PI,
			vb : 34
		}
	},
	computed : {
		cx () {
			return this.vb / 2
		},
		cy () {
			return this.cx
		},
		r () {
			return this.cx - (this.strokeWidth / 2)
		},
		dashArray () {
			return this.dashOffset * (this.percent / 100)
		},
		dashOffset () {
			return 2 * this.r * this.pi
		}
	}
}
</script>

<style lang="scss">
.aioseo-progress-circle__circle {
	transform: rotate(270deg);
	transform-origin: center;
	stroke: $blue;
}

.aioseo-progress-circle__background {
	stroke: rgba($blue, 0.25);
}
</style>