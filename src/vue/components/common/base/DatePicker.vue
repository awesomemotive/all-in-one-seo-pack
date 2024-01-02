<template>
	<div class="aioseo-datepicker">
		<el-date-picker
			v-model="value"
			ref="picker"
			:type="type"
			:size="size"
			:range-separator="separator"
			@change="value => $emit('change', value, rolling)"
			:clearable="false"
			:shortcuts="shortcuts"
			:disabled-date="isDisabledDate"
			:start-placeholder="strings.startDate"
			:end-placeholder="strings.endDate"
		/>

		<div
			class="aioseo-datepicker-picker"
			:class="[`aioseo-datepicker-picker--${size}`]"
			@click="openPicker"
		>
			<svg-calendar />

			<div class="label">
				<template
					v-if="isDateRange"
				>
					{{ label[0] }}
					<span>{{ separator }}</span>
					{{ label[1] }}
				</template>

				<template
					v-if="!isDateRange"
				>
					{{ label }}
				</template>
			</div>

			<div
				v-if="clearable && value"
				class="clear"
				@click.stop="clear"
			>
				<svg-circle-close />
			</div>
		</div>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import dateFormat from '@/vue/utils/dateFormat'
import SvgCalendar from '@/vue/components/common/svg/Calendar'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import { ElDatePicker } from 'element-plus'
import en from 'element-plus/dist/locale/en.mjs'
import 'element-plus/theme-chalk/el-date-picker.css'
export default {
	setup () {
		return {
			rootStore : useRootStore(),
			locale    : en
		}
	},
	emits      : [ 'change', 'updated' ],
	components : {
		ElDatePicker,
		SvgCalendar,
		SvgCircleClose
	},
	props : {
		size : {
			type : String,
			default () {
				return 'default'
			}
		},
		type : {
			type : String,
			default () {
				return 'daterange'
			}
		},
		separator : {
			type : String,
			default () {
				return '-'
			}
		},
		clearable : {
			type : Boolean,
			default () {
				return true
			}
		},
		isDisabledDate : {
			type : Function,
			default () {
				return false
			}
		},
		shortcuts : {
			type : Array,
			default () {
				return []
			}
		},
		defaultRolling : {
			type : String,
			default () {
				return null
			}
		},
		dateFormat   : String,
		defaultValue : [ Array, Object ]
	},
	data () {
		return {
			rolling : null,
			value   : null,
			strings : {
				startDate : this.$t.__('Start date', this.$td),
				endDate   : this.$t.__('End date', this.$td)
			}
		}
	},
	computed : {
		format () {
			return this.dateFormat || this.rootStore.aioseo.data.dateFormat
		},
		label () {
			if (!this.value) {
				return ' '
			}
			if (!this.isDateRange) {
				return dateFormat(this.value, this.format)
			}
			const start = dateFormat(this.value[0], this.format)
			const end = dateFormat(this.value[1], this.format)
			return [ start, end ]
		},
		isDateRange () {
			return Array.isArray(this.value)
		}
	},
	methods : {
		openPicker () {
			this.$refs.picker.focus()
		},
		clear () {
			this.value = null
			this.$emit('change', this.value, this.rolling)
		}
	},
	mounted () {
		this.$nextTick(() => {
			if (!this.value && this.defaultValue) {
				this.value = this.defaultValue
			}
		})
		this.rolling = this.defaultRolling
		window.aioseoBus.$on('rolling', (rolling) => {
			this.rolling = rolling
		})
		this.$emit('updated', this.rolling)
	},
	updated () {
		this.$emit('updated', this.rolling)
		// Each time the date range is updated, the local rolling value needs to be reset.
		// Otherwise the rolling value will persist even when the user selects a custom date range.
		this.$nextTick(() => {
			this.rolling = ''
		})
	}
}
</script>

<style lang="scss">
.el-popper {
	--el-datepicker-inrange-bg-color: #E6EEFC;
	--el-datepicker-active-color: #{$blue};
	--el-color-primary: #{$blue};
	--el-datepicker-border-color: #{$input-border};
	--el-border-color-light: #{$input-border};
	font-family: $font-family;
}

