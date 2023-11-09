import { isBlockEditor } from '@/vue/utils/context'
import { taxonomyHasPrimaryTermSupport } from './helpers'

if (isBlockEditor() && window.wp) {
	/**
	 * WordPress dependencies
	 */
	const { createElement, Fragment }    = window.wp.element
	const { addFilter }                  = window.wp.hooks
	const { createHigherOrderComponent } = window.wp.compose
	const { subscribe }                  = window.wp.data

	addFilter(
		'editor.PostTaxonomyType',
		'aioseo/primary-term',
		createHigherOrderComponent((OriginalComponent) => {
			return (props) => {
				const { slug: taxonomy } = props

				if (!taxonomyHasPrimaryTermSupport(taxonomy)) {
					return createElement(OriginalComponent, props)
				}

				return createElement(Fragment, {},
					createElement(OriginalComponent, props),
					createElement('div', { id: `aioseo-primary-term-${taxonomy}` },
						createElement('div', {
							className : 'aioseo-primary-term-app',
							taxonomy  : taxonomy
						})
					)
				)
			}
		}, 'withInspectorControls')
	)

	subscribe(() => {
		window.aioseoBus.$emit('updateSelectedTerms')
	})
}