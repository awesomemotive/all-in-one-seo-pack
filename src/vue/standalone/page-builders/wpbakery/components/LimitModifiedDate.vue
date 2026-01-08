<template>
	<a
		href="#"
		@click.prevent.exact.stop="$emit('click')"
	>
		<p>{{ strings.buttonTitle }}</p>
	</a>
</template>

<script setup>
import { __ } from '@/vue/plugins/translations'

const strings = {
	buttonTitle : __('Save (Don\'t Modify Date)', import.meta.env.VITE_TEXTDOMAIN)
}

defineEmits([ 'click' ])
</script>