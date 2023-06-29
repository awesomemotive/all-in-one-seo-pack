import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

export const useTagsStore = defineStore('TagsStore', {
	state : () => ({
		tags     : [],
		context  : {},
		liveTags : {
			post_title             : '',
			post_content           : '',
			post_excerpt           : '',
			taxonomy_title         : '',
			taxonomy_description   : '',
			custom_field           : [],
			permalink              : '',
			attachment_caption     : '',
			attachment_description : '',
			alt_tag                : '',
			categories             : '',
			woocommerce_brand      : '',
			woocommerce_sku        : '',
			woocommerce_price      : ''
		},
		permalinkSlug : null
	}),
	actions : {
		getTags () {
			return http.get(links.restUrl('tags'))
				.then(response => {
					this.tags = response.body.tags
				})
		},
		updateTaxonomyTitle (payload) {
			this.liveTags.taxonomy_title = payload
		},
		updateTaxonomyDescription (payload) {
			this.liveTags.taxonomy_description = payload
		},
		updatePermalink (payload) {
			this.liveTags.permalink = payload
		},
		updatePostTitle (payload) {
			this.liveTags.post_title = payload
		},
		updatePostContent (payload) {
			this.liveTags.post_content = payload
		},
		updatePostExcerpt (payload) {
			this.liveTags.post_excerpt = payload
		},
		updateAttachmentCaption (payload) {
			this.liveTags.attachment_caption = payload
		},
		updateAttachmentDescription (payload) {
			this.liveTags.attachment_description = payload
		},
		updateAltTag (payload) {
			this.liveTags.alt_tag = payload
		},
		updateCategories (payload) {
			this.liveTags.categories = payload
		},
		updateWooCommerceBrand (payload) {
			this.liveTags.woocommerce_brand = payload
		},
		updateWooCommerceSku (payload) {
			this.liveTags.woocommerce_sku = payload
		},
		updateWooCommercePrice (payload) {
			this.liveTags.woocommerce_price = payload
		},
		updatePermalinkSlug (payload) {
			this.permalinkSlug = payload
		}
	}
})