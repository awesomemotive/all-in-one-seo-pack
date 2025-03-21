<template>
	<div class="aioseo-getting-started">
		<core-getting-started
			v-if="allowed('aioseo_setup_wizard')"
			disable-close
		/>

		<cta
			v-if="!rootStore.isPro"
			class="aioseo-getting-started-cta"
			:type="2"
			:floating="false"
			:button-text="strings.cta.button"
			:cta-link="links.utmUrl('getting-started', 'main-cta')"
			:learn-more-link="links.getUpsellUrl('getting-started', 'main-cta', rootStore.isPro ? 'pricing' : 'liteUpgrade')"
			:feature-list="UPSELL_FEATURE_LIST"
			:showLink="false"
		>
			<template #header-text>
				{{ strings.cta.header }}
			</template>

			<template #description>
				{{ upgradeToday }}
			</template>

			<template #featured-image>
				<img
					alt="Getting Started with AIOSEO"
					:src="getAssetUrl(ctaImg)"
				/>
			</template>
		</cta>

		<!--div class="aioseo-getting-started-videos">
			<grid-row class="header">
				<grid-column
					class="header-title"
					sm="6"
					md="6"
				>
					{{ strings.videos.title }}
				</grid-column>

				<grid-column
					class="header-link"
					sm="6"
					md="6"
				>
					<a
						:href="strings.videos.linkUrl"
						target="_blank"
					>
						{{ strings.videos.linkText }} →
					</a>
				</grid-column>
			</grid-row>

			<grid-row class="videos">
				<grid-column
					sm="12"
					md="6"
					v-for="(video, index) in videos"
					:key="index"
				>
					<a
						class="video"
						target="_blank"
						:href="video.url"
						:title="video.title"
					>
						<img
							:src="getAssetUrl(thumbnailImg)"
						/>
						<div>{{ video.title }}</div>
					</a>
				</grid-column>
			</grid-row>
		</div-->

		<div class="aioseo-getting-started-documentation">
			<grid-row class="header">
				<grid-column
					class="header-title"
					sm="6"
					md="6"
				>
					{{ strings.documentation.title }}
				</grid-column>

				<grid-column
					sm="6"
					md="6"
					class="header-link"
				>
					<a
						:href="strings.documentation.linkUrl"
						target="_blank"
					>
						{{ strings.documentation.linkText }} →
					</a>
				</grid-column>
			</grid-row>

			<grid-row class="docs">
				<grid-column
					class="doc"
					v-for="(doc, index) in docs"
					:key="index"
					sm="12"
					md="6"
				>
					<div class="d-flex">
						<svg-book />
						<a
							:href="doc.url"
							target="_blank"
						>
							{{ doc.title }}
						</a>
					</div>
				</grid-column>
			</grid-row>
		</div>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import { UPSELL_FEATURE_LIST } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { getAssetUrl } from '@/vue/utils/helpers'
