<template>
	<div class="type-3">
		<div class="sub-header">
			{{ strings.upgradeToPro }}
		</div>
		<div class="header-text">
			<slot name="header-text">
				{{ strings.ctaHeader }}
			</slot>
		</div>

		<grid-row
			v-if="featureList"
			class="feature-list"
		>
			<grid-column
				md="6"
				v-for="(feature, index) in featureList"
				:key="index"
			>
				<svg-circle-check />
				{{ feature }}
			</grid-column>
		</grid-row>

		<core-alert
			v-if="!ctaButtonVisible && ctaButtonVisibleWarning"
			type="yellow"
			v-html="ctaButtonVisibleWarning"
		/>

		<base-button
			v-if="ctaButtonVisible"
			type="green"
			tag="a"
			:href="ctaLink"
			:target="target"
			@click.native="ctaButtonClick"
			:loading="ctaButtonLoading"
			size="medium"
		>
			{{ buttonText }}
		</base-button>

		<base-button
			v-if="showLink"
			type="gray"
			tag="a"
			:href="learnMoreLink"
			target="_blank"
			size="medium"
		>
			<slot name="learn-more-text">
				{{ strings.seeAllFeatures }}
			</slot>
		</base-button>
	</div>
</template>

<script setup>
import CoreAlert from '@/vue/components/common/core/alert/Index'
import BaseButton from '@/vue/components/common/base/Button'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'

defineProps({
	strings                 : Object,
	featureList             : Array,
	ctaButtonVisible        : Boolean,
	ctaButtonLoading        : Boolean,
	ctaButtonVisibleWarning : String,
	ctaLink                 : String,
	target                  : String,
	buttonText              : String,
	ctaButtonClick          : Function,
	showLink                : Boolean,
	learnMoreLink           : String
})
</script>

<style lang="scss">
.aioseo-app,
.aioseo-blc-app {
	.aioseo-cta {
		.aioseo-cta-background {
			.type-3 {
				display: flex;
				flex-wrap: wrap;
				justify-content: center;
				column-gap: 20px;
				row-gap: 8px;

				> * {
					width: 100%;
				}

				.sub-header {
					line-height: 1.4;
					font-size: 12px;
					font-weight: $font-bold;
					color: $blue;
					text-align: center;
				}

				.header-text {
					text-align: center;
				}

				.feature-list {
					margin: 22px 0;

					.aioseo-col {

						svg.aioseo-circle-check {
							color: $green;
							width: 21px;
							min-width: 21px;
							min-height: 21px;
							margin-right: 10px;
						}
					}
				}

				.aioseo-button {
					width: auto;
				}
			}
		}
	}
}
</style>