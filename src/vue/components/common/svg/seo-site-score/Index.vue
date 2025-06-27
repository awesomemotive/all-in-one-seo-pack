<template>
	<svg
		class="aioseo-seo-site-score"
		viewBox="0 0 34 34"
		xmlns="http://www.w3.org/2000/svg"
	>
		<circle
			class="aioseo-seo-site-score__background"
			:stroke-width="strokeWidth"
			fill="none"
			cx="17"
			cy="17"
			:r="17 - (strokeWidth / 2)"
		/>
		<circle
			class="aioseo-seo-site-score__circle"
			:class="getClass"
			:stroke-width="strokeWidth"
			:stroke-dasharray="`${score},100`"
			stroke-linecap="round"
			fill="none"
			cx="17"
			cy="17"
			:r="17 - (strokeWidth / 2)"
		/>
	</svg>
</template>

<script>
export default {
	props : {
		scoreColor : String,
		score      : {
			type     : Number,
			required : true
		},
		strokeWidth : {
			type : Number,
			default () {
				return 2
			}
		}
	},
	computed : {
		getClass () {
			let speedClass = '', colorClass = ''

			switch (true) {
				case 33 >= this.score:
					speedClass = 'fast'
					colorClass = 'stroke-red'
					break
				case 66 >= this.score:
					speedClass = 'medium'
					colorClass = 'stroke-orange'
					break
				default:
					speedClass = 'slow'
					colorClass = 'stroke-green'
			}

			if (this.scoreColor !== undefined) {
				colorClass = `stroke-${this.scoreColor}`
			}

			return `${speedClass} ${colorClass}`
		}
	}
}
</script>

<style lang="scss">
.aioseo-seo-site-score__circle {
	animation: aioseo-seo-site-score-fill 1s reverse;
	transform: rotate(-180deg);
	transform-origin: center;

	&.fast {
		animation-duration: 0.5s;
	}

	&.medium {
		animation-duration: 0.75s;
	}

	&.stroke-red {
		stroke: $red;
	}

	&.stroke-orange {
		stroke: $orange;
	}

	&.stroke-green {
		stroke: $green;
	}

	&.stroke-blue {
		stroke: $blue;
	}
}

.aioseo-seo-site-score__background {
	stroke: $border;
}

@keyframes aioseo-seo-site-score-fill {
	to { stroke-dasharray: 0 100; }
}
</style>