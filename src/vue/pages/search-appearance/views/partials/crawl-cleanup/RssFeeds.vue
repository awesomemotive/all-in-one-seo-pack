<template>
	<div class="content">

		<core-settings-row
			id="crawl-cleanup-description"
			:name="strings.rssTopDescriptionTitle"
			leftSize="12"
			align
		>
			<template #description>
				<div v-html="strings.rssTopDescriptionText"></div>
			</template>
		</core-settings-row>

		<core-settings-row
			id="crawl-content-global-feed"
			:name="strings.globalFeed"
			align
		>
			<template #content>

				<base-radio-toggle
					v-model="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.global"
					name="global"
					:options="[
						{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.on, value: true }
					]"
				/>

				<div class="aioseo-description"
					v-if="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.global"
				>
					{{ strings.globalFeedDescription }}
					{{ strings.disableGlobalFeedAlert }}

					<div class="rss-link">
						<a
							:href="rootStore.aioseo.urls.feeds.global"
							target="_blank"
						>{{ strings.openYourRssFeed }}</a>

						<a
							class="no-underline"
							:href="rootStore.aioseo.urls.feeds.global"
							target="_blank"
						>&nbsp;<svg-external /></a>
					</div>
				</div>

				<core-alert
					v-if="!optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.global"
					type="red"
				>
					{{ strings.disableGlobalFeedAlert }}
				</core-alert>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.globalCommentsFeed"
			align
		>
			<template #content>
				<base-radio-toggle
					v-model="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.globalComments"
					name="globalComments"
					:options="[
						{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.on, value: true }
					]"
				/>

				<div class="aioseo-description">
					{{ strings.globalCommentsFeedDescription }}
				</div>

				<div class="aioseo-description"
					v-if="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.globalComments"
				>
					<a
						:href="rootStore.aioseo.urls.feeds.globalComments"
						target="_blank"
					>{{ strings.openYourCommentsRssFeed }}</a>
					<a
						class="no-underline"
						:href="rootStore.aioseo.urls.feeds.globalComments"
						target="_blank"
					>&nbsp;<svg-external /></a>
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			v-if="rootStore.aioseo.data.staticBlogPage"
			:name="strings.staticBlogPageFeed"
			align
		>
			<template #content>
				<base-radio-toggle
					v-model="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.staticBlogPage"
					name="staticBlogPage"
					:options="[
						{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.on, value: true }
					]"
				/>

				<div class="aioseo-description">
					{{ strings.staticBlogPageFeedDescription }}
				</div>

				<div class="aioseo-description"
					v-if="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.staticBlogPage"
				>
					<a
						:href="rootStore.aioseo.urls.feeds.staticBlogPage"
						target="_blank"
					>{{ strings.openYourStaticBlogPageFeed }}</a>

					<a
						class="no-underline"
						:href="rootStore.aioseo.urls.feeds.staticBlogPage"
						target="_blank"
					>&nbsp;<svg-external /></a>
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.authorsFeed"
			align
		>
			<template #content>
				<base-radio-toggle
					v-model="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.authors"
					name="authors"
					:options="[
						{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.on, value: true }
					]"
				/>

				<div class="aioseo-description">
					{{ strings.authorsFeedDescription }}
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.postCommentsFeed"
			align
		>
			<template #content>
				<base-radio-toggle
					v-model="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.postComments"
					name="postComments"
					:options="[
						{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.on, value: true }
					]"
				/>

				<div class="aioseo-description">
					{{ strings.postCommentsFeedDescription }}
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.searchFeed"
			align
		>
			<template #content>
				<base-radio-toggle
					v-model="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.search"
					name="search"
					:options="[
						{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.on, value: true }
					]"
				/>

				<div class="aioseo-description">
					{{ strings.searchFeedDescription }}
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.attachmentsFeed"
			align
		>
			<template #content>
				<base-radio-toggle
					v-model="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.attachments"
					name="attachments"
					:options="[
						{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.on, value: true }
					]"
				/>

				<div class="aioseo-description">
					{{ strings.attachmentsFeedDescription }}
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.paginatedFeed"
			align
		>
			<template #content>
				<base-radio-toggle
					v-model="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.paginated"
					name="paginated"
					:options="[
						{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.on, value: true }
					]"
				/>

				<div class="aioseo-description">
					{{ strings.paginatedFeedDescription }}
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			v-if="getArchives.included.length"
			:name="strings.postTypesFeed"
		>
			<template #content>
				<base-checkbox
					size="medium"
					v-model="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.archives.all"
				>
					{{ strings.includeAllPostTypes }}
				</base-checkbox>

				<core-post-type-options
					v-if="!optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.archives.all"
					:options="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds"
					:excluded="getArchives.excluded"
					type="archives"
				/>

				<div class="aioseo-description">
					{{ strings.selectPostTypes }}
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.taxonomiesFeed"
		>
			<template #content>
				<base-checkbox
					size="medium"
					v-model="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.taxonomies.all"
				>
					{{ strings.includeAllTaxonomies }}
				</base-checkbox>

				<core-post-type-options
					v-if="!optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.taxonomies.all"
					:options="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds"
					type="taxonomies"
				/>

				<div class="aioseo-description">
					{{ strings.selectTaxonomies }}
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.atomFeed"
			align
		>
			<template #content>
				<base-radio-toggle
					v-model="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.atom"
					name="atom"
					:options="[
						{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.on, value: true }
					]"
				/>

				<div class="aioseo-description"
					v-html="strings.atomFeedDescription"
				/>

				<div class="aioseo-description"
					v-if="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.atom"
				>
					<a
						:href="rootStore.aioseo.urls.feeds.atom"
						target="_blank"
					>{{ strings.openYourAtomFeed }}</a>
					<a
						class="no-underline"
						:href="rootStore.aioseo.urls.feeds.atom"
						target="_blank"
					>&nbsp;<svg-external /></a>
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.rdfFeed"
			align
		>
			<template #content>
				<base-radio-toggle
					v-model="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.rdf"
					name="rdf"
					:options="[
						{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.on, value: true }
					]"
				/>

				<div class="aioseo-description"
					v-html="strings.rdfFeedDescription"
				/>

				<div class="aioseo-description"
					v-if="optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.rdf"
				>
					<a
						:href="rootStore.aioseo.urls.feeds.rdf"
						target="_blank"
					>{{ strings.openYourRdfFeed }}</a>
					<a
						class="no-underline"
						:href="rootStore.aioseo.urls.feeds.rdf"
						target="_blank"
					>&nbsp;<svg-external /></a>
				</div>
			</template>
		</core-settings-row>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { useJsonValues } from '@/vue/composables/JsonValues'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CorePostTypeOptions from '@/vue/components/common/core/PostTypeOptions'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import SvgExternal from '@/vue/components/common/svg/External'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			getJsonValue,
			setJsonValue
		} = useJsonValues()

		return {
			GLOBAL_STRINGS,
			getJsonValue,
			links,
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore(),
			setJsonValue
		}
	},
	components : {
		BaseCheckbox,
		BaseRadioToggle,
		CoreAlert,
		CorePostTypeOptions,
		CoreSettingsRow,
		SvgExternal
	},
	data () {
		return {
			emptyString : '',
			strings     : {
				rssFeedToggle            : __('RSS Feed', td),
				rssFeedToggleDescription : __('Enable RSS Feed settings', td),
				rssTopDescriptionTitle   : __('Disabling Unnecessary RSS Feeds', td),
				rssTopDescriptionText    : sprintf(
					// Translators: 1 - "Learn More" link.
					__('Disabling unnecessary RSS feeds can help save search engine crawl quota and speed up content indexing for larger sites. If you choose to disable any feeds, those feed links will automatically redirect to your homepage or applicable archive page. %2$s', td),
					import.meta.env.VITE_SHORT_NAME,
					links.getDocLink(GLOBAL_STRINGS.learnMore, 'crawlCleanup', true)
				),
				crawlCleanup                  : __('Crawl Cleanup', td),
				globalFeed                    : __('Global RSS Feed', td),
				globalFeedDescription         : __('The global RSS feed is how users subscribe to any new content that has been created on your site.', td),
				openYourRssFeed               : __('Open Your RSS Feed', td),
				disableGlobalFeedAlert        : __('Disabling the global RSS feed is NOT recommended. This will prevent users from subscribing to your content and can hurt your SEO rankings.', td),
				globalCommentsFeed            : __('Global Comments RSS Feed', td),
				globalCommentsFeedDescription : __('The global comments feed allows users to subscribe to any new comments added to your site.', td),
				openYourCommentsRssFeed       : __('Open Your Comments RSS Feed', td),
				staticBlogPageFeed            : __('Static Posts Page Feed', td),
				staticBlogPageFeedDescription : __('The static posts page feed allows users to subscribe to any new content added to your blog page.', td),
				openYourStaticBlogPageFeed    : __('Open Your Static Posts Page RSS Feed', td),
				authorsFeed                   : __('Author Feeds', td),
				authorsFeedDescription        : __('The authors feed allows your users to subscribe to any new content written by a specific author.', td),
				postCommentsFeed              : __('Post Comment Feeds', td),
				postCommentsFeedDescription   : __('The post comments feed allows your users to subscribe to any new comments on a specific page or post.', td),
				searchFeed                    : __('Search Feed', td),
				searchFeedDescription         : __('The search feed allows visitors to subscribe to your content based on a specific search term.', td),
				attachmentsFeed               : __('Attachments Feed', td),
				attachmentsFeedDescription    : __('The attachments feed allows users to subscribe to any changes to your site made to media file categories.', td),
				postTypesFeed                 : __('Post Type Archive Feeds', td),
				includeAllPostTypes           : __('Include All Post Type Archives', td),
				selectPostTypes               : __('Select which post type archives should include an RSS feed. This only applies to post types that include an archive page.', td),
				taxonomiesFeed                : __('Taxonomy Feeds', td),
				includeAllTaxonomies          : __('Include All Taxonomies', td),
				selectTaxonomies              : __('Select which Taxonomies should include an RSS feed.', td),
				atomFeed                      : __('Atom Feed', td),
				atomFeedDescription           : sprintf(
					// Translators: 1 - Learn more link.
					__('This is a global feed of your site output in the Atom format. %1$s', td),
					links.getPlainLink(GLOBAL_STRINGS.learnMore, 'http://www.atomenabled.org/', true)
				),
				openYourAtomFeed   : __('Open Your Atom Feed', td),
				rdfFeed            : __('RDF/RSS 1.0 Feed', td),
				rdfFeedDescription : sprintf(
					// Translators: 1 - Learn more link.
					__('This is a global feed of your site output in the RDF/RSS 1.0 format. %1$s', td),
					links.getPlainLink(GLOBAL_STRINGS.learnMore, 'https://web.resource.org/rss/1.0/', true)
				),
				openYourRdfFeed          : __('Open Your RDF Feed', td),
				paginatedFeed            : __('Paginated RSS Feeds', td),
				paginatedFeedDescription : __('The paginated RSS feeds are for any posts or pages that are paginated.', td)
			}
		}
	},
	computed : {
		getArchives () {
			const excluded = []
			const included = this.rootStore.aioseo.postData.archives.filter(a => {
				if (a.buddyPress && 'bp-activity' !== a.name) {
					excluded.push(a.name)

					return false
				}

				return true
			})

			return {
				excluded,
				included
			}
		}
	}
}
</script>