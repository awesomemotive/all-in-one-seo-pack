<template>
	<div
		class="aioseo-cta"
		:class="{
			floating: floating,
			'align-top' : alignTop
		}"
	>
		<div class="aioseo-cta-background">
			<component
				:is="getCtaComponent"
				:strings="strings"
				:feature-list="featureList"
				:cta-button-visible="ctaButtonVisible"
				:cta-button-loading="ctaButtonLoading"
				:cta-button-visible-warning="ctaButtonVisibleWarning"
				:cta-link="ctaLink"
				:target="target"
				:button-text="buttonText"
				:cta-button-click="ctaButtonClick"
				:show-link="showLink"
				:learn-more-link="learnMoreLink"
				:hide-bonus="hideBonus"
			>
				<template #header-text>
					<slot name="header-text" />
				</template>

				<template #description>
					<slot name="description" />
				</template>

				<template #learn-more-text>
					<slot name="learn-more-text" />
				</template>

				<template #featured-image>
					<slot name="featured-image" />
				</template>
			</component>
		</div>
	</div>
</template>

<script>
import Type1 from '@/vue/components/common/cta/Type/1'
import Type2 from '@/vue/components/common/cta/Type/2'
import Type3 from '@/vue/components/common/cta/Type/3'
import Type4 from '@/vue/components/common/cta/Type/4'
import Type5 from '@/vue/components/common/cta/Type/5'
export default {
	emits      : [ 'cta-button-click' ],
	components : {
		Type1,
		Type2,
		Type3,
		Type4,
		Type5
	},
	props : {
		type : {
			type : Number,
			default () {
				return 1
			},
			validator (value) {
				return [ 1, 2, 3, 4, 5 ].includes(value)
			}
		},
		featureList      : Array,
		sameTab          : Boolean,
		ctaButtonAction  : Boolean,
		ctaButtonLoading : Boolean,
		ctaLink          : {
			type     : String,
			required : false
		},
		learnMoreLink : {
			type     : String,
			required : false
		},
		buttonText : {
			type     : String,
			required : false
		},
		floating : {
			type : Boolean,
			default () {
				return true
			}
		},
		showLink : {
			type : Boolean,
			default () {
				return true
			}
		},
		ctaButtonVisible : {
			type : Boolean,
			default () {
				return true
			}
		},
		ctaButtonVisibleWarning : String,
		alignTop                : {
			type : Boolean,
			default () {
				return false
			}
		},
		hideBonus : Boolean
	},
	data () {
		return {
			target  : '_blank',
			strings : {
				upgradeToPro : this.$t.sprintf(
					// Translators: 1 - "Pro".
					this.$t.__('Upgrade to %1$s', this.$td),
					'Pro'
				),
				ctaHeader : this.$t.sprintf(
					// Translators: 1 - "PRO".
					this.$t.__('This is a %1$s Feature', this.$td),
					'PRO'
				),
				ctaDescription : this.$t.sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO"), 2 - "Pro".
					this.$t.__('%1$s %2$s comes with many additional features to help take your site\'s SEO to the next level!', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					'Pro'
				),
				learnMoreAllFeatures : this.$t.__('Learn more about all features', this.$td),
				seeAllFeatures       : this.$t.__('See all features', this.$td)
			}
		}
	},
	computed : {
		getCtaComponent () {
			return `Type${this.type}`
		}
	},
	methods : {
		ctaButtonClick (event) {
			if (this.ctaButtonAction) {
				event.preventDefault()
				this.$emit('cta-button-click')
			}
		}
	},
	mounted () {
		if (this.sameTab) {
			this.target = '_self'
		}
	}
}
</script>

<style lang="scss">
.aioseo-app,
.aioseo-blc-app {
	.aioseo-cta {
		margin-top: 30px;
		width: 100%;

		&.floating {
			margin-top: 0;
			position: absolute;
			max-width: 850px;
			left: 50%;
			top: 50%;
			transform: translateX(-50%) translateY(-50%);
			padding: 20px;

			.aioseo-cta-background {
				box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.1);
				border-radius: 3px;
			}

			&.align-top {
				top: 50px;
				transform: translateX(-50%) translateY(0);
			}
		}

		.aioseo-cta-background {
			background: #fff;
			padding: 40px;
			box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);
			border: 1px solid $border;

			.header-text {
				line-height: 1.25;
				font-weight: 600;
				font-size: 18px;
				text-align: center;
				color: $black;

				span.large {
					line-height: 1.4;
					font-size: 22px;
				}
			}

			.description {
				margin-block: 16px 30px;
				width: 100%;
				max-width: 600px;
				text-align: center;
				font-size: 16px;
				color: $black;
				line-height: 1.25;

				.aioseo-alert {
					margin-bottom: 16px;
					text-align: left;
				}
			}

			.feature-list {
				--aioseo-gutter: 32px;

				color: $black;
				font-size: 14px;
				line-height: 1.25;
				width: 100%;
				max-width: 500px;
				margin-bottom: 30px;
				row-gap: 16px;

				.aioseo-col {
					display: flex;
					align-items: center;

					svg.aioseo-circle-check {
						color: $green;
						width: 20px;
						height: 20px;
						min-width: 20px;
						margin-right: 12px;
					}
				}
			}

			a.learn-more {
				margin-top: 20px;
				color: $placeholder-color;
				font-size: 14px;
			}
		}
	}
}
</style>