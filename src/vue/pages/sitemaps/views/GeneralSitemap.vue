<template>
	<div class="aioseo-general-sitemap">
		<core-card
			slug="generalSitemap"
			:header-text="strings.general"
		>
			<div class="aioseo-settings-row aioseo-section-description">
				{{ strings.description }}
				<span
					v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'xmlSitemaps', true)"
				/>
			</div>

			<core-settings-row
				:name="strings.enableSitemap"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.sitemap.general.enable"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.sitemap.general.enable"
				:name="$constants.GLOBAL_STRINGS.preview"
			>
				<template #content>
					<div class="aioseo-sitemap-preview">
						<base-button
							size="medium"
							type="blue"
							tag="a"
							:href="rootStore.aioseo.urls.generalSitemapUrl"
							target="_blank"
						>
							<svg-external />
							{{ strings.openSitemap }}
						</base-button>
					</div>

					<div class="aioseo-description">
						{{ strings.noIndexDisplayed }}
						{{ strings.doYou404 }}

						<span
							v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'blankSitemap', true)"
						/>
					</div>

					<core-alert
						class="static-regeneration-notice"
						v-if="!optionsStore.options.deprecated.sitemap.general.advancedSettings.dynamic && rootStore.aioseo.scheduledActions && rootStore.aioseo.scheduledActions.sitemap && rootStore.aioseo.scheduledActions.sitemap.includes('staticSitemapRegeneration')"
						type="blue"
					>
						{{ strings.warningStaticRegeneration }}
					</core-alert>
				</template>
			</core-settings-row>
		</core-card>

		<core-card
			v-if="optionsStore.options.sitemap.general.enable"
			slug="generalSitemapSettings"
			:header-text="strings.sitemapSettings"
		>
			<core-settings-row
				:name="strings.enableSitemapIndexes"
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.sitemap.general.indexes"
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
				v-if="optionsStore.options.sitemap.general.indexes"
				:name="strings.linksPerSitemap"
			>
				<template #content>
					<base-input
						v-model="optionsStore.options.sitemap.general.linksPerIndex"
						class="aioseo-links-per-site"
						type="number"
						size="medium"
						:min="1"
						:max="50000"
						@keyup="validateLinksPerIndex"
					/>

					<div class="aioseo-description">
						{{ strings.maxLinks }}

						<span
							v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'maxLinks', true)"
						/>
					</div>

					<core-alert
						class="index-notice"
						v-if="optionsStore.options.sitemap.general.indexes && 1000 < optionsStore.options.sitemap.general.linksPerIndex"
						type="yellow"
					>
						{{ strings.warningLinksPerSitemap }}
					</core-alert>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.postTypes"
			>
				<template #content>
					<base-checkbox
						size="medium"
						v-model="optionsStore.options.sitemap.general.postTypes.all"
					>
						{{ strings.includeAllPostTypes }}
					</base-checkbox>

					<core-post-type-options
						v-if="!optionsStore.options.sitemap.general.postTypes.all"
						:options="optionsStore.options.sitemap.general"
						type="postTypes"
					/>

					<div class="aioseo-description">
						{{ strings.selectPostTypes }}

						<span
							v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'selectPostTypes', true)"
						/>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.taxonomies"
			>
				<template #content>
					<base-checkbox
						size="medium"
						v-model="optionsStore.options.sitemap.general.taxonomies.all"
					>
						{{ strings.includeAllTaxonomies }}
					</base-checkbox>

					<core-post-type-options
						v-if="!optionsStore.options.sitemap.general.taxonomies.all"
						:options="optionsStore.options.sitemap.general"
						type="taxonomies"
					/>

					<div class="aioseo-description">
						{{ strings.selectTaxonomies }}

						<span
							v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'selectPostTypes', true)"
						/>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.dateArchiveSitemap"
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.sitemap.general.date"
						name="dateArchiveSitemap"
						:options="[
							{ label: $constants.GLOBAL_STRINGS.disabled, value: false, activeClass: 'dark' },
							{ label: $constants.GLOBAL_STRINGS.enabled, value: true }
						]"
					/>

					<div class="aioseo-description">
						{{ strings.includeDateArchives }}

						<span
							v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'includeArchivePages', true)"
						/>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.authorSitemap"
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.sitemap.general.author"
						name="authorSitemap"
						:options="[
							{ label: $constants.GLOBAL_STRINGS.disabled, value: false, activeClass: 'dark' },
							{ label: $constants.GLOBAL_STRINGS.enabled, value: true }
						]"
					/>

					<div class="aioseo-description">
						{{ strings.includeAuthorArchives }}

						<span
							v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'includeArchivePages', true)"
						/>
					</div>
				</template>
			</core-settings-row>
		</core-card>

		<AdditionalPages />

		<core-card
			v-if="optionsStore.options.sitemap.general.enable"
			slug="advancedSettings"
			:toggles="optionsStore.options.sitemap.general.advancedSettings.enable"
		>
			<template #header>
				<base-toggle
					v-model="optionsStore.options.sitemap.general.advancedSettings.enable"
				/>

				<span>{{ strings.advancedSettings }}</span>
			</template>

			<core-settings-row
				:name="strings.excludePostsPages"
				class="aioseo-exclude-pages-posts"
			>
				<template #content>
					<core-exclude-posts
						:options="optionsStore.options.sitemap.general.advancedSettings"
						type="posts"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.excludeTerms"
				class="aioseo-exclude-terms"
			>
				<template #content>
					<core-exclude-posts
						:options="optionsStore.options.sitemap.general.advancedSettings"
						type="terms"
					/>

					<div class="aioseo-description">
						{{ strings.excludeTermsDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.priorityScore"
				align
			>
				<template #content>
					<core-priority-score
						:priority="optionsStore.options.sitemap.general.advancedSettings.priority"
						:rows="[
							'homePage',
							'archive',
							'author'
						]"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.priorityScorePostTypes"
				align
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.sitemap.general.advancedSettings.priority.postTypes.grouped"
					>
						{{ strings.grouped }}
					</base-toggle>
					<core-priority-score
						v-if="optionsStore.options.sitemap.general.advancedSettings.priority.postTypes.grouped"
						:priority="optionsStore.options.sitemap.general.advancedSettings.priority"
						:rows="[
							'postTypes'
						]"
					/>
					<core-priority-score
						v-if="!optionsStore.options.sitemap.general.advancedSettings.priority.postTypes.grouped"
						:priority="optionsStore.dynamicOptions.sitemap.priority.postTypes"
						:rows="getPostTypeRows"
						:labels="getPostTypeLabels"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.priorityScoreTaxonomies"
				align
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.sitemap.general.advancedSettings.priority.taxonomies.grouped"
					>
						{{ strings.grouped }}
					</base-toggle>
					<core-priority-score
						v-if="optionsStore.options.sitemap.general.advancedSettings.priority.taxonomies.grouped"
						:priority="optionsStore.options.sitemap.general.advancedSettings.priority"
						:rows="[
							'taxonomies'
						]"
					/>
					<core-priority-score
						v-if="!optionsStore.options.sitemap.general.advancedSettings.priority.taxonomies.grouped"
						:priority="optionsStore.dynamicOptions.sitemap.priority.taxonomies"
						:rows="getTaxonomyRows"
						:labels="getTaxonomyLabels"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.dynamicallyGenerate"
				v-if="optionsStore.internalOptions.internal.deprecatedOptions.includes('staticSitemap')"
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.deprecated.sitemap.general.advancedSettings.dynamic"
						name="dynamic"
						:options="[
							{ label: $constants.GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: $constants.GLOBAL_STRINGS.yes, value: true }
						]"
					/>

					<div class="aioseo-description">
						{{ strings.dynamicallyGenerateDescription }}

						<span
							v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'dynamicallyGenerate', true)"
						/>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.excludeImages"
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.sitemap.general.advancedSettings.excludeImages"
						name="excludeImages"
						:options="[
							{ label: $constants.GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: $constants.GLOBAL_STRINGS.yes, value: true }
						]"
					/>

					<div class="aioseo-description">
						{{ strings.excludeImagesDescription }}

						<span
							v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'excludeImages', true)"
						/>
					</div>
				</template>
			</core-settings-row>
		</core-card>
	</div>
