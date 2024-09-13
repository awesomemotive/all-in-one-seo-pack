<template>
	<div class="aioseo-locations-blur">
		<core-blur>
			<div
				class="aioseo-settings-row"
			>
				{{ strings.description }}
			</div>
			<core-settings-row
				:name="strings.name"
				class="info-name-row"
				align
			>
				<template #content>
					<div class="aioseo-col col-xs-12 text-xs-left">
						<base-input
							type="text"
							size="medium"
						/>
						<span class="field-description">{{ strings.nameDesc }}</span>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				class="info-business-image"
				:name="strings.image"
			>
				<template #content>
					<div class="image-upload">
						<base-input
							size="medium"
							:placeholder="strings.pasteYourImageUrl"
						/>
						<base-button
							class="insert-image"
							size="medium"
							type="black"
						>
							<svg-circle-plus />
							{{ strings.uploadOrSelectImage }}
						</base-button>

						<base-button
							class="remove-image"
							size="medium"
							type="gray"
						>
							{{ strings.remove }}
						</base-button>
					</div>

					<div class="aioseo-description">
						{{ strings.minimumSize }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.businessType"
				class="info-business-type"
				align
			>
				<template #content>
					<base-select
						size="large"
						:options="businessTypes"
						modelValue="default"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.urls"
				class="info-urls-row"
				align
			>
				<template #content>
					<div class="aioseo-col col-xs-12 text-xs-left">
						<div class="aioseo-col col-xs-12 text-xs-left">
							<span class="field-description">{{ strings.websiteDesc }}</span>
							<base-input
								type="text"
								size="medium"
							/>
						</div>
						<div class="aioseo-col col-xs-12 text-xs-left">
							<span class="field-description mt-8">{{ strings.aboutDesc }}</span>
							<base-input
								type="text"
								size="medium"
							/>
						</div>
						<div class="aioseo-col col-xs-12 text-xs-left">
							<span class="field-description mt-8">{{ strings.contactDesc }}</span>
							<base-input
								type="text"
								size="medium"
							/>
						</div>
					</div>
				</template>
			</core-settings-row>
		</core-blur>
	</div>
</template>

<script>
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreBlur,
		CoreSettingsRow,
		SvgCirclePlus
	},
	data () {
		return {
			strings : {
				description : sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__('Whether your business has multiple locations, or just one, %1$s makes it easy to configure and display relevant information about your local business. You can use the custom-built tools below, or you can use the Locations custom post type (multiple locations only) to generate relevant and necessary information for search engines or for your customers.', td),
					import.meta.env.VITE_SHORT_NAME
				),
				name                : __('name', td),
				nameDesc            : __('Your name or company name.', td),
				businessType        : __('Type', td),
				urls                : __('URLs', td),
				image               : __('Image', td),
				uploadOrSelectImage : __('Upload or Select Image', td),
				pasteYourImageUrl   : __('Paste your image URL or select a new image', td),
				minimumSize         : __('Minimum size: 112px x 112px, The image must be in JPG, PNG, GIF, SVG, or WEBP format.', td),
				remove              : __('Remove', td),
				websiteDesc         : __('Website URL:', td),
				aboutDesc           : __('About Page URL:', td),
				contactDesc         : __('Contact Page URL:', td)
			},
			businessTypes : [
				{ label: __('default', td), value: 'LocalBusiness' },
				{ label: __('Animal Shelter', td), value: 'Animal Shelter' }
			]
		}
	}
}
</script>

<style lang="scss">
.info-urls-row {
	.field-description {
		display: block;
		padding-bottom: 6px;
		font-size: 12px;
	}
}
</style>