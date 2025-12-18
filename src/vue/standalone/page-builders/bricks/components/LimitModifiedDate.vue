<template>
	<span
		id="aioseo-limit-modified-date-bricks"
		@click.prevent.exact.stop="showOptions = !showOptions"
	>
		<svg-caret
			width="12"
			height="12"
		/>

		<span class="aioseo-options" v-if="showOptions">
			<span @click.prevent.exact.stop="initSave()">
				{{ strings.buttonTitle }}
			</span>
		</span>
	</span>
</template>

<script setup>
import { ref } from 'vue'
import { __ } from '@wordpress/i18n'
import { getBricksVueGlobalProperties } from '../helpers'
import SvgCaret from '@/vue/components/common/svg/Caret'

const showOptions = ref(false)

const strings = {
	buttonTitle : __('Save (Don\'t Modify Date)', import.meta.env.VITE_TEXTDOMAIN)
}

const initSave = () => {
	try {
		const $savePost = getBricksVueGlobalProperties('$_savePost')
		const $vueState = getBricksVueGlobalProperties('$_state')
		const unsavedChanges = $vueState?.unsavedChanges || []

		if ('function' === typeof $savePost) {
			if (unsavedChanges && 0 < unsavedChanges.length) {
				unsavedChanges.push('aioseo_limit_modified_date')

				$vueState.aioseo_limit_modified_date = true
			}

			$savePost()

			delete $vueState.aioseo_limit_modified_date

			unsavedChanges.splice(unsavedChanges.indexOf('aioseo_limit_modified_date'), 1)
		}
	} catch (error) {
		console.error(error)
	} finally {
		showOptions.value = false
	}
}
</script>

<style lang="scss" scoped>
#aioseo-limit-modified-date-bricks {
	display: flex;
	align-items: center;
	justify-content: center;
	justify-items: center;
	line-height: 1;
	cursor: pointer;

	.svg {
		width: 12px;
		height: 12px;
		flex-basis: 12px;
	}

	.aioseo-options {
		position: absolute;
		top: 100%;
		right: 0;
		white-space: nowrap;
		background-color: #fff;
		border-radius: 4px;
		padding: 8px 10px;
		font-size: 12px;
		font-weight: 400;
		color: #000;
		user-select: none;

		&:hover {
			background-color: #d9d9d9;
		}
	}
}
</style>