import { nextTick } from 'vue'
import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useOptionsStore,
	usePostEditorStore,
	useLicenseStore
} from '@/vue/stores'

import { useSchema } from '@/vue/standalone/post-settings/composables/schema'

import { escapeRegex } from '@/vue/utils/regex'
import { isBlockEditor } from '@/vue/utils/context'

export const useSchemaStore = defineStore('SchemaStore', {
	state : () => ({
		custom : {
			graphName : '',
			schema    : ''
		},
		defaultGraphParent      : '',
		graph                   : null,
		graphCardsKey           : 0,
		isDirty                 : false,
		isEditingCustomGraph    : false,
		isEditingCustomTemplate : false,
		isEditingDefaultGraph   : false,
		isEditingGraph          : false,
		isEditingTemplate       : false,
		modalOpenMetabox        : false,
		modalOpenSidebar        : false,
		output                  : '',
		outputKey               : 0,
		tabs                    : {
			generator : 'schema-templates',
			templates : 'schema-catalog'
		},
		templateName              : '',
		previousOutputRequestData : null
	}),
	actions : {
		getCustomObject () {
			return {
				id        : '#aioseo-custom-' + new Date().getTime().toString(36),
				custom    : true,
				graphName : this.custom.graphName,
				schema    : this.custom.schema
			}
		},
		resetSessionState () {
			this.custom = {
				graphName : '',
				schema    : ''
			}

			this.defaultGraphParent = ''
			this.graph              = null
			this.templateName       = ''

			this.isEditingCustomGraph    = false
			this.isEditingCustomTemplate = false
			this.isEditingDefaultGraph   = false
			this.isEditingGraph          = false
			this.isEditingTemplate       = false

			this.isDirty = false
		},
		addCustomAsGraph () {
			const postEditorStore = usePostEditorStore()
			postEditorStore.currentPost.schema.customGraphs.push(this.getCustomObject())

			postEditorStore.currentPost.schema.customGraphs = postEditorStore.currentPost.schema.customGraphs.sort((a, b) => {
				return (a.graphName < b.graphName) ? -1 : 1
			})

			this.resetSessionState()

			this.graphCardsKey    = this.graphCardsKey + 1
			this.modalOpenMetabox = false
			this.modalOpenSidebar = false
		},
		addCustomAsTemplate () {
			const postEditorStore = usePostEditorStore()
			const template        = JSON.parse(JSON.stringify(this.getCustomObject()))

			postEditorStore.internalOptions.internal.schema.templates.push(this.getCustomObject())

			this.resetSessionState()

			this.setTabTemplates('your-templates')

			http.post(links.restUrl('schema/templates'))
				.send({
					template : template
				})
				.then((response) => {
					if (response.body.success && response.body.templates) {
						this.parseAndUpdateTemplates(response.body.templates)
					}
				})
		},
		addCustomTemplateAsGraph () {
			const postEditorStore = usePostEditorStore()
			postEditorStore.currentPost.schema.customGraphs.push(this.graph)

			postEditorStore.currentPost.schema.customGraphs = postEditorStore.currentPost.schema.customGraphs.sort((a, b) => {
				return (a.graphName < b.graphName) ? -1 : 1
			})

			this.resetSessionState()

			this.graphCardsKey    = this.graphCardsKey + 1
			this.modalOpenMetabox = false
			this.modalOpenSidebar = false
		},
		addDefaultGraph () {
			const postEditorStore = usePostEditorStore()
			postEditorStore.currentPost.schema.default.isEnabled = true

			this.resetSessionState()

			this.graphCardsKey    = this.graphCardsKey + 1
			this.modalOpenMetabox = false
			this.modalOpenSidebar = false
		},
		addGraph () {
			const postEditorStore = usePostEditorStore()
			postEditorStore.currentPost.schema.graphs.push(this.graph)

			postEditorStore.currentPost.schema.graphs = postEditorStore.currentPost.schema.graphs.sort((a, b) => {
				return (a.graphName < b.graphName) ? -1 : 1
			})

			this.resetSessionState()

			this.graphCardsKey    = this.graphCardsKey + 1
			this.modalOpenMetabox = false
			this.modalOpenSidebar = false
		},
		addGraphAsTemplate () {
			const schema = useSchema()

			// First, set the name the user entered for the template.
			if (this.graph?.properties?.type && this.graph.properties.type !== this.graph.graphName) {
				this.graph.label = schema.getGraphObject(this.graph, true)?.label + ' - ' + schema.getGraphObject(this.graph)?.label + ' - ' + this.templateName
			} else {
				this.graph.label = schema.getGraphObject(this.graph, true)?.label + ' - ' + this.templateName
			}

			const template = JSON.parse(JSON.stringify(this.graph))

			const optionsStore = useOptionsStore()
			optionsStore.internalOptions.internal.schema.templates.push(this.graph)

			this.resetSessionState()

			this.setTabTemplates('your-templates')

			http.post(links.restUrl('schema/templates'))
				.send({
					template : template
				})
				.then((response) => {
					if (response.body.success && response.body.templates) {
						this.parseAndUpdateTemplates(response.body.templates)
					}
				})
		},
		addTemplateAsGraph (templateIndex, searchTerm = '') {
			const optionsStore = useOptionsStore()

			// First, filter the templates based on the search term.
			let templates = optionsStore.internalOptions.internal.schema.templates
			if (searchTerm) {
				const pattern = new RegExp(escapeRegex(searchTerm).replace(/\s/g, '\\s'), 'i')
				templates = templates.filter(x => x.label.match(pattern))
			}

			if (undefined === templateIndex) {
				// If we don't have a template index, then we're adding the template from the edit screen. In that case, we just need to grab it from the session.
				templateIndex = templates.findIndex(x => x.id === this.graph.id)
			}

			const template = JSON.parse(JSON.stringify(templates[templateIndex]))

			// Add a random suffix to the ID to prevent duplicate keys in case the same graph has been added twice.
			template.id = template.id + new Date().getTime().toString(18)

			const postEditorStore = usePostEditorStore()
			if (template.custom) {
				postEditorStore.currentPost.schema.customGraphs.push(template)

				postEditorStore.currentPost.schema.customGraphs = postEditorStore.currentPost.schema.customGraphs.sort((a, b) => {
					return (a.graphName < b.graphName) ? -1 : 1
				})
			} else {
				postEditorStore.currentPost.schema.graphs.push(template)

				postEditorStore.currentPost.schema.graphs = postEditorStore.currentPost.schema.graphs.sort((a, b) => {
					return (a.graphName < b.graphName) ? -1 : 1
				})
			}

			this.resetSessionState()

			this.graphCardsKey    = this.graphCardsKey + 1
			this.modalOpenMetabox = false
			this.modalOpenSidebar = false
		},
		deleteCustomGraph (graphIndex) {
			const postEditorStore = usePostEditorStore()
			if (undefined === graphIndex) {
				// If we don't have a graph index, then we're deleting the graph from the edit screen. In that case, we just need to grab it from the session.
				graphIndex = postEditorStore.currentPost.schema.customGraphs.findIndex(x => x.id === this.graph.id)
			}

			postEditorStore.currentPost.schema.customGraphs.splice(graphIndex, 1)

			this.resetSessionState()

			this.graphCardsKey    = this.graphCardsKey + 1
			this.modalOpenMetabox = false
			this.modalOpenSidebar = false
		},
		deleteDefaultGraph () {
			const postEditorStore = usePostEditorStore()
			postEditorStore.currentPost.schema.default.isEnabled = false

			this.resetSessionState()

			this.graphCardsKey    = this.graphCardsKey + 1
			this.modalOpenMetabox = false
			this.modalOpenSidebar = false
		},
		deleteGraph (graphIndex) {
			const postEditorStore = usePostEditorStore()
			if (undefined === graphIndex) {
				// If we don't have a graph index, then we're deleting the graph from the edit screen. In that case, we just need to grab it from the session.
				graphIndex = postEditorStore.currentPost.schema.graphs.findIndex(x => x.id === this.graph.id)
			}

			postEditorStore.currentPost.schema.graphs.splice(graphIndex, 1)

			this.resetSessionState()

			this.graphCardsKey    = this.graphCardsKey + 1
			this.modalOpenMetabox = false
			this.modalOpenSidebar = false
		},
		deleteTemplate (templateIndex) {
			const templateId   = this.graph.id
			const optionsStore = useOptionsStore()

			if (undefined === templateIndex) {
				// If we don't have a template index, then we're adding the template from the edit screen. In that case, we just need to grab it from the session.
				templateIndex = optionsStore.internalOptions.internal.schema.templates.findIndex(x => x.id === templateId)
			}

			optionsStore.internalOptions.internal.schema.templates.splice(templateIndex, 1)

			this.resetSessionState()

			this.setTabTemplates('your-templates')

			http.delete(links.restUrl('schema/templates'))
				.send({
					templateId : templateId
				})
				.then((response) => {
					if (response.body.success && response.body.templates) {
						this.parseAndUpdateTemplates(response.body.templates)
					}
				})
		},
		editCustomGraph ({ customGraphIndex, isSidebar }) {
			// It's important to create a clone so that we're not editing the existing graph object.
			const postEditorStore   = usePostEditorStore()
			const editedCustomGraph = JSON.parse(JSON.stringify(postEditorStore.currentPost.schema.customGraphs[customGraphIndex]))
			this.graph = editedCustomGraph
			this.isEditingCustomGraph = true

			this.tabs.generator = 'custom-schema'

			if (isSidebar) {
				this.modalOpenSidebar = true
			} else {
				this.modalOpenMetabox = true
			}
		},
		editDefaultGraph ({ isSidebar, parentGraphName }) {
			this.defaultGraphParent = parentGraphName

			// First, check the default graph already has properties set.
			const postEditorStore = usePostEditorStore()
			if (postEditorStore.currentPost.schema.default.data[parentGraphName]) {
				this.graph = postEditorStore.currentPost.schema.default.data[parentGraphName]
			}

			this.isEditingDefaultGraph = true
			this.tabs.generator = 'schema-templates'

			if (isSidebar) {
				this.modalOpenSidebar = true
			} else {
				this.modalOpenMetabox = true
			}
		},
		async editGraph ({ graphIndex, isSidebar }) {
			// It's important to create a clone so that we're not editing the existing graph object.
			const postEditorStore = usePostEditorStore()
			const editedGraph     = JSON.parse(JSON.stringify(postEditorStore.currentPost.schema.graphs[graphIndex]))

			this.graph          = editedGraph
			this.isEditingGraph = true
			this.tabs.generator = 'schema-templates'

			if (isSidebar) {
				this.modalOpenSidebar = true
			} else {
				this.modalOpenMetabox = true
			}

			// We need to wait for the next tick so that the modal is open before we turn off the isDirty flag.
			await nextTick()

			// Turn off the isDirty flag since we JUST opened up the edit modal.
			this.isDirty = false
		},
		async editTemplate (templateIndex, searchTerm = '') {
			const optionsStore = useOptionsStore()

			// First, filter the templates based on the search term.
			let templates = optionsStore.internalOptions.internal.schema.templates
			if (searchTerm) {
				const pattern = new RegExp(escapeRegex(searchTerm).replace(/\s/g, '\\s'), 'i')
				templates = templates.filter(x => x.label.match(pattern))
			}

			// It's important to create a clone so that we're not editing the existing template object.
			const editedTemplate = JSON.parse(JSON.stringify(templates[templateIndex]))

			this.graph = editedTemplate

			if (editedTemplate.custom) {
				this.templateName = this.graphName

				this.isEditingCustomTemplate = true
				this.tabs.generator = 'custom-schema'
			} else {
				const pattern            = new RegExp(`^${this.graph.graphName} -`, 'i')
				const labelWithoutPrefix = this.graph.label.replace(pattern, '')
				this.templateName = labelWithoutPrefix

				this.isEditingTemplate = true
			}

			// We need to wait for the next tick so that the modal is open before we turn off the isDirty flag.
			await nextTick()

			// Turn off the isDirty flag since we JUST opened up the edit modal.
			this.isDirty = false
		},
		updateCustomGraph () {
			const postEditorStore = usePostEditorStore()
			const index = postEditorStore.currentPost.schema.customGraphs.findIndex(x => x.id === this.graph.id)
			if (-1 === index) {
				return
			}

			postEditorStore.currentPost.schema.customGraphs[index] = this.graph

			postEditorStore.currentPost.schema.customGraphs = postEditorStore.currentPost.schema.customGraphs.sort((a, b) => {
				return (a.graphName < b.graphName) ? -1 : 1
			})

			this.resetSessionState()

			this.graphCardsKey    = this.graphCardsKey + 1
			this.modalOpenMetabox = false
			this.modalOpenSidebar = false
		},
		updateDefaultGraph () {
			const postEditorStore = usePostEditorStore()
			postEditorStore.currentPost.schema.default.data[this.defaultGraphParent] = this.graph

			this.resetSessionState()

			this.graphCardsKey    = this.graphCardsKey + 1
			this.modalOpenMetabox = false
			this.modalOpenSidebar = false
		},
		updateGraph () {
			const postEditorStore = usePostEditorStore()
			const index = postEditorStore.currentPost.schema.graphs.findIndex(x => x.id === this.graph.id)
			if (-1 === index) {
				return
			}

			postEditorStore.currentPost.schema.graphs[index] = this.graph

			postEditorStore.currentPost.schema.graphs = postEditorStore.currentPost.schema.graphs.sort((a, b) => {
				return (a.graphName < b.graphName) ? -1 : 1
			})

			this.resetSessionState()

			this.graphCardsKey    = this.graphCardsKey + 1
			this.modalOpenMetabox = false
			this.modalOpenSidebar = false
		},
		updateSchemaOutput () {
			const postEditorStore = usePostEditorStore()
			const licenseStore    = useLicenseStore()

			if (licenseStore.isUnlicensed) {
				return
			}

			let postId = postEditorStore.currentPost.id
			if (isBlockEditor()) {
				postId = window.wp.data.select('core/editor').getCurrentPostId()
			}

			if (!postId || 'post' !== postEditorStore.currentPost.context) {
				return
			}

			const data = {
				postId       : postId,
				graphs       : postEditorStore.currentPost.schema.graphs,
				customGraphs : postEditorStore.currentPost.schema.customGraphs,
				default      : postEditorStore.currentPost.schema.default,
				blockGraphs  : postEditorStore.currentPost.schema.blockGraphs
			}

			if (JSON.stringify(data) === this.previousOutputRequestData) {
				return
			}

			this.previousOutputRequestData = JSON.stringify(data)

			http.post(links.restUrl('schema/validator/output'))
				.send(data)
				.then((response) => {
					if (response.body.success && response.body.output) {
						let output = null
						try {
							output = JSON.parse(response.body.output)
						} catch {
							return
						}

						response.body.output = JSON.stringify(output, null, '\t').trim()

						if (response.body.output === this.output) {
							return
						}

						this.output    = response.body.output
						this.outputKey = this.outputKey + 1
					}
				})
		},
		updateTemplate () {
			// First, set the name the user entered for the template.
			this.graph.label = this.graph.graphName + ' - ' + this.templateName

			const template     = JSON.parse(JSON.stringify(this.graph))
			const optionsStore = useOptionsStore()

			const index = optionsStore.internalOptions.internal.schema.templates.findIndex(x => x.id === this.graph.id)
			if (-1 === index) {
				return
			}

			optionsStore.internalOptions.internal.schema.templates[index] = this.graph

			this.resetSessionState()

			this.setTabTemplates('your-templates')

			http.put(links.restUrl('schema/templates'))
				.send({
					template : template
				})
				.then((response) => {
					if (response.body.success && response.body.templates) {
						this.parseAndUpdateTemplates(response.body.templates)
					}
				})
		},
		parseAndUpdateTemplates (templates) {
			templates = templates.map((template) => {
				if ('string' !== typeof template) {
					return template
				}
				template = JSON.parse(template)
				return template
			})

			// Sort alphabetically but also move custom schema templates to the rear.
			templates = templates.sort((a, b) => {
				const custom = a.custom ? 0 : 1
				return (a.graphName < b.graphName) ? -1 : custom
			})

			const optionsStore = useOptionsStore()
			optionsStore.internalOptions.internal.schema.templates = templates
		},
		setModalOpen ({ isOpen, initialTab, isSidebar }) {
			if (!isOpen) {
				this.resetSessionState()

				this.setTabTemplates('schema-catalog')
			}

			if (isSidebar) {
				this.modalOpenSidebar = isOpen
			} else {
				this.modalOpenMetabox = isOpen
			}

			if (initialTab) {
				this.tabs.generator = initialTab
			}
		},
		setTabTemplates (tabName) {
			this.tabs.generator = 'schema-templates'
			this.tabs.templates = tabName
		}
	}
})