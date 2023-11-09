<template>
	<core-post-column :post="post" />
</template>

<script>
import CorePostColumn from '@/vue/components/common/core/PostColumn'
export default {
	components : {
		CorePostColumn
	},
	props : {
		post : Object
	}
}
</script>