<template>
  <div
    v-if="['draft', 'private', 'pending', 'future'].includes(props.status)"
    class="aioseo-seo-analysis-post-status-badge"
  >
    <core-tooltip>
      <span
        class="aioseo-seo-analysis-post-status-badge__status"
        :class="props.status"
      >
        {{ postStatus }}
        <svg-info />
      </span>

      <template #tooltip>
        <span>{{ strings.tooltip }}</span>
      </template>
    </core-tooltip>
  </div>
</template>

<script setup>
import { computed } from 'vue'

import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgInfo from '@/vue/components/common/svg/Info'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	status : {
		type     : String,
		required : true
	}
})

const strings = {
	tooltip : __('The results for this post may be inaccurate or vary slightly until the post is published.', td)
}

const postStatus = computed(() => {
	if ('future' === props.status) {
		return __('Scheduled', td)
	}

	return props.status.toUpperCase()
})
</script>

<style lang="scss" scoped>
.aioseo-seo-analysis-post-status-badge {
  display: block;

  .aioseo-tooltip {
    display: block;
  }

  &__status {
    display: flex;
    align-items: center;
    cursor: pointer;
    border-radius: 3px;
    background: $border;
    color: $black2;
    font-size: 12px;
    line-height: 18px;
    font-weight: 600;
    padding: 4px;
    gap: 4px;

    &.future {
      background: $orange;
      color: $white;
    }

    svg {
      width: 16px;
      height: 16px;
    }
  }

}
</style>