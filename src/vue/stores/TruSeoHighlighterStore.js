import {
	usePostEditorStore
} from '@/vue/stores'

import { defineStore } from 'pinia'
import { nextTick } from 'vue'
import { getOuterText } from '@/vue/utils/html'

export const useTruSeoHighlighterStore = defineStore('TruSeoHighlighterStore', {
	state : () => ({
		allowScrolling        : false,
		allowHighlighting     : true,
		dispatchAnnotations   : window?.wp?.data?.dispatch('core/annotations') || null,
		enabled               : true,
		highlightAnalyzer     : null,
		highlightMarks        : [],
		highlightPopover      : {},
		source                : 'aioseo-tru-seo-highlighter',
		wpBodyContentObserver : null
	}),
	getters : {
		highlightAnalysis () {
			const postEditorStore = usePostEditorStore()

			return Object.values(postEditorStore.currentPost.page_analysis.analysis).flat().find((a) => !!a[this.highlightAnalyzer]) || {}
		},
		highlightAnalyzerHasError () {
			return !!(this.highlightAnalysis[this.highlightAnalyzer]?.error || false)
		},
		highlightSentences () {
			let sentences = []
			if (Array.isArray(this.highlightAnalysis[this.highlightAnalyzer]?.highlightSentences)) {
				sentences = this.highlightAnalysis[this.highlightAnalyzer].highlightSentences.flat()
				sentences = sentences.map(s => {
					// Remove one HTML entity at the end of the sentence to prevent the annotation API from malfunctioning.
					s = s.replace(/&[a-zA-Z0-9#]{2,};$/, '')
					// Keep line breaks otherwise `getOuterText()` don't recognize them.
					s = s.replace(/<br[^>]*>/gi, '\n')

					return getOuterText(s)
				})
			}

			return sentences.length ? sentences : null
		},
		activeMark () {
			if (!this.highlightMarks.length) {
				return null
			}

			return this.highlightMarks.find(hm => hm.active && hm.node)
		}
	},
	actions : {
		clearHighlightPopover () {
			if (this.highlightPopover?.observer) {
				this.highlightPopover.observer.disconnect()
			}

			if (this.highlightPopover?.node) {
				this.highlightPopover.node.remove()
			}

			if (this.highlightPopover?.app) {
				this.highlightPopover.app.unmount()
			}

			this.highlightPopover = {}
		},
		clearAnnotations () {
			if ('function' === typeof this.dispatchAnnotations?.__experimentalRemoveAnnotationsBySource) {
				this.dispatchAnnotations.__experimentalRemoveAnnotationsBySource(this.source)
			}

			if ('object' === typeof window?.tinymce?.editors) {
				for (const editor of window.tinymce.editors) {
					const selection = editor.selection.win.getSelection()
					const annotations = Object.values(editor.annotator.getAll(this.source)).flat()
					for (const annotation of Object.values(annotations)) {
						editor.undoManager.ignore(() => {
							selection.selectAllChildren(annotation)
							selection.setPosition(annotation, 0)

							editor.annotator.remove(this.source)
						})
					}
				}
			}

			this.highlightMarks = []
		},
		clearAll () {
			this.clearHighlightPopover()
			this.clearAnnotations()
		},
		sanitizeHighlightMarks () {
			this.highlightMarks = this.highlightMarks.filter(hm => !!hm.node)
			if (this.highlightMarks.length && !this.highlightMarks.find(hm => !!hm.active)) {
				this.highlightMarks[0].active = true
			}
		},
		toggleHighlightAnalyzer (analyzer) {
			this.allowScrolling = true
			if (this.highlightAnalyzer === analyzer) {
				this.highlightAnalyzer = null

				return false
			}

			/* The analyzer might have changed, but this analyzer's highlight sentences could be the same,
			   and to deal with that scenario we always set the variable as `null` first
			   to make sure the mixin watcher runs. */
			this.highlightAnalyzer = null

			nextTick().then(() => { this.highlightAnalyzer = analyzer })
		}
	}
})