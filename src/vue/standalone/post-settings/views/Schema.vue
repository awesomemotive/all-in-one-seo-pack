<template>
	<div class="aioseo-post-schema-view">
		<schema
			v-if="!licenseStore.isUnlicensed"
			:parentComponentContext="parentComponentContext"
		/>

		<schema-lite
			v-if="licenseStore.isUnlicensed"
			:parentComponentContext="parentComponentContext"
		/>
	</div>
</template>

<script>
import {
	useLicenseStore
} from '@/vue/stores'

import Schema from './AIOSEO_VERSION/Schema'
import SchemaLite from './lite/Schema'
export default {
	setup () {
		return {
			licenseStore : useLicenseStore()
		}
	},
	components : {
		Schema,
		SchemaLite
	},
	props : {
		parentComponentContext : String
	}
}
</script>