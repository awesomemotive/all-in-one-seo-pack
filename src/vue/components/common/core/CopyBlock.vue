<template>
	<div class="aioseo-copy-block">
		<div class="message">
			{{ message }}
		</div>

		<core-tooltip
			class="copy-tooltip"
			type="action"
		>
			<div
				ref="copy"
				class="copy"
				v-clipboard:copy="message"
				v-clipboard:success="onCopy"
				v-clipboard:error="onError"
			>
				<svg-copy
					v-if="!copied"
				/>

				<svg-circle-check-solid
					v-if="copied"
				/>
			</div>

			<template #tooltip>
				{{ copyText }}
			</template>
		</core-tooltip>
	</div>
</template>

<script>
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCircleCheckSolid from '@/vue/components/common/svg/circle/CheckSolid'
import SvgCopy from '@/vue/components/common/svg/Copy'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreTooltip,
		SvgCircleCheckSolid,
		SvgCopy
	},
	props : {
		message : {
			type     : String,
			required : true
		}
	},
	data () {
		return {
			copied : false
		}
	},
	computed : {
		copyText () {
			return this.copied ? __('Copied!', td) : __('Click to Copy', td)
		}
	},
	methods : {
		onCopy () {
			this.copied = true
			const tooltip = this.$refs.copy

			if (tooltip.popperJS) {
				tooltip.popperJS.destroy()
				tooltip.popperJS = null
			}
			tooltip.showPopper = false

			setTimeout(() => {
				if (tooltip.popperJS) {
					tooltip.popperJS.destroy()
					tooltip.popperJS = null
				}

				tooltip.showPopper = false
				this.copied        = false
			}, 2000)
		},
		onError () {}
	}
}
</script>

<style lang="scss">
@use 'sass:color';

.aioseo-copy-block {
	display: inline-flex;

	.message {
		background-color: #fff;
		min-height: 56px;
		display: flex;
		align-items: center;
		border: 1px solid $gray;
		border-radius: 3px 0 0 3px;
		padding: 10px 24px;
		font-weight: 600;
	}

	.copy-tooltip {
		display: flex;
	}

	.copy {
		background-color: #fff;
		min-height: 56px;
		display: flex;
		align-items: center;
		border: 1px solid $gray;
		border-left-width: 0;
		border-radius: 0 3px 3px 0;
		padding: 10px 16px;
		font-weight: 600;
		cursor: pointer;

		&:hover {
			svg.aioseo-copy {
				color: color.adjust($gray2, $lightness: -20%);
			}
		}

		svg.aioseo-copy {
			width: 20px;
			height: 20px;
			color: $gray2;
		}

		svg.aioseo-circle-check-solid {
			width: 20px;
			height: 20px;
			color: $green;
		}
	}
}
</style>