<template>
	<core-popper
		:classes="[
			'aioseo-tooltip'
		]"
		:trigger="trigger"
		:force-show="forceShow"
		:disabled="disabled"
		:options="{
			placement,
			modifiers : {
				flip: {
					enabled : flip
				},
				preventOverflow : {
					escapeWithReference : true
				},
				offset : {
					offset : offset
				}
			}
		}"
	>
		<span
			class="popper"
			:class="{ [type]: type }"
			:style="{
				zIndex
			}"
		>
			<component
				:is="tag"
				v-if="tooltip"
			>{{ tooltip }}</component>

			<slot name="tooltip" />
		</span>

		<template #reference>
			<slot />
		</template>
	</core-popper>
</template>

<script>
import CorePopper from '@/vue/components/common/core/Popper'
export default {
	components : {
		CorePopper
	},
	props : {
		tooltip   : String,
		type      : String,
		disabled  : Boolean,
		placement : {
			type : String,
			default () {
				return 'top'
			}
		},
		trigger : {
			type : String,
			default () {
				return 'hover'
			}
		},
		forceShow : {
			type : Boolean,
			default () {
				return false
			}
		},
		tag : {
			type : String,
			default () {
				return 'span'
			}
		},
		offset : {
			type : String,
			default () {
				return '0,0'
			}
		},
		flip : {
			type : Boolean,
			default () {
				return false
			}
		},
		zIndex : String
	}
}
</script>

<style lang="scss">
.aioseo-tooltip {
	margin-left: 12px;
	display: inline-flex;
	align-items: center;
	justify-content: center;

	> div:nth-of-type(2) {
		display: flex;
	}
}
</style>