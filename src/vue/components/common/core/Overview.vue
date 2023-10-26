<template>
	<div
		class="aioseo-overview"
		:class="[
			isWpDashboard ? 'aioseo-overview--wp-styles' : '',
			postType?.value ? '' : 'aioseo-overview--invalid'
		]"
	>
		<template v-if="postType?.value">
			<p
				class="aioseo-overview-description"
				v-if="!toHide.includes('description')"
			>
				{{ strings.description }}
			</p>

			<div class="aioseo-overview-selector">
				<strong>{{ strings.choosePostType }}</strong>

				<base-select
					v-if="!isWpDashboard"
					size="medium"
					:placeholder="strings.choosePostType"
					:options="postTypes"
					v-model="postType"
				/>

				<select
					v-if="isWpDashboard"
					v-model="postType"
				>
					<option
						v-for="option in postTypes"
						:key="option.value"
						:value="option"
					>
						{{ option.label }}
					</option>
				</select>
			</div>

			<core-donut-chart-with-legend
				:parts="sortedParts"
				:total="totalPosts"
				:label="totalPostsLabel"
				:animatedNumber="!isWpDashboard"
			/>

			<core-alert
				v-if="!toHide.includes('upgradeAlert') && !$isPro"
				type="yellow"
				v-html="strings.upgradeToPro"
			/>
		</template>

		<template v-else>
			<div>
				<div class="aioseo-overview-title">{{ strings.invalidTitle }}</div>

				<div class="aioseo-overview-description">{{ strings.invalidDescription }}</div>

				<base-button
					v-if="!isWpDashboard"
					type="blue"
					size="medium"
					tag="a"
					:href="searchAppearanceUrl"
				>
					<svg-edit-paper /> {{ strings.invalidButton }}
				</base-button>

				<a
					v-if="isWpDashboard"
					:href="searchAppearanceUrl"
					class="button button-primary"
				>
					<svg-edit-paper /> {{ strings.invalidButton }}
				</a>
			</div>

			<svg-overview />
		</template>
	</div>
</template>

