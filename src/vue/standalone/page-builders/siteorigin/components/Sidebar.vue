<template>
	<div class="edit-post-sidebar editor-sidebar aioseo-site-origin-sidebar">
		<div class="aioseo-site-origin-sidebar__header">
			<div class="aioseo-site-origin-sidebar__header-title">
				{{ strings.headerTitle }}
			</div>

			<div
				class="aioseo-site-origin-sidebar__header-close"
				v-on:click="emitter.emit('siteOriginAioseoClosed', false)"
			>
				<svg-close />
			</div>
		</div>

		<div class="aioseo-site-origin-sidebar__content">
			<post-settings />
		</div>
	</div>
</template>

<script setup>
import PostSettings from '@/vue/standalone/post-settings/App'
import SvgClose from '@/vue/components/common/svg/Close'
import emitter from 'tiny-emitter/instance'

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
.aioseo-site-origin-sidebar {
	position: absolute;
	z-index: 100;
	top: 47px;
	background: $white;
	overflow: visible;
	transition: left 0.25s ease;

	width: 100%;
	left: -100%;
	right: auto;

	@at-root .aioseo-site-origin-sidebar--active & {
		left: 0;
		box-shadow: 0 0 15px rgba(0, 0, 0, 0.15);
	}

	&__header {
		display: flex;
		align-items: center;
		flex-shrink: 0;
		border-bottom: 1px solid #E0E3E7;
		padding: 12px;
		font-family: $font-family;

		&-title {
			font-size: 16px;
			font-style: normal;
			font-weight: 700;
			line-height: 125%;
			color: $black;
		}

		&-close {
			margin-left: auto;
			margin-right: 5px;
			cursor: pointer;
			color: $black;

			svg {
				width: 15px;
				height: 15px;
				vertical-align: middle;
			}
		}
	}

	&__content {
		overflow: auto;
	}
}
</style>