import ctaImg from '@/vue/assets/images/upsells/news-sitemap.png'
// import thumbnailImg from '@/vue/assets/images/about/thumbnail.jpg'
import CoreGettingStarted from '@/vue/components/common/core/GettingStarted'
import Cta from '@/vue/components/common/cta/Index'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgBook from '@/vue/components/common/svg/Book'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		CoreGettingStarted,
		Cta,
		GridColumn,
		GridRow,
		SvgBook
	},
	data () {
		return {
			UPSELL_FEATURE_LIST,
			links,
			allowed,
			ctaImg,
			// thumbnailImg,
			strings : {
				cta : {
					title : sprintf(
						// Translators: 1 - The plugin short name ("AIOSEO"), 2 - "Pro" string.
						__('Get %1$s %2$s and Unlock all the Powerful Features', td),
						import.meta.env.VITE_SHORT_NAME,
						'Pro'
					),
					header : sprintf(
						// Translators: 1 - The plugin short name ("AIOSEO"), 2 - "Pro" string.
						__('Get %1$s %2$s and Unlock all the Powerful Features.', td),
						import.meta.env.VITE_SHORT_NAME,
						'Pro'
					),
					button : sprintf(
						// Translators: 1 - "Pro".
						__('Upgrade to %1$s Today', td),
						'Pro'
					)
				},
				videos : {
					title    : __('Video Tutorials', td),
					linkText : __('View all video tutorials', td),
					linkUrl  : 'https://changeme'
				},
				documentation : {
					title : sprintf(
						// Translators: 1 - The plugin short name ("AIOSEO").
						__('%1$s Documentation', td),
						import.meta.env.VITE_SHORT_NAME
					),
					linkText : __('See our full documentation', td),
					linkUrl  : links.getDocUrl('home')
				}
			},
			videos : {
				video1 : {
					title : __('Basic Guide to Google Analytics', td),
					url   : 'https://changeme'
				},
				video2 : {
					title : __('Basic Guide to Google Search Console', td),
					url   : 'https://changeme'
				},
				video3 : {
					title : __('Best Practices for Domains and URLs', td),
					url   : 'https://changeme'
				},
				video4 : {
					title : __('How to Control Search Results', td),
					url   : 'https://changeme'
				},
				video5 : {
					title : sprintf(
						// Translators: 1 - The plugin short name ("AIOSEO Pro"), 2 - "Pro" string.
						__('Installing %1$s %2$s', td),
						import.meta.env.VITE_SHORT_NAME,
						'Pro'
					),
					url : 'https://changeme'
				},
				video6 : {
					title : __('Optimizing your Content Headings', td),
					url   : 'https://changeme'
				}
			},
			docs : {
				doc1 : {
					title : 'How do I use your API code examples?',
					url   : links.getDocUrl('apiCodeExamples')
				},
				doc2 : {
					title : 'What are media attachments and should I submit them to search engines?',
					url   : links.getDocUrl('whatAreMediaAttachments')
				},
				doc3 : {
					title : 'When to use NOINDEX or the robots.txt?',
					url   : links.getDocUrl('whenToUseNoindex')
				},
				doc4 : {
					title : 'How do I troubleshoot issues with AIOSEO?',
					url   : links.getDocUrl('troubleshootIssues')
				},
				doc5 : {
					title : 'How does the import process for SEO data work?',
					url   : links.getDocUrl('importProcessSeoData')
				},
				doc6 : {
					title : 'Installation instructions for AIOSEO Pro',
					url   : links.getDocUrl('installAioseoPro')
				},
				doc87 : {
					title : 'What are the minimum requirements for All in One SEO?',
					url   : links.getDocUrl('minimumRequirements')
				}
			}
		}
	},
	computed : {
		upgradeToday () {
			return sprintf(
				// Translators: 1 - Plugin short name ("AIOSEO"), 2 - "Pro".
				__('%1$s %2$s comes with many additional features to help take your site\'s SEO to the next level!', td),
				import.meta.env.VITE_SHORT_NAME,
				'Pro'
			)
		}
	},
	methods : {
		getAssetUrl
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-getting-started {

	.aioseo-getting-started-cta {
		margin-block: var(--aioseo-gutter);
	}

	.aioseo-getting-started-videos,
	.aioseo-getting-started-documentation {
		margin-top: var(--aioseo-gutter);
		background: #fff;
		width: 100%;
		padding: 40px;
		box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);
		border: 1px solid $border;
		color: $black;

		a {
			text-decoration: none;
		}

		.header {
			align-items: center;
			font-weight: bold;

			.header-title {
				font-size: 28px;
				line-height: 40px;
			}

			.header-link {
				// This is needed to override the text-align value of the grid row.
				display: flex;
				justify-content: flex-end;

				a {
					text-decoration: underline;
					color: $blue;
				}

				@media screen and (max-width: 782px) {
					justify-content: start !important;
				}
			}
		}
		.videos,
		.docs {
			margin: var(--aioseo-gutter) 0;
			font-weight: bold;
			font-size: 14px;
			line-height: 22px;
			color: $black;
		}
	}
	.aioseo-getting-started-videos {
		.videos {
			div {
				padding: 5px 16px 5px 0;
			}
			.video {
				display: flex;
				align-items: center;
				padding: 16px;
				background-color: $box-background;
				color: $black;
				img {
					flex: 1 1 auto;
					max-width: 180px;
					max-height: 100px;
				}
				div {
					flex: 2 2 auto;
					margin: 0 0 0 var(--aioseo-gutter);
				}
				@media screen and (max-width: 520px) {
					flex-direction: column;
					img {
						margin: 0 0 10px 0;
						max-width: 100%;
					}
					div {
						margin: 0;
						text-align: center;
					}
				}
			}
		}
	}
	.aioseo-getting-started-documentation {
		.docs {
			.d-flex {
				align-items: flex-start;
			}
			.doc {
				svg {
					margin: 1px 8px 0 0;
					width: 16px;
					height: 16px;
					color: $blue;
				}
				a {
					color: $black;
				}
			}
		}
	}
}
</style>