</template>

<script>
import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { useCommonSitemap } from '@/vue/pages/sitemaps/composables'

import AdditionalPages from './AdditionalPages'
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreExcludePosts from '@/vue/components/common/core/ExcludePosts'
import CorePostTypeOptions from '@/vue/components/common/core/PostTypeOptions'
import CorePriorityScore from '@/vue/components/common/core/PriorityScore'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import SvgExternal from '@/vue/components/common/svg/External'

export default {
	setup () {
		const { validateLinksPerIndex } = useCommonSitemap()

		return {
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore(),
			validateLinksPerIndex
		}
	},
	components : {
		AdditionalPages,
		BaseCheckbox,
		BaseRadioToggle,
		CoreAlert,
		CoreCard,
		CoreExcludePosts,
		CorePostTypeOptions,
		CorePriorityScore,
		CoreSettingsRow,
		SvgExternal
	},
	data () {
		return {
			pagePostOptions : [],
			strings         : {
				general     : this.$t.__('General Sitemap', this.$td),
				description : this.$t.sprintf(
					// Translators: 1 - The plugin name ("All in One SEO").
					this.$t.__('An XML Sitemap is a list of all your content that search engines use when they crawl your site. This is an essential part of SEO because it contains some important pieces of information that search engines need when crawling your site. The XML Sitemap created by %1$s tells search engines where to find all of the content on your site.', this.$td),
					import.meta.env.VITE_NAME
				),
				enableSitemap                  : this.$t.__('Enable Sitemap', this.$td),
				sitemapSettings                : this.$t.__('Sitemap Settings', this.$td),
				enableSitemapIndexes           : this.$t.__('Enable Sitemap Indexes', this.$td),
				sitemapIndexes                 : this.$t.__('Organize sitemap entries into distinct files in your sitemap. We recommend you enable this setting if your sitemap contains more than 1,000 URLs.', this.$td),
				linksPerSitemap                : this.$t.__('Links Per Sitemap', this.$td),
				noIndexDisplayed               : this.$t.__('Noindexed content will not be displayed in your sitemap.', this.$td),
				doYou404                       : this.$t.__('Do you get a blank sitemap or 404 error?', this.$td),
				openSitemap                    : this.$t.__('Open Sitemap', this.$td),
				maxLinks                       : this.$t.__('Allows you to specify the maximum number of posts in a sitemap (up to 50,000).', this.$td),
				automaticallyPingSearchEngines : this.$t.__('Automatically Ping Search Engines', this.$td),
				postTypes                      : this.$t.__('Post Types', this.$td),
				taxonomies                     : this.$t.__('Taxonomies', this.$td),
				dateArchiveSitemap             : this.$t.__('Date Archive Sitemap', this.$td),
				includeDateArchives            : this.$t.__('Include Date Archives in your sitemap.', this.$td),
				authorSitemap                  : this.$t.__('Author Sitemap', this.$td),
				includeAuthorArchives          : this.$t.__('Include Author Archives in your sitemap.', this.$td),
				includeAllPostTypes            : this.$t.__('Include All Post Types', this.$td),
				selectPostTypes                : this.$t.__('Select which Post Types appear in your sitemap.', this.$td),
				includeAllTaxonomies           : this.$t.__('Include All Taxonomies', this.$td),
				selectTaxonomies               : this.$t.__('Select which Taxonomies appear in your sitemap.', this.$td),
				advancedSettings               : this.$t.__('Advanced Settings', this.$td),
				excludePostsPages              : this.$t.__('Exclude Posts / Pages', this.$td),
				excludeTerms                   : this.$t.__('Exclude Terms', this.$td),
				excludeTermsDescription        : this.$t.__('Any posts that are assigned to these terms will also be excluded from your sitemap.', this.$td),
				priorityScore                  : this.$t.__('Priority Score', this.$td),
				grouped                        : this.$t.__('Grouped', this.$td),
				priorityScorePostTypes         : this.$t.__('Post Type Priority Score', this.$td),
				priorityScoreTaxonomies        : this.$t.__('Taxonomy Priority Score', this.$td),
				excludeImages                  : this.$t.__('Exclude Images', this.$td),
				excludeImagesDescription       : this.$t.__('Exclude Images from your sitemap.', this.$td),
				dynamicallyGenerate            : this.$t.__('Dynamically Generate', this.$td),
				dynamicallyGenerateDescription : this.$t.__('Dynamically creates the XML Sitemap instead of using a static file.', this.$td),
				warningLinksPerSitemap         : this.$t.__('We recommend setting the amount of URLs per sitemap index to 1,000 or less. The more links, the longer it will take for the sitemap to load.', this.$td),
				warningStaticRegeneration      : this.$t.__('Your static sitemap is currently being regenerated. This may take some time based on the size of your site. This may also cause the sitemap content to look outdated.', this.$td)
			}
		}
	},
	computed : {
		getPostTypeRows () {
			return this.rootStore.aioseo.postData.postTypes.map(p => p.name)
		},
		getPostTypeLabels () {
			const labels = {}
			this.rootStore.aioseo.postData.postTypes.forEach(p => {
				labels[p.name] = p.label
			})
			return labels
		},
		getTaxonomyRows () {
			return this.rootStore.aioseo.postData.taxonomies.map(t => t.name)
		},
		getTaxonomyLabels () {
			const labels = {}
			this.rootStore.aioseo.postData.taxonomies.forEach(t => {
				labels[t.name] = t.label
			})
			return labels
		}
	}
}
</script>

<style lang="scss">
.aioseo-general-sitemap {
	.aioseo-links-per-site {
		max-width: 110px;
	}

	.aioseo-sitemap-preview {
		margin-right: 10px;
		display: flex;
		align-items: center;
		margin-bottom: 10px;

		.aioseo-select {
			max-width: 180px;
			margin-right: 16px;
		}

		svg.aioseo-external {
			width: 14px;
			height: 14px;
			margin-right: 10px;
		}
	}

	.ping {
		> div:first-child {
			margin-right: 40px;
		}
	}

	.aioseo-priority-score {
		max-width: 500px;

		.header-row {
			font-size: 14px;
		}
	}
}
</style>