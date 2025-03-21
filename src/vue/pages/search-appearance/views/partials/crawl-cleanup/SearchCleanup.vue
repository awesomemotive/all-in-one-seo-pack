<template>
	<div class="content">

		<core-settings-row
			id="search-cleanup-description"
			:name="strings.searchFilterTopDescriptionTitle"
			leftSize="12"
			align
		>
			<template #description>
				{{ strings.searchFilterTopDescriptionText }}
			</template>
		</core-settings-row>

		<core-settings-row
			id="search-cleanup-toggle"
			:name="strings.searchFilterToggle"
			align
		>
			<template #content>
				<base-radio-toggle
					v-model="optionsStore.options.searchAppearance.advanced.searchCleanup.enable"
					name=""
					:options="[
						{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.on, value: true }
					]"
				/>

				<div class="aioseo-description">
					{{ strings.searchFilterToggleDescription }}
				</div>
			</template>
		</core-settings-row>

		<template
			v-if="optionsStore.options.searchAppearance.advanced.searchCleanup.enable"
		>
			<core-settings-row
				id="search-cleanup-max-allowed-number-of-chars"
				:name="strings.maxAllowedNumberOfChars"
				align
			>
				<template #content>
					<base-input
						size="medium"
						v-model="optionsStore.options.searchAppearance.advanced.searchCleanup.settings.maxAllowedNumberOfChars"
						name="maxAllowedNumberOfChars"
						:type="'number'"
						:min="minCharacters"
					/>

					<div class="aioseo-description">
						{{ strings.maxAllowedNumberOfCharsDescription }}
						{{ strings.maxAllowedNumberOfCharsDescription2 }}
					</div>

				</template>
			</core-settings-row>

			<core-settings-row
				id="search-cleanup-emojis-and-symbols"
				:name="strings.emojisAndSymbols"
				align
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.searchAppearance.advanced.searchCleanup.settings.emojisAndSymbols"
						name="emojisAndSymbols"
						:options="[
							{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.on, value: true }
						]"
					/>

					<div class="aioseo-description">
						{{ strings.emojisAndSymbolsDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				id="search-cleanup-common-patterns"
				:name="strings.commonPatterns"
				align
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.searchAppearance.advanced.searchCleanup.settings.commonPatterns"
						name="commonPatterns"
						:options="[
							{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.on, value: true }
						]"
					/>

					<div class="aioseo-description">
						{{ strings.commonPatternsDescription }}
					</div>
				</template>
			</core-settings-row>
		</template>

		<core-settings-row
			id="search-cleanup-redirect-pretty-urls"
			:name="strings.redirectPrettyUrls"
			align
		>
			<template #content>
				<base-radio-toggle
					v-model="optionsStore.options.searchAppearance.advanced.searchCleanup.settings.redirectPrettyUrls"
					name="redirectPrettyUrls"
					:options="[
						{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.on, value: true }
					]"
				/>

				<div class="aioseo-description">
					<div v-html="strings.redirectPrettyUrlsDescription"></div>
					<div v-html="strings.redirectPrettyUrlsExample"></div>
				</div>
			</template>
		</core-settings-row>

		<prevent-crawling />
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import {
	useOptionsStore
} from '@/vue/stores'

import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import PreventCrawling from './PreventCrawling'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			GLOBAL_STRINGS,
			optionsStore : useOptionsStore()
		}
	},
	components : {
		BaseRadioToggle,
		CoreSettingsRow,
		PreventCrawling
	},
	data () {
		return {
			minCharacters : 1,
			maxCharacters : 50,
			emptyString   : '',
			strings       : {
				searchFilterToggle                  : __('Filter Search', td),
				searchFilterToggleDescription       : __('Enables advanced settings that help to prevent spammers from abusing your internal site search.', td),
				searchFilterTopDescriptionTitle     : __('Internal Site Search Cleanup', td),
				searchFilterTopDescriptionText      : __('Your internal site search can create lots of confusing URLs for search engines, and can even be used as a way for SEO spammers to attack your site. Most sites will benefit from experimenting with these protections and optimizations, even if you don\'t have a search feature in your theme.', td),
				searchCleanup                       : __('Internal Site Search Cleanup', td),
				searchCleanupDescription            : __('The internal site search feature can create lots of confusing URLs for search engines, and can even be used by SEO spammers to attack your site. Most sites benefit from these protections and optimizations, even if internal search has been disabled.', td),
				maxAllowedNumberOfChars             : __('Max. Number of Characters', td),
				maxAllowedNumberOfCharsDescription  : __('Limit the length of internal site search queries to reduce the impact of spam attacks and confusing URLs.', td),
				maxAllowedNumberOfCharsDescription2 : __('We recommend entering a number between 1 and 50 characters.', td),
				maxAllowedNumberOfCharsAlert        : __('The number must be between 1 and 50.', td),
				emojisAndSymbols                    : __('Emojis and Other Special Characters', td),
				emojisAndSymbolsDescription         : __('Block internal site searches which contain complex and non-alphanumeric characters.', td),
				commonPatterns                      : __('Common Spam Patterns', td),
				commonPatternsDescription           : __('Block internal site searches which match the patterns of known spam attacks.', td),
				redirectPrettyUrls                  : __('Redirect Pretty to "RAW" URLs', td),
				redirectPrettyUrlsDescription       : sprintf(
					// Translators: 1 - "?s=".
					__('Consolidates WordPress\' multiple site search URL formats into the %1$s e.g.,', td),
					'<code>?s=</code>'
				),
				redirectPrettyUrlsExample : sprintf(
					// Translators: 1 - Example URL, 2 - Example URL.
					__('%1$s will redirect to %2$s', td),
					'<code>https://example.com/<b>search/cats</b></code>',
					'<code>https://example.com/<b>?s=cats</b></code>'
				)
			}
		}
	}
}
</script>