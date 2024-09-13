<template>
	<div class="aioseo-wp-items-per-page alignleft">
		<label>
			<select
				v-model="itemsPerPage"
				:disabled="disableTable"
			>
				<option
					v-for="(option, index) in items"
					:key="index"
					:value="option"
				>{{ option }}</option>
			</select>

			<span>{{ strings.itemsPerPage }}</span>
		</label>
	</div>
</template>

<script>
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	props : {
		modelValue   : Number,
		disableTable : Boolean
	},
	data () {
		return {
			items        : [ 5, 10, 20, 25, 50, 100 ],
			itemsPerPage : 20,
			strings      : {
				itemsPerPage : __('items per page', td)
			}
		}
	},
	watch : {
		itemsPerPage (newVal) {
			this.$emit('update:modelValue', newVal)
		}
	},
	mounted () {
		this.itemsPerPage = this.modelValue
	}
}
</script>

<style lang="scss">
.aioseo-wp-items-per-page {
	label {
		display: flex;
		align-items: center;
	}

	select {
		margin-right: 5px;
	}
}
</style>