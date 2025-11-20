<template>
	<svg
		viewBox="0 0 24 24"
		xmlns="http://www.w3.org/2000/svg"
		:width="width"
		:height="height"
	>
		<path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 2.18l8 3.99v8.68c0 4.76-3.18 9.18-8 10.48V4.18z" />
		<path d="M10.5 9h-2v2h2zm0 3h-2v2h2zm3-3h-2v2h2zm0 3h-2v2h2zm3-3h-2v2h2zm0 3h-2v2h2z" opacity="0.7" />
		<circle cx="9" cy="10" r="1.5" />
		<circle cx="12" cy="10" r="1.5" />
		<circle cx="15" cy="10" r="1.5" />
		<path d="M9 14c0 1.66 1.34 3 3 3s3-1.34 3-3" stroke-width="1.5" stroke-linecap="round" fill="none" stroke="currentColor" />
	</svg>
</template>

<script>
export default {
	props: {
		width: {
			type: [Number, String],
			default: 24
		},
		height: {
			type: [Number, String],
			default: 24
		}
	}
}
</script>
