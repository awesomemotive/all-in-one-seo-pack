<template>
	<div class="aioseo-app">
		<router-view />
	</div>
</template>

<script setup>
import { useScrollAndHighlight } from '@/vue/composables/ScrollAndHighlight'

// We just need to call this to make sure the composable is used.
useScrollAndHighlight({
	scrollTimeout             : 1000,
	scrollAndHighlightTimeout : 2000
})
</script>