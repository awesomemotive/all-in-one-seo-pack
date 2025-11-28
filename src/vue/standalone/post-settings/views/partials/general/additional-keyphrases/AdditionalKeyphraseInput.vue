<template>
	<div class="additional-keyphrase-input-wrapper">
		<base-input
			size="medium"
			:class="`add-keyphrase-${screenContext}-input`"
			@keydown.enter="handleEnter"
		/>

		<base-button
			v-if="!maxReached"
			id="add-additional-keyphrase"
			class="add-keyphrase gray medium"
			@click="handleAddClick"
		>
			<svg-circle-plus width="14" height="14" />
			{{ strings.addKeyphrase }}
		</base-button>

		<core-tooltip v-if="maxReached">
			<base-button
				id="add-additional-keyphrase"
				class="add-keyphrase gray medium"
				:disabled="true"
			>
				<svg-circle-plus width="14" height="14" />
				{{ strings.addKeyphrase }}
			</base-button>

			<template #tooltip>
				<span>{{ strings.maxAmountReached }}</span>
			</template>
		</core-tooltip>
	</div>
</template>

<script>
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits      : [ 'add' ],
	components : {
		CoreTooltip,
		SvgCirclePlus
	},
	props : {
		screenContext : {
			type     : String,
			required : true
		},
		maxReached : {
			type    : Boolean,
			default : false
		},
		maxAdditionalKeyphrases : {
			type     : Number,
			required : true
		}
	},
	computed : {
		strings () {
			return {
				addKeyphrase     : __('Add Additional Keywords', td),
				maxAmountReached : sprintf(
					// Translators: 1 - Number of maximum keywords.
					__('You have reached the maximum of %1$s additional keywords.', td),
					this.maxAdditionalKeyphrases
				)
			}
		}
	},
	methods : {
		handleAddClick () {
			this.$emit('add')
		},
		handleEnter (event) {
			event.preventDefault()
			const addButton = document.getElementById('add-additional-keyphrase')
			if (addButton) {
				addButton.click()
			}
		}
	}
}
</script>

<style lang="scss" scoped>
.additional-keyphrase-input-wrapper {
	margin-top: 16px;
}
</style>