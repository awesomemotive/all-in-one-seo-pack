import {
	usePostEditorStore,
	useTagsStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'

// Update post data
export const maybeUpdateAttachment = (run = false) => {
	const postEditorStore = usePostEditorStore()
	if ('attachment' !== postEditorStore.currentPost.postType) {
		return
	}

	const tagsStore = useTagsStore()

	// Attachment Caption
	const attachmentCaption = document.querySelector('textarea#attachment_caption')
	if (attachmentCaption) {
		tagsStore.updateAttachmentCaption(attachmentCaption.value)
		attachmentCaption.addEventListener('input', event => {
			tagsStore.updateAttachmentCaption(event.target.value)
		})
	}

	const postAttachmentCaption = document.querySelector('textarea#attachment-details-caption')
	if (postAttachmentCaption) {
		tagsStore.updateAttachmentCaption(postAttachmentCaption.value)
		postAttachmentCaption.addEventListener('input', event => {
			tagsStore.updateAttachmentCaption(event.target.value)
		})
	}

	// Attachment Description.
	const attachmentDescription = document.querySelector('textarea#attachment_content')
	if (attachmentDescription) {
		tagsStore.updateAttachmentDescription(attachmentDescription.value)
		attachmentDescription.addEventListener('input', event => {
			tagsStore.updateAttachmentDescription(event.target.value)
		})
	}

	// Alt Tag
	const altTag = document.querySelector('input#attachment_alt')
	if (altTag) {
		tagsStore.updateAltTag(altTag.value)
		altTag.addEventListener('input', event => {
			tagsStore.updateAltTag(event.target.value)
		})
	}
	const postAttachmentAltTag = document.querySelector('input#attachment-details-alt-text')
	if (postAttachmentAltTag) {
		tagsStore.updateAltTag(altTag.value)
		postAttachmentAltTag.addEventListener('input', event => {
			tagsStore.updateAltTag(event.target.value)
		})
	}

	if (run) {
		(new TruSeo()).runAnalysis({ postId: postEditorStore.currentPost.id })
	}
}