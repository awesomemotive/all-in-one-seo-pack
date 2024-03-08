<template>
	<core-settings-row
		class="aioseo-display-info"
		:name="label"
	>
		<template #content>
			<base-box-toggle
				v-model="currentItem"
				name="displayInfo"
				:options="boxToggleOptions"
				@update:modelValue="(value) => inputEvent(value)"
			>
				<template #extra>
					<slot name="extra" />
				</template>
				<template #shortcode>
					<svg-shortcode />
					<p>{{ strings.shortcode }}</p>
				</template>
				<template #block>
					<svg-gutenberg-block />
					<p>{{ strings.gutenbergBlock }}</p>
				</template>
				<template #php>
					<svg-php />
					<p>{{ strings.phpCode }}</p>
				</template>
				<template #widget>
					<svg-widget />
					<p>{{ strings.widget }}</p>
				</template>
			</base-box-toggle>

			<div class="displayInfo-show-content">
				<transition-slide
					:key="index" v-for="(item, index) in boxToggleOptions"
					:active="item.value === currentItem"
				>
					<div
						class="copy-box"
						v-if="'extra' !== item.slot"
					>
						<div>
							<div
								class="aioseo-description"
								v-if="item.desc"
								v-html="item.desc"
							/>
							<core-copy-block
								v-if="item.copy"
								:message="item.copy"
							/>

							<a
								v-if="$slots[item.slot + 'Advanced'] && !showAdvancedSettings"
								class="advanced-settings-link"
								href="#"
								@click.prevent="showAdvancedSettings = !showAdvancedSettings"
							>{{ strings.advancedSettings }}</a>
						</div>

						<transition-slide
							v-if="$slots[item.slot + 'Advanced']"
							:class="{ 'advanced-settings' : true, 'advanced-settings-hidden' : !showAdvancedSettings }"
							:active="showAdvancedSettings"
						>
							<slot :name="item.slot + 'Advanced'"/>
						</transition-slide>
					</div>

					<div
						class="extra-box"
						v-if="'extra' === item.slot"
					>
						<slot
							name="extraBox"
							:item="item"
						/>
					</div>
				</transition-slide>
			</div>
		</template>
	</core-settings-row>
</template>

<script>
import BaseBoxToggle from '@/vue/components/common/base/BoxToggle'
import CoreCopyBlock from '@/vue/components/common/core/CopyBlock'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
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
		CoreCopyBlock,
		CoreSettingsRow,
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
			type : Object,
			default () {
				return {
					block     : { copy: '', desc: '', input: false },
					shortcode : { copy: '', desc: '', input: false },
					widget    : { copy: '', desc: '', input: false },
					php       : { copy: '', desc: '', input: false },
					extra     : { copy: '', desc: '', input: false }
				}
			}
		}
	},
	data () {
		return {
			currentItem          : Object.keys(this.options)[0],
			errors               : [],
			showAdvancedSettings : false,
			strings              : {
				advancedSettings : __('Advanced Settings', td),
				gutenbergBlock   : __('Gutenberg Block', td),
				phpCode          : __('PHP Code', td),
				shortcode        : __('Shortcode', td),
				widget           : __('Widget', td)
			}
		}
	},
	computed : {
		boxToggleOptions () {
			const boxOptions = Object.keys(this.options)
			return boxOptions.map((key) => ({
				value : key,
				slot  : key,
				copy  : this.options[key].copy,
				desc  : this.options[key].desc
			}))
		}
	},
	watch : {
		currentItem (newValue) {
			this.currentItem = newValue
		}
	},
	methods : {
		inputEvent (value) {
			this.$emit('input', value)
			this.showAdvancedSettings = false
		}
	}
}
</script>

<style lang="scss">
.aioseo-display-info {
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

	.copy-box,
	.extra-box {
		margin-top: 16px;
		padding: 12px;
		border-radius: 3px;
		background-color: $box-background;

		.aioseo-description {
			margin: 0;
		}

		.aioseo-copy-block {
			margin: 20px 0 0;

			.message,
			.copy {
				min-height: 40px;
				align-self: stretch;
				font-size: $font-md;
				font-weight: 400;
				line-height: 22px;
				padding: 8px 12px;
			}
		}

		.advanced-settings-link {
			display: inline-block;
			padding-top: 5px;
			margin: 16px 0 0 16px;
			color: $placeholder-color;
			text-decoration: underline;
			font-size: 13px;

			&:hover {
				text-decoration: none;
			}
		}

		.advanced-settings {
			padding-top: 20px;
		}
	}

	.advanced-settings.advanced-settings-hidden {
		display: none;
	}

	.aioseo-tooltip {
		margin-left: 0;
	}
}
</style>