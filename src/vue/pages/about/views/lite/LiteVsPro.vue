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
				:href="$links.utmUrl('about-us-page', 'header-button')"
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
				:href="$links.utmUrl('about-us-page', 'footer-button')"
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
import CoreAlert from '@/vue/components/common/core/alert/Index'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgClose from '@/vue/components/common/svg/Close'
export default {
	components : {
		CoreAlert,
		SvgCircleCheck,
		SvgClose
	},
	data () {
		return {
			strings : {
				header : {
					title : this.$t.sprintf(
						// Translators: 1 - The abbreviated plugin name ("AIOSEO").
						this.$t.__('%1$s Lite vs. Pro', this.$td),
						import.meta.env.VITE_SHORT_NAME
					),
					description : this.$t.sprintf(
						// Translators: 1 - The plugin name ("All in One SEO").
						this.$t.__('Get the most out of %1$s by upgrading to Pro and unlocking all of the powerful features.', this.$td),
						import.meta.env.VITE_NAME
					)
				},
				grid : {
					features : this.$t.__('Features:', this.$td)
				},
				cta : {
					title       : this.$t.__('Upgrade to Pro to Unlock Powerful SEO Features', this.$td),
					description : this.$t.sprintf(
						// Translators: 1 - Plugin name ("All in One SEO"), 2 - The number of active users, 3 - Plugin short name ("AIOSEO").
						this.$t.__('%1$s is the best WordPress SEO plugin. Join over %2$s Professionals who are already using %3$s to improve their website search rankings.', this.$td),
						import.meta.env.VITE_NAME,
						'3,000,000+',
						import.meta.env.VITE_SHORT_NAME
					),
					button : this.$t.sprintf(
						// Translators: 1 - "Pro".
						this.$t.__('Upgrade to %1$s Today', this.$td),
						'Pro'
					),
					bonus : this.$t.sprintf(
						// Translators: 1 - Opening bold tag, 2 - Closing bold tag, 3 - "Pro", 4 - Opening bold tag, 5 - A discount percentage (e.g. "50%"), 6 - Closing bold tag.
						this.$t.__('%1$sBonus:%2$s You can upgrade to the %3$s plan today and %4$ssave %5$s off%6$s (discount auto-applied).', this.$td),
						'<strong>',
						'</strong>',
						'Pro',
						'<strong>',
						this.$constants.DISCOUNT_PERCENTAGE,
						'</strong>'
					)
				}
			},
			features : {
				seo : {
					name : this.$t.__('Search Engine Optimization (SEO)', this.$td),
					lite : {
						title       : this.$t.__('Limited Support', this.$td),
						description : this.$t.__('Posts, Pages and custom Post Types only', this.$td)
					},
					pro : {
						title       : this.$t.__('Complete Support', this.$td),
						description : this.$t.__('Posts, Pages, custom Post Types + Categories, Tags and custom Taxonomies', this.$td)
					}
				},
				socialMeta : {
					name : this.$t.__('Social Meta (Open Graph Markup)', this.$td),
					lite : {
						title       : this.$t.__('Limited Support', this.$td),
						description : this.$t.__('Posts, Pages and custom Post Types only', this.$td)
					},
					pro : {
						title       : this.$t.__('Complete Support', this.$td),
						description : this.$t.__('Posts, Pages, custom Post Types + Categories, Tags and custom Taxonomies', this.$td)
					}
				},
				searchStatistics : {
					name : this.$t.__('Google Search Console Integration', this.$td),
					lite : {
						title : this.$t.__('Not Available', this.$td)
					},
					pro : {
						title       : this.$t.__('Included as Pro Feature', this.$td),
						description : this.$t.__('Connect with Google Search Console to track how your site is performing in search rankings and generate reports with actionable insights that help you get the most out of your content. (Elite plan only)', this.$td)
					}
				},
				openai : {
					name : this.$t.__('Open AI Integration', this.$td),
					lite : {
						title : this.$t.__('Not Available', this.$td)
					},
					pro : {
						title       : this.$t.__('Included as Pro Feature', this.$td),
						description : this.$t.__('Use the power of ChatGPT to generate engaging SEO titles and meta descriptions for your posts.', this.$td)
					}
				},
				wooCommerce : {
					name : this.$t.__('WooCommerce Integration', this.$td),
					lite : {
						title       : this.$t.__('Limited Support', this.$td),
						description : this.$t.__('WooCommerce Products only', this.$td)
					},
					pro : {
						title       : this.$t.__('Complete Support', this.$td),
						description : this.$t.__('WooCommerce Products, Product Categories, Product Tags, Product Attributes + WooCommerce smart tags (price, brand, etc.)', this.$td)
					}
				},
				schema : {
					name : this.$t.__('Schema Rich Snippets', this.$td),
					lite : {
						title       : this.$t.__('Limited Support', this.$td),
						description : this.$t.__('Posts, Pages, Categories and Tags only', this.$td)
					},
					pro : {
						title       : this.$t.__('Complete Support', this.$td),
						description : this.$t.__('Posts, Pages, Categories, Tags + Breadcrumb Navigation + advanced graphs (Product, FAQ Page, Recipe, etc.)', this.$td)
					}
				},
				breadcrumbs : {
					name : this.$t.__('Visual Breadcrumb Trails', this.$td),
					lite : {
						title       : this.$t.__('Limited Support', this.$td),
						description : this.$t.__('Default template for all pages.', this.$td)
					},
					pro : {
						title       : this.$t.__('Complete Support', this.$td),
						description : this.$t.__('Granular control over the template for each post type, taxonomy and archive.', this.$td)
					}
				},
				sitemap : {
					name : this.$t.__('XML Sitemap', this.$td),
					lite : {
						title       : this.$t.__('Limited Support', this.$td),
						description : this.$t.__('Control the priority & frequency per Post Type/Taxonomy', this.$td)
					},
					pro : {
						title       : this.$t.__('Complete Support', this.$td),
						description : this.$t.__('Control the priority & frequency of each Post, Page, Category, Tag, etc.', this.$td)
					}
				},
				videoSitemap : {
					name : this.$t.__('Video Sitemap', this.$td),
					lite : {
						title : this.$t.__('Not Available', this.$td)
					},
					pro : {
						title       : this.$t.__('Available as Addon Plugin', this.$td),
						description : this.$t.__('Submit your videos to search engines (Pro & Elite plans only)', this.$td)
					}
				},
				newsSitemap : {
					name : this.$t.__('News Sitemap', this.$td),
					lite : {
						title : this.$t.__('Not Available', this.$td)
					},
					pro : {
						title       : this.$t.__('Available as Addon Plugin', this.$td),
						description : this.$t.__('Submit your latest news stories to Google News (Pro & Elite plans only)', this.$td)
					}
				},
				imageSeo : {
					name : this.$t.__('Image SEO', this.$td),
					lite : {
						title : this.$t.__('Not Available', this.$td)
					},
					pro : {
						title       : this.$t.__('Available as Addon Plugin', this.$td),
						description : this.$t.__('Control the title, alt tag, caption, description and filename of your images (Plus, Pro & Elite plans only)', this.$td)
					}
				},
				localSeo : {
					name : this.$t.__('Local SEO', this.$td),
					lite : {
						title : this.$t.__('Not Available', this.$td)
					},
					pro : {
						title       : this.$t.__('Available as Addon Plugin', this.$td),
						description : this.$t.__('Local Business schema (multiple locations supported) + Business Info & Location blocks, widgets & shortcodes (Plus, Pro & Elite plans only)', this.$td)
					}
				},
				redirectionManager : {
					name : this.$t.__('Redirection Manager', this.$td),
					lite : {
						title : this.$t.__('Not Available', this.$td)
					},
					pro : {
						title       : this.$t.__('Available as Addon Plugin', this.$td),
						description : this.$t.__('Create, manage and monitor redirects for 404\'s or modified posts + server redirects, full site redirects and site aliases (Pro & Elite plans only)', this.$td)
					}
				},
				linkAssistant : {
					name : this.$t.__('Link Assistant', this.$td),
					lite : {
						title : this.$t.__('Not Available', this.$td)
					},
					pro : {
						title       : this.$t.__('Available as Addon Plugin', this.$td),
						description : this.$t.__('View detailed link & domain reports, manage existing links and discover new internal linking opportunities through smart suggestions (Pro & Elite plans only)', this.$td)
					}
				},
				seoRevisions : {
					name : this.$t.__('SEO Revisions', this.$td),
					lite : {
						title : this.$t.__('Not Available', this.$td)
					},
					pro : {
						title       : this.$t.__('Included as Pro Feature', this.$td),
						description : this.$t.__('SEO Revisions provide a historical record of SEO updates, allowing you to monitor the effectiveness of your SEO efforts and make informed decisions. (Plus, Pro & Elite plans only)', this.$td)
					}
				},
				restApi : {
					name : this.$t.__('REST API', this.$td),
					lite : {
						title : this.$t.__('Not Available', this.$td)
					},
					pro : {
						title       : this.$t.__('Available as Addon Plugin', this.$td),
						description : this.$t.__('Manage your post and term SEO meta via the WordPress REST API. This addon also works seamlessly with headless WordPress installs. (Plus, Pro & Elite plans only)', this.$td)
					}
				},
				support : {
					name : this.$t.__('Customer Support', this.$td),
					lite : {
						title : this.$t.__('Limited Support', this.$td)
					},
					pro : {
						title : this.$t.__('Priority Support', this.$td)
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