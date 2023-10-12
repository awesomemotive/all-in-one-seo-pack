<template>
	<core-blur>
		<div class="aioseo-settings-row aioseo-section-description">
			{{ strings.extendedDescription }}
			<span
				v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'videoSitemaps', true)"
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
			:name="strings.enableSitemapIndexes"
		>
			<template #content>
				<base-radio-toggle
					name="sitemapIndexes"
					:options="[
							{ label: $constants.GLOBAL_STRINGS.disabled, value: false, activeClass: 'dark' },
							{ label: $constants.GLOBAL_STRINGS.enabled, value: true }
						]"
				/>

				<div class="aioseo-description">
					{{ strings.sitemapIndexes }}

					<span
						v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'sitemapIndexes', true)"
					/>
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.linksPerSitemap"
		>
			<template #content>
				<base-input
					class="aioseo-links-per-site"
					type="number"
					size="medium"
					:min="1"
					:max="50000"
				/>

				<div class="aioseo-description">
					{{ strings.maxLinks }}

					<span
						v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'maxLinks', true)"
					/>
				</div>
			</template>
		</core-settings-row>

	</core-blur>
</template>

<script>
import { useVideoSitemap } from '@/vue/pages/sitemaps/composables'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import SvgExternal from '@/vue/components/common/svg/External'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
export default {
	setup () {
		const { strings } = useVideoSitemap()

		return {
			strings
		}
	},
	components : {
		CoreBlur,
		CoreSettingsRow,
		SvgExternal,
		BaseRadioToggle
	}
}
</script>