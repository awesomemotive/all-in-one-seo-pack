<template>
	<ul class="aioseo-search-statistics-seo-meta">
		<li v-if="meta.title">
			<div class="meta-title">{{strings.title}}:</div>
			<div class="meta-content meta-container">{{meta.title}}</div>
		</li>
		<li v-if="meta.description">
			<div class="meta-title">{{strings.description}}:</div>
			<div class="meta-content meta-container">{{meta.description}}</div>
		</li>
		<li v-if="meta.schema">
			<div class="meta-title">{{strings.schema}}:</div>
			<div class="meta-content meta-container">{{meta.schema}}</div>
		</li>
		<li v-if="meta.canonicalUrl">
			<div class="meta-title">{{strings.canonicalUrl}}:</div>
			<div class="meta-content meta-container"><a :href="meta.canonicalUrl" target="_blank">{{meta.canonicalUrl}}</a></div>
		</li>
		<li v-if="meta.robots">
			<div class="meta-title">{{strings.robots}}:</div>
			<div class="meta-content meta-container">{{meta.robots}}</div>
			<div
				v-if="showTooltip('robots')"
				class="meta-tooltip"
			>
				<core-tooltip>
					<svg-circle-exclamation />

					<template #tooltip>
						{{ strings.robotsNoIndex }}
					</template>
				</core-tooltip>
			</div>
		</li>
		<li v-if="meta.additionalKeywords">
			<div class="meta-title">{{ strings.additionalKeywords }}:</div>
			<div
				class="meta-content"
				:class="{
					'meta-container': meta.additionalKeywords.length
				}"
			>
				<div v-if="meta.additionalKeywords.length">
					<span
						v-for="(keyword, index) in meta.additionalKeywords"
						:key="index"
						class="keyword-tag"
					>{{ keyword }}</span>
				</div>
				<div v-else>
					<base-button
						type="blue"
						tag="a"
						:href="editUrl"
						size="small"
					>
						{{ strings.addAdditionalKeyword }}
					</base-button>
				</div>
			</div>
		</li>
	</ul>
</template>

<script>
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreTooltip,
		SvgCircleExclamation
	},
	props : {
		meta : {
			type : Object,
			default () {
				return {}
			}
		},
		editUrl : String
	},
	data () {
		return {
			strings : {
				title                : __('Title', td),
				description          : __('Description', td),
				schema               : __('Schema', td),
				canonicalUrl         : __('Canonical URL', td),
				robots               : __('Robots Meta', td),
				robotsNoIndex        : __('This post is currently set to noindex which will prevent search engines from including it in search results.', td),
				additionalKeywords   : __('Additional Keywords', td),
				addAdditionalKeyword : __('Add Additional Keyword', td)
			}
		}
	},
	methods : {
		showTooltip (section) {
			if ('robots' === section && this.meta.robots.includes('noindex')) {
				return true
			}

			return false
		}
	}
}
</script>

<style lang="scss">
@use 'sass:color';

.aioseo-search-statistics-seo-meta {
	list-style: none;
	margin: 0;
	padding: 0;

	li {
		margin-bottom: 16px;
		display: flex;
		font-size: 14px;
		line-height: 125%;
		color: $black;

		.meta-title {
			padding-top: 5px;
			flex-basis: 25%;
			max-width: 25%;
			font-weight: 700;
			margin-right: 24px;
		}

		.meta-content {
			flex: 1;

			&.meta-container {
				padding: 5px;
				font-size: 13px;
				border-radius: 3px;
			}
		}

		.meta-tooltip {
			display: flex;
			align-content: center;

			svg.aioseo-circle-exclamation {
				width: 17px;
				height: 17px;
				color: $red;
				transition: background-color 0.2s ease;

				&:hover {
					color: color.adjust($red, $lightness: -20%);
				}
			}
		}

		.keyword-tag {
			margin-right: 12px;
			margin-bottom: 12px;
			display: inline-block;
			color: $black2;
			font-size: 14px;
			font-weight: 700;
			padding: 3px 10px;
			background-color: $background;
			border-radius: 3px;
		}
	}
}
</style>