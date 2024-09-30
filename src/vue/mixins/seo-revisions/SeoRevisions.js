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
	data () {
		return {
			updatingSeoRevisions : false
		}
	},
	methods : {
		updateSeoRevisions () {
			if (
				window.wp.data.select('core/editor').isSavingPost() &&
				!window.wp.data.select('core/editor').isAutosavingPost()
			) {
				this.updatingSeoRevisions = true

				const $this = this
				const seoRevisionsStore = useSeoRevisionsStore()

				setTimeout(() => {
					seoRevisionsStore.fetch().finally(() => {
						$this.updatingSeoRevisions = false
					})
				}, 2500)
			}
		},
		async watchObjectRevisionsOnSavePost () {
			await this.$nextTick()

			window.wp.data.subscribe(() => {
				if (!this.updatingSeoRevisions) {
					this.updateSeoRevisions()
				}
			})
		}
	}
}