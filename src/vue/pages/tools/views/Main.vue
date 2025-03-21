<template>
	<core-main
		:page-name="strings.pageName"
		:show-save-button="showSaveButton"
	>
		<component :is="getRoute" />
	</core-main>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

import {
	useRootStore
} from '@/vue/stores'

import BadBotBlocker from './BadBotBlocker'
import CoreMain from '@/vue/components/common/core/main/Index'
import DatabaseTools from './DatabaseTools'
import Debug from './AIOSEO_VERSION/Debug'
import HtaccessEditor from './HtaccessEditor'
import ImportExport from './ImportExport'
import RobotsEditor from './RobotsEditor'
import SystemStatus from './SystemStatus'
import WpCode from './WpCode'

import { __ } from '@/vue/plugins/translations'
import { useRobotsTxt } from '@/vue/composables/RobotsTxt'

const rootStore = useRootStore()
const route  = useRoute()
const getRoute = computed(() => {
	switch (route.name) {
		case 'bad-bot-blocker':
			return BadBotBlocker
		case 'database-tools':
			return DatabaseTools
		case 'debug':
			return Debug
		case 'htaccess-editor':
			return HtaccessEditor
		case 'import-export':
			return ImportExport
		case 'system-status':
			return SystemStatus
		case 'wp-code':
			return WpCode
		default:
			return RobotsEditor
	}
})

useRobotsTxt()

const td = import.meta.env.VITE_TEXTDOMAIN

const showSaveButton = computed(() => {
	return 'system-status' !== route.name &&
		'import-export' !== route.name &&
		'database-tools' !== route.name &&
		'debug' !== route.name &&
		'wp-code' !== route.name
})

const strings = {
	pageName : rootStore.aioseo.data.isNetworkAdmin
		? __('Network Tools', td)
		: __('Tools', td)
}
</script>