<template>
	<div class="aioseo-llms-sitemap">
		<!-- General Settings -->
		<core-card
			slug="llmsSitemap"
		>
			<template #header>
				<span>{{ strings.title }}</span>
			</template>

			<div class="aioseo-settings-row aioseo-section-description">
				{{ strings.description }}
				<span
					v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'llmsTxt', true)"
				/>
			</div>

			<!-- LLMS.txt -->
			<core-settings-row
				:name="strings.llmsTxtEnable"
			>
				<template #content>
					<base-toggle v-model="optionsStore.options.sitemap.llms.enable"/>

					<div class="aioseo-description aioseo-llms-txt-description">
						<div>{{ strings.llmsTxtDescription }}</div>

						<core-tooltip
							v-if="(!(llmsTxtAccessible && optionsStore.options.sitemap.llms.enable) || (llmsButtonLocked && ! optionsStore.options.sitemap.llms.enable))"
							type="action"
							tag="div"
							class="aioseo-llms-txt-tooltip"
						>
							<span>
								<base-button
									v-if="optionsStore.options.sitemap.llms.enable"
									:disabled="true"
									class="aioseo-llms-txt-button"
									size="medium"
									type="blue"
									tag="button"
								>
										<svg-external />
										{{ strings.llmsTxtButton }}
								</base-button>
							</span>

							<template #tooltip>
								{{ strings.llmsTxtTooltip }}
							</template>
						</core-tooltip>

						<base-button
							v-if="(! llmsButtonLocked && llmsTxtAccessible && optionsStore.options.sitemap.llms.enable) || ( llmsButtonLocked && llmsTxtAccessible && optionsStore.options.sitemap.llms.enable )"
							class="aioseo-llms-txt-button"
							size="medium"
							type="blue"
							tag="a"
							:href="sanitizeUrl(rootStore.aioseo.urls.llmsUrl.url)"
							target="_blank"
						>
								<svg-external />
								{{ strings.llmsTxtButton }}
						</base-button>
					</div>
				</template>
			</core-settings-row>

			<!-- llms-full.txt -->
			<core-settings-row>
				<template #name>
					{{ strings.llmsTxtFullEnable }}

					<core-pro-badge
						v-if="licenseStore.isUnlicensed"
					/>
				</template>

				<template #content>
					<base-toggle
						v-model="optionsStore.options.sitemap.llms.enableFull"
						:disabled="licenseStore.isUnlicensed"
					/>

					<div class="aioseo-description aioseo-llms-txt-description">
						<div>{{ strings.llmsTxtFullDescription }}</div>

						<core-tooltip
							v-if="!licenseStore.isUnlicensed &&
								(!(llmsTxtAccessibleFull && optionsStore.options.sitemap.llms.enableFull) || (llmsButtonLockedFull && ! optionsStore.options.sitemap.llms.enableFull))"
							type="action"
							tag="div"
							class="aioseo-llms-txt-tooltip"
						>
							<span>
								<base-button
									v-if="optionsStore.options.sitemap.llms.enableFull"
									:disabled="true"
									class="aioseo-llms-txt-button"
									size="medium"
									type="blue"
									tag="button"
								>
										<svg-external />
										{{ strings.llmsTxtFullButton }}
								</base-button>
							</span>

							<template #tooltip>
								{{ strings.llmsTxtFullTooltip }}
							</template>
						</core-tooltip>

						<base-button
							v-if="(! llmsButtonLockedFull && llmsTxtAccessibleFull && optionsStore.options.sitemap.llms.enableFull) || ( llmsButtonLockedFull && llmsTxtAccessibleFull && optionsStore.options.sitemap.llms.enableFull )"
							class="aioseo-llms-txt-button"
							size="medium"
							type="blue"
							tag="a"
							:href="sanitizeUrl(rootStore.aioseo.urls.llmsFullUrl.url)"
							target="_blank"
						>
								<svg-external />
								{{ strings.llmsTxtFullButton }}
						</base-button>
					</div>

					<core-alert
						v-if="licenseStore.isUnlicensed"
						type="blue"
						v-html="strings.llmsFullTxtUpsell"
					/>
				</template>
			</core-settings-row>

			<!-- Convert to Markdown -->
			<core-settings-row>
				<template #name>
					{{ strings.llmsTxtConvertToMd }}
					<core-pro-badge
						v-if="licenseStore.isUnlicensed"
					/>
				</template>

				<template #content>
					<base-toggle
						v-model="optionsStore.options.sitemap.llms.convertToMd"
						:disabled="licenseStore.isUnlicensed"
					/>

					<div class="aioseo-description aioseo-llms-txt-description">
						<div>{{ strings.llmsTxtConvertToMdDescription }}</div>
					</div>

					<core-alert
						class="inline-upsell"
						v-if="licenseStore.isUnlicensed"
						type="blue"
					>
						<div v-html="strings.llmsTxtConvertToMdUpsell" />
					</core-alert>
				</template>
			</core-settings-row>
		</core-card>

		<!-- Advanced Settings -->
		<core-card
			slug="llmsSitemapAdvancedSettings"
		>
			<template #header>
				<span>{{ strings.llmsAdvancedSettings }}</span>
			</template>

			<div>

				<core-settings-row
					:name="strings.llmsAdvancedSettingsTitle"
					align
				>
					<template #content>
						<core-html-tags-editor
							v-model="optionsStore.options.sitemap.llms.advancedSettings.title"
							:line-numbers="false"
							single
							@counter="count => titleCount = count.length"
							tags-context="llmsTitle"
							:default-tags="[
								'site_title',
								'tagline',
							]"
							:show-all-tags-link="false"
						>
							<template #tags-description>
								{{ strings.llmsAdvancedSettingsClickToAddLlmsTitle }}
							</template>
						</core-html-tags-editor>

						<div
							class="max-recommended-count"
							v-html="maxRecommendedCount(titleCount, 95)"
						/>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.llmsAdvancedSettingsClickToAddLlmsDescriptionTitle"
					align
				>
					<template #content>
						<core-html-tags-editor
							v-model="optionsStore.options.sitemap.llms.advancedSettings.description"
							:line-numbers="false"
							description
							@counter="count => descriptionCount = count.length"
							tags-context="llmsDescription"
							:default-tags="[
								'site_title',
								'tagline',
							]"
							:show-all-tags-link="false"
						>
							<template #tags-description>
								{{ strings.llmsAdvancedSettingsClickToAddLlmsDescription }}
							</template>
						</core-html-tags-editor>

						<div
							class="max-recommended-count"
							v-html="maxRecommendedCount(descriptionCount, 200)"
						/>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.llmsAdvancedSettingsLinksPerPostTax"
				>
					<template #content>
						<base-input
							v-model="linksPerPostTaxValue"
							class="aioseo-links-per-post-tax"
							type="number"
							size="medium"
							:min="1"
							:max="50000"
						/>

						<div class="aioseo-description">
							{{ strings.llmsAdvancedSettingsLinksPerPostTaxDescription }}

							<span
								v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'llmsTxt', true)"
							/>
						</div>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.llmsAdvancedSettingsPostTypes"
				>
					<template #content>
						<base-checkbox
							size="medium"
							v-model="optionsStore.options.sitemap.llms.advancedSettings.postTypes.all"
						>
							{{ strings.llmsAdvancedSettingsIncludeAllPostTypes }}
						</base-checkbox>

						<core-post-type-options
							v-if="!optionsStore.options.sitemap.llms.advancedSettings.postTypes.all"
							:options="optionsStore.options.sitemap.llms.advancedSettings"
							type="postTypes"
							:excluded="getExcludedPostTypes"
						/>

						<div class="aioseo-description">
							{{ strings.llmsAdvancedSettingsSelectPostTypes }}

							<span
								v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'llmsTxt', true)"
							/>
						</div>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.llmsAdvancedSettingsTaxonomies"
				>
					<template #content>
						<base-checkbox
							size="medium"
							v-model="optionsStore.options.sitemap.llms.advancedSettings.taxonomies.all"
						>
							{{ strings.llmsAdvancedSettingsIncludeAllTaxonomies }}
						</base-checkbox>

						<core-post-type-options
							v-if="!optionsStore.options.sitemap.llms.advancedSettings.taxonomies.all"
							:options="optionsStore.options.sitemap.llms.advancedSettings"
							type="taxonomies"
							:excluded="[]"
						/>

						<div class="aioseo-description">
							{{ strings.llmsAdvancedSettingsSelectTaxonomies }}

							<span
								v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'llmsTxt', true)"
							/>
						</div>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.llmsAdvancedSettingsExcludePostsPages"
					class="aioseo-exclude-pages-posts"
					align
				>
					<template #content>
						<core-exclude-posts
							:options="optionsStore.options.sitemap.llms.advancedSettings"
							type="posts"
						/>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.llmsAdvancedSettingsExcludeTerms"
					class="aioseo-exclude-terms"
					align
				>
					<template #content>
						<core-exclude-posts
							:options="optionsStore.options.sitemap.llms.advancedSettings"
							type="terms"
						/>
					</template>
				</core-settings-row>
			</div>
		</core-card>
	</div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useRootStore,
	useOptionsStore
} from '@/vue/stores'
import { useCommonSitemap } from '@/vue/pages/sitemaps/composables/CommonSitemap'
import { useButtonAccessibility } from '@/vue/composables/llms/ButtonAccessibility'
import { useMaxCounts } from '@/vue/composables/MaxCounts'

