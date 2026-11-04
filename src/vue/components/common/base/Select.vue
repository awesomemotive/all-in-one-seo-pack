<template>
	<multiselect
		class="aioseo-select"
		:class="[
			{ [size]: size },
			{ multiple: multiple }
		]"
		:modelValue="modelValue"
		@update:modelValue="$emit('update:modelValue', $event)"
		:options="effectiveOptions"
		:multiple="multiple"
		:taggable="taggable"
		:placeholder="placeholder || strings.searchPlaceholder"
		:tag-placeholder="tagPlaceholder"
		:show-labels="false"
		:track-by="trackBy"
		:custom-label="customLabel"
		:allow-empty="allowEmpty || !!multiple"
		:filterable="filterable"
		:internal-search="true"
		:loading="isLoading"
		:searchable="searchable"
		:open-direction="openDirection"
		:use-teleport="floatDropdown"
		:content-wrapper-class="floatDropdown ? contentWrapperClasses : ''"
		:group-values="groupValues"
		:group-label="groupLabel"
		:disabled="disabled"
		:close-on-select="closeOnSelect"
		@search-change="searchChange"
		@tag="addTag"
		@open="onOpen"
		@close="onClose"
		ref="aioseo-select"
	>
		<template #singleLabel="{ option }">
			<slot name="singleLabel" :option="option">
				{{ option.label }}
			</slot>
		</template>

		<template #tag="{ option, search, remove }">
			<slot name="tag" :option="option" :search="search" :remove="remove">
				<div class="multiselect__tag">
					<div class="multiselect__tag-value">
						{{ option.label }}
					</div>

					<div
						class="multiselect__tag-remove"
						@mousedown.prevent
						@click.stop="remove(option)"
					>
						<svg-close />
					</div>
				</div>
			</slot>
		</template>

		<template #option="{ option, search }">
			<slot name="option" :option="option" :search="search">
				{{ option.$isLabel ? option.$groupLabel : option.label }}

				<span class="docLink"
					v-if="option.docLink"
					v-html="option.docLink"
				/>
			</slot>
		</template>

		<template #caret="{ toggle }">
			<slot name="caret" :toggle="toggle">
				<div
					class="multiselect__select"
				>
					<svg-caret />
				</div>
			</slot>
		</template>

		<template #noOptions>
			<slot name="noOptions" />
		</template>

		<template #noResult>
			<slot name="noResult" />
		</template>
	</multiselect>
</template>

