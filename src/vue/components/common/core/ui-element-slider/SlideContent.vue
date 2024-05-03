<template>
	<div class="aioseo-slide-content">
		<div
			class="main-box"
			v-if="'extra' !== item.slot"
		>
			<div>
				<div>
					<div
						class="aioseo-description"
						v-if="item.desc"
						v-html="item.desc"
					/>

					<core-copy-block
						v-if="item.copy"
						:message="item.copy"
					/>

					<a
						v-if="item.hasAdvanced && !showAdvancedSettings"
						class="advanced-settings-link"
						href="#"
						@click.prevent="showAdvancedSettings = !showAdvancedSettings"
					>
						{{ strings.advancedSettings }}
					</a>
				</div>

				<transition-slide
					v-if="$slots.advanced"
					:active="showAdvancedSettings"
				>
					<div class="advanced-settings" v-if="showAdvancedSettings">
						<slot name="advanced"/>
					</div>
				</transition-slide>
			</div>
		</div>

		<div
			class="extra-box"
			v-if="'extra' === item.slot"
		>
			<slot name="extraBox" />
		</div>
	</div>
</template>

<script setup>
import { ref } from 'vue'
import { __ } from '@wordpress/i18n'

import CoreCopyBlock from '@/vue/components/common/core/CopyBlock'
import TransitionSlide from '@/vue/components/common/transition/Slide'

const td = import.meta.env.VITE_TEXTDOMAIN

defineProps({
	item : {
		type     : Object,
		required : true
	}
})

const showAdvancedSettings = ref(false)

const strings = {
	advancedSettings : __('Advanced Settings', td)
}
</script>

<style lang="scss">
.aioseo-ui-element-slider .aioseo-slide-content {
	.main-box,
	.extra-box {
		margin-top: 16px;
		padding: 12px;
		background-color: $box-background;

		.aioseo-description {
			margin: 0;
		}

		.aioseo-copy-block {
			margin: 20px 0 0;

			.message,
			.copy {
				min-height: 40px;
				align-self: stretch;
				font-size: $font-md;
				font-weight: 400;
				line-height: 22px;
				padding: 8px 12px;
			}
		}

		.advanced-settings-link {
			display: inline-block;
			padding-top: 5px;
			margin: 16px 0 0 16px;
			color: $placeholder-color;
			text-decoration: underline;
			font-size: 13px;

			&:hover {
				text-decoration: none;
			}
		}

		.advanced-settings {
			display: block;
			margin-top: 20px;
		}
	}

	.advanced-settings.advanced-settings-hidden {
		display: none;
	}

	.aioseo-tooltip {
		margin-left: 0;
	}
}
</style>