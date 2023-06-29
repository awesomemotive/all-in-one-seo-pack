<template>
	<div class="wrap">
		<seo-revisions v-if="!isUnlicensed"/>

		<seo-revisions-lite v-else />
	</div>
</template>

<script setup>
import {
	useLicenseStore
} from '@/vue/stores'

import { storeToRefs } from 'pinia'

import SeoRevisions from './AIOSEO_VERSION/Index'
import SeoRevisionsLite from './lite/Index'

const { isUnlicensed } = storeToRefs(useLicenseStore())
</script>