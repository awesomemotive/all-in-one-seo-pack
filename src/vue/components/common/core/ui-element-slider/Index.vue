<template>
	<core-settings-row
		class="aioseo-ui-element-slider"
		:name="label"
	>
		<template #content>
			<base-box-toggle
				v-model="currentItem"
				name="ui-element-slider"
				:options="boxToggleOptions"
			>
				<template #extra>
					<slot name="extra" />
				</template>

				<template #shortcode>
					<svg-shortcode />
					<p>{{ boxStrings.shortcode }}</p>
				</template>

				<template #block>
					<svg-gutenberg-block />
					<p>{{ boxStrings.gutenbergBlock }}</p>
				</template>

				<template #php>
					<svg-php />
					<p>{{ boxStrings.phpCode }}</p>
				</template>

				<template #widget>
					<svg-widget />
					<p>{{ boxStrings.widget }}</p>
				</template>
			</base-box-toggle>

			<div class="ui-element-slider-content">
				<transition-slide
					:key="index" v-for="(item, index) in boxToggleOptions"
					:active="item.value === currentItem"
				>
					<slide-content
						v-if="!item.multiple"
						:item="item"
					>
						<template #[slotName(item)]>
							<core-attributes-list
								v-if="item.attributes"
								:attributes="item.attributes"
								:description="item.attributesDescription"
							/>

							<slot :name="slotName(item)" :item="item" />
						</template>
					</slide-content>

					<slide-content
						v-if="item.multiple"
						v-for:="(subItem, subIndex) in item.multiple"
						:key="subIndex"
						:item="subItem"
					>
						<template #[slotName(subItem)]>
							<core-attributes-list
								v-if="subItem.attributes"
								:attributes="subItem.attributes"
								:description="subItem.attributesDescription"
							/>
						</template>
					</slide-content>
				</transition-slide>
			</div>
		</template>
	</core-settings-row>
</template>

<script>
import BaseBoxToggle from '@/vue/components/common/base/BoxToggle'
import CoreAttributesList from '@/vue/components/common/core/AttributesList'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import SlideContent from '@/vue/components/common/core/ui-element-slider/SlideContent'
import SvgGutenbergBlock from '@/vue/components/common/svg/GutenbergBlock'
import SvgPhp from '@/vue/components/common/svg/Php'
import SvgShortcode from '@/vue/components/common/svg/Shortcode'
import SvgWidget from '@/vue/components/common/svg/Widget'
import TransitionSlide from '@/vue/components/common/transition/Slide'

import { __ } from '@wordpress/i18n'
const td = import.meta.env.VITE_TEXTDOMAIN
export default {
	components : {
		BaseBoxToggle,
		CoreAttributesList,
		CoreSettingsRow,
		SlideContent,
		SvgGutenbergBlock,
		SvgPhp,
		SvgShortcode,
		SvgWidget,
		TransitionSlide
	},
	props : {
		label : {
			type : String,
			default () {
				return __('Display Info', td)
			}
		},
		options : {
			type     : Object,
			required : true
		},
		plural : {
			type : Boolean,
			default () {
				return false
			}
		}
	},
	data () {
		return {
			currentItem : Object.keys(this.options)[0],
			strings     : {
				singular : {
					gutenbergBlock : __('Gutenberg Block', td),
					phpCode        : __('PHP Code', td),
					shortcode      : __('Shortcode', td),
					widget         : __('Widget', td)
				},
				plural : {
					gutenbergBlock : __('Gutenberg Blocks', td),
					phpCode        : __('PHP Code', td),
					shortcode      : __('Shortcodes', td),
					widget         : __('Widgets', td)
				}
			}
		}
	},
	computed : {
		boxToggleOptions () {
			const boxOptions = Object.keys(this.options)
			return boxOptions.map((key) => ({
				value : key,
				slot  : key,
				...this.options[key]
			}))
		},
		boxStrings () {
			return this.plural ? this.strings.plural : this.strings.singular
		}
	},
	methods : {
		slotName (item) {
			if (item.hasAdvanced) {
				return 'advanced'
			}

			if ('extra' === item.slot) {
				return 'extraBox'
			}

			return item.slot
		}
	},
	watch : {
		currentItem (newValue) {
			this.currentItem = newValue
		}
	}
}
</script>

<style lang="scss">
.aioseo-ui-element-slider {
	.aioseo-box-toggle {
		> .aioseo-row {

			--aioseo-gutter: 16px;
			@include aioseoGrid(4, 200px);
		}
	}

	svg.aioseo-widget,
	svg.aioseo-new-page,
	svg.aioseo-shortcode {
		width: 100%;
		height: auto;
		max-width: 60px;
	}

	svg.aioseo-gutenberg-block {
		width: 59px;
		height: 54px;

		rect {
			width: 100%;
			width: 56px;
			height: 51px;
		}
	}

	svg.aioseo-php {
		width: 110px;
	}

	.aioseo-slide-content {
		&:first-of-type {
			.main-box {
				border-top-left-radius: 3px;
				border-top-right-radius: 3px;
			}
		}

		&:last-of-type {
			.main-box {
				border-bottom-left-radius: 3px;
				border-bottom-right-radius: 3px;
			}
		}

		&:not(:first-of-type) {
			.main-box {
				margin-top: 0;
			}
		}

		&:not(:last-of-type) {
			.main-box {
				> div {
					padding-bottom: 12px;
					border-bottom: 1px solid $border;
				}
			}
		}
	}
}
</style>