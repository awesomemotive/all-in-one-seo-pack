<template>
	<div class="aioseo-exclude-posts">
		<base-select
			:options="excludeOptions"
			:ajax-search="processGetObjects"
			size="medium"
			multiple
			:modelValue="getJsonValues(optionName)"
			@update:modelValue="values => optionName = setJsonValues(values)"
			:placeholder="strings.typeToSearch"
		>
			<template #noOptions>
				{{ noOptions }}
			</template>

			<template #noResult>
				{{ strings.noResult }}
			</template>

			<template #caret="{ toggle }">
				<base-button
					class="multiselect-toggle"
					style="padding: 10px 13px;width: 40px;position: absolute;height: 36px;right: 2px;top: 2px;text-align: center;transition: transform .2s ease;"
					type="gray"
					@click="toggle"
				>
					<svg-add-plus
						style="width: 14px; height: 14px;color: black;"
					/>
				</base-button>
			</template>

			<template #option="{ option, search }">
				<div class="option">
					<div class="option-title"
						v-html="getOptionTitle(option.label, search)"
					/>

					<div class="option-details">
						<span>{{ strings.id }}: #{{ option.value }}</span>
						<span>{{ strings.type }}: {{ option.type }}</span>
					</div>
				</div>

				<a
					class="option-permalink"
					:href="option.link"
					target="_blank"
					@click.stop="() => {}"
				>
					<svg-external />
				</a>
			</template>

			<template #tag="{ option, remove }">
				<div class="multiselect__tag">
					<div class="multiselect__tag-value">
						{{ option.label }} - #{{ option.value }}
					</div>

					<div
						class="multiselect__tag-remove"
						@click.stop="remove(option)"
					>
						<svg-close
							@click.native.stop="remove(option)"
						/>
					</div>
				</div>
			</template>
		</base-select>

		<base-button
			type="gray"
			size="small"
			@click="optionName = []"
		>
			{{ strings.clear }}
		</base-button>
	</div>
</template>

<script>
import {
	useOptionsStore
} from '@/vue/stores'

import { useJsonValues } from '@/vue/composables/JsonValues'

import SvgAddPlus from '@/vue/components/common/svg/AddPlus'
import SvgClose from '@/vue/components/common/svg/Close'
import SvgExternal from '@/vue/components/common/svg/External'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			getJsonValues,
			setJsonValues
		} = useJsonValues()

		return {
			getJsonValues,
			optionsStore : useOptionsStore(),
			setJsonValues
		}
	},
	components : {
		SvgAddPlus,
		SvgClose,
		SvgExternal
	},
	props : {
		type : {
			type     : String,
			required : true
		}
	},
	data () {
		return {
			excludeOptions : [],
			strings        : {
				typeToSearch   : __('Type to search...', td),
				noOptionsPosts : __('Begin typing a post ID, title or slug to search...', td),
				noOptionsTerms : __('Begin typing a term ID or name to search...', td),
				noResult       : __('No results found for your search. Try again!', td),
				clear          : __('Clear', td),
				id             : __('ID', td),
				type           : __('Type', td)
			}
		}
	},
	computed : {
		optionName : {
			get () {
				return 'posts' === this.type ? JSON.parse(this.$root.$data.excluded_posts) : JSON.parse(this.$root.$data.excluded_terms)
			},
			set (value) {
				value = JSON.stringify(value)
				if ('posts' === this.type) {
					this.$root.$data.excluded_posts = value
					return
				}
				this.$root.$data.excluded_terms = value
			}
		},
		noOptions () {
			if ('posts' === this.type) {
				return this.strings.noOptionsPosts
			}
			return this.strings.noOptionsTerms
		}
	},
	methods : {
		processGetObjects (term) {
			return this.optionsStore.getObjects({ query: term, type: this.type })
				.then((response) => {
					this.excludeOptions = response.body.objects
				})
		},
		getOptionTitle (title, search) {
			// First, sanitize the title and search term. We must do this as we'll be using the result of this method in a v-html directive.
			title  = title.replace(/<\/?[^>]+(>|$)/g, '')
			search = search.replace(/<\/?[^>]+(>|$)/g, '')

			const regex = new RegExp(`(${search})`, 'gi')
			return title.replace(regex, '<span class="search-term">$1</span>')
		}
	}
}
</script>

<style lang="scss">
.aioseo-exclude-posts {
	display: block;

	.aioseo-select {
		max-width: 600px;
		display: inline-block;
		margin-right: 16px;

		button{
			display: none;
		}

		.multiselect__option {
			display: flex;
		}

		.multiselect__option--highlight {
			.option-title {
				color: $blue;
			}
		}
	}

	.aioseo-button.gray {
		margin-top: 10px;
	}

	.option {
		flex: 1 0 auto;

		.option-title {
			font-weight: 400;
			font-size: 16px;
			color: $black;

			.search-term {
				font-weight: 600;
			}
		}

		.option-details {
			display: flex;
			align-items: center;
			font-size: 14px;
			color: $placeholder-color;

			span {
				margin-right: 15px;
			}
		}
	}

	.option-permalink {
		display: flex;
		align-items: center;

		svg.aioseo-external {
			width: 15px;
			height: 15px;
			color: $black2;
		}
	}

	.multiselect-toggle {
		padding: 10px 13px;
		width: 40px;
		position: absolute;
		height: 36px;
		right: 2px;
		top: 2px;
		text-align: center;
		z-index: 1;

		svg.aioseo-add-plus {
			width: 14px;
			height: 14px;
			color: black;
		}
	}
}
</style>