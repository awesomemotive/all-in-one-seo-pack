<template>
	<core-blur>
		<div class="aioseo-settings-row aioseo-section-description">
			{{ strings.description }} {{ strings.extendedDescription }}
			<span
				v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'newsSitemaps', true)"
			/>
		</div>

		<core-settings-row
			:name="strings.enableSitemap"
		>
			<template #content>
				<base-toggle
					:modelValue="true"
				/>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="$constants.GLOBAL_STRINGS.preview"
		>
			<template #content>
				<div class="aioseo-sitemap-preview">
					<base-button
						size="medium"
						type="blue"
					>
						<svg-external />
						{{ strings.openSitemap }}
					</base-button>
				</div>

				<div class="aioseo-description">
					{{ strings.noIndexDisplayed }}
					<br>
					{{ strings.doYou404 }}

					<span
						v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'blankSitemap', true)"
					/>
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.publicationName"
			align
		>
			<template #content>
				<base-input
					size="medium"
				/>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.postTypes"
		>
			<template #content>
				<base-checkbox
					size="medium"
				>
					{{ strings.includeAllPostTypes }}
				</base-checkbox>

				<div class="aioseo-description">
					{{ strings.selectPostTypes }}

					<span
						v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'selectPostTypesNews', true)"
					/>
				</div>
			</template>
		</core-settings-row>
	</core-blur>
</template>

<script>
import { useNewsSitemap } from '@/vue/pages/sitemaps/composables'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import SvgExternal from '@/vue/components/common/svg/External'
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
export default {
	setup () {
		const { strings } = useNewsSitemap()

		return {
			strings
		}
	},
	components : {
		CoreBlur,
		CoreSettingsRow,
		SvgExternal,
		BaseCheckbox
	}
}
</script>