import { sanitizeUrl } from '@/vue/utils/strings'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreExcludePosts from '@/vue/components/common/core/ExcludePosts'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CorePostTypeOptions from '@/vue/components/common/core/PostTypeOptions'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgExternal from '@/vue/components/common/svg/External'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const licenseStore = useLicenseStore()
const rootStore    = useRootStore()
const optionsStore = useOptionsStore()

const { maxRecommendedCount } = useMaxCounts()
const { validateLinksPerIndex } = useCommonSitemap(1, 50000)

const { llmsButtonLocked, llmsTxtAccessible } = useButtonAccessibility('enable')
const { llmsButtonLocked: llmsButtonLockedFull, llmsTxtAccessible: llmsTxtAccessibleFull } = useButtonAccessibility('enableFull')

const titleCount = ref(0)
const descriptionCount = ref(0)

const getExcludedPostTypes = computed(() => {
	return [ 'attachment' ]
})

const linksPerPostTaxValue = computed({
	get () {
		return optionsStore.options.sitemap.llms.advancedSettings.linksPerPostTax
	},
	set (value) {
		const validatedValue = validateLinksPerIndex(value)
		optionsStore.options.sitemap.llms.advancedSettings.linksPerPostTax = validatedValue
	}
})

const strings = {
	title                                              : __('LLMs.txt', td),
	description                                        : __('The llms.txt is a specialized file designed to help AI engines (such as language models) discover the content on your site more easily. Similar to how XML sitemaps assist search engines, the llms.txt file guides AI crawlers by providing important details about the available site content, improving visibility and discoverability across AI-driven tools.', td),
	llmsTxtEnable                                      : __('Enable llms.txt', td),
	llmsTxt                                            : __('llms.txt', td),
	llmsTxtDescription                                 : __('Generate an llms.txt file to help AI engines discover the content on your site more easily.', td),
	llmsTxtButton                                      : __('Open llms.txt', td),
	llmsTxtTooltip                                     : __('To view the llms.txt file, enable the setting, save changes and wait a minute for the file to be generated.', td),
	llmsTxtFullEnable                                  : __('Enable llms-full.txt', td),
	llmsTxtFullDescription                             : __('Generate a detailed llms-full.txt file to help AI engines index all of your site’s content quickly without straining your server’s resources.', td),
	llmsTxtFullButton                                  : __('Open llms-full.txt', td),
	llmsTxtFullTooltip                                 : __('To view the llms-full.txt file, enable the setting, save changes and wait a minute for the file to be generated.', td),
	llmsTxtConvertToMd                                 : __('Convert Posts to Markdown', td),
	llmsTxtConvertToMdDescription                      : __('Generate a markdown version of your posts when `.md` is appended to their permalink. This provides AI engines with clean version of your pages that can be crawled more quickly.', td),
	llmsAdvancedSettings                               : __('LLMs.txt Settings', td),
	llmsAdvancedSettingsExcludePostsPages              : __('Exclude Posts / Pages', td),
	llmsAdvancedSettingsExcludeTerms                   : __('Exclude Terms', td),
	llmsAdvancedSettingsPostTypes                      : __('Post Types', td),
	llmsAdvancedSettingsTaxonomies                     : __('Taxonomies', td),
	llmsAdvancedSettingsSelectPostTypes                : __('Select which Post Types to include in the llms.txt file.', td),
	llmsAdvancedSettingsSelectTaxonomies               : __('Select which Taxonomies to include in the llms.txt file.', td),
	llmsAdvancedSettingsIncludeAllPostTypes            : __('Include All Post Types', td),
	llmsAdvancedSettingsIncludeAllTaxonomies           : __('Include All Taxonomies', td),
	llmsAdvancedSettingsLinksPerPostTax                : __('URLs per Post Type / Taxonomy', td),
	llmsAdvancedSettingsLinksPerPostTaxDescription     : __('Allows you to specify the maximum number of pages per post type/taxonomy in your llms.txt file.', td),
	llmsAdvancedSettingsTitle                          : __('Title', td),
	llmsAdvancedSettingsDescription                    : __('The title of the llms.txt file.', td),
	llmsAdvancedSettingsClickToAddLlmsTitle            : __('Click on the tags below to insert variables into your title.', td),
	llmsAdvancedSettingsClickToAddLlmsDescriptionTitle : __('Description', td),
	llmsAdvancedSettingsClickToAddLlmsDescription      : __('Click on the tags below to insert variables into your description.', td),
	llmsTxtConvertToMdUpsell                           : sprintf(
		// Translators: 1 - "PRO", 2 - "Learn more".
		__('Converting posts to markdown is a %1$s feature. %2$s', td),
		'PRO',
		links.getUpsellLink('sitemaps', 'convert-posts-to-markdown', GLOBAL_STRINGS.learnMore, 'liteUpgrade', true)
	),
	llmsFullTxtUpsell : sprintf(
		// Translators: 1 - "PRO", 2 - "Learn more".
		__('The llms-full.txt is a %1$s feature. %2$s', td),
		'PRO',
		links.getUpsellLink('sitemaps', 'llms-full-txt', GLOBAL_STRINGS.learnMore, 'liteUpgrade', true)
	)
}
</script>

<style lang="scss">
.aioseo-llms-sitemap {
	.aioseo-links-per-post-tax {
		max-width: 110px;
	}

	.aioseo-llms-txt-description {
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		gap: 8px;

		.aioseo-llms-txt-tooltip {
			margin: 0;
		}

		.aioseo-llms-txt-button {
			svg.aioseo-external {
				width: 14px;
				height: 14px;
				margin-right: 10px;
			}
		}
	}

	.aioseo-alert {
		margin-top: 8px;
	}
}

</style>