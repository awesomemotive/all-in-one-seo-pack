<template>
	<grid-column class="tool-settings">
		<div
			v-for="(setting, index) in tool.settings"
			:key="index"
		>
			<core-settings-row
				noHorizontalMargin
				align-small
			>
				<template #name>
					{{ setting.label }}
				</template>

				<template #content>
					<div class="d-flex">
						<base-input
							size="small"
							@blur="maybeUpdateId(setting.option)"
							v-model="optionsStore.options.webmasterTools[setting.option]"
						/>
					</div>

					<p
						class="aioseo-description"
						v-html="setting.description"
					/>
				</template>
			</core-settings-row>
		</div>
	</grid-column>
</template>

<script setup>
import {
	useOptionsStore
} from '@/vue/stores'

import { useMetaTags } from '@/vue/composables/MetaTags'

import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import GridColumn from '@/vue/components/common/grid/Column'

const optionsStore = useOptionsStore()

const { maybeUpdateId } = useMetaTags()

defineProps({
	tool : {
		type     : Object,
		required : true
	},
	isConnected : {
		type : Boolean,
		default () {
			return false
		}
	}
})
</script>