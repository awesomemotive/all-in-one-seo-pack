<template>
	<div
		class="aioseo-card"
		:class="{
			'disabled' : disabled,
			...cardClass
		}"
		:id="cardId ? `aioseo-card-${cardId}` : null"
	>
		<div
			v-if="!hideHeader"
			class="header"
			:class="[ { toggles : toggles } ]"
			@click="toggleCard"
		>
			<div class="text">
				<div class="header-icon">
					<slot name="header-icon" />
				</div>

				<div
					class="header-title"
					@click.stop
				>
					<slot name="header">
						<span>{{ headerText }}</span>
					</slot>
				</div>

				<core-tooltip
					v-if="$slots.tooltip"
				>
					<svg-circle-question-mark />

					<template #tooltip>
						<slot name="tooltip" />
					</template>
				</core-tooltip>

				<div class="header-extra">
					<slot name="header-extra" />
				</div>
			</div>

			<svg-caret
				v-if="!closes && toggles && settingsStore.settings.toggledCards && !noSlide && !disabled"
				:class="{ rotated: !settingsStore.settings.toggledCards[slug] }"
			/>

			<svg-close
				@click="$emit('close-card', true)"
				v-if="closes"
			/>
		</div>

		<div v-if="$slots['disabled'] && disabled" class="content">
			<slot name="disabled" />
		</div>

		<transition-slide
			v-if="(settingsStore.settings.toggledCards || noSlide) && !disabled"
			:active="(settingsStore.settings.toggledCards[slug] && toggles) || noSlide"
		>
			<div
				v-if="$slots['before-tabs']"
				class="content"
			>
				<slot name="before-tabs" />
			</div>

			<slot name="tabs" />

			<div
				v-if="$slots.default"
				class="content"
			>
				<slot />
			</div>
		</transition-slide>
	</div>
</template>

<script>
import {
	useSettingsStore
} from '$/vue/stores'

import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import SvgClose from '@/vue/components/common/svg/Close'
import TransitionSlide from '@/vue/components/common/transition/Slide'
export default {
	emits : [ 'close-card', 'toggle-card' ],
	setup () {
		return {
			settingsStore : useSettingsStore()
		}
	},
	components : {
		CoreTooltip,
		SvgCaret,
		SvgCircleQuestionMark,
		SvgClose,
		TransitionSlide
	},
	props : {
		slug : {
			type     : String,
			required : true
		},
		headerText : String,
		toggles    : {
			type : Boolean,
			default () {
				return true
			}
		},
		hideHeader       : Boolean,
		noSlide          : Boolean,
		closes           : Boolean,
		saveToggleStatus : {
			type : Boolean,
			default () {
				return true
			}
		},
		disabled  : Boolean,
		cardClass : {
			type : Object,
			default () {
				return {}
			}
		},
		cardId : String
	},
	watch : {
		toggles (value) {
			const slug = this.slug
			if (value && !this.settingsStore.settings.toggledCards[slug]) {
				this.settingsStore.toggleCard({ slug, shouldSave: true })
			}
		}
	},
	methods : {
		toggleCard () {
			this.settingsStore.toggleCard({ slug: this.slug, shouldSave: this.saveToggleStatus })
			this.$emit('toggle-card')
		}
	}
}
</script>

<style lang="scss">
.aioseo-card {
	position: relative;
	color: $black;
	background-color: #fff;
	border: 1px solid $border;
	box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);
	margin: var(--aioseo-gutter) 0;

	&.disabled {
		.content {
			background: $box-background;
		}
	}

	svg.aioseo-circle-question-mark {
		width: 17px;
		height: 17px;
		color: $placeholder-color;
		transition: background-color 0.2s ease;

		&:hover {
			color: darken($placeholder-color, 20%);
		}
	}

	> .header {
		display: flex;
		align-items: center;
		height: 60px;
		padding: 0 $gutter;
		font-weight: 600;
		font-size: 16px;
		border-bottom: 1px solid $border;

		&.toggles {
			cursor: pointer;
		}

		.text {
			flex: 1 1 auto;
			display: flex;
			align-items: center;

			&> * {
				cursor: auto;
			}

			.header-icon {
				display: flex;

				svg {
					width: 24px;
					height: 24px;
					margin-right: 16px;
				}
			}

			.header-title {
				display: flex;
				align-items: center;

				.aioseo-tooltip,
				+ .aioseo-tooltip {
						> div:has(svg) {
						display: inline-flex;
					}
				}
			}

			svg.aioseo-circle-question-mark {
				cursor: pointer;
				width: 17px;
				height: 17px;
			}

			.aioseo-pro-badge {
				margin-left: 10px;
			}

			.card-score {
				display: flex;
				flex: 1;
				align-items: center;
				justify-content: flex-end;
				padding-right: 10px;
				font-size: 12px;
				&.green {
					color: $green;
				}
				&.orange {
					color: $orange;
				}
				&.red {
					color: $red;
				}
				svg {
					margin-right: 4px;
				}
			}
		}

		svg.aioseo-caret {
			width: 24px;
			height: 24px;
			cursor: pointer;
			transform: rotate(-180deg);
			transition: transform 0.3s;

			&.rotated {
				transform: rotate(-90deg);
			}
		}

		svg.aioseo-close {
			width: 14px;
			height: 14px;
			cursor: pointer;
		}
	}

	.content {
		font-size: $font-md;
		line-height: 22px;
		padding: $gutter;
		position: relative;
	}

	div.aioseo-settings-row:last-child {
		margin-bottom: 0;
		border-bottom: none;
		padding-bottom: 0;
	}
}

@media only screen and (max-width: 911px) {

	.aioseo-col {
		&.col-sm-12,
		&.col-xs-12  {

			> .aioseo-card:last-child {
				margin-bottom: 0;
			}

			+ .aioseo-col > .aioseo-card:first-child {
				margin-top: 0;
			}
		}
	}
}
</style>