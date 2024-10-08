<template>
	<div class="aioseo-tab-content aioseo-link-assistant">
		<div v-if="'metabox' === $root.$data.screenContext">
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
				:cta-link="links.getPricingUrl('link-assistant', 'link-assistant-upsell', 'metabox')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="links.getUpsellUrl('link-assistant', 'metabox', rootStore.isPro ? 'pricing' : 'liteUpgrade')"
				:feature-list="[
					strings.linkOpportunities,
					strings.domainReports,
					strings.orphanedPosts,
					strings.affiliateLinks
				]"
				:hide-bonus="!licenseStore.isUnlicensed"
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

		<core-modal
			:show="postEditorStore.currentPost.linkAssistant.modalOpen && 'sidebar' === $root.$data.screenContext"
			:classes="[ 'aioseo-link-assistant-modal' ]"
			@close="postEditorStore.currentPost.linkAssistant.modalOpen = false"
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
					:cta-link="links.getPricingUrl('link-assistant', 'link-assistant-upsell', 'metabox')"
					:button-text="strings.ctaButtonText"
					:learn-more-link="links.getUpsellUrl('link-assistant', 'metabox', rootStore.isPro ? 'pricing' : 'liteUpgrade')"
					:feature-list="[
						strings.linkOpportunities,
						strings.domainReports,
						strings.orphanedPosts,
						strings.affiliateLinks
					]"
					:hide-bonus="!licenseStore.isUnlicensed"
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
		</core-modal>

		<links-side-bar v-if="'modal' !== parentComponentContext" />
	</div>
</template>

<script>
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreModal from '@/vue/components/common/core/modal/Index'
import Cta from '@/vue/components/common/cta/Index'
import LinksSideBar from './../../LinksSideBar'
import LinkAssistantInboundInternal from '@/vue/components/common/link-assistant/InboundInternal'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
import SvgLinkAffiliate from '@/vue/components/common/svg/link/Affiliate'
import SvgLinkExternal from '@/vue/components/common/svg/link/External'
import SvgLinkInternalInbound from '@/vue/components/common/svg/link/InternalInbound'
import SvgLinkInternalOutbound from '@/vue/components/common/svg/link/InternalOutbound'
import SvgLinkSuggestion from '@/vue/components/common/svg/link/Suggestion'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			licenseStore    : useLicenseStore(),
			postEditorStore : usePostEditorStore(),
			rootStore       : useRootStore(),
			links
		}
	},
	components : {
		CoreAlert,
		CoreBlur,
		CoreMainTabs,
		CoreModal,
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
									editLink  : '#',
									postType  : {
										singular : 'Post'
									}
								}
							},
							{
								phrase  : 'AIOSEO also allows you to create video sitemaps and comes with tons of different SEO tools.',
								anchor  : 'video sitemaps',
								url     : '#',
								context : {
									postTitle : 'How to Create WordPress Image Sitemap',
									permalink : '#',
									editLink  : '#',
									postType  : {
										singular : 'Post'
									}
								}
							},
							{
								phrase  : 'You may also want to check out how to name your image files for SEO in WordPress and write alt text for images.',
								anchor  : 'how to name your image files for SEO in WordPress',
								url     : '#',
								context : {
									postTitle : 'What’s the Best Image Format for Your Website?',
									permalink : '#',
									editLink  : '#',
									postType  : {
										singular : 'Post'
									}
								}
							},
							{
								phrase  : 'Are you looking for an easy way to do an SEO analysis for your website?',
								anchor  : 'SEO analysis',
								url     : '#',
								context : {
									postTitle : 'How to Perform an SEO Analysis on Your WordPress Website',
									permalink : '#',
									editLink  : '#',
									postType  : {
										singular : 'Post'
									}
								}
							},
							{
								phrase  : 'To learn more, please check out our post on image SEO: how to best optimize your images in WordPress. ',
								anchor  : 'image SEO',
								url     : '#',
								context : {
									postTitle : 'How to Write Alt Text for Images for SEO',
									permalink : '#',
									editLink  : '#',
									postType  : {
										singular : 'Post'
									}
								}
							},
							{
								phrase  : 'So let’s now move on to showing you exactly how easy it is to import and export redirects using the best redirect plugin for WordPress.',
								anchor  : 'best redirect plugin for WordPress',
								url     : '#',
								context : {
									postTitle : 'How to Easily Import and Export Redirects in WordPress',
									permalink : '#',
									editLink  : '#',
									postType  : {
										singular : 'Post'
									}
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
				ctaButtonText : __('Unlock Link Assistant', td),
				ctaHeader     : sprintf(
					// Translators: 1 - "PRO".
					__('Link Assistant is a %1$s Feature', td),
					'PRO'
				),
				linkAssistantDescription : __('Get relevant suggestions for adding internal links to all your content as well as finding any orphaned posts that have no internal links.', td),
				thisFeatureRequires      : __('This feature requires one of the following plans:', td),
				linkOpportunities        : __('Actionable Link Suggestions', td),
				orphanedPosts            : __('See Orphaned Posts', td),
				affiliateLinks           : __('See Affiliate Links', td),
				domainReports            : __('Top Domain Reports', td)
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
					name : sprintf(
						'%1$s %2$s',
						this.mockedPostData.links.inboundInternal.totals.total,
						__('Inbound Internal', td)
					)
				},
				{
					slug : 'outbound-internal',
					icon : 'svg-link-internal-outbound',
					name : sprintf(
						'%1$s %2$s',
						this.mockedPostData.links.outboundInternal.totals.total,
						__('Outbound Internal', td)
					)
				},
				{
					slug : 'affiliate',
					icon : 'svg-link-affiliate',
					name : sprintf(
						'%1$s %2$s',
						this.mockedPostData.links.affiliate.totals.total,
						__('Affiliate', td)
					)
				},
				{
					slug : 'external',
					icon : 'svg-link-external',
					name : sprintf(
						'%1$s %2$s',
						this.mockedPostData.links.external.totals.total,
						__('External', td)
					)
				},
				{
					slug : 'link-suggestions',
					icon : 'svg-link-suggestion',
					name : sprintf(
						'%1$s %2$s',
						this.mockedPostData.links.suggestionsOutbound.totals.total + this.mockedPostData.links.suggestionsInbound.totals.total,
						__('Link Suggestions', td)
					)
				}
			]
		},
		modalHeader () {
			return sprintf(
				// Translators: 1 - The post title.
				__('Links & Suggestions for "%1$s"', td),
				window.wp.data.select('core/editor').getEditedPostAttribute('title')
			)
		}
	}
}
</script>