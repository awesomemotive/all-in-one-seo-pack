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
export default {
	components : {
		BaseButton,
		BaseSelect,
		CoreBlur
	},
	data () {
		return {
			strings : {
				importOthersDescription : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					this.$t.__('Choose a plugin to import Local SEO directly into %1$s.', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				selectPlugin : this.$t.__('Select a plugin...', this.$td),
				import       : this.$t.__('Import', this.$td),
				allLocalSeo  : this.$t.__('All Local Seo', this.$td),
				notInstalled : this.$t.__('not installed', this.$td)
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