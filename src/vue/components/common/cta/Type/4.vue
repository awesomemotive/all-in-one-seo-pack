<template>
	<div class="type-4">
		<div class="header-text">
			<slot name="header-text">
				{{ strings.ctaHeader }}
			</slot>
		</div>

		<div class="description">
			<slot name="description">
				{{ strings.ctaDescription }}
			</slot>
		</div>
	</div>
</template>

<script setup>
defineProps({
	strings : Object
})
</script>

<style lang="scss">
.aioseo-app,
.aioseo-blc-app,
#aioseo-user-profile-tab {
	.aioseo-cta {
		.aioseo-cta-background {
			.type-4 {

				.header-text {
					font-weight: $font-bold;
					font-size: 16px;
					margin-bottom: 8px;
				}

				.description {
					font-weight: 400;
					font-size: 14px;
					margin: 0;
				}
			}
		}
	}
}
</style>