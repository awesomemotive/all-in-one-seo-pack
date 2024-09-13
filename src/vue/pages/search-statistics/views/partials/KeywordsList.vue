<template>
	<ul class="keywords-list">
		<li
			v-for="(row, index) in items"
			:key="index"
		>
			<span class="marker" :style="{ backgroundColor: markerColors[index] }"></span>
			<span class="keyword">{{ row.keyword }}</span>
			<span class="remove" @click="$emit('delete', row)">
				<svg-close />
			</span>
		</li>
	</ul>
</template>

<script>
import SvgClose from '@/vue/components/common/svg/Close'
export default {
	emits      : [ 'delete' ],
	components : {
		SvgClose
	},
	props : {
		items : {
			type : Array,
			default () {
				return []
			}
		}
	},
	data () {
		return {
			markerColors : [ '#005AE0', '#00AA63', '#F18200', '#DF2A4A', '#8B5CF6' ]
		}
	}
}
</script>
<style lang="scss">
.keywords-list {
	li {
		$removeWidth: 19px;

		display: inline-flex;
		align-items: center;
		position: relative;
		background-color: $background;
		padding: 4px ( 10px + $removeWidth ) 4px 10px;
		margin-right: 16px;
		border-radius: 3px;
		overflow: hidden;

		.marker {
			width: 12px;
			height: 12px;
			margin-right: 8px;
			border-radius: 50%;
			background-color: $blue;
		}

		.keyword {
			font-weight: 700;
			font-size: 14px;
		}

		.remove {
			position: absolute;
			top: 0;
			right: 0;
			width: $removeWidth;
			height: 100%;
			background-color: $border;
			cursor: pointer;
			display: flex;
			align-items: center;
			justify-content: center;

			svg {
				width: 7px;
				height: 7px;
			}
		}
	}
}
</style>