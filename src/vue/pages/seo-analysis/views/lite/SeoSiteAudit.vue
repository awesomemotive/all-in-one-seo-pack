<template>
	<seo-site-audit-unlicensed>
		<template #upsell>
			<div class="aioseo-seo-analysis__global-site-audit">
				<seo-analysis-upsell :parent-component-context="'modal'"/>
			</div>
		</template>
	</seo-site-audit-unlicensed>
</template>

<script setup>
import SeoSiteAuditUnlicensed from '@/vue/pages/seo-analysis/views/partials/SeoSiteAuditUnlicensed'
import SeoAnalysisUpsell from '@/vue/components/common/seo-analysis/Upsell'
</script>
<style lang="scss" scoped>
.aioseo-seo-analysis__global-site-audit .aioseo-cta {
	top: 30%;
}
</style>