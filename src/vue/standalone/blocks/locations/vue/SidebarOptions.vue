<template>
	<div class="aioseo-locations-sidebar">
		<div class="sidebar-row">
			<p class="title">{{ strings.selectLocation }}</p>
			<base-select
				size="medium"
				:options="locationCategories"
				:modelValue="getCategoryOptions($root.$data.categoryId)"
				@update:modelValue="values => $root.$data.categoryId = values.value"
				track-by="value"
			/>
		</div>
	</div>
</template>

<script>
import {
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import BaseSelect from '@/vue/components/common/base/Select'
export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore(),
			rootStore       : useRootStore()
		}
	},
	components : {
		BaseSelect
	},
	data () {
		return {
			locationCategories : [],
			strings            : {
				selectLocation : this.rootStore.aioseo.localBusiness.taxonomySingleLabel
			}
		}
	},
	methods : {
		getCategoryOptions (option) {
			let selected = this.locationCategories.find(u => u.value === option)

			// Try to find the current category ( inside a location ) or default to the first category in the list.
			if (!selected) {
				selected = this.locationCategories.find(u => u.value === this.postEditorStore.currentPost.localBusinessCategory) || this.locationCategories.find(u => !!u)
				if (selected) {
					this.$root.$data.categoryId = selected.value
				}
			}

			return selected
		}
	},
	created () {
		if (this.$root.$data.categories) {
			this.$root.$data.categories.forEach(category => {
				this.locationCategories.push({
					value : category.id,
					label : category.name
				})
			})
		}
	}
}
</script>