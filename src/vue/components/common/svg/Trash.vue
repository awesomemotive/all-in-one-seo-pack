<template>
	<svg
		v-if="!trim"
		viewBox="0 0 20 20"
		xmlns="http://www.w3.org/2000/svg"
		class="aioseo-trash"
	>
		<path
			fill-rule="evenodd"
			clip-rule="evenodd"
			d="M12.0833 2.5H7.91663L7.08329 3.33333H4.16663V5H15.8333V3.33333H12.9166L12.0833 2.5ZM13.3333 7.5V15.8333H6.66663V7.5H13.3333ZM4.99996 5.83333H15V15.8333C15 16.75 14.25 17.5 13.3333 17.5H6.66663C5.74996 17.5 4.99996 16.75 4.99996 15.8333V5.83333Z"
			fill="currentColor"
		/>
	</svg>

	<svg
		v-else
		viewBox="4.17 2.5 11.67 15"
		xmlns="http://www.w3.org/2000/svg"
		class="aioseo-trash"
	>
		<path
			fill-rule="evenodd"
			clip-rule="evenodd"
			d="M12.0833 2.5H7.91663L7.08329 3.33333H4.16663V5H15.8333V3.33333H12.9166L12.0833 2.5ZM13.3333 7.5V15.8333H6.66663V7.5H13.3333ZM4.99996 5.83333H15V15.8333C15 16.75 14.25 17.5 13.3333 17.5H6.66663C5.74996 17.5 4.99996 16.75 4.99996 15.8333V5.83333Z"
			fill="currentColor"
		/>
	</svg>
</template>

<script setup>
defineProps({
	trim : Boolean
})
</script>