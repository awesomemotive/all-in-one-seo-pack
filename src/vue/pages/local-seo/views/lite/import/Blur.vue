<template>
	<core-blur>
		<div class="aioseo-section-description">
			{{ strings.importOthersDescription }}
		</div>

		<base-select
			size="medium"
			v-model="plugin"
			:options="plugins"
			:placeholder="strings.selectPlugin"
		>
			<template #option="{ option }">
				<div class="import-plugin-label">
					<span class="plugin-label">{{ option.label }}</span>
				</div>
			</template>
		</base-select>

		<base-button
			type="blue"
			size="medium"
			class="import"
		>
			{{ strings.import }}
		</base-button>

	</core-blur>
</template>

<script>
import CoreBlur from '@/vue/components/common/core/Blur'
import BaseSelect from '@/vue/components/common/base/Select'
import BaseButton from '@/vue/components/common/base/Button'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		BaseButton,
		BaseSelect,
		CoreBlur
	},
	data () {
		return {
			plugin  : null,
			strings : {
				importOthersDescription : sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__('Choose a plugin to import Local SEO directly into %1$s.', td),
					import.meta.env.VITE_SHORT_NAME
				),
				selectPlugin : __('Select a plugin...', td),
				import       : __('Import', td),
				notInstalled : __('not installed', td)
			}
		}
	},
	computed : {
		plugins () {
			return [ {
				value       : 'yoast-seo',
				label       : 'yoast-seo',
				canImport   : false,
				version     : '1.0.0',
				$isDisabled : false
			} ]
		}
	}
}
</script>