<template>
	<div class="aioseo-app">
		<router-view />

		<core-additional-js />
	</div>
</template>

<script setup>
import '@/vue/assets/scss/main.scss'

import CoreAdditionalJs from '@/vue/components/AIOSEO_VERSION/core/AdditionalJs'
</script>