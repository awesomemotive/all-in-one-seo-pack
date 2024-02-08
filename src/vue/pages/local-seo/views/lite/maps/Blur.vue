<template>
	<div class="aioseo-maps-blur">
		<core-blur>
			<div
				class="aioseo-settings-row"
			>
				{{ strings.description }}
			</div>

			<core-settings-row
				:name="strings.apiKey"
				align
			>
				<template #content>
					<base-input
						size="medium"
					>
					</base-input>
				</template>
			</core-settings-row>

			<core-ui-element-slider
				:options="displayInfo"
			/>

			<core-settings-row
				:name="strings.mapPreview"
				align
			>
				<template #content>
					{{ strings.description }}
				</template>
			</core-settings-row>

		</core-blur>
	</div>
</template>

<script>
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreUiElementSlider from '@/vue/components/common/core/ui-element-slider/Index'
export default {
	components : {
		CoreBlur,
		CoreSettingsRow,
		CoreUiElementSlider
	},
	data () {
		return {
			strings : {
				description : this.$t.__('Integrating with Google Maps will allow your users to find exactly where your business is located. Our interactive maps let them see your Google Reviews and get directions directly from your site. Create multiple maps for use with multiple locations.', this.$td),
				apiKey      : this.$t.__('API Key', this.$td),
				mapPreview  : this.$t.__('Map Preview', this.$td)
			},
			displayInfo : {
				block : {
					copy : '',
					desc : ''
				}
			}
		}
	}
}
</script>