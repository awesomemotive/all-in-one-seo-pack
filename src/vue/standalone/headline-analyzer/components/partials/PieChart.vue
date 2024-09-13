<template>
	<div
		class="aioseo-donut-container"
		style='flex-direction: column;'
	>
		<svg
			className="aioseo-donut-score-svg"
			viewBox="0 0 33.83098862 33.83098862"
			xmlns="http://www.w3.org/2000/svg"
		>
			<circle
				class="aioseo-seo-headline-analyzer-score__background"
				stroke="#e8e8eb"
				stroke-width="2"
				fill="none"
				cx="16.91549431"
				cy="16.91549431"
				r="15.91549431"
			/>
			<circle
				class="aioseo-seo-headline-analyzer-score__circle"
				:stroke="color"
				stroke-width="2"
				:stroke-dasharray="currentScore + ', 100'"
				stroke-linecap="round"
				fill="none"
				cx="16.91549431"
				cy="16.91549431"
				r="15.91549431"
			/>
		</svg>
	</div>
</template>

<script>
export default {
	props : {
		color : {
			type     : String,
			required : true
		},
		currentScore : {
			type     : Number,
			required : true
		}
	},
	data () {
		return {}
	}
}
</script>