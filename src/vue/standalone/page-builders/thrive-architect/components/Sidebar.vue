<template>
	<div class="edit-post-sidebar aioseo-thrive-sidebar">
		<div class="aioseo-thrive-sidebar__header">
			<div class="aioseo-thrive-sidebar__header-title">
				{{ strings.headerTitle }}
			</div>
		</div>

		<div class="aioseo-thrive-sidebar__content">
			<post-settings />
		</div>
	</div>
</template>

<script setup>
import PostSettings from '@/vue/standalone/post-settings/App'
import { __, sprintf } from '@wordpress/i18n'
const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	headerTitle : sprintf(
		// Translators: 1 - Plugin short name ("AIOSEO").
		__('%1$s Settings', td),
		import.meta.env.VITE_SHORT_NAME
	)
}
</script>

<style lang="scss">
.aioseo-thrive-sidebar {
	&__header {
		height: 48px;
		display: flex;
		align-items: center;
		flex-shrink: 0;
		border-bottom: 1px solid #E0E3E7;
		padding: 0 10px;
		font-family: $font-family;

		&-title {
			font-size: 13px;
			font-weight: 700;
			line-height: 17.7px;
			color: #247BA0;
		}

		.aioseo-score-button {
			border-radius: 4px;
		}
	}

	.popper {
		max-width: 220px;
	}

	&__content {
		overflow: auto;
	}
}
</style>