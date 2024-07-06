<template>
	<div class="aioseo-site-origin-lmd">
		<button
			class="aioseo-site-origin-lmd__button button-primary"
			@click.prevent="toggleOptions"
		>
			<svg-caret :class="{ rotated: showOptions }" />
		</button>

		<div
			class="aioseo-site-origin-lmd__options"
			v-if="showOptions"
			@click.prevent="save"
		>
			{{ strings.option }}
		</div>
	</div>
</template>

<script setup>
import { ref } from 'vue'
import { usePostEditorStore } from '@/vue/stores'
import { isBlockEditor } from '@/vue/utils/context'
import { __ } from '@wordpress/i18n'
import SvgCaret from '@/vue/components/common/svg/Caret'

const td = import.meta.env.VITE_TEXTDOMAIN
const showOptions = ref(false)
const postEditorStore = usePostEditorStore()

const toggleOptions = () => {
	showOptions.value = !showOptions.value
}

const save = () => {
	showOptions.value = false
	postEditorStore.currentPost.limit_modified_date = true
	postEditorStore.savePostState()

	if (isBlockEditor()) {
		// Add the attribute to the block editor.
		window.wp.data.dispatch('core/editor').editPost({
			aioseo_limit_modified_date : postEditorStore.currentPost.limit_modified_date
		})
	}

	document.querySelector('.live-editor-save')?.click()
}

const strings = {
	option : __('Don\'t update the modified date', td)
}
</script>

<style lang="scss">
.aioseo-site-origin-lmd {
	float: right;
	margin: 9px 10px 0 -15px;

	.aioseo-caret {
		width: 18px;
		height: 18px;
		transition: transform 0.3s;
		vertical-align: sub;

		&.rotated {
			transform: rotate(-180deg);
		}
	}

	&__button {
		&:focus {
			box-shadow: none !important;
		}
	}

	&__options {
		position: absolute;
		top: 95%;
		right: 10px;
		background: #F5F5F5;
		border: 1px solid #BCB9B9;
		border-radius: 3px;
		box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.19);
		padding: 10px;
		z-index: 999;
		cursor: pointer;

		&:hover {
			background: #E9E9E9;
		}
	}
}
</style>