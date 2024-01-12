<template>
	<div class="type-2">
		<div>
			<div class="header-text">
				<slot name="header-text">
					{{ strings.ctaHeader }}
				</slot>
			</div>

			<div class="description">
				<slot name="description">
					{{ strings.ctaDescription }}
				</slot>
			</div>

			<grid-row
				v-if="featureList && featureList.length <= 5"
				class="feature-list"
			>
				<grid-column
					md="12"
					v-for="(feature, index) in featureList"
					:key="index"
				>
					<svg-circle-check />
					{{ feature }}
				</grid-column>
			</grid-row>

			<grid-row
				v-if="featureList && featureList.length > 5"
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
			>
				{{ buttonText }}
			</base-button>

			<br>
			<br>

			<a
				v-if="showLink"
				class="learn-more"
				:href="learnMoreLink"
				target="_blank"
			>
				<slot name="learn-more-text">
					{{ strings.learnMoreAllFeatures }}
				</slot>
			</a>
		</div>

		<div class="featured-image">
			<slot name="featured-image" />
		</div>
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
.aioseo-blc-app,
#aioseo-user-profile-tab {
	.aioseo-cta {
		.aioseo-cta-background {
			.type-2 {
				// margin: 30px 0 30px 50px;
				display: flex;

				.header-text,
				.description {
					text-align: left;
				}

				.description {
					margin: 16px 0 30px;
				}

				.feature-list {
					margin: 30px 0;
				}

				> div {
					margin-right: 20px;
					flex: 0 0 50%;
				}

				.featured-image {
					max-height: 540px;
					border: 1px solid $border;
					flex: 1;
					overflow: hidden;
					margin-right: -41px;
					margin-bottom: -71px;
					border-radius: 5px 0 0;

					img {
						max-height: 600px;
					}
				}

				@media only screen and (max-width: 912px) {
					flex-direction: column;
					align-items: center;

					.header-text,
					.description {
						text-align: center;
					}

					> div {
						text-align: center;
						margin-right: 0;
						margin-bottom: 30px;
						flex: 1 0 100%;
						width: 100%;
					}

					.featured-image {
						margin: 0 -10px -41px -10px;
						border-radius: 5px 5px 0 0;
						max-height: 300px;
					}
				}
			}
		}
	}
}
</style>