<template>
	<div class="aioseo-wp-additional-filters alignleft actions">
		<template
			v-for="(filter, index) in additionalFilters"
			:key="index"
		>
			<label
				for="filter-by-date"
				class="screen-reader-text"
			>{{ filter.label }}</label>

			<select
				:name="filter.name"
				v-model="selectedFilters[filter.name]"
				@change="event => $emit('change', {name : filter.name, selectedValue : event.target.value})"
			>
				<option
					v-for="(option, index) in filter.options"
					:key="index"
					:value="option.value"
				>
					{{ option.label }}
				</option>
			</select>
		</template>

		<button
			class="button action"
			@click="$emit('process-additional-filters', selectedFilters)"
		>
			{{strings.filter}}
		</button>
	</div>
</template>

<script>
export default {
	emits : [ 'process-additional-filters', 'change' ],
	props : {
		additionalFilters : {
			type     : Array,
			required : true
		},
		selectedFilters : {
			type : Object,
			default () {
				return {}
			}
		}
	},
	data () {
		return {
			strings : {
				filter : this.$t.__('Filter', this.$td)
			}
		}
	},
	mounted () {
		this.setInitialOptions()
	},
	updated () {
		this.setInitialOptions()
	},
	methods : {
		setInitialOptions () {
			this.additionalFilters.forEach(filter => {
				if (!this.selectedFilters[filter.name]) {
					this.selectedFilters[filter.name] = filter.options[0].value
				}
			})
		}
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-wp-table .aioseo-wp-additional-filters {
	margin: 0 0 9px;
}
</style>