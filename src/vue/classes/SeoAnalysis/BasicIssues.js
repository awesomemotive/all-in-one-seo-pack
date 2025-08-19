/* eslint-disable no-unused-vars */
import { __, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

class BasicIssues {
	get title () {
		const fixActionParams = '&aioseo-scroll=aioseo-post-settings-post-title-row&aioseo-highlight=aioseo-post-settings-post-title-row'

		return {
			'title-missing' : {
				title       : __('No SEO title found.', td),
				text        : (result) => __('No SEO title found.', td),
				description : __('The SEO title is the title of your page that appears in search results. It should be 40 to 60 characters long, include your focus keyword and incentivize readers to click through to your page.', td),
				fixActionParams
			},
			'title-too-long' : {
				title : __('The SEO title is too long.', td),
				text  : (result) => sprintf(
					// Translator: 1: The length of the SEO title.
					__('The SEO title is %1$d characters long, which is higher than our recommended range of 40-60 characters.', td),
					result?.length
				),
				description : __('The SEO title is the title of your page that appears in search results. It should be 40 to 60 characters long, include your focus keyword and incentivize readers to click through to your page.', td),
				fixActionParams
			},
			'title-too-short' : {
				title : __('The SEO title is too short.', td),
				text  : (result) => {
					if (!result?.length) {
						return __('No SEO title found.', td)
					}

					return sprintf(
						// Translator: 1: The length of the SEO title.
						__('The SEO title is %1$d characters long, which is lower than our recommended range of 40-60 characters.', td),
						result?.length
					)
				},
				description : __('The SEO title is the title of your page that appears in search results. It should be 40 to 60 characters long, include your focus keyword and incentivize readers to click through to your page.', td),
				fixActionParams
			},
			'title-ok' : {
				title : __('The SEO title is of good length.', td),
				text  : (result) => sprintf(
					// Translator: 1: The length of the SEO title.
					__('The SEO title is %1$d characters long, which is within our recommended range of 40-60 characters.', td),
					result?.length
				),
				description : __('The SEO title is the title of your page that appears in search results. It should be 40 to 60 characters long, include your focus keyword and incentivize readers to click through to your page.', td)
			}
		}
	}

	get description () {
		const fixActionParams = '&aioseo-scroll=aioseo-post-settings-meta-description-row&aioseo-highlight=aioseo-post-settings-meta-description-row'

		return {
			'description-missing' : {
				title       : __('No meta description found.', td),
				text        : (result) => __('No meta description was found for the page.', td),
				description : __('The meta description is a summary of your page that appears in search results. It should be 120 to 160 characters long, include your focus keyword and incentivize readers to click through to your page.', td),
				fixActionParams
			},
			'description-too-short' : {
				title : __('The meta description is too short.', td),
				text  : (result) => sprintf(
					// Translator: 1: The length of the meta description.
					__('The meta description is %1$d characters long, which is lower than our recommended range of 120-160 characters.', td),
					result?.length
				),
				description : __('The meta description is a summary of your page that appears in search results. It should be 120 to 160 characters long, include your focus keyword and incentivize readers to click through to your page.', td),
				fixActionParams
			},
			'description-too-long' : {
				title : __('The meta description is too long.', td),
				text  : (result) => sprintf(
					// Translator: 1: The length of the meta description.
					__('The meta description is %1$d characters long, which is higher than our recommended range of 120-160 characters.', td),
					result?.length
				),
				description : __('The meta description is a summary of your page that appears in search results. It should be 120 to 160 characters long, include your focus keyword and incentivize readers to click through to your page.', td),
				fixActionParams
			},
			'description-ok' : {
				title : __('The meta description is of good length.', td),
				text  : (result) => sprintf(
					// Translator: 1: The length of the meta description.
					__('The meta description is %1$d characters long, which is within our recommended range of 120-160 characters.', td),
					result?.length
				),
				tooltip : __('The meta description is a summary of your page that appears in search results.', td)
			}
		}
	}

	get h1Tags () {
		return {
			'h1-missing' : {
				title       : __('No H1 tag found.', td),
				description : __('The H1 tag is the main heading on the page and signals the topic of your page to search engines. It should be clear and include your focus keyword.', td)
			},
			'h1-too-many' : {
				title       : __('More than one H1 tag found.', td),
				description : __('The H1 tag is the main heading on the page and signals the topic of your page to search engines. You should only have a single H1 tag on your page.', td)
			},
			'h1-missing-focus-keyword' : {
				title       : __('The H1 tag does not include your focus keyword.', td),
				description : __('The H1 tag is the main heading on the page and signals the topic of your page to search engines. It should be clear and include your focus keyword.', td)
			},
			'h1-ok' : {
				title       : __('The H1 tag includes your focus keyword.', td),
				description : __('The H1 tag is the main heading on the page and signals the topic of your page to search engines. It should be clear and include your focus keyword.', td)
			}
		}
	}

	get subheadingTags () {
		return {
			'subheading-missing' : {
				title       : __('No subheadings found.', td),
				description : __('Subheadings (H2, H3, H4, H5, H6, etc.) are important for SEO and user experience. They help break up the content and make it more readable. At least one subheading should have the focus keyword.', td)
			},
			'subheading-missing-focus-keyword' : {
				title : __('Subheadings do not include your focus keyword.', td),
				text  : (result) => sprintf(
					// Translator: 1: The amount of the subheadings.
					__('%1$d subheadings were found on the page but none of them have the focus keyword.', td),
					result?.length
				),
				description : __('Subheadings (H2, H3, H4, H5, H6, etc.) are important for SEO and user experience. They help break up the content and make it more readable. At least one subheading should have the focus keyword.', td)
			},
			'subheading-ok' : {
				title : __('Subheadings include your focus keyword.', td),
				text  : (result) => sprintf(
					// Translator: 1: The amount of the subheadings.
					__('%1$d subheadings were found on the page.', td),
					result?.length
				),
				description : __('Subheadings (H2, H3, H4, H5, H6, etc.) are important for SEO and user experience. They help break up the content and make it more readable. At least one subheading should have the focus keyword.', td)
			}
		}
	}

	get noImgAltAtts () {
		return {
			'image-missing' : {
				title       : __('No images found.', td),
				description : __('Images are important for SEO and improve user experience & engagement. Images should be optimized (relevant filename, alt text, small file size) so they can also rank separately in image search results.', td)
			},
			'image-missing-alt' : {
				title : __('Images have no alt text.', td),
				text  : (result) => sprintf(
					// Translator: 1: The amount of the images without alt attributes.
					1 === result?.length ? __('%1$d image has no alt text.', td) : __('%1$d images have no alt text.', td),
					result?.length
				),
				content : (result) => 0 < result?.items?.length
					? result?.items?.map(item => {
						return item
					}).join('<br/>')
					: '',
				description : __('Images are important for SEO and improve user experience & engagement. Images should be optimized (relevant filename, alt text, small file size) so they can also rank separately in image search results.', td)
			},
			'image-ok' : {
				title       : __('Images have alt text.', td),
				description : __('Images are important for SEO and improve user experience & engagement. Images should be optimized (relevant filename, alt text, small file size) so they can also rank separately in image search results.', td)
			}
		}
	}

	get linksRatio () {
		return {
			'internal-links-missing' : {
				title       : __('No internal links found.', td),
				description : __('Internal links are links that point to other pages on your site. They help search engines understand the structure of your site, discover new pages and improve your site\'s authority. We recommend adding at least 1 internal link per 500 words of content.', td)
			},
			'internal-links-too-few' : {
				title : __('Too few internal links found.', td),
				text  : (result) => sprintf(
					// Translator: 1: The amount of the internal links.
					__('%1$d internal links were found, which is too few.', td),
					result?.length
				),
				description : __('Internal links are links that point to other pages on your site. They help search engines understand the structure of your site, discover new pages and improve your site\'s authority. We recommend adding at least 1 internal link per 500 words of content.', td)
			},
			'links-ratio-ok' : {
				title       : __('The page has a correct number of internal and external links.', td),
				description : __('Internal links are links that point to other pages on your site. They help search engines understand the structure of your site, discover new pages and improve your site\'s authority. We recommend adding at least 1 internal link per 500 words of content.', td)
			}
		}
	}

	get hasThumbnail () {
		return {
			'thumbnail-missing' : {
				title       : __('No featured image found.', td),
				description : __('The featured image is the main image that represents your page. They grab attention, improve click-through rates on social and search results, and provide context to both users and search engines.', td)
			},
			'thumbnail-ok' : {
				title       : __('The featured image was found.', td),
				description : __('The featured image is the main image that represents your page. They grab attention, improve click-through rates on social and search results, and provide context to both users and search engines.', td)
			}
		}
	}

	get contentLength () {
		return {
			'content-length-too-short' : {
				title : __('The content is too short.', td),
				text  : (result) => {
					if (!result?.length) {
						return __('No content found.', td)
					}

					return sprintf(
						// Translator: 1: The length of the content.
						__('The content is %1$d words long, which is too short. We recommend adding at least 300 words of content.', td),
						result?.length
					)
				},
				description : __('The content is the main body of text on your page that provides information to your visitors. It should be at least 300 words long. Articles that are too short lack depth and are less likely to rank well because search engines prefer longer, more informative content.', td)
			},
			'content-length-ok' : {
				title       : __('The content is long enough.', td),
				description : __('The content is the main body of text on your page that provides information to your visitors. It should be at least 300 words long. Articles that are too short lack depth and are less likely to rank well because search engines prefer longer, more informative content.', td)
			}
		}
	}

	get keywordCannibalization () {
		const fixActionParams = '&aioseo-scroll=aioseo-post-settings-snippet-focus-keyphrase-row&aioseo-highlight=aioseo-post-settings-snippet-focus-keyphrase-row'

		return {
			'keyword-cannibalization' : {
				title       : __('The focus keyword is targeted by multiple pages.', td),
				text        : (result) => __('The following pages on your site are competing for the same keyword:', td),
				description : __('The focus keyword is the main keyword that the page is ranking for. It should be unique to the page. If multiple pages are targeting the same focus keyword, they will all compete for the same traffic and perform worse. This is known as keyword cannibalization.', td),
				content     : (result) => result?.posts?.map(post => {
					if (!post?.editLink) {
						return post.title
					}

					const editLink = `${post.editLink}${fixActionParams}`

					return `<a href="${editLink}" target="_blank">${post.title}</a>`
				}).join('<br/>'),
				fixActionParams
			},
			'keyword-cannibalization-ok' : {
				title       : __('The focus keyword is targeted only by this page.', td),
				description : __('The focus keyword is the main keyword that the page is ranking for. It should be unique to the page. If multiple pages are targeting the same focus keyword, they will all compete for the same traffic and perform worse. This is known as keyword cannibalization.', td)
			}
		}
	}

	get focusKeywordInFirstParagraph () {
		return {
			'first-paragraph-missing-focus-keyword' : {
				title       : __('The first paragraph does not include the focus keyword.', td),
				description : __('The first paragraph on the page should include your focus keyword. This helps search engines quickly understand what the page is about and signals relevance.', td)
			},
			'first-paragraph-ok' : {
				title       : __('The first paragraph includes the focus keyword.', td),
				description : __('The first paragraph on the page should include your focus keyword. This helps search engines quickly understand what the page is about and signals relevance.', td)
			}
		}
	}

	get focusKeywordInTitle () {
		const fixActionParams = '&aioseo-scroll=aioseo-post-settings-post-title-row&aioseo-highlight=aioseo-post-settings-post-title-row'

		return {
			'title-missing-focus-keyword' : {
				title       : __('The SEO title does not include the focus keyword.', td),
				description : __('The SEO title is the title of your page that appears in search results. It should be 40 to 60 characters long, include your focus keyword and incentivize readers to click through to your page.', td),
				fixActionParams
			},
			'title-focus-keyword-ok' : {
				title       : __('The SEO title includes the focus keyword.', td),
				description : __('The SEO title is the title of your page that appears in search results. It should be 40 to 60 characters long, include your focus keyword and incentivize readers to click through to your page.', td)
			}
		}
	}

	get focusKeywordInDescription () {
		const fixActionParams = '&aioseo-scroll=aioseo-post-settings-meta-description-row&aioseo-highlight=aioseo-post-settings-meta-description-row'

		return {
			'description-missing-focus-keyword' : {
				title       : __('The meta description does not include the focus keyword.', td),
				description : __('The meta description is a summary of your page that appears in search results. It should be 120 to 160 characters long, include your focus keyword and incentivize readers to click through to your page.', td),
				fixActionParams
			},
			'description-focus-keyword-ok' : {
				title       : __('The meta description includes the focus keyword.', td),
				description : __('The meta description is a summary of your page that appears in search results. It should be 120 to 160 characters long, include your focus keyword and incentivize readers to click through to your page.', td)
			}
		}
	}

	get focusKeywordInUrl () {
		return {
			'url-missing-focus-keyword' : {
				title       : __('The URL does not include the focus keyword.', td),
				description : __('The URL is the address of your page that appears in search results. It should be 50 characters or less and include your focus keyword to help search engines understand the topic of your page.', td)
			},
			'url-focus-keyword-ok' : {
				title       : __('The URL includes the focus keyword.', td),
				description : __('The URL is the address of your page that appears in search results. It should be 50 characters or less and include your focus keyword to help search engines understand the topic of your page.', td)
			}
		}
	}

	get urlLength () {
		return {
			'url-length-too-long' : {
				title : __('The URL is too long.', td),
				text  : (result) => sprintf(
					// Translator: 1: The length of the URL.
					__('The URL is %1$d characters long, it should be less than 50 characters.', td),
					result?.length
				),
				description : __('The URL is the address of your page that appears in search results. It should be 50 characters or less and include your focus keyword to help search engines understand the topic of your page.', td)
			},
			'url-length-ok' : {
				title       : __('The URL is of good length.', td),
				description : __('The URL is the address of your page that appears in search results. It should be 50 characters or less and include your focus keyword to help search engines understand the topic of your page.', td)
			}
		}
	}

	get productSchema () {
		const fixActionParams = '&aioseo-tab=schema&aioseo-scroll=aioseo-post-schema&aioseo-highlight=aioseo-post-schema'

		return {
			'product-schema-missing' : {
				title       : __('No product schema found.', td),
				description : __('Product schema is a type of structured data that helps search engines understand your product. Google and other search engines use product schema for rich snippets (e.g. price and star rating) and other features.', td),
				fixActionParams
			},
			'product-schema-ok' : {
				title       : __('The product schema was found.', td),
				description : __('Product schema is a type of structured data that helps search engines understand your product. Google and other search engines use product schema for rich snippets (e.g. price and star rating) and other features.', td)
			}
		}
	}

	get strings () {
		return {
			...this.title,
			...this.description,
			...this.h1Tags,
			...this.subheadingTags,
			...this.noImgAltAtts,
			...this.linksRatio,
			...this.hasThumbnail,
			...this.contentLength,
			...this.keywordCannibalization,
			...this.focusKeywordInFirstParagraph,
			...this.focusKeywordInTitle,
			...this.focusKeywordInDescription,
			...this.focusKeywordInUrl,
			...this.urlLength,
			...this.productSchema
		}
	}
}

export default new BasicIssues()