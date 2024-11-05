<template>
	<div class="aioseo-app">
		<div class="aioseo-writing-assistant">
			<div v-if="writingAssistantStore.seoBoost.isLoggedIn">
				<Report></Report>
			</div>
			<div v-if="!writingAssistantStore.seoBoost.isLoggedIn">
				<div class="aioseo-writing-assistant-metabox__padding">
					<authenticate-seoboost></authenticate-seoboost>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import '@/vue/assets/scss/main.scss'
import Report from './views/report/Main'
import AuthenticateSeoboost from './views/partials/authenticate/Seoboost'

import { useWritingAssistantStore } from '@/vue/stores'

const writingAssistantStore = useWritingAssistantStore()

</script>

<style lang="scss">
#aioseo-writing-assistant-metabox {
	.aioseo-writing-assistant-metabox__padding {
		padding: 20px;
	}

	.inside {
		padding: 0 20px 20px;
		margin: 0;
		border-top: 1px solid $border;
	}

	.aioseo-writing-assistant {
		.md-tabs-navigation {
			margin-top: 0 !important;
		}

		.aioseo-tabs {
			background: $background;
			border-top: 1px solid $border;
			border-bottom-width: 1px;

			.md-tabs-indicator {
				bottom: -1px;
			}
		}
	}
}

#aioseo-writing-assistant-sidebar-button {
	display: flex;
	flex-direction: row;
	align-items: center;
	justify-content: center;
	height: 36px;
	min-width: 36px;
	margin: -7px -7px;
	border-radius: 3px;
	border: 1px solid #4B5EEC;

	svg, svg path {
		fill: #4B5EEC;
	}

	.components-menu-group & {
		background: none;
		margin: 0;
		border: 0;
	}

	.is-pressed & {
		background: linear-gradient(45deg, #1A82EA 0%, #7C3AED 100%);

		& svg path {
			fill: $white;
		}
	}
}
</style>