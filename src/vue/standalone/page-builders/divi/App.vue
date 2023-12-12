<template>
	<div>
		<Modal v-model:is-open="isModalOpen" />
	</div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import Modal from '../Modal.vue'

const isModalOpen = ref(false)

const toggleModal = () => {
	isModalOpen.value = !isModalOpen.value
}

onMounted(() => {
	document.addEventListener('aioseo-pagebuilder-toggle-modal', toggleModal)
})
onBeforeUnmount(() => {
	document.removeEventListener('aioseo-pagebuilder-toggle-modal', toggleModal)
})
</script>