<template>
	<div class="focus-keyphrase-input-wrapper">
		<base-input
			size="medium"
			:class="`add-focus-keyphrase-${screenContext}-input`"
			@keydown.enter="handleEnter"
		/>

		<div
			:class="`add-focus-keyphrase-${screenContext}-button`"
		>
			<base-button
				id="add-focus-keyphrase"
				class="add-keyphrase gray medium"
				@click="handleAddClick"
			>
				<svg-circle-plus width="14" height="14" />
				{{ strings.addKeyphrase }}
			</base-button>

			<core-tooltip>
				<div class="disabled-button gray medium">
					<svg-circle-plus width="14" height="14" />
					{{ strings.getAdditionalKeyphrases }}
				</div>

				<template #tooltip>
					<span>
						{{ strings.semrushGetAdditionalKeyphrases }}
						{{ strings.pleaseAddFocusKeyphrase }}
					</span>
				</template>
			</core-tooltip>
		</div>
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
		}
	},
	data () {
		return {
			strings : {
				addKeyphrase                   : __('Add Focus Keyword', td),
				getAdditionalKeyphrases        : __('Get Additional Keywords', td),
				semrushGetAdditionalKeyphrases : sprintf(
					// Translators: 1 - Plugin short name "AIOSEO", 2 - Semrush.
					__('%1$s integrates directly with %2$s to provide you with actionable keywords to help you write better content.', td),
					import.meta.env.VITE_SHORT_NAME,
					'Semrush'
				),
				pleaseAddFocusKeyphrase : __('To use this feature, first add a focus keyword.', td)
			}
		}
	},
	methods : {
		handleAddClick () {
			this.$emit('add')
		},
		handleEnter (event) {
			event.preventDefault()
			const addButton = document.getElementById('add-focus-keyphrase')
			if (addButton) {
				addButton.click()
			}
		}
	}
}
</script>

<style lang="scss" scoped>
.focus-keyphrase-input-wrapper {
	.add-focus-keyphrase-metabox-button {
		display: flex;
	}

	.add-focus-keyphrase-sidebar-button {
		.add-keyphrase {
			margin-bottom: 0 !important;
		}

		.aioseo-tooltip {
			margin-left: 0;
			display: block;

			.disabled-button {
				display: flex;
			}

			@at-root .is-page-builder & div:nth-child(2) {
				display: block;
			}
		}
	}
}
</style>