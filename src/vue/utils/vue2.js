import { configureCompat } from '@vue/compat'

configureCompat({
	MODE                     : 3,
	OPTIONS_DATA_MERGE       : true,
	INSTANCE_SET             : true,
	ATTR_FALSE_VALUE         : true,
	ATTR_ENUMERATED_COERCION : true
})