<template>
	<core-main
		:page-name="strings.pageName"
		:show-save-button="false"
		:disable-tabs="true"
	>
		<component :is="Redirects" />
	</core-main>
</template>

<script setup>
import CoreMain from '@/vue/components/common/core/main/Index'
import Redirects from './redirects/Redirects'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	pageName : __('Redirects', td)
}
</script>

<style lang="scss">
.aioseo-redirects {
	position: relative;
}
</style>