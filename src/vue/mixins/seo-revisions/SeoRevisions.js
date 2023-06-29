import {
	useSeoRevisionsStore
} from '@/vue/stores'

export const RevisionMeta = {
	methods : {
		getAuthorEmailAndLogin (revision) {
			// User might have been deleted.
			if ('object' !== typeof revision?.author || !revision.author.email || !revision.author.login) {
				return ''
			}

			return revision.author.email + ' (' + revision.author.login + ')'
		}
	}
}

export const ObjectRevisions = {
	methods : {
		async watchObjectRevisionsOnSavePost (dispatchEditor) {
			await this.$nextTick()

			const savePost = dispatchEditor?.savePost || null
			if ('function' !== typeof savePost) {
				return false
			}

			const seoRevisionsStore = useSeoRevisionsStore()

			dispatchEditor.savePost = (options) => {
				options = options || {}

				return savePost(options)
					.then(() => {
						if (!options.isAutosave && !options.isPreview) {
							setTimeout(() => {
								seoRevisionsStore.fetch()
							}, 2000)
						}
					})
			}
		}
	}
}