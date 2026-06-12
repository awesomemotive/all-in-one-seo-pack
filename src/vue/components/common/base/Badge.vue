<template>
  <span
    class="aioseo-badge round"
    :class="color"
  >
    {{ text }}
  </span>
</template>

<script setup>
defineProps({
	color : {
		type        : String,
		default     : 'black',
		description : 'Badge color(green|red|orange|black|blue)'
	},
	text : {
		type        : [ String, Number ],
		default     : '',
		description : 'Badge text'
	}
})
</script>

<style lang="scss" scoped>
.aioseo-badge {
  border-radius: 10px;

  &.black {
    color: $black;
    border: 1px solid $black;
    background-color: $gray !important;
  }

  &.green {
    color: $green;
    border: 1px solid $green;
    background-color: #ecfdf5 !important;
  }

  &.red {
    color: $red;
    border: 1px solid $red;
    background-color: #fef2f2 !important;
  }

  &.orange {
    color: $orange;
    border: 1px solid $orange;
    background-color: #fffbeb !important;
  }

  &.blue {
    color: $blue;
    border: 1px solid $blue;
    background-color: #eff6ff !important;
  }
}
</style>