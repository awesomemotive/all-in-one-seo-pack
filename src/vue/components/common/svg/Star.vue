<template>
	<svg viewBox="0 0 21 19" fill="none" xmlns="http://www.w3.org/2000/svg" class="aioseo-star">
		<path
			fill="currentColor"
			d="M10.5 5.13l.97 2.29.47 1.11 1.2.1 2.47.21-1.88 1.63-.91.79.27 1.18.56 2.41-2.12-1.28-1.03-.64-1.03.62-2.12 1.28.56-2.41.27-1.18-.91-.79-1.88-1.63 2.47-.21 1.2-.1.47-1.11.97-2.27zm0-5.13L7.69 6.63.5 7.24l5.46 4.73L4.32 19l6.18-3.73L16.68 19l-1.64-7.03 5.46-4.73-7.19-.61L10.5 0z"
		/>
		<path
			fill="currentColor"
			d="M 10.5 5.13 m 0 -5.13 L 7.69 6.63 L 0.5 7.24 l 5.46 4.73 L 4.32 19 l 6.18 -3.73 L 16.68 19 l -1.64 -7.03 l 5.46 -4.73 l -7.19 -0.61 L 10.5 0 z"
			:style="{ opacity: active ? 1 : 0 }"
		/>
	</svg>
</template>
<script>
export default {
	props : {
		active : {
			type : Boolean,
			default () {
				return false
			}
		}
	}
}
</script>