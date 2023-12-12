<template>
	<div class="edit-post-sidebar aioseo-avada-sidebar">
		<div class="aioseo-avada-sidebar__header">
			<div class="aioseo-avada-sidebar__header-title">
				AIOSEO Settings
			</div>

			<core-score-button
				v-if="currentPost.seo_score"
				:score="currentPost.seo_score"
				class="aioseo-score-button--active"
			/>
		</div>

		<div class="aioseo-avada-sidebar__content">
			<post-settings />
		</div>
	</div>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { usePostEditorStore } from '@/vue/stores'
import CoreScoreButton from '@/vue/components/common/core/ScoreButton'
import PostSettings from '@/vue/standalone/post-settings/App'

const { currentPost } = storeToRefs(usePostEditorStore())
</script>

<style lang="scss">
.aioseo-avada-sidebar {
	$toolbar-height: 54px;
	height: calc(100vh - $toolbar-height);
	min-width: 320px;
	position: fixed;
	z-index: 100;
	top: 0;
	background: $white;
	overflow: visible;
	margin-top: $toolbar-height;
	transition: left 0.25s ease;

	width: 320px;
	left: -320px;
	right: auto;

	@at-root .aioseo-avada-sidebar--active:not(.fusion-builder-preview-mode) & {
		left: 0;
		box-shadow: 0 0 15px rgba(0, 0, 0, 0.15);
	}

	&__header {
		display: flex;
		height: $toolbar-height;
		align-items: center;
		flex-shrink: 0;
		border-bottom: 1px solid #E0E3E7;
		padding: 10px;
		font-family: $font-family;

		&-title {
			font-size: 14px;
			font-style: normal;
			font-weight: 700;
			line-height: 22px;
			color: #198FD9;
			margin-right: 8px;
		}

		.aioseo-score-button {
			border-radius: 4px;
		}
	}

	&__content {
		overflow: auto;
		height: calc(100% - $toolbar-height);
	}
}
</style>