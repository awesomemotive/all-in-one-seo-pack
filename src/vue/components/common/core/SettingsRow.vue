<template>
	<grid-row
		class="aioseo-settings-row"
		:class="{
			'no-horizontal-margin': noHorizontalMargin,
			'no-vertical-margin': noVerticalMargin,
			'no-border': noBorder,
			'no-right-max-width': noRightMaxWidth
		}"
	>
		<slot name="header" />

		<grid-column
			v-if="showLeftColumn"
			:md="leftSize"
		>
			<div
				:class="[
					'settings-name',
					{ 'no-name': !name && !$slots.name }
				]"
			>
				<div
					class="name"
					:class="[
						{ align: align },
						{ 'align-small': alignSmall }
					]"
				>
					<slot name="name">
						{{ name }}

						<span
							class="required-field"
							v-if="required"
						>
							*
						</span>
					</slot>
				</div>

				<div
					v-if="$slots.description"
					class="aioseo-description"
				>
					<slot name="description" />
				</div>
			</div>
		</grid-column>

		<grid-column
			:md="rightSize"
			v-if="$slots.content"
		>
			<div class="settings-content">
				<slot name="content" />
			</div>
		</grid-column>
	</grid-row>
</template>

<script>
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
export default {
	components : {
		GridColumn,
		GridRow
	},
	props : {
		align              : Boolean,
		alignSmall         : Boolean,
		name               : String,
		required           : Boolean,
		noHorizontalMargin : {
			type    : Boolean,
			default : false
		},
		noVerticalMargin : {
			type    : Boolean,
			default : false
		},
		noBorder : {
			type    : Boolean,
			default : false
		},
		noRightMaxWidth : {
			type    : Boolean,
			default : false
		},
		leftSize : {
			type : String,
			default () {
				return '3'
			}
		},
		rightSize : {
			type : String,
			default () {
				return '9'
			}
		},
		showLeftColumn : {
			type    : Boolean,
			default : true
		}
	}
}
</script>

<style lang="scss">
.aioseo-settings-row {
	margin-bottom: var(--aioseo-gutter);
	padding-bottom: var(--aioseo-gutter);
	border-bottom: 1px solid $border;

	@media only screen and (min-width: 912px) {

		> .col-md-3:first-child {
			max-width: 240px;
		}

		&:not(.no-right-max-width) {
			> .col-md-9:last-child {
				max-width: 940px;
				flex: 1;
			}
		}
	}

	:where(.aioseo-row) {
		--aioseo-gutter: 12px;
	}

	> :last-child {
		margin-bottom: 0;
	}

	&.no-margin {
		margin-bottom: 0;
	}

	&.small-padding {
		padding-bottom: 4px;
	}

	&.medium-margin {
		margin-bottom: 16px;
	}

	&.no-border {
		border: none;
		margin-bottom: 0;
	}

	&.no-horizontal-margin {
		margin-left: 0 !important;
		margin-right: 0 !important;
	}

	&.no-vertical-margin {
		margin-top: 0 !important;
		margin-bottom: 0 !important;
	}

	> .aioseo-col {
		padding-block: 0;
	}

	.settings-name {
		color: $black;

		.name {
			font-weight: $font-bold;
			font-size: 14px;
			line-height: 22px;
			display: flex;
			align-items: center;

			&.small-margin {
				margin-bottom: 5px;
			}

			&.align-small {
				line-height: 30px;
			}

			.aioseo-pro-badge {
				margin-left: 10px;
			}
		}

		.required-field {
			color: $red;
		}

		.aioseo-description {
			margin-top: 12px;
		}
	}

	.settings-content {
		font-size: $font-md;
		line-height: 22px;
	}

	p.description {
		font-size: 14px;
	}
}
</style>