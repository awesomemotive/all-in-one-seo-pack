<template>
	<div class="aioseo-post-breadcrumbs-view">
		<template v-if="!licenseStore.isUnlicensed">
			<template v-if="'sidebar' === $root.$data.screenContext">
				<base-button
					class="edit-snippet gray small"
					@click="showModal = true"
				>
					<svg-pencil />
					{{ strings.editBreadcrumbsLabel }}
				</base-button>
			</template>
			<template v-if="'sidebar' !== $root.$data.screenContext">
				<breadcrumbs
					:parentComponentContext="parentComponentContext"
				/>
			</template>
			<core-modal
				:show="showModal"
				@close="showModal = false"
			>
				<template #headerTitle>
					{{ strings.modalTitle }}
				</template>
				<template #body>
					<div class="bd">
						<breadcrumbs
							:parentComponentContext="parentComponentContext"
						/>
					</div>
				</template>
			</core-modal>
		</template>

		<breadcrumbs-lite
			v-if="licenseStore.isUnlicensed"
			:parentComponentContext="parentComponentContext"
		/>
	</div>
</template>

<script setup>
import {
	useLicenseStore
} from '@/vue/stores'

import { ref } from 'vue'
import { __ } from '@/vue/plugins/translations'
import CoreModal from '@/vue/components/common/core/modal/Index'
import BaseButton from '@/vue/components/common/base/Button'

import Breadcrumbs from './AIOSEO_VERSION/Breadcrumbs'
import BreadcrumbsLite from './lite/Breadcrumbs'
import SvgPencil from '@/vue/components/common/svg/Pencil'

const licenseStore = useLicenseStore()
const showModal = ref(false)

defineProps({
	parentComponentContext : String
})

const strings = {
	editBreadcrumbsLabel : __('Edit Breadcrumbs', 'all-in-one-seo-pack-pro'),
	modalTitle           : __('Breadcrumbs', 'all-in-one-seo-pack-pro')
}
</script>

<style lang="scss" scoped>
.aioseo-modal .bd {
	padding: 20px;
}
.aioseo-button svg {
	width: 12px;
	height: 12px;
	margin-right: 11px;
	color: $black2
}
</style>