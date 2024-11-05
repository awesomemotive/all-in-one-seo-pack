<template>
	<span
		class="aioseo-writing-assistant__keyword-importance"
	>
		<span class="aioseo-writing-assistant__keyword-importance-value">{{ keyword.importance }}</span> <span class="aioseo-writing-assistant__keyword-importance-limit">/ 10</span>
	</span>
</template>

<script>
export default {
	props : {
		keyword : Object
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant__keyword-importance {
	&-value {
		font-weight: 700;
	}

	&-limit {
		color: $placeholder-color;
	}
}
</style>