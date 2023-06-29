<template>
	<div class="aioseo-rss-content">
		<core-card
			slug="rssContent"
			:header-text="strings.rssContent"
		>
			<template #tooltip>
				<div>{{ strings.tooltip }}</div>
			</template>

			<div class="aioseo-settings-row aioseo-section-description">
				{{ strings.description }}

				<span
					v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'rssContent', true)"
				/>

				<core-alert
					v-if="optionsStore.options.searchAppearance.advanced.crawlCleanup.enable && !optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.global"
					type="red"
					v-html="strings.rssFeedDisabled"
				/>
			</div>

			<core-settings-row
				:name="$constants.GLOBAL_STRINGS.preview"
			>
				<template #content>
					<base-button
						size="medium"
						type="blue"
						tag="a"
						:href="rootStore.aioseo.urls.feeds.global"
						target="_blank"
						:disabled="optionsStore.options.searchAppearance.advanced.crawlCleanup.enable && !optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.global"
					>
						<svg-external />
						{{ strings.openYourRssFeed }}
					</base-button>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.rssBeforeContent"
			>
				<template #content>
					<core-html-tags-editor
						checkUnfilteredHtml
						v-model="optionsStore.options.rssContent.before"
						:minimum-line-numbers="5"
						tags-context="rss"
						:default-tags="[
							'post_link',
							'site_link',
							'author_link'
						]"
						:disabled="optionsStore.options.searchAppearance.advanced.crawlCleanup.enable && !optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.global"
					/>

					<div class="aioseo-description">
						{{ strings.beforeRssDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.rssAfterContent"
			>
				<template #content>
					<core-html-tags-editor
						checkUnfilteredHtml
						v-model="optionsStore.options.rssContent.after"
						:minimum-line-numbers="5"
						tags-context="rss"
						:default-tags="[
							'post_link',
							'site_link',
							'author_link'
						]"
						:disabled="optionsStore.options.searchAppearance.advanced.crawlCleanup.enable && !optionsStore.options.searchAppearance.advanced.crawlCleanup.feeds.global"
					/>

					<div class="aioseo-description">
						{{ strings.afterRssDescription }}
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

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import SvgExternal from '@/vue/components/common/svg/External'
export default {
	setup () {
		return {
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		CoreAlert,
		CoreCard,
		CoreHtmlTagsEditor,
		CoreSettingsRow,
		SvgExternal
	},
	data () {
		return {
			strings : {
				tooltip         : this.$t.__('Automatically add content to your site\'s RSS feed.', this.$td),
				description     : this.$t.__('This feature is used to automatically add content to your site\'s RSS feed. More specifically, it allows you to add links back to your blog and your blog posts so scrapers will automatically add these links too. This helps search engines identify you as the original source of the content.', this.$td),
				learnMore       : this.$t.__('Learn more', this.$td),
				rssFeedDisabled : this.$t.sprintf(
					// Translators: 1 - Opening link tag, 2 - Closing link tag.
					this.$t.__('Your RSS feed has been disabled. Disabling the global RSS feed is NOT recommended. This will prevent users from subscribing to your content and can hurt your SEO rankings. You can re-enable the global RSS feed in the %1$scrawl content settings%2$s.', this.$td),
					'<a href="' + this.rootStore.aioseo.urls.aio.searchAppearance + '&aioseo-scroll=crawl-content-global-feed&aioseo-highlight=crawl-content-global-feed#/advanced">',
					'</a>'
				),
				rssContent           : this.$t.__('RSS Content Settings', this.$td),
				openYourRssFeed      : this.$t.__('Open Your RSS Feed', this.$td),
				rssBeforeContent     : this.$t.__('RSS Before Content', this.$td),
				rssAfterContent      : this.$t.__('RSS After Content', this.$td),
				beforeRssDescription : this.$t.__('Add content before each post in your site feed.', this.$td),
				afterRssDescription  : this.$t.__('Add content after each post in your site feed.', this.$td),
				unfilteredHtmlError  : this.$t.sprintf(
					// Translators: 1 - Learn more link.
					this.$t.__('Your user account role does not have access to edit this field. %1$s', this.$td),
					this.$links.getDocLink(this.$constants.GLOBAL_STRINGS.learnMore, 'unfilteredHtml', true)
				)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-rss-content {
	svg.aioseo-external {
		width: 14px;
		height: 14px;
		margin-right: 10px;
	}

	.no-access {
		margin-bottom: 30px;
	}

	.aioseo-alert {
		margin-top: 10px;
	}

	.aioseo-rss-content-advanced {
		.aioseo-settings-row {
			.aioseo-col {
				padding-top: 0;
			}
		}
	}
}
</style>