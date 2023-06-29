<template>
	<div class="aioseo-seo-revisions-comparison__controls">
		<div class="aioseo-seo-revisions-comparison__controls__compare-mode">
			<input type="checkbox">

			{{ strings.compareTwoRevisions }}
		</div>

		<div class="aioseo-seo-revisions-comparison__controls__previous">
			<button
				class="button"
				disabled
			>
				{{ strings.previous }}
			</button>
		</div>

		<div class="aioseo-seo-revisions-comparison__controls__slider">
			<div class="marks">
				<div
					v-for="index in 15"
					:key="index"
				/>
			</div>

			<button
				class="aioseo-seo-revisions-comparison__controls__slider__handle to"
				disabled
			>
				<svg-slider-handle/>
			</button>
		</div>

		<div class="aioseo-seo-revisions-comparison__controls__next">
			<button
				class="button"
				disabled
			>
				{{ strings.next }}
			</button>
		</div>
	</div>
</template>

<script>
import SvgSliderHandle from '@/vue/components/common/svg/seo-revisions/SliderHandle'

export default {
	components : {
		SvgSliderHandle
	},
	data () {
		return {
			strings : {
				compareTwoRevisions : this.$t.__('Compare any two revisions', this.$td),
				next                : this.$t.__('Next', this.$td),
				previous            : this.$t.__('Previous', this.$td)
			}
		}
	}
}
</script>

<style lang="scss" scoped>
.aioseo-seo-revisions-comparison__controls {
	align-items: center;
	display: flex;
	flex-wrap: wrap;
	gap: 18px;
	justify-content: space-between;
	margin: 30px 0 0;
	opacity: 0.6;
	pointer-events: none;
	touch-action: none;
	user-select: none;

	&__previous,
	&__next {
		flex: 0 1 auto;
	}

	&__compare-mode {
		flex: 1 1 100%;
		text-align: right;
	}

	&__slider {
		flex: 1 1 auto;
		max-width: 828px;
		position: relative;

		.marks {
			background-color: #fff;
			border: none;
			border-bottom: 1px solid #bbbbbb;
			border-top: 1px solid #bbbbbb;
			box-shadow: none;
			cursor: pointer;
			display: flex;
			flex-flow: row-reverse;
			height: 9px;
			justify-content: space-between;
			outline: none;

			div {
				background-color: #bbbbbb;
				height: 100%;
				pointer-events: none;
				width: 1px;
			}
		}

		&__handle {
			--handle-size: 24px;

			background: none;
			border-radius: 50%;
			border: none;
			box-shadow: none;
			cursor: pointer;
			height: var(--handle-size);
			left: 100%;
			outline: transparent solid 0;
			padding: 0;
			position: absolute;
			top: 50%;
			touch-action: none;
			transform: translate(calc(var(--handle-size) / 2 * -1), -50%);
			width: var(--handle-size);
		}
	}
}
</style>