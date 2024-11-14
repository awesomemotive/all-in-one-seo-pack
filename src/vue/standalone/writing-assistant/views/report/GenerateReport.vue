<template>
	<div class="aioseo-writing-assistant__generate-report">
		<base-button
			type="blue"
			@click="showModal = true"
			:size="props.buttonSize"
			:disabled="0 >= writingAssistantStore.userInfo?.left"
			:title="0 >= writingAssistantStore.userInfo?.left ? strings.noReportsLeft : strings.generateReport"
		>
			{{ strings.generateReport }}
		</base-button>
		<div v-if="props.showReportsLeft">
			<reports-remaining />
		</div>
		<core-modal
			:show="showModal && !writingAssistantStore.polling"
			:classes="['aioseo-writing-assistant__generate-report-modal']"
			@close="showModal = false"
			:allow-overflow="true"
			:allow-bg-close="false"
		>
			<template #headerTitle>
				{{ strings.generateReport }}
			</template>
			<template #body>
				<new-keyword/>
			</template>
		</core-modal>
	</div>
</template>

<script setup>
import { ref } from 'vue'
import BaseButton from '@/vue/components/common/base/Button'
import { useWritingAssistantStore } from '@/vue/stores'
import { __ } from '@/vue/plugins/translations'
import CoreModal from '@/vue/components/common/core/modal/Index'
import NewKeyword from '@/vue/standalone/writing-assistant/views/partials/keyword/New'
import ReportsRemaining from '@/vue/standalone/writing-assistant/views/partials/seoboost/ReportsRemaining'

const td = import.meta.env.VITE_TEXTDOMAIN
const writingAssistantStore = useWritingAssistantStore()
const showModal = ref(false)
const strings = {
	generateReport : __('Generate a New Report', td),
	noReportsLeft  : __('You have no reports left.', td)
}

const props = defineProps({
	buttonSize      : String,
	showReportsLeft : {
		type    : Boolean,
		default : false
	}
})
</script>

<style lang="scss">
.aioseo-writing-assistant {
	&__generate-report {
		display: flex;
		flex-direction: column;
		gap: 12px;

		.aioseo-button {
			width: 100%;
		}

		.aioseo-writing-assistant-sidebar & {
			width: 100%;
		}
	}

	&__generate-report-modal {
		.modal-container {
			max-width: 490px !important;
		}

		.modal-body {
			padding: 20px !important;
		}
	}
}
</style>