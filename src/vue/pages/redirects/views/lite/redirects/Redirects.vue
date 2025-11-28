<template>
	<upsell-redirects :noCoreCard="props.noCoreCard" />
</template>

<script setup>
import UpsellRedirects from '../../partials/UpsellRedirects'

const props = defineProps({
	noCoreCard : {
		type : Boolean
	}
})
</script>