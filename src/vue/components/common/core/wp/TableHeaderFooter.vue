<template>
	<th
		scope="col"
		:style="{ width: column.width }"
		class="aioseo-manage-column manage-column"
		:class="[{
			sortable : !disableTable && column.sortable,
			asc      : 'asc' === column.sortDir && column.sortable,
			desc     : 'desc' === column.sortDir && column.sortable,
			sorted   : column.sortable && column.sorted,
		}, column.slug]"
	>
		<template v-if="allowTooltipIcon && column.tooltipIcon">
			<div class="aioseo-table-header-tooltip-icon">
				<core-tooltip
					class="action"
					type="action"
				>
					<component :is="column.tooltipIcon"></component>

					<template #tooltip>
						{{ column.label }}
					</template>
				</core-tooltip>
			</div>
		</template>

		<template v-if="!allowTooltipIcon || !column.tooltipIcon">
			<template v-if="column.sortable">
				<a
					href="#"
					@click.prevent="event => $emit('sort-column', column, event)"
				>
					<span>{{ column.label }}</span>
					<span class="sorting-indicator" />
				</a>
			</template>

			<template v-if="!column.sortable">
				<template v-if="$slots.headerFooter ">
					<slot name="headerFooter"/>
				</template>

				<template v-if="!$slots.headerFooter">
					{{ column.label }}
				</template>
			</template>
		</template>
	</th>
</template>

<script>
import CoreTooltip from '@/vue/components/common/core/Tooltip'
export default {
	emits      : [ 'sort-column' ],
	components : {
		CoreTooltip
	},
	props : {
		column : {
			type     : Object,
			required : true
		},
		disableTable     : Boolean,
		allowTooltipIcon : Boolean
	}
}
</script>

<style lang="scss">
.aioseo-manage-column {
	.aioseo-table-header-tooltip-icon {
		display: flex;
		justify-content: center;

		.aioseo-tooltip {
			margin: 0;
		}
	}

	&.sorted {
		.sorting-indicator {
			border-left: 4px solid transparent;
			border-right: 4px solid transparent;
			border-top: 4px solid;
			display: inline-block;
			height: 0;
			margin-left: 5px;
			vertical-align: middle;
			width: 0;

			&:before, &:after{
				display: none;
			}
		}

		&.asc {
			.sorting-indicator {
				border-top: none;
				border-bottom: 4px solid;
			}
		}
	}
}
</style>