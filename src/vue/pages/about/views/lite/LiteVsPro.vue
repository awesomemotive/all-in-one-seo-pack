<template>
	<div class="aioseo-lite-vs-pro">
		<div class="aioseo-lite-vs-pro-header">
			<div>
				<div class="header-title">{{ strings.header.title }}</div>

				<div class="header-text">{{ strings.header.description }}</div>
			</div>

			<base-button
				type="green"
				tag="a"
				:href="links.utmUrl('about-us-page', 'header-button')"
				target="_blank"
			>
				{{ strings.cta.button }}
			</base-button>
		</div>

		<div class="aioseo-lite-vs-pro-grid">
			<div class="header">
				<div>{{ strings.grid.features }}</div>

				<div class="header-lite">Lite</div>

				<div class="header-pro">Pro</div>
			</div>

			<hr />

			<div>
				<div v-for="(feature, index) in features" :key="index">
					<div class="feature">
						<div>{{ feature.name }}</div>

						<svg-close />

						<div>
							<div class="feature-title">{{ feature.lite.title }}</div>
							<div v-if="feature.lite.description">{{ feature.lite.description }}</div>
						</div>

						<svg-circle-check />

						<div>
							<div class="feature-title">{{ feature.pro.title }}</div>
							<div v-if="feature.pro.description">{{ feature.pro.description }}</div>
						</div>
					</div>

					<hr />
				</div>
			</div>
		</div>

		<div class="aioseo-lite-vs-pro-cta">
			<div class="cta-title">{{ strings.cta.title }}</div>

			<div class="cta-text">{{ strings.cta.description }}</div>

			<base-button
				type="green"
				tag="a"
				:href="links.utmUrl('about-us-page', 'footer-button')"
				target="_blank"
			>
				{{ strings.cta.button }}
			</base-button>

			<core-alert
				class="cta-text bonus-alert"
				type="yellow"
			>
				🎁 <span v-html="strings.cta.bonus" />
			</core-alert>
		</div>
	</div>
</template>

