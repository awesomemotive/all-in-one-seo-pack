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

<script>
import {
	useOptionsStore
} from '@/vue/stores'

import { useWebmasterTools } from '@/vue/composables'
import { WebmasterTools } from '@/vue/pages/settings/mixins'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import GridColumn from '@/vue/components/common/grid/Column'
export default {
	setup () {
		const { strings } = useWebmasterTools()

		return {
			optionsStore : useOptionsStore(),
			strings
		}
	},
	components : {
		CoreSettingsRow,
		GridColumn
	},
	mixins : [ WebmasterTools ]
}
</script>