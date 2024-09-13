<template>
	<core-card
		class="aioseo-monsterinsights-intro"
		slug="monsterinsights-intro"
		:hideHeader="true"
		:noSlide="true"
		:cardClass="{
			'aioseo-card--intro': true
		}"
	>
		<div class="aioseo-analytics__intro">
			<template v-if="prefersEm">
				<div class="intro-image">
					<svg-aioseo-logo-gear />
					<span>♥</span>
					<img
						:src="getAssetUrl(emLogoImg)"
						height="90"
						alt="ExactMetrics"
					/>
				</div>
				<h2 class="intro-heading">
					{{ strings.emIntroH }}
				</h2>
				<p>
					{{ strings.emIntroP }}
				</p>
				<div class="preview-list">
					<img
						:src="getAssetUrl(emGraphImg)"
						height="200"
						alt="mi-graph-preview"
					/>
					<ul>
						<li>
							<svg-circle-check />
							{{ strings.emIntroLi1 }}
						</li>
						<li>
							<svg-circle-check />
							{{ strings.emIntroLi2 }}
						</li>
						<li>
							<svg-circle-check />
							{{ strings.emIntroLi3 }}
						</li>
					</ul>
				</div>
			</template>
			<template v-else>
				<div class="intro-image">
					<svg-aioseo-logo-gear />
					<span>♥</span>
					<img
						:src="getAssetUrl(miLogoImg)"
						height="90"
						alt="MonsterInsights"
					/>
				</div>
				<h2 class="intro-heading">
					{{ strings.miIntroH }}
				</h2>
				<p>
					{{ strings.miIntroP }}
				</p>
				<div class="preview-list">
					<img
						:src="getAssetUrl(miGraphImg)"
						height="200"
						alt="mi-graph-preview"
					/>
					<ul>
						<li>
							<svg-circle-check />
							{{ strings.miIntroLi1 }}
						</li>
						<li>
							<svg-circle-check />
							{{ strings.miIntroLi2 }}
						</li>
						<li>
							<svg-circle-check />
							{{ strings.miIntroLi3 }}
						</li>
						<li>
							<svg-circle-check />
							{{ strings.miIntroLi4 }}
						</li>
					</ul>
				</div>
			</template>

			<base-button
				v-if="showButton"
				type="blue"
				size="medium"
				tag="a"
				:href="rootStore.aioseo.urls.aio.monsterinsights"
			>
				{{ strings.getStartedGaFree }}
			</base-button>
		</div>
	</core-card>
</template>

<script setup>
import {
	useRootStore
} from '@/vue/stores'

import { getAssetUrl } from '@/vue/utils/helpers'
import { __, sprintf } from '@/vue/plugins/translations'

import CoreCard from '@/vue/components/common/core/Card'
import SvgAioseoLogoGear from '@/vue/components/common/svg/aioseo/LogoGear'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'

import emLogoImg from '@/vue/assets/images/about/plugins/em.png'
import emGraphImg from '@/vue/assets/images/plugins/em-graph-preview.png'
import miLogoImg from '@/vue/assets/images/plugins/mi-logo.png'
import miGraphImg from '@/vue/assets/images/plugins/mi-graph-preview.png'

const rootStore = useRootStore()

const td      = import.meta.env.VITE_TEXTDOMAIN
const strings = {
	emIntroH : __('The Best Google Analytics Plugin for WordPress', td),
	emIntroP : sprintf(
		// Translators: 1 - The name of one of our partner plugins, 2 - The plugin short name ("AIOSEO").
		__('%1$s connects %2$s to Google Analytics, providing a powerful integration. %1$s is a partner of %2$s.', td),
		'ExactMetrics',
		import.meta.env.VITE_SHORT_NAME
	),
	emIntroLi1 : __('Quick & Easy Google Analytics Setup', td),
	emIntroLi2 : __('Google Analytics Dashboard + Real Time Stats', td),
	emIntroLi3 : __('Google Analytics Enhanced Ecommerce Tracking', td),
	miIntroH   : __('The Best Google Analytics Plugin for WordPress', td),
	miIntroP   : sprintf(
		// Translators: 1 - The name of one of our partner plugins, 2 - The plugin short name ("AIOSEO").
		__('%1$s connects %2$s to Google Analytics, providing a powerful integration. %1$s is a partner of %2$s.', td),
		'MonsterInsights',
		import.meta.env.VITE_SHORT_NAME
	),
	miIntroLi1       : __('Quick & Easy Google Analytics Setup', td),
	miIntroLi2       : __('Google Analytics Dashboard + Real Time Stats', td),
	miIntroLi3       : __('Google Analytics Enhanced Ecommerce Tracking', td),
	miIntroLi4       : __('Universal Tracking for AMP and Instant Articles', td),
	getStartedGaFree : __('Get Started with Google Analytics for Free', td)
}

defineProps({
	prefersEm  : Boolean,
	showButton : Boolean
})
</script>

<style lang="scss">
.aioseo-monsterinsights-intro {
	&.aioseo-card.aioseo-card--intro {
		background-color: $box-background2;
		box-shadow: none;
	}

	.aioseo-analytics__intro {
		text-align: center;

		.intro-image {
			max-width: 300px;
			margin: 0 auto;
			display: flex;
			justify-content: space-between;
			font-size: 45px;
			align-items: center;

			.aioseo-logo-gear {
				width: 100%;
				max-width: 90px;
				color: $blue;
			}

			span {
				color: $red;
			}
		}

		.intro-heading {
			margin: 24px auto;
			font-size: 36px;
			line-height: 1.25;
		}

		p {
			margin: 0 auto;
		}

		.aioseo-button {
			margin-top: 30px;
		}
	}

	.preview-list {
		display: flex;
		text-align: left;
		align-items: center;
		max-width: 800px;
		margin: 24px auto 0;

		ul {
			margin-left: 24px;
		}

		li {
			display: flex;
			align-items: flex-start;
			font-size: 16px;
		}
	}

	.aioseo-circle-check {
		color: $green;
		max-width: 18px;
		margin-right: 18px;
		margin-top: 3px;
	}
}
</style>