<script>
import { DISCOUNT_PERCENTAGE } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgClose from '@/vue/components/common/svg/Close'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreAlert,
		SvgCircleCheck,
		SvgClose
	},
	data () {
		return {
			links,
			strings : {
				header : {
					title : sprintf(
						// Translators: 1 - The abbreviated plugin name ("AIOSEO").
						__('%1$s Lite vs. Pro', td),
						import.meta.env.VITE_SHORT_NAME
					),
					description : sprintf(
						// Translators: 1 - The plugin name ("All in One SEO").
						__('Get the most out of %1$s by upgrading to Pro and unlocking all of the powerful features.', td),
						import.meta.env.VITE_NAME
					)
				},
				grid : {
					features : __('Features:', td)
				},
				cta : {
					title       : __('Upgrade to Pro to Unlock Powerful SEO Features', td),
					description : sprintf(
						// Translators: 1 - Plugin name ("All in One SEO"), 2 - The number of active users, 3 - Plugin short name ("AIOSEO").
						__('%1$s is the best WordPress SEO plugin. Join over %2$s Professionals who are already using %3$s to improve their website search rankings.', td),
						import.meta.env.VITE_NAME,
						'3,000,000+',
						import.meta.env.VITE_SHORT_NAME
					),
					button : sprintf(
						// Translators: 1 - "Pro".
						__('Upgrade to %1$s Today', td),
						'Pro'
					),
					bonus : sprintf(
						// Translators: 1 - Opening bold tag, 2 - Closing bold tag, 3 - "Pro", 4 - Opening bold tag, 5 - A discount percentage (e.g. "50%"), 6 - Closing bold tag.
						__('%1$sBonus:%2$s You can upgrade to the %3$s plan today and %4$ssave %5$s off%6$s (discount auto-applied).', td),
						'<strong>',
						'</strong>',
						'Pro',
						'<strong>',
						DISCOUNT_PERCENTAGE,
						'</strong>'
					)
				}
			},
			features : {
				seo : {
					name : __('Search Engine Optimization (SEO)', td),
					lite : {
						title       : __('Limited Support', td),
						description : __('Posts, Pages and custom Post Types only', td)
					},
					pro : {
						title       : __('Complete Support', td),
						description : __('Posts, Pages, custom Post Types + Categories, Tags and custom Taxonomies', td)
					}
				},
				socialMeta : {
					name : __('Social Meta (Open Graph Markup)', td),
					lite : {
						title       : __('Limited Support', td),
						description : __('Posts, Pages and custom Post Types only', td)
					},
					pro : {
						title       : __('Complete Support', td),
						description : __('Posts, Pages, custom Post Types + Categories, Tags and custom Taxonomies', td)
					}
				},
				searchStatistics : {
					name : __('Google Search Console Integration', td),
					lite : {
						title : __('Not Available', td)
					},
					pro : {
						title       : __('Included as Pro Feature', td),
						description : __('Connect with Google Search Console to track how your site is performing in search rankings and generate reports with actionable insights that help you get the most out of your content. (Elite plan only)', td)
					}
				},
				openai : {
					name : __('Open AI Integration', td),
					lite : {
						title : __('Not Available', td)
					},
					pro : {
						title       : __('Included as Pro Feature', td),
						description : __('Use the power of ChatGPT to generate engaging SEO titles and meta descriptions for your posts.', td)
					}
				},
				wooCommerce : {
					name : __('WooCommerce Integration', td),
					lite : {
						title       : __('Limited Support', td),
						description : __('WooCommerce Products only', td)
					},
					pro : {
						title       : __('Complete Support', td),
						description : __('WooCommerce Products, Product Categories, Product Tags, Product Attributes + WooCommerce smart tags (price, brand, etc.)', td)
					}
				},
				schema : {
					name : __('Schema Rich Snippets', td),
					lite : {
						title       : __('Limited Support', td),
						description : __('Posts, Pages, Categories and Tags only', td)
					},
					pro : {
						title       : __('Complete Support', td),
						description : __('Posts, Pages, Categories, Tags + Breadcrumb Navigation + advanced graphs (Product, FAQ Page, Recipe, etc.)', td)
					}
				},
				breadcrumbs : {
					name : __('Visual Breadcrumb Trails', td),
					lite : {
						title       : __('Limited Support', td),
						description : __('Default template for all pages.', td)
					},
					pro : {
						title       : __('Complete Support', td),
						description : __('Granular control over the template for each post type, taxonomy and archive.', td)
					}
				},
				sitemap : {
					name : __('XML Sitemap', td),
					lite : {
						title       : __('Limited Support', td),
						description : __('Control the priority & frequency per Post Type/Taxonomy', td)
					},
					pro : {
						title       : __('Complete Support', td),
						description : __('Control the priority & frequency of each Post, Page, Category, Tag, etc.', td)
					}
				},
				videoSitemap : {
					name : __('Video Sitemap', td),
					lite : {
						title : __('Not Available', td)
					},
					pro : {
						title       : __('Available as Addon Plugin', td),
						description : __('Submit your videos to search engines (Pro & Elite plans only)', td)
					}
				},
				newsSitemap : {
					name : __('News Sitemap', td),
					lite : {
						title : __('Not Available', td)
					},
					pro : {
						title       : __('Available as Addon Plugin', td),
						description : __('Submit your latest news stories to Google News (Pro & Elite plans only)', td)
					}
				},
				imageSeo : {
					name : __('Image SEO', td),
					lite : {
						title : __('Not Available', td)
					},
					pro : {
						title       : __('Available as Addon Plugin', td),
						description : __('Control the title, alt tag, caption, description and filename of your images (Plus, Pro & Elite plans only)', td)
					}
				},
				localSeo : {
					name : __('Local SEO', td),
					lite : {
						title : __('Not Available', td)
					},
					pro : {
						title       : __('Available as Addon Plugin', td),
						description : __('Local Business schema (multiple locations supported) + Business Info & Location blocks, widgets & shortcodes (Plus, Pro & Elite plans only)', td)
					}
				},
				redirectionManager : {
					name : __('Redirection Manager', td),
					lite : {
						title : __('Not Available', td)
					},
					pro : {
						title       : __('Available as Addon Plugin', td),
						description : __('Create, manage and monitor redirects for 404\'s or modified posts + server redirects, full site redirects and site aliases (Pro & Elite plans only)', td)
					}
				},
				linkAssistant : {
					name : __('Link Assistant', td),
					lite : {
						title : __('Not Available', td)
					},
					pro : {
						title       : __('Available as Addon Plugin', td),
						description : __('View detailed link & domain reports, manage existing links and discover new internal linking opportunities through smart suggestions (Pro & Elite plans only)', td)
					}
				},
				seoRevisions : {
					name : __('SEO Revisions', td),
					lite : {
						title : __('Not Available', td)
					},
					pro : {
						title       : __('Included as Pro Feature', td),
						description : __('SEO Revisions provide a historical record of SEO updates, allowing you to monitor the effectiveness of your SEO efforts and make informed decisions. (Plus, Pro & Elite plans only)', td)
					}
				},
				restApi : {
					name : __('REST API', td),
					lite : {
						title : __('Not Available', td)
					},
					pro : {
						title       : __('Available as Addon Plugin', td),
						description : __('Manage your post and term SEO meta via the WordPress REST API. This addon also works seamlessly with headless WordPress installs. (Plus, Pro & Elite plans only)', td)
					}
				},
				support : {
					name : __('Customer Support', td),
					lite : {
						title : __('Limited Support', td)
					},
					pro : {
						title : __('Priority Support', td)
					}
				}
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-lite-vs-pro {
	margin-top: var(--aioseo-gutter);
	width: 100%;
	padding: 2px;
	background: #fff;
	box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);
	border: 1px solid $border;
	color: $black;

	.aioseo-lite-vs-pro-header,
	.aioseo-lite-vs-pro-grid,
	.aioseo-lite-vs-pro-cta {
		padding: 40px;
	}
	.aioseo-lite-vs-pro-header,
	.aioseo-lite-vs-pro-cta {
		font-size: 14px;
		background-color: $box-background;
	}
	.aioseo-lite-vs-pro-header {
		display: flex;
		justify-content: space-between;
		align-items: center;

		div {
			margin: 0 30px 0 0;

			.header-title {
				font-size: 20px;
				font-weight: bold;
			}
			.header-text {
				margin: 5px 0;
			}
		}

		a {
			width: fit-content;
			min-width: 220px;
		}

		@media screen and (max-width: 782px) {
			flex-direction: column;
			align-items: start;

			div {
				.header-text {
					margin: 15px 0;
				}
			}
		}
	}
	.aioseo-lite-vs-pro-grid {
		font-size: 14px;

		.header {
			display: grid;
			grid-template-columns: 1.5fr 1fr 1fr;
			align-items: end;
			margin: 0 0 24px 0;
			font-weight: bold;

			.header-lite,
			.header-pro {
				font-size: 18px;
			}

			.header-pro {
				color: $green;
			}
		}
		.feature {
			display: grid;
			grid-template-columns: 1.5fr 30px 1fr 30px 1fr;

			> div {
				padding: 15px 6px;
			}

			svg {
				margin: 15px 0;

				&.aioseo-close {
					width: 13px;
					height: 13px;
					margin: 18px 0;
					color: $placeholder-color;
				}

				&.aioseo-circle-check {
					width: 18px;
					height: 18px;
					margin: 15px 0;
					color: $green;
				}
			}
			.feature-title {
				font-weight: bold;
			}
		}
	}

	.aioseo-lite-vs-pro-cta {
		text-align: center;

		.cta-title {
			font-size: 18px;
			font-weight: bold;
		}

		.cta-text {
			margin: 24px auto;
			max-width: 650px;
		}
	}
}
</style>