<script>
import { createPopper } from '@popperjs/core'
import { debounce } from '@/vue/utils/debounce'
import 'vue-multiselect/dist/vue-multiselect.css'
import Multiselect from 'vue-multiselect'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgClose from '@/vue/components/common/svg/Close'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits      : [ 'open', 'close', 'update:modelValue' ],
	components : {
		Multiselect,
		SvgCaret,
		SvgClose
	},
	props : {
		options : {
			type     : Array,
			required : true
		},
		trackBy : {
			type : String,
			default () {
				return 'value'
			}
		},
		multiple   : Boolean,
		taggable   : Boolean,
		filterable : Boolean,
		searchable : {
			type : Boolean,
			default () {
				return true
			}
		},
		placeholder : {
			type : String,
			default () {
				return ''
			}
		},
		customLabel : {
			type    : Function,
			default : ({ label }) => label
		},
		name          : String,
		modelValue    : [ String, Array, Number, Object ],
		ajaxSearch    : Function,
		noDataText    : String,
		popperClass   : String,
		loading       : Boolean,
		disabled      : Boolean,
		size          : String,
		openDirection : {
			type : String,
			default () {
				return null
			}
		},
		groupValues   : String,
		groupLabel    : String,
		closeOnSelect : {
			type : Boolean,
			default () {
				return true
			}
		},
		tagPlaceholder : String,
		allowEmpty     : {
			type : Boolean,
			default () {
				return false
			}
		},
		preserveOptions : {
			type : Boolean,
			default () {
				return true
			}
		},
		// When true, the dropdown panel is positioned with `position: fixed` and tracked
		// against the trigger so it escapes any `overflow: hidden` / scroll-containing
		// ancestor. Use for selects rendered inside modals or other constrained surfaces.
		floatDropdown : {
			type    : Boolean,
			default : false
		}
	},
	data () {
		return {
			isLoading       : false,
			internalOptions : [],
			popperInstance  : null,
			popperRaf       : null,
			popperObserver  : null,
			strings         : {
				searchPlaceholder : __('Type to search...', td)
			}
		}
	},
	watch : {
		options () {
			this.resetFirstLastOption()
		}
	},
	computed : {
		contentWrapperClasses () {
			const classes = [ 'aioseo-select__dropdown' ]
			if (this.size) {
				classes.push(`aioseo-select__dropdown--${this.size}`)
			}

			return classes.join(' ')
		},
		effectiveOptions () {
			if (this.preserveOptions) {
				// Combine internal options with current options
				const allOptions = [ ...this.internalOptions ]

				// Add any new options that aren't in the internal list
				this.options.forEach(option => {
					if (!allOptions.some(internalOption => internalOption.value === option.value)) {
						allOptions.push(option)
					}
				})

				return allOptions
			}

			return this.options
		}
	},
	methods : {
		addTag (newTag) {
			newTag.split(',').forEach(originalTag => {
				const tag = {
					label : originalTag,
					value : originalTag
				}

				// Add only if there are no duplicates.
				if (this.preserveOptions) {
					// Use internal options when preserveOptions is enabled
					if (!this.internalOptions.some(option => option.value === tag.value)) {
						this.internalOptions.push(tag)
					}
				} else {
					// Original behavior
					if (!this.options.some(option => option.value === tag.value)) {
						this.options.push(tag)
					}
				}

				if (!this.modelValue.some(model => model.value === tag.value)) {
					this.modelValue.push(tag)
				}
			})

			this.$emit('update:modelValue', this.modelValue)

			this.$refs['aioseo-select'].$el.focus()
		},
		searchChange (query) {
			if (!query) {
				return
			}

			if (this.ajaxSearch) {
				this.isLoading = true
				debounce(() => {
					this.ajaxSearch(query)
						.then(() => (this.isLoading = false))
				}, 500)
			}
		},
		resetFirstLastOption () {
			this.$nextTick(() => {
				if (!this.$refs['aioseo-select']) {
					return
				}

				const elements = this.$refs['aioseo-select']?.$el?.querySelectorAll('li.multiselect__element') || []
				elements.forEach((element, index) => {
					element.classList.remove('last')
					element.classList.remove('first')

					if (0 === index) {
						element.classList.add('first')
					}

					if (index === elements.length - 1) {
						element.classList.add('last')
					}
				})
			})
		},
		onOpen () {
			this.$emit('open')
			if (!this.floatDropdown) {
				return
			}
			this.popperRaf = requestAnimationFrame(() => this.createDropdownPopper())
		},
		onClose () {
			this.$emit('close')
			this.destroyDropdownPopper()
		},
		createDropdownPopper () {
			const select    = this.$refs['aioseo-select']
			const reference = select?.$el?.querySelector('.multiselect__tags')
			const floating  = select?.$refs?.list
			if (!reference || !floating) {
				return
			}

			this.popperInstance = createPopper(reference, floating, {
				placement : 'bottom-start',
				strategy  : 'fixed',
				modifiers : [
					{ name: 'offset', options: { offset: [ 0, 0 ] } },
					{ name: 'flip', options: { fallbackPlacements: [ 'top-start' ], padding: 8 } },
					{ name: 'preventOverflow', options: { padding: 8, altAxis: true } },
					{ name: 'computeStyles', options: { gpuAcceleration: false, adaptive: true } },
					{ name: 'eventListeners', options: { scroll: true, resize: true } },
					{ name: 'hide' },
					{ name: 'applyStyles', enabled: false },
					{
						name     : 'writeDropdownStyles',
						enabled  : true,
						phase    : 'write',
						requires : [ 'computeStyles' ],
						fn ({ state }) {
							// The panel lives in <body>, so it would otherwise keep following a field scrolled out of its container.
							if (state.modifiersData.hide?.isReferenceHidden) {
								select.deactivate()
								return
							}

							state.elements.popper.setAttribute('data-popper-placement', state.placement)
							select.dropdownStyles = {
								...state.styles.popper,
								width : `${state.rects.reference.width}px`
							}
						}
					}
				]
			})

			this.popperObserver = new ResizeObserver(() => this.popperInstance?.update())
			this.popperObserver.observe(reference)
		},
		destroyDropdownPopper () {
			if (this.popperRaf) {
				cancelAnimationFrame(this.popperRaf)
				this.popperRaf = null
			}
			if (this.popperObserver) {
				this.popperObserver.disconnect()
				this.popperObserver = null
			}
			if (this.popperInstance) {
				this.popperInstance.destroy()
				this.popperInstance = null
			}
		}
	},
	mounted () {
		this.resetFirstLastOption()

		// Initialize internal options when preserveOptions is enabled
		if (this.preserveOptions && 0 < this.options.length) {
			this.internalOptions = [ ...this.options ]
		}
	},
	beforeUnmount () {
		this.destroyDropdownPopper()
	}
}
</script>

<style lang="scss">
@mixin aioseo-select-dropdown-content {
	border: 1px solid $input-border;
	border-top: none;
	border-bottom-left-radius: 3px;
	border-bottom-right-radius: 3px;
	z-index: 50;
	-webkit-overflow-scrolling: touch;

	.multiselect__content {
		max-width: 100%;
	}

	li.multiselect__element {
		margin: 0;
		border-bottom: 1px solid $border;

		&.last {
			border-bottom: none;
		}

		.multiselect__option {
			color: $black;
			font-weight: 400;
			font-size: $font-md;
			white-space: normal;
			line-height: 22px;
			padding: 8px;
			min-height: 38px;

			&[data-select='']::after {
				content: none;
			}

			&--highlight {
				background-color: $inline-background;

				&:after {
					background-color: $blue;
					color: #fff;
					font-size: 12px;
				}
			}

			&--selected {
				background-color: $inline-background;
			}

			&--disabled {
				font-weight: initial;
				background-color: #fff !important;
				color: $placeholder-color;
			}

			.docLink {
				font-size: 13px;
				float: right;
			}
		}
	}
}

