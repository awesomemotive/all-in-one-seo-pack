<template>
	<div class="aioseo-rss-sitemap">
		<search-console v-if="optionsStore.options.sitemap.rss.enable" />

		<core-card
			slug="rssSitemap"
			:header-text="strings.rss"
		>
			<div class="aioseo-settings-row aioseo-section-description">
				{{ strings.description }}
				<span
					v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'rssSitemaps', true)"
				/>
			</div>

			<core-settings-row
				:name="strings.enableSitemap"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.sitemap.rss.enable"
					/>

					<search-console-inline v-if="optionsStore.options.sitemap.rss.enable" />
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.sitemap.rss.enable"
				:name="GLOBAL_STRINGS.preview"
			>
				<template #content>
					<div class="aioseo-sitemap-preview">
						<base-button
							size="medium"
							type="blue"
							tag="a"
							:href="rootStore.aioseo.urls.rssSitemapUrl"
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
							v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'blankSitemap', true)"
						/>
					</div>
				</template>
			</core-settings-row>
		</core-card>

		<core-card
			v-if="optionsStore.options.sitemap.rss.enable"
			slug="rssSitemapSettings"
			:header-text="strings.sitemapSettings"
		>
			<core-settings-row
				:name="strings.linksPerSitemap"
			>
				<template #content>
					<base-input
						v-model="optionsStore.options.sitemap.rss.linksPerIndex"
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
							v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'maxLinksRss', true)"
						/>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.postTypes"
			>
				<template #content>
					<base-checkbox
						size="medium"
						v-model="optionsStore.options.sitemap.rss.postTypes.all"
					>
						{{ strings.includeAllPostTypes }}
					</base-checkbox>

					<core-post-type-options
						v-if="!optionsStore.options.sitemap.rss.postTypes.all"
						:options="optionsStore.options.sitemap.rss"
						type="postTypes"
						:excluded="getExcludedPostTypes"
					/>

					<div class="aioseo-description">
						{{ strings.selectPostTypes }}

						<span
							v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'selectPostTypesRss', true)"
						/>
					</div>
				</template>
			</core-settings-row>
		</core-card>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { useCommonSitemap } from '@/vue/pages/sitemaps/composables/CommonSitemap'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import CoreCard from '@/vue/components/common/core/Card'
import CorePostTypeOptions from '@/vue/components/common/core/PostTypeOptions'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import SearchConsole from './partials/SearchConsole'
import SearchConsoleInline from './partials/SearchConsoleInline'
import SvgExternal from '@/vue/components/common/svg/External'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const { validateLinksPerIndex } = useCommonSitemap()

		return {
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore(),
			validateLinksPerIndex,
			GLOBAL_STRINGS,
			links
		}
	},
	components : {
		BaseCheckbox,
		CoreCard,
		CorePostTypeOptions,
		CoreSettingsRow,
		SearchConsole,
		SearchConsoleInline,
		SvgExternal
	},
	data () {
		return {
			pagePostOptions : [],
			strings         : {
				rss                            : __('RSS Sitemap', td),
				description                    : __('This option will generate a separate RSS Sitemap which can be submitted to Google, Bing and any other search engines that support this type of sitemap. The RSS Sitemap contains an RSS feed of the latest updates to your site content. It is not a full sitemap of all your content.', td),
				enableSitemap                  : __('Enable Sitemap', td),
				sitemapSettings                : __('Sitemap Settings', td),
				enableSitemapIndexes           : __('Enable Sitemap Indexes', td),
				sitemapIndexes                 : __('Organize sitemap entries into distinct files in your sitemap. We recommend you enable this setting if your sitemap contains more than 1,000 URLs.', td),
				linksPerSitemap                : __('Number of Posts', td),
				noIndexDisplayed               : __('Noindexed content will not be displayed in your sitemap.', td),
				doYou404                       : __('Do you get a blank sitemap or 404 error?', td),
				openSitemap                    : __('Open RSS Sitemap', td),
				maxLinks                       : __('Allows you to specify the maximum number of posts for the RSS Sitemap. We recommend an amount of 50 posts.', td),
				automaticallyPingSearchEngines : __('Automatically Ping Search Engines', td),
				postTypes                      : __('Post Types', td),
				taxonomies                     : __('Taxonomies', td),
				dateArchiveSitemap             : __('Date Archive Sitemap', td),
				includeDateArchives            : __('Include Date Archives in your sitemap.', td),
				authorSitemap                  : __('Author Sitemap', td),
				includeAuthorArchives          : __('Include Author Archives in your sitemap.', td),
				includeAllPostTypes            : __('Include All Post Types', td),
				selectPostTypes                : __('Select which Post Types appear in your sitemap.', td),
				includeAllTaxonomies           : __('Include All Taxonomies', td),
				selectTaxonomies               : __('Select which Taxonomies appear in your sitemap.', td)
			}
		}
	},
	computed : {
		getExcludedPostTypes () {
			return [ 'attachment' ]
		}
	}
}
</script>

<style lang="scss">
.aioseo-rss-sitemap {
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
			margin-right: 10px;
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
		max-width: 350px;

		.header-row {
			font-size: 14px;
		}
	}
}
</style>