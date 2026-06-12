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
						@click.stop="remove(option)"
					>
						<svg-close
							@click.native.stop="remove(option)"
						/>
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
			this.$nextTick(() => this.positionFloatingDropdown())
			window.addEventListener('scroll', this.positionFloatingDropdown, true)
			window.addEventListener('resize', this.positionFloatingDropdown)
		},
		onClose () {
			this.$emit('close')
			if (!this.floatDropdown) {
				return
			}
			window.removeEventListener('scroll', this.positionFloatingDropdown, true)
			window.removeEventListener('resize', this.positionFloatingDropdown)
			// Don't strip the inline positioning here: the panel is still rendered while it
			// fades out, and reverting to vue-multiselect's base styles mid-fade snaps it
			// above the trigger (a visible flicker). positionFloatingDropdown() rewrites every
			// property on the next open, so leaving the stale values behind is harmless.
		},
		positionFloatingDropdown () {
			const el = this.$refs['aioseo-select']?.$el
			if (!el) {
				return
			}
			const wrapper = el.querySelector('.multiselect__content-wrapper')
			const trigger = el.querySelector('.multiselect__tags')
			if (!wrapper || !trigger) {
				return
			}

			const rect           = trigger.getBoundingClientRect()
			const viewportHeight = window.innerHeight

			// vue-multiselect opens the panel with `transition: all 0.15s` (it only means to
			// fade opacity). That `all` also animates the position/size we set below, so the
			// panel visibly slides and expands into place — scope it to opacity to kill the jerk.
			wrapper.style.transition = 'opacity 0.15s ease'
			// Apply fixed positioning and the final width before measuring height, so the
			// measurement reflects how the option text wraps. `bottom: auto` neutralizes
			// vue-multiselect's `.multiselect--above { bottom: 100% }`: without it an
			// open-above panel is constrained by both top and bottom and collapses to ~1px.
			wrapper.style.position  = 'fixed'
			wrapper.style.bottom    = 'auto'
			wrapper.style.left      = `${rect.left}px`
			wrapper.style.width     = `${rect.width}px`
			wrapper.style.maxHeight = '300px'
			// Above the modal stack — base modals in this app top out around 9999.
			wrapper.style.zIndex    = '100000'

			const spaceBelow  = viewportHeight - rect.bottom
			const spaceAbove  = rect.top
			const panelHeight = wrapper.offsetHeight
			const openAbove   = panelHeight > spaceBelow && spaceAbove > spaceBelow
			const maxHeight   = Math.max(120, Math.min(300, (openAbove ? spaceAbove : spaceBelow) - 8))

			wrapper.style.maxHeight = `${maxHeight}px`
			wrapper.style.top       = openAbove
				? `${rect.top - Math.min(panelHeight, maxHeight)}px`
				: `${rect.bottom}px`
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
		if (this.floatDropdown) {
			window.removeEventListener('scroll', this.positionFloatingDropdown, true)
			window.removeEventListener('resize', this.positionFloatingDropdown)
		}
	}
}
</script>

<style lang="scss">
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

		.multiselect__content-wrapper li.multiselect__element .multiselect__option {
			font-size: 14px;
			padding: 8px;
			min-height: 30px;
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
}
</style>