<template>
	<div class="aioseo-sa-ct-custom-fields-view">
		<custom-fields
			v-if="!licenseStore.isUnlicensed"
			:type="type"
			:object="object"
			:options="options"
			:show-bulk="showBulk"
		/>
		<custom-fields-lite
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

import CustomFields from './AIOSEO_VERSION/CustomFields'
import CustomFieldsLite from './lite/CustomFields'
export default {
	setup () {
		return {
			licenseStore : useLicenseStore()
		}
	},
	components : {
		CustomFields,
		CustomFieldsLite
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