<script>
import {
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreDonutChartWithLegend from '@/vue/components/common/core/DonutChartWithLegend'
import SvgEditPaper from '@/vue/components/common/svg/EditPaper'
import SvgOverview from '@/vue/components/common/svg/Overview'
export default {
	setup () {
		return {
			rootStore     : useRootStore(),
			settingsStore : useSettingsStore()
		}
	},
	components : {
		CoreAlert,
		CoreDonutChartWithLegend,
		SvgEditPaper,
		SvgOverview
	},
	props : {
		isWpDashboard : {
			type : Boolean,
			default () {
				return false
			}
		},
		toHide : {
			type : Array,
			default () {
				return []
			}
		},
		showDescription : {
			type : Boolean,
			default () {
				return true
			}
		}
	},
	data () {
		return {
			strings : {
				description    : this.$t.__('Below are the TruSEO scores of your published posts. Take some time to improve your TruSEO score to help increase your rankings.', this.$td),
				choosePostType : this.$t.__('Choose a Post Type', this.$td),
				upgradeToPro   : this.$t.sprintf(
					// Translators: 1 - The upgrade call to action.
					this.$t.__('Get additional keyphrases and many more modules! %1$s', this.$td), this.$links.getUpsellLink('dashboard-overview', this.$t.__('Upgrade to Pro Today!', this.$td), 'liteUpgrade', true)
				),
				invalidTitle       : this.$t.__('It looks like you haven\'t selected any post types yet!', this.$td),
				invalidDescription : this.$t.__('TruSEO scoring can improve your search engine rankings. To see TruSEO scores for your published posts, enable at least one post type by turning on "Show in Search Results" in the Search Appearance settings.', this.$td),
				invalidButton      : this.$t.__('Enable Post Types', this.$td)
			},
			postTypeInitial : true,
			postType        : {},
			parts           : [
				{
					slug  : 'needsImprovement',
					name  : this.$t.__('Needs Improvement', this.$td),
					color : '#DF2A4A'
				},
				{
					slug  : 'okay',
					name  : this.$t.__('Okay', this.$td),
					color : '#F18200'
				},
				{
					slug  : 'good',
					name  : this.$t.__('Good', this.$td),
					color : '#00AA63'
				},
				{
					slug  : 'withoutFocusKeyphrase',
					name  : this.$t.__('Without a Focus Keyphrase', this.$td),
					color : '#E8E8EB'
				}
			]
		}
	},
	watch : {
		postType (newValue) {
			if (this.postTypeInitial) {
				this.postTypeInitial = false
				return
			}

			this.settingsStore.toggleRadio({ slug: 'overviewPostType', value: newValue.value })
		}
	},
	computed : {
		postTypes () {
			const postTypes = []
			this.rootStore.aioseo.postData.postTypes.forEach(postType => {
				if (this.rootStore.aioseo.seoOverview[postType.name]) {
					postTypes.push({
						value : postType.name,
						label : postType.label
					})
				}
			})
			return postTypes
		},
		totalPosts () {
			return this.rootStore.aioseo.seoOverview[this.postType.value].total
		},
		totalPostsLabel () {
			return this.$t.sprintf(
				// Translators: 1 - The post type plural name.
				this.$t.__('Total %1$s', this.$td),
				this.postType.label
			)
		},
		sortedParts () {
			const parts = this.parts

			// Set the count and the ratio.
			parts.forEach((part, index) => {
				parts[index].count = this.rootStore.aioseo.seoOverview[this.postType.value][part.slug]
				parts[index].ratio = 0 === index ? 100 : (part.count / this.totalPosts) * 100
				parts[index].link  = `${this.rootStore.aioseo.urls.editScreen}?post_status=publish&post_type=${this.postType.value}&aioseo-filter=${part.slug}`
			})

			parts.filter(part => 0 !== part.count)

			// At this point, the ratios are still incorrect because the donuts won't take the "size" of the other donuts into account.
			// This can be resolved by adding the ratios of the remaining parts to the ratio of the current part.
			parts.forEach((part, index) => {
				if (0 === index) {
					return part
				}

				parts.forEach((part2, index2) => {
					if (index < index2) {
						part.ratio = part.ratio + part2.ratio
					}
					return part
				})
				return part
			})
			return parts
		},
		searchAppearanceUrl () {
			return this.rootStore.aioseo.urls.aio.searchAppearance + '#/content-types'
		}
	},
	mounted () {
		this.$nextTick(() => {
			const selectedPostType = this.settingsStore.settings.toggledRadio?.overviewPostType
			const postTypeIndex = this.postTypes.findIndex(postType => selectedPostType === postType.value)
			this.postType = this.postTypes[postTypeIndex] || this.postTypes[0] || null
		})
	}
}
</script>

<style lang="scss">
.aioseo-overview {
	&-title {
		font-weight: 600;
		margin-bottom: 15px;
		font-size: 14px;
		line-height: 21px;
	}

	&-description {
		color: $black2;
		font-size: 14px !important;
		margin: 0 0 16px;
	}

	&-selector {
		margin: 0 0 16px;

		strong {
			font-size: 14px;
			display: inline-block;
			margin-bottom: 4px;
			font-weight: $font-bold;
		}
	}

	.aioseo-donut-chart-with-legend {
		margin: 0 0 16px;

		.chart-left {
			max-width: 145px;
		}

		.chart-right {
			margin-left: 32px;

			@media screen and (max-width: 600px) {
				margin-left: auto;
			}

			@media screen and (max-width: 911px) and (min-width: 601px) {
				margin-left: auto;
				max-width: 50%;
			}
		}
	}

	.aioseo-alert {
		margin: 0 0 24px;

		a {
			color: $black2-hover !important;
		}
	}

	.aioseo-separator {
		width: 100%;
		width: calc( 100% + 60px );
		margin: 24px 0;
		margin-left: -30px;
		border-color: $border;
	}

	.aioseo-button {
		font-size: $font-sm;
		height: 32px;

		svg {
			width: 16px;
			height: 16px;
			margin-right: 10px;
		}
	}

	.aioseo-overview {
		max-width: 300px;
		min-width: 275px;
		width: 100%;
		height: auto;
		@media screen and (max-width: 1280px) {
			min-width: 0;
		}
	}

	> :last-child {
		margin-bottom: 0;
	}

	&--invalid {
		display: flex;
		flex-direction: row;
		align-items: center;
	}

	&--wp-styles {
		.aioseo-overview-description {
			color: $black2;
		}

		.aioseo-overview-selector {
			select {
				width: 100%;
			}

			strong {
				font-weight: 400;
			}
		}

		a:not(.button) {
			color: #2271B1 !important;
		}

		.button {
			svg {
				width: 16px;
				height: 16px;
				vertical-align: sub;
				margin-right: 10px;
			}
		}

		.aioseo-alert {
			font-weight: 400 !important;
		}

		.aioseo-separator {
			width: calc( 100% + 24px );
			margin: 12px 0;
			margin-left: -12px;
			border-color: #C3C4C7;
			border-top: 0;
		}

		.aioseo-overview {
			max-width: 225px;
			min-width: 175px;
		}
	}
}
</style>