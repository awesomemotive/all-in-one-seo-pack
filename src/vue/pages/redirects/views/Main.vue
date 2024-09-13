<template>
	<core-main
		:page-name="strings.pageName"
		:show-save-button="showSaveButton"
		:exclude-tabs="excludedTabs"
	>
		<component :is="getRoute" />
	</core-main>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router'
import { computed } from 'vue'

import {
	useRedirectsStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'

import { useRequiresActivation } from '@/vue/composables/RequiresActivation'
import { useRequiresUpdate } from '@/vue/composables/RequiresUpdate'

import CoreMain from '@/vue/components/common/core/main/Index'
import FullSiteRedirect from './AIOSEO_VERSION/FullSiteRedirect'
import ImportExport from './AIOSEO_VERSION/ImportExport'
import Logs from './AIOSEO_VERSION/Logs'
import Logs404 from './AIOSEO_VERSION/Logs404'
import Redirects from './Redirects'
import Settings from './AIOSEO_VERSION/Settings'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const route  = useRoute()
const router = useRouter()
const getRoute = computed(() => {
	if ('full-site-redirect' === route.name) {
		return FullSiteRedirect
	}
	if ('import-export' === route.name) {
		return ImportExport
	}
	if ('logs' === route.name) {
		return Logs
	}
	if ('logs-404' === route.name) {
		return Logs404
	}
	if ('redirects' === route.name) {
		return Redirects
	}
	if ('settings' === route.name) {
		return Settings
	}

	return Redirects
})

const {
	getExcludedActivationTabs
} = useRequiresActivation()

const {
	getExcludedUpdateTabs
} = useRequiresUpdate()

const strings = {
	pageName : __('Redirects', td)
}

const showSaveButton = computed(() => {
	return 'redirects' !== route.name &&
		'groups' !== route.name &&
		'logs-404' !== route.name &&
		'logs' !== route.name &&
		'import-export' !== route.name
})

const redirectsStore = useRedirectsStore()
const excludedTabs = computed(() => {
	const exclude = !addons.isActive('aioseo-redirects')
		? getExcludedActivationTabs(router, 'aioseo-redirects')
		: getExcludedUpdateTabs(router, 'aioseo-redirects')

	if (!redirectsStore.options?.logs?.log404?.enabled) {
		exclude.push('logs-404')
	}

	if (!redirectsStore.options?.logs?.redirects?.enabled || 'server' === redirectsStore.options?.main?.method) {
		exclude.push('logs')
	}

	return exclude
})
</script>

<style lang="scss">
.aioseo-redirects {
	position: relative;
}
</style>