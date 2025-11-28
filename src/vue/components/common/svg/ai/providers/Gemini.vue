<template>
	<svg width="20" height="20" viewBox="0 0 20 20" class="aioseo-provider-icon gemini" fill="none" xmlns="http://www.w3.org/2000/svg">
		<rect width="20" height="20" rx="10" fill="white"/>
		<path :fill="`url(#gradient-${uniqueId})`" d="M17.5 9.515C15.5539 9.63443 13.7187 10.4614 12.34 11.84C10.9614 13.2187 10.1344 15.0539 10.015 17H9.985C9.86577 15.0539 9.0389 13.2185 7.66019 11.8398C6.28148 10.4611 4.44614 9.63423 2.5 9.515L2.5 9.485C4.44614 9.36577 6.28148 8.5389 7.66019 7.16019C9.0389 5.78148 9.86577 3.94614 9.985 2L10.015 2C10.1344 3.94607 10.9614 5.7813 12.34 7.15997C13.7187 8.53864 15.5539 9.36557 17.5 9.485V9.515Z"/>
		<defs>
			<radialGradient :id="`gradient-${uniqueId}`" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(3.98875 8.09656) rotate(18.6832) scale(15.9656 127.895)">
				<stop offset="0.067" stop-color="#9168C0"/>
				<stop offset="0.343" stop-color="#5684D1"/>
				<stop offset="0.672" stop-color="#1BA1E3"/>
			</radialGradient>
		</defs>
	</svg>
</template>

<script>
export default {
	name : 'GeminiIcon',
	setup () {
		// Generate a unique ID per component instance
		const uniqueId = Math.random().toString(36).substring(2, 11)
		return {
			uniqueId
		}
	}
}
</script>