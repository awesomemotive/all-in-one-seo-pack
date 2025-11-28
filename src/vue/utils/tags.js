import { __ } from '@/vue/plugins/translations'

import {
	useRootStore,
	useTagsStore
} from '@/vue/stores'

const td = import.meta.env.VITE_TEXTDOMAIN

const context = context => {
	const tagsStore = useTagsStore()

	if (!tagsStore.tags.length) {
		return []
	}

	const current = tagsStore.context[context]
	if (!current) {
		return tagsStore.tags
			.filter(tag => !tag.context || tag.context.includes('all'))
	}

	const contextualTags = []
	let tags             = []
	current.forEach(key => {
		const allTags = tagsStore.tags.filter(tag => tag.id === key)
		allTags.forEach(tag => {
			if (tag.context) {
				if (!tag.context.includes('all') && !tag.context.includes(context)) {
					return
				}

				contextualTags.push(tag.id)
			}
			if (tag && !tag.deprecated) {
				tags.push({ ...tag })
			}
		})
	})

	// If the tags are futher contextual, let's remove the ones that conflict.
	if (contextualTags.length) {
		const deletedIndexes = []
		tags.forEach((tag, index) => {
			if (contextualTags.includes(tag.id) && !tag.context) {
				deletedIndexes.push(index)
			}
		})

		for (let i = deletedIndexes.length - 1; 0 <= i; i--) {
			tags.splice(deletedIndexes[i], 1)
		}
	}

	const rootStore = useRootStore()
	const postType = rootStore.aioseo.postData.postTypes.find(pt => pt.name + 'Title' === context || pt.name + 'Description' === context || 'breadcrumbs-post-type-' + pt.name === context)
	if (postType) {
		tags = tags.map(tag => {
			tag.name        = tag.name.replace('Post', postType.singular)
			tag.description = tag.description.replace('page/post', postType.singular.toLowerCase())
			return tag
		})

		if ('product' === postType.name) {
			const excerptIndex = tags.findIndex(t => 'post_excerpt' === t.id)
			if (-1 !== excerptIndex) {
				tags[excerptIndex].name        = __('Product Short Description', td)
				tags[excerptIndex].description = __('A short description for your product.', td)
				tags[excerptIndex].value       = __('Sample short description for your product.', td)
			}
		}
	}

	const taxonomy = rootStore.aioseo.postData.taxonomies.find(tax => tax.name + 'Title' === context || tax.name + 'Description' === context || 'breadcrumbs-taxonomy-' + tax.name === context)
	if (taxonomy) {
		tags = tags.map(tag => {
			tag.name = tag.name.replace('Category', taxonomy.singular)
			return tag
		})
	}

	if ('dateTitle' === context || 'dateDescription' === context) {
		tags = tags.map(tag => {
			tag.name        = tag.name.replace('Category', __('Archive', td))
			tag.name        = tag.name.replace('Post', __('Archive', td))
			tag.description = tag.description.replace('page/post', __('archive', td))
			return tag
		})
	}

	// Last, we need to sort all tags alphabetically by name.
	return tags.sort((a, b) => {
		return a.name < b.name
			? -1
			: (a.name > b.name ? 1 : 0)
	})
}

const getDefaultTags = (type, name, location) => {
	switch (location) {
		case 'title':
			switch (type) {
				case 'taxonomies':
					return [
						'taxonomy_title',
						'separator_sa',
						'site_title'
					]
				case 'archives':
					switch (name) {
						case 'author':
							return [
								'author_name',
								'separator_sa',
								'site_title'
							]
						case 'search':
							return [
								'search_term',
								'separator_sa',
								'site_title'
							]
						case 'date':
						default:
							return [
								'archive_title',
								'separator_sa',
								'site_title'
							]
					}
				default:
					return [
						'post_title',
						'separator_sa',
						'site_title'
					]
			}
		case 'description':
			switch (type) {
				case 'taxonomies':
					return [
						'taxonomy_title',
						'separator_sa',
						'taxonomy_description'
					]
				case 'attachment':
					return [
						'attachment_caption',
						'separator_sa',
						'site_title'
					]
				case 'archives':
					switch (name) {
						case 'author':
							return [
								'author_name',
								'separator_sa',
								'site_title'
							]
						case 'search':
							return [
								'search_term',
								'separator_sa',
								'site_title'
							]
						case 'date':
						default:
							return [
								'archive_title',
								'separator_sa',
								'site_title'
							]
					}
				default:
					return 'attachment' === name
						? [
							'attachment_caption',
							'separator_sa',
							'site_title'
						]
						: 'product' === name
							? [
								'post_excerpt',
								'post_content'
							]
							: [
								'post_excerpt',
								'post_content',
								'separator_sa'
							]
			}
	}
}

/**
 * Constructs the tag text based on the provided parameters.
 *
 * @param   {string}           fallback The fallback type, such as post, taxonomy, product, etc.
 * @param   {string|undefined} type     The type of tag, such as post, product, product_cat, post_tag, etc.
 * @param   {string}           postfix  The postfix to append, such as Title, Description.
 * @returns {string}                    The constructed tag text.
 */
const getTagText = (fallback, type, postfix) => {
	const tagsStore = useTagsStore()

	if (!type) {
		return `${fallback}${postfix}`
	}

	const ctx = `${type}${postfix}`

	return Object.prototype.hasOwnProperty.call(tagsStore.context, ctx) ? ctx : `${fallback}${postfix}`
}

export default {
	context,
	getDefaultTags,
	getTagText
}