<template>
	<div class="aioseo-sa-ct-schema-view">
		<schema
			v-if="!licenseStore.isUnlicensed"
			:type="type"
			:object="object"
			:options="options"
			:show-bulk="showBulk"
		/>

		<schema-lite
			v-if="licenseStore.isUnlicensed"
			:type="type"
			:object="object"
			:options="options"
			:show-bulk="showBulk"
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
		type : {
			type     : String,
			required : true
		},
		object : {
			type     : Object,
			required : true
		},
		options : {
			type     : Object,
			required : true
		},
		showBulk : Boolean
	}
}
</script>