@mixin aioseo-select-dropdown-small {
	li.multiselect__element .multiselect__option {
		font-size: 14px;
		padding: 8px;
		min-height: 30px;
	}
}

.aioseo-select {
	height: 48px;

	&.multiselect--disabled {
		.multiselect__select {
			background: none;
		}
	}

	.multiselect__select {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: 40px;

		&:before {
			display: none;
		}

		svg.aioseo-caret {
			color: $black;
			width: 18px;
			height: 18px;
			transition: transform 0.3s;
		}
	}

	.multiselect__tags {
		pointer-events: none !important;
		border: 1px solid $input-border;
		border-radius: 3px;
		display: flex;
		justify-content: center;
		flex-direction: column;
		padding: 16px 40px 16px 16px;

		.multiselect__spinner {
			height: calc(100% - 2px);
			border: 2px solid transparent;

			&:after,
			&:before {
				border-top-color: $black2;
			}
		}

		.multiselect__tag-remove {
			pointer-events: auto !important;
		}

		.multiselect__single {
			display: inline-flex;
			margin: 0;
			padding: 0;
			font-size: $font-md;
			color: $black;
			overflow: hidden;
			white-space: nowrap;
			text-overflow: ellipsis;
		}

		.multiselect__placeholder {
			color: $placeholder-color;
			font-size: 14px;
			line-height: 20px;
			margin: 0;
			padding: 0;
		}

		.multiselect__input {
			padding: 0;
			margin: 0 10px 0 0;
			border-radius: 0;
			border: none;
			color: $black;
			min-height: auto;
			font-size: 14px;
			line-height: 20px;

			&::placeholder {
				font-weight: inherit;
				text-transform: none;
				letter-spacing: inherit;
			}

			&:focus {
				outline: 0;
				box-shadow: none;
				border: none;
			}

			&::placeholder {
				color: $placeholder-color;
			}
		}

		.multiselect__tags-wrap {
			display: flex;
			align-items: center;
			flex-wrap: wrap;
			gap: 6px;

			.multiselect__tag {
				margin: 0 3px 0 0;
				padding: 0;
				display: inline-flex;
				align-items: center;
				font-size: 14px;
				font-weight: 600;
				color: $black2;
				margin-bottom: 0;
				height: 24px;
				background-color: $background;
				flex-shrink: 0;

				.multiselect__tag-value {
					padding: 0 5px 0 10px;
				}

				.multiselect__tag-remove {
					padding: 0 10px;
					height: 100%;
					cursor: pointer;
					background-color: $background;
					display: flex;
					align-items: center;
					pointer-events: all !important;

					&:hover {
						background-color: $black2;
						color: #fff;

						svg.aioseo-close {
							color: #fff;
						}
					}

					svg.aioseo-close {
						color: $black2;
						width: 10px;
						height: 10px;
					}
				}
			}
		}

		.aioseo-error & {
			border-color: $red;
		}
	}

	&.multiselect--active {
		.multiselect__tags-wrap {
			margin-bottom: 7px;
		}
	}

	&.small {
		height: 30px;

		&.multiselect {
			height: auto;
		}

		.multiselect__tags {
			height: 30px;
			padding: 0 34px 0 8px;

			.multiselect__placeholder {
				font-size: 14px;
			}
		}

		.multiselect__select {
			height: 30px;
			padding: 0 0 0 8px;
		}

		.multiselect__input {
			font-size: 14px;
		}

		.multiselect__content-wrapper {
			@include aioseo-select-dropdown-small;
		}
	}

&.medium {
		height: 40px;

		.multiselect__tags {
			padding: 9px 40px 9px 12px;
		}

		.multiselect__select {
			min-height: 38px;
		}
	}

	&.multiple {
		height: auto;
		min-height: 48px;

		&.small {
			min-height: 30px;
		}

		&.medium {
			min-height: 40px;
		}
	}

	&.multiselect--above {
		.multiselect__content-wrapper {
			border-top: 1px solid $input-border;
			border-bottom: none;
		}
	}

	.multiselect__content-wrapper {
		@include aioseo-select-dropdown-content;
	}
}

.multiselect__content-wrapper.aioseo-select__dropdown {
	box-sizing: border-box;

	*,
	*::before,
	*::after {
		box-sizing: border-box;
	}

	@include aioseo-select-dropdown-content;

	z-index: var(--aioseo-select-dropdown-z-index, 100000);

	&[data-popper-placement^="top"] {
		border-top: 1px solid $input-border;
		border-bottom: none;
		border-top-left-radius: 3px;
		border-top-right-radius: 3px;
		border-bottom-left-radius: 0;
		border-bottom-right-radius: 0;
	}

	&.aioseo-select__dropdown--small {
		@include aioseo-select-dropdown-small;
	}
}
</style>