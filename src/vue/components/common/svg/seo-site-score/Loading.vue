<template>
	<svg
		class="aioseo-seo-site-score-loading"
		viewBox="0 0 33.83098862 33.83098862"
		xmlns="http://www.w3.org/2000/svg"
	>
		<circle
			fill="none"
			class="aioseo-seo-site-score-loading__circle"
			cx="16.91549431"
			cy="16.91549431"
			r="15.91549431"
			stroke-linecap="round"
			stroke-width="2"
			stroke-dasharray="93"
			stroke-dashoffset="90"
		/>
	</svg>
</template>

<style lang="scss">
.aioseo-seo-site-score-loading {
	animation: 2s linear infinite aioseo-seo-site-score-animation;
}

.aioseo-seo-site-score-loading__circle {
	animation: 2s ease-in-out infinite both aioseo-seo-site-score-fill-loading;
	transform: rotate(-180deg);
	transform-origin: center;
	stroke: $blue;
}

@keyframes aioseo-seo-site-score-animation {
	0% {
		transform: rotateZ(0deg);
	}
	100% {
		transform: rotateZ(360deg)
	}
}

@keyframes aioseo-seo-site-score-fill-loading {
	0%,
	25% {
		stroke-dashoffset: 90;
		transform: rotate(0);
	}

	50%,
	75% {
		stroke-dashoffset: 10;
		transform: rotate(45deg);
	}

	100% {
		stroke-dashoffset: 90;
		transform: rotate(360deg);
	}
}
</style>