.aioseo-datepicker {
	position: relative;

	&-picker {
		display: flex;
		flex-direction: row;
		align-items: center;
		padding: 5px 8px;
		background: $white;
		border: 1px solid $input-border;
		border-radius: 4px;
		color: $black;
		cursor: pointer;
		font-weight: 400;
		font-size: 14px;

		.aioseo-calendar {
			width: 12px;
			height: 13px;
			color: $placeholder-color;
			margin-right: 6px;
			pointer-events: none;
		}

		.label {
			span {
				font-weight: 400;
			}
		}

		.clear {
			opacity: 1;
			margin-left: auto;
			line-height: 0;

			svg {
				color: $placeholder-color;
				width: 15px;
				height: 15px;
			}
		}

		&--small {
			padding: 4px 10px;
			font-size: 13px;
		}

		&--default {
			padding: 6px;
			font-size: 14px;
		}

		&--large {
			padding: 7px 10px;
			min-height: 40px;
			line-height: 25px;
			font-size: 14px;
		}
	}

	// Hide El input and build ours instead.
	.el-date-editor {
		position: absolute;
		width: 100%;
		height: 100%;
		opacity: 0;
		pointer-events: none;
	}

	@at-root {
		.el-popper {
			&.el-picker__popper {
				border: 1px solid $input-border;
				box-shadow: 0 4px 26px rgba(44, 50, 76, 0.12);
				z-index: 9998 !important; /* Needs to override the .modal-mask */
			}
		}

		.el-picker-panel {
			color: $black2-hover;

			&__shortcut {
				color: $black2;
				font-weight: 400;
				font-size: 14px;
				padding-left: 16px;

				&:hover {
					color: $blue;
				}

				&.active {
					background-color: #E6EEFC;
					color: $blue;
				}
			}

			&__sidebar {
				border-right: 0;
				padding-top: 16px;
				width: 130px;
				background-color: transparent;
			}

			&__sidebar + &__body {
				margin-left: 130px;
			}

			&.el-date-range-picker {
				.el-picker-panel__content {
					border-left: 0 !important;
					border-right: 0 !important;
					padding: 10px 0;

					&::after {
						content: '';
						position: absolute;
						top: 16px;
						left: 0;
						width: 1px;
						height: 90%;
						background: $border;
					}
				}

				.el-picker-panel__icon-btn {
					padding: 1px 6px;
				}
			}

			&__footer {
				.el-button {
					font-size: 12px;
					line-height: 1;
					padding: 5px 11px;
				}
			}
		}

		.el-date-range-picker__content {
			padding: 0 8px;

			.el-date-range-picker__header div {
				font-size: 14px;
				font-weight: $font-bold;
				color: $black2-hover;
			}

			.el-date-table {
				border: 0;
			}
		}

		.el-date-picker,
		.el-date-range-picker {
			&__time-header {
				.el-input__inner {
					border: 0;

					&:focus {
						outline: 0;
						box-shadow: none;
					}
				}
			}
		}

		.el-time {
			&-spinner {
				&__item {
					margin-bottom: 0;
				}
			}
			&-panel {
				&__footer {
					.el-time-panel__btn {
						border: none;
						line-height: 28px;
						padding: 0 5px;
						margin: 0 5px;
						cursor: pointer;
						background-color: transparent;
						outline: 0;
						font-size: 12px;
						color: var(--el-text-color-primary);
					}
				}
			}
		}

		.el-date-table {
			th {
				font-size: 12px;
				font-weight: $font-bold;
				color: $black2;
				border-bottom: 0;
			}

			td {
				padding: 2px 0;
				span {
					font-size: 14px;
				}
			}

			td.end-date span,
			td.start-date span {
				background-color: $blue;
			}

			td.in-range div,
			td.in-range div:hover,
			&.is-week-mode .el-date-table__row.current div,
			&.is-week-mode .el-date-table__row:hover div {
				background-color: rgba($blue, 0.1);
			}

			td.next-month,
			td.prev-month {
				color: $placeholder-color;
			}

			td.available:hover {
				color: $blue;
			}
		}
	}
}
</style>