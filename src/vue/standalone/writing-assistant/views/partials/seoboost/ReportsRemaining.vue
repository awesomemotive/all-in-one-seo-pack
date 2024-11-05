<template>
	<div
		class="aioseo-writing-assistant__reports-remaining"
		v-if="writingAssistantStore.userInfo?.limit"
	>
		<span>
			<span class="aioseo-writing-assistant__reports-remaining-red">*</span>{{ strings.reportsRemaining }}: {{ writingAssistantStore.userInfo?.left }}/{{ writingAssistantStore.userInfo?.limit }}
		</span>
		<span
			class="aioseo-writing-assistant__reports-remaining-upgrade"
			v-if="writingAssistantStore.userInfo?.subscription_info?.canUserUpgrade && 10 >= writingAssistantStore.userInfo?.left"
		>
			<span class="aioseo-writing-assistant__reports-remaining-upgrade-divider">|</span>
			<a
				href="https://app.seoboost.com/subscription/"
				target="_blank"
			>
				{{ strings.upgrade }}
			</a>
		</span>
	</div>
</template>

<script setup>
import { useWritingAssistantStore } from '@/vue/stores'
import { onBeforeMount } from 'vue'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN
const writingAssistantStore = useWritingAssistantStore()
const strings = {
	reportsRemaining : __('Reports', td),
	upgrade          : __('Get More Reports', td)
}

onBeforeMount(() => {
	writingAssistantStore.updateUserInfo()
})
</script>

<style lang="scss">
.aioseo-writing-assistant {
	&__reports-remaining {
		font-size: 14px;
		font-weight: 700;
		color: $black2;
		display: flex;
		justify-content: center;

		.aioseo-writing-assistant-sidebar & {
			font-size: 13px;
		}

		&-red {
			color: $red;
			font-weight: 400;
		}

		&-upgrade {
			display: flex;
			align-items: center;

			&-divider {
				margin: 0 5px;
			}
		}
	}
}
</style>