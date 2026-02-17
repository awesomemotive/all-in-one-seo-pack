<template>
	<div
		class="aioseo-seo-checklist"
		:class="isWpDashboard ? 'aioseo-seo-checklist--wp-styles' : ''"
	>
		<div class="seo-checklist-content">
			<div class="checklist-progress">
				<seo-checklist-progress-bar
					color="blue"
					:inline-text="!isWpDashboard"
				/>
			</div>

			<div class="checklist-description">
				{{ strings.description }}
			</div>

		<base-button
			v-if="!isWpDashboard"
			type="blue"
			size="medium"
			tag="a"
			:href="checklistUrl"
		>
			{{ strings.goToChecklist }}
		</base-button>

		<a
			v-if="isWpDashboard"
			:href="checklistUrl"
			class="button button-primary"
		>
			{{ strings.goToChecklist }}
		</a>
		</div>

		<svg-seo-checklist />
	</div>
</template>

<script setup>
import { useRootStore } from '@/vue/stores'

import BaseButton from '@/vue/components/common/base/Button'
import SeoChecklistProgressBar from '@/vue/components/common/core/SeoChecklistProgressBar'
import SvgSeoChecklist from '@/vue/components/common/svg/SeoChecklist'
import { __ } from '@/vue/plugins/translations'
import { computed } from 'vue'

const td = import.meta.env.VITE_TEXTDOMAIN
const rootStore = useRootStore()

defineProps({
	isWpDashboard : {
		type    : Boolean,
		default : false
	}
})

const strings = {
	seoChecklist  : __('SEO Checklist', td),
	description   : __('Complete this checklist to set up your site, identify and fix SEO issues, and discover essential AIOSEO features.', td),
	goToChecklist : __('Go to SEO Checklist', td)
}

const checklistUrl = computed(() => {
	return `${rootStore.aioseo.urls.aio.settings}#/seo-checklist`
})
</script>

<style lang="scss">
.aioseo-seo-checklist {
	display: flex;
	flex-direction: row;
	align-items: center;

	.checklist-status {
		font-size: 14px;
		line-height: 22px;
		font-weight: 700;
		color: $black;
		margin-bottom: 16px;
	}

	.checklist-progress {
		margin-bottom: 16px;
	}

	.checklist-description {
		font-size: 14px;
		line-height: 1.6;
		color: $black2;
		margin-bottom: 20px;
	}

	.aioseo-button {
		font-size: $font-sm;
		height: 32px;
	}

	.aioseo-seochecklist {
		max-width: 300px;
		min-width: 275px;
		width: 100%;
		height: auto;
		@media screen and (max-width: 1280px) {
			min-width: 0;
		}
	}

	@media screen and (max-width: 912px) {
		flex-direction: column;
		gap: 24px;
	}

	&--wp-styles {
		.checklist-status {
			margin-bottom: 12px;
		}

		.checklist-description {
			color: #3C434A;
			margin-bottom: 12px;
		}

		.aioseo-seochecklist {
			display: none;
		}

		@media screen and (max-width: 520px) {
			flex-direction: column;
		}
	}
}
</style>