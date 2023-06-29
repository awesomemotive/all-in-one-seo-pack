<template>
	<div class="aioseo-tab-content aioseo-link-assistant">
		<div v-if="'metabox' === $root._data.screenContext">
			<core-blur>
				<core-main-tabs
					class="link-tabs"
					internal
					:tabs="tabs"
					:active="initialTab"
					:showSaveButton="false"
				>
					<template #var-tab-icon="{ tab }">
						<component
							:class="[
								{ warning: tab.warning }
							]"
							:is="tab.icon"
						/>
					</template>
				</core-main-tabs>

				<link-assistant-inbound-internal
					v-if="'inbound-internal' === activeTab"
					:post="mockedPostData"
					metabox
				/>
			</core-blur>

			<cta
				:cta-link="$links.getPricingUrl('link-assistant', 'link-assistant-upsell', 'metabox')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="$links.getUpsellUrl('link-assistant', 'metabox', 'home')"
				:feature-list="[
					strings.linkOpportunities,
					strings.domainReports,
					strings.orphanedPosts,
					strings.affiliateLinks
				]"
			>
				<template #header-text>
					{{ strings.ctaHeader }}
				</template>
				<template #description>
					<required-plans addon="aioseo-link-assistant" />

					{{ strings.linkAssistantDescription }}
				</template>
			</cta>
		</div>

		<core-modal-portal
			:classes="[ 'aioseo-link-assistant-modal' ]"
			v-if="postEditorStore.currentPost.linkAssistant.modalOpen && 'sidebar' === $root._data.screenContext"
			@close="postEditorStore.toggleLinkAssistantModal"
		>
			<template #headerTitle>
				{{ modalHeader }}
			</template>

			<template #body>
				<core-blur>
					<core-main-tabs
						class="link-tabs"
						internal
						:tabs="tabs"
						:active="initialTab"
						:showSaveButton="false"
					>
						<template #var-tab-icon="{ tab }">
							<component
								:class="[
									{ warning: tab.warning }
								]"
								:is="tab.icon"
							/>
						</template>
					</core-main-tabs>

					<link-assistant-inbound-internal
						v-if="'inbound-internal' === activeTab"
						:post="mockedPostData"
						metabox
					/>
				</core-blur>

				<cta
					:cta-link="$links.getPricingUrl('link-assistant', 'link-assistant-upsell', 'metabox')"
					:button-text="strings.ctaButtonText"
					:learn-more-link="$links.getUpsellUrl('link-assistant', 'metabox', 'home')"
					:feature-list="[
						strings.linkOpportunities,
						strings.domainReports,
						strings.orphanedPosts,
						strings.affiliateLinks
					]"
				>
					<template #header-text>
						{{ strings.ctaHeader }}
					</template>
					<template #description>
						<required-plans addon="aioseo-link-assistant" />

						{{ strings.linkAssistantDescription }}
					</template>
				</cta>
			</template>
		</core-modal-portal>

		<links-side-bar v-if="'modal' !== this.parentComponentContext" />
	</div>
</template>

