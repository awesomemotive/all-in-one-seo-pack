<template>
  <div class="aioseo-seo-site-analysis-issues-item__header">
    <span
      class="result-status"
      :class="props.result.status"
    ></span>

    <div class="aioseo-seo-site-analysis-issues-item__content">
      {{ seoAnalysis.strings[props.result.code]?.title }}
    </div>

    <div class="result-toggle">
      <svg-caret />
    </div>
  </div>
</template>

<script setup>
import SeoAnalysis from '@/vue/classes/SeoAnalysis/SeoAnalysis'
import SvgCaret from '@/vue/components/common/svg/Caret'

const props = defineProps({
	result : Object
})

const seoAnalysis = SeoAnalysis
</script>