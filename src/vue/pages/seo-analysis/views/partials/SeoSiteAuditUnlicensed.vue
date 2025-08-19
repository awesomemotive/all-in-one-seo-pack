<template>
	<div class="aioseo-seo-audit-checklist">
		<core-blur>
			<core-card
				slug="connectOrScore"
				no-slide
				:toggles="false"
			>
				<template #header>
					<span>{{ strings.siteOverview }}</span>
				</template>

				<core-donut-chart-with-legend
					:parts="sortedParts"
					:total="100"
					:label="strings.totalPostsLabel"
					:animatedNumber="false"
				/>
			</core-card>
		</core-blur>

    <core-blur v-if="!rootStore.isPro">
      <core-card
        slug="completeSeoChecklist"
        no-slide
        :toggles="false"
      >
        <template #header>
          <span>{{ strings.completeSeoChecklist }}</span>

          <core-tooltip>
            <svg-circle-question-mark />

            <template #tooltip>
              <span v-html="strings.cardDescription"/>
            </template>
          </core-tooltip>
        </template>

        <site-audit-global-tab />
      </core-card>
    </core-blur>

		<slot name="upsell"></slot>
	</div>
</template>

<script setup>
import { computed } from 'vue'

import { useRootStore } from '@/vue/stores'

import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'

import CoreBlur from '@/vue/components/common/core/Blur'
import CoreCard from '@/vue/components/common/core/Card'
import CoreDonutChartWithLegend from '@/vue/components/common/core/DonutChartWithLegend'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SiteAuditGlobalTab from '@/vue/pages/seo-analysis/partials/AIOSEO_VERSION/tabs/SiteAuditGlobalTab'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'

import { getSortedParts } from '@/vue/pages/seo-analysis/utils'

import { __, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const rootStore = useRootStore()

const strings = {
	completeSeoChecklist : __('Site Audit Report', td),
	cardDescription      : __('These are the results our SEO Analzyer has generated after analyzing the pages of your website.', td) +
			' ' + links.getDocLink(GLOBAL_STRINGS.learnMore, 'seoAnalyzer', true),
	siteOverview      : __('Site Overview', td),
	totalPostsLabel   : __('Total Checks', td),
	connectWithAioseo : sprintf(
		// Translators: 1 - The plugin short name ("AIOSEO").
		__('Connect with %1$s', td),
		import.meta.env.VITE_SHORT_NAME
	)
}

const sortedParts = computed(() => {
	return getSortedParts({
		good     : 50,
		warnings : 30,
		issues   : 20,
		total    : 100
	})
})
</script>

<style lang="scss" scoped>
.aioseo-seo-audit-checklist {
	position: relative;

  .aioseo-blur {
		display: flex;
		align-items: center;
		justify-content: center;

		.aioseo-donut-chart-with-legend {
			max-width: 400px;
			margin: 0 auto;
		}
	}

	.round {
		position: relative;
		border-radius: 50%;
		width: 24px;
		min-width: 24px;
		max-width: 24px;
		height: 24px;
		line-height: 24px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		margin-right: 10px;
		font-size: 12px;
		color: #fff;
		font-weight: 600;

		&.red {
			background-color: $red;
		}

		&.blue {
			background-color: $blue;
		}

		&.dark-blue {
			background-color: $blue3;
		}

		&.orange {
			background-color: $orange;
		}

		&.green {
			background-color: $green;
		}

		&.black {
			background-color: $black2;
		}

		&.gray {
			background-color: $placeholder-color;
		}
	}

	.aioseo-card {
    width: 100%;

		.header-title {
			display: inline-flex;
			flex: 1;
		}
	}

	.aioseo-donut-chart-with-legend {
		justify-content: center;

		.chart-right {
			flex: unset;
		}
	}
}
</style>