<script>
import {
	usePostEditorStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreModalPortal from '@/vue/components/common/core/modal/Portal'
import Cta from '@/vue/components/common/cta/Index'
import LinksSideBar from './../../LinksSideBar'
import LinkAssistantInboundInternal from '@/vue/components/common/link-assistant/InboundInternal'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
import SvgLinkAffiliate from '@/vue/components/common/svg/link/Affiliate'
import SvgLinkExternal from '@/vue/components/common/svg/link/External'
import SvgLinkInternalInbound from '@/vue/components/common/svg/link/InternalInbound'
import SvgLinkInternalOutbound from '@/vue/components/common/svg/link/InternalOutbound'
import SvgLinkSuggestion from '@/vue/components/common/svg/link/Suggestion'
export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore()
		}
	},
	components : {
		CoreAlert,
		CoreBlur,
		CoreMainTabs,
		CoreModalPortal,
		Cta,
		LinkAssistantInboundInternal,
		LinksSideBar,
		SvgLinkAffiliate,
		SvgLinkExternal,
		SvgLinkInternalInbound,
		SvgLinkInternalOutbound,
		SvgLinkSuggestion,
		RequiredPlans
	},
	props : {
		parentComponentContext : String
	},
	data () {
		return {
			activeTab           : 'inbound-internal',
			activeSuggestionTab : 'suggestions-inbound',
			mockedPostData      : {
				links : {
					inboundInternal : {
						rows : [
							{
								phrase  : 'Are you looking for an easy way to do an SEO analysis for your website?',
								anchor  : 'SEO analysis',
								url     : '#',
								context : {
									postTitle : 'How to Perform an SEO Analysis on Your WordPress Website',
									permalink : '#',
									editLink  : '#'
								}
							},
							{
								phrase  : 'AIOSEO also allows you to create video sitemaps and comes with tons of different SEO tools.',
								anchor  : 'video sitemaps',
								url     : '#',
								context : {
									postTitle : 'How to Create WordPress Image Sitemap',
									permalink : '#',
									editLink  : '#'
								}
							},
							{
								phrase  : 'You may also want to check out how to name your image files for SEO in WordPress and write alt text for images.',
								anchor  : 'how to name your image files for SEO in WordPress',
								url     : '#',
								context : {
									postTitle : 'What’s the Best Image Format for Your Website?',
									permalink : '#',
									editLink  : '#'
								}
							},
							{
								phrase  : 'Are you looking for an easy way to do an SEO analysis for your website?',
								anchor  : 'SEO analysis',
								url     : '#',
								context : {
									postTitle : 'How to Perform an SEO Analysis on Your WordPress Website',
									permalink : '#',
									editLink  : '#'
								}
							},
							{
								phrase  : 'To learn more, please check out our post on image SEO: how to best optimize your images in WordPress. ',
								anchor  : 'image SEO',
								url     : '#',
								context : {
									postTitle : 'How to Write Alt Text for Images for SEO',
									permalink : '#',
									editLink  : '#'
								}
							},
							{
								phrase  : 'So let’s now move on to showing you exactly how easy it is to import and export redirects using the best redirect plugin for WordPress.',
								anchor  : 'best redirect plugin for WordPress',
								url     : '#',
								context : {
									postTitle : 'How to Easily Import and Export Redirects in WordPress',
									permalink : '#',
									editLink  : '#'
								}
							}
						],
						totals : {
							total : 6
						}
					},
					outboundInternal : {
						totals : {
							total : 12
						}
					},
					affiliate : {
						totals : {
							total : 8
						}
					},
					external : {
						totals : {
							total : 24
						}
					},
					suggestionsInbound : {
						totals : {
							total : 9
						}
					},
					suggestionsOutbound : {
						totals : {
							total : 9
						}
					}
				}
			},
			strings : {
				ctaButtonText : this.$t.sprintf(
					// Translators: 1 - "Pro".
					this.$t.__('Upgrade to %1$s and Unlock Link Assistant', this.$td),
					'Pro'
				),
				ctaHeader : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO"), 2 - "Pro".
					this.$t.__('Link Assistant is only available for licensed %1$s %2$s users.', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					'Pro'
				),
				linkAssistantDescription : this.$t.__('Get relevant suggestions for adding internal links to all your content as well as finding any orphaned posts that have no internal links.', this.$td),
				thisFeatureRequires      : this.$t.__('This feature requires one of the following plans:', this.$td),
				linkOpportunities        : this.$t.__('Actionable Link Suggestions', this.$td),
				orphanedPosts            : this.$t.__('See Orphaned Posts', this.$td),
				affiliateLinks           : this.$t.__('See Affiliate Links', this.$td),
				domainReports            : this.$t.__('Top Domain Reports', this.$td)
			}
		}
	},
	computed : {
		initialTab () {
			return 'inbound-internal'
		},
		tabs () {
			return [
				{
					slug : 'inbound-internal',
					icon : 'svg-link-internal-inbound',
					name : this.$t.sprintf(
						'%1$s %2$s',
						this.mockedPostData.links.inboundInternal.totals.total,
						this.$t.__('Inbound Internal', this.$td)
					)
				},
				{
					slug : 'outbound-internal',
					icon : 'svg-link-internal-outbound',
					name : this.$t.sprintf(
						'%1$s %2$s',
						this.mockedPostData.links.outboundInternal.totals.total,
						this.$t.__('Outbound Internal', this.$td)
					)
				},
				{
					slug : 'affiliate',
					icon : 'svg-link-affiliate',
					name : this.$t.sprintf(
						'%1$s %2$s',
						this.mockedPostData.links.affiliate.totals.total,
						this.$t.__('Affiliate', this.$td)
					)
				},
				{
					slug : 'external',
					icon : 'svg-link-external',
					name : this.$t.sprintf(
						'%1$s %2$s',
						this.mockedPostData.links.external.totals.total,
						this.$t.__('External', this.$td)
					)
				},
				{
					slug : 'link-suggestions',
					icon : 'svg-link-suggestion',
					name : this.$t.sprintf(
						'%1$s %2$s',
						this.mockedPostData.links.suggestionsOutbound.totals.total + this.mockedPostData.links.suggestionsInbound.totals.total,
						this.$t.__('Link Suggestions', this.$td)
					)
				}
			]
		},
		modalHeader () {
			return this.$t.sprintf(
				// Translators: 1 - The post title.
				this.$t.__('Links & Suggestions for "%1$s"', this.$td),
				window.wp.data.select('core/editor').getEditedPostAttribute('title')
			)
		}
	}
}
</script>