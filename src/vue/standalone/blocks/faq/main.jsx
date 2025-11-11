import { registerBlock } from '../utils'

import { generateUniqueSchemaBlockId } from '@/vue/standalone/blocks/utils'

import icon from './icon'
import metadata from './block.json'
import { Sidebar } from './AIOSEO_VERSION/Sidebar'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const {
	name,
	title,
	description,
	category,
	supports,
	attributes
} = metadata
export { metadata, name }

const { useSelect }     = window.wp.data
const { useEffect }     = window.wp.element
const { serialize }     = window.wp.blocks
const wp                = window.wp
const InspectorControls = wp.blockEditor?.InspectorControls || wp.editor.InspectorControls
const {
	RichText,
	InnerBlocks
} = wp.blockEditor
const hasInitialized    = []

/**
 * Strips comments and unsafe attributes from HTML.
 *
 * @param   {string} htmlString The HTML to sanitize.
 * @returns {string}            The cleaned HTML.
 */
const cleanSchemaText = (htmlString) => {
	const { body } = document.implementation.createHTMLDocument('')
	body.innerHTML = htmlString

	function clean (html) {
		const nodes = html.childNodes
		for (const node of nodes) {
			// Remove HTML comments.
			if (node.nodeName?.toLowerCase().includes('comment')) {
				node.parentNode.removeChild(node)
			}

			// Remove unsafe and useless attributes.
			if (node.attributes) {
				for (const { name, value } of node.attributes) {
					const val = value.replace(/\s+/g, '').toLowerCase()

					if (
						![ 'src', 'href' ].includes(name) ||
						name.startsWith('on') ||
						val.includes('javascript:') ||
						val.includes('data:')
					) {
						node.removeAttribute(name)
					}
				}
			}
			clean(node)
		}
	}

	clean(body)

	return body.innerHTML.trim().replace(/\s+/g, ' ')
}

export const settings = {
	title,
	description,
	category,
	supports,
	attributes,
	icon,
	edit : function (props) {
		const {
			attributes,
			className,
			clientId,
			setAttributes,
			isSelected
		} = props

		const {
			hidden,
			question,
			tagName,
			schemaBlockId
		} = attributes

		// When saved blocks first load they won't be "Selected".
		// Let's add existing FAQ blocks to the hasInitialized object.
		if (!hasInitialized.includes(clientId) && !isSelected) {
			hasInitialized.push(clientId)
		}

		const setSchemaBlockAttributes = (blockAttributes) => {
			window.requestAnimationFrame(() => {
				setAttributes(blockAttributes)
			})

			window.aioseoBus.$emit('schemaBlockUpdated')
		}

		useEffect(() => {
			if (!schemaBlockId || 1 < (document.querySelectorAll(`[data-schema-block-id='${schemaBlockId}']`) || []).length) {
				// Set a unique ID so that we can use this to track updates and deletes.
				setSchemaBlockAttributes({ schemaBlockId: generateUniqueSchemaBlockId() })
			}
		}, [])

		const closestFaqAttributes = useSelect(
			select => {
				const blockEditorStore = select('core/block-editor')

				if (!blockEditorStore) {
					return
				}

				const {
					getAdjacentBlockClientId,
					getBlockAttributes,
					getBlockName,
					getClientIdsWithDescendants,
					getGlobalBlockCount
				} = blockEditorStore

				if (hasInitialized.includes(clientId) || 2 > getGlobalBlockCount('aioseo/faq')) {
					return null
				}

				// Return attributes from the FAQ block immediately before.
				const previousBlock = getAdjacentBlockClientId(clientId, -1)
				if ('aioseo/faq' === getBlockName(previousBlock)) {
					return getBlockAttributes(previousBlock)
				}

				// Return attributes from the FAQ block immediately after.
				const nextBlock = getAdjacentBlockClientId(clientId, 1)
				if ('aioseo/faq' === getBlockName(nextBlock)) {
					return getBlockAttributes(nextBlock)
				}

				// No neighbors. Look for the closest FAQ.
				const allClientIds = getClientIdsWithDescendants()
				const allFaqIds    = allClientIds.filter(id => 'aioseo/faq' === getBlockName(id))
				const faqIndex     = allFaqIds.indexOf(clientId)
				const closestFaqId = allFaqIds[faqIndex - 1] || allFaqIds[faqIndex + 1]

				// Return the closest FAQ block.
				if ('aioseo/faq' === getBlockName(closestFaqId)) {
					return getBlockAttributes(closestFaqId)
				}
				return null
			},
			[ clientId, hasInitialized ]
		)

		if (isSelected && !hasInitialized.includes(clientId) && !attributes.question && closestFaqAttributes) {
			hasInitialized.push(clientId)

			window.requestAnimationFrame(() => {
				setAttributes(
					{
						backgroundColor : closestFaqAttributes.backgroundColor,
						textColor       : closestFaqAttributes.textColor,
						tagName         : closestFaqAttributes.tagName,
						hidden          : closestFaqAttributes.hidden,
						fontSize        : closestFaqAttributes.fontSize,
						style           : closestFaqAttributes.style
					}
				)
			})
		}

		const answer = useSelect(
			select => {
				const blockEditorStore = select('core/block-editor')

				if (!blockEditorStore) {
					return
				}

				const {
					getBlocks
				} = blockEditorStore

				const getInnerBlocksContent = (faqClientId) => {
					const answerBlocks     = getBlocks(faqClientId)
					const answerBlocksHtml = serialize(answerBlocks)

					// Sanitize HTML and strip comments from the answer blocks.
					return cleanSchemaText(answerBlocksHtml)
				}

				return getInnerBlocksContent(clientId)
			},
			[ clientId ]
		)

		setSchemaBlockAttributes({ answer })

		const TEMPLATE = [
			[
				'core/paragraph', { placeholder: __('Write an answer...', td) }
			]
		]
		const ALLOWED_BLOCKS = [
			'core/paragraph',
			'core/heading',
			'core/list',
			'core/image',
			'core/media-text'
		]

		return (
			<>
				<div data-schema-only={hidden} className={className} data-schema-block-id={schemaBlockId}>
					<InspectorControls>
						<Sidebar setSchemaBlockAttributes={setSchemaBlockAttributes} attributes={attributes} />
					</InspectorControls>
					<RichText
						tagName={tagName}
						placeholder={__('Write a question...', td)}
						className='aioseo-faq-block-question'
						value={question}
						onChange={value => setSchemaBlockAttributes({ question: value })}
					/>
					<div className="aioseo-faq-block-answer">
						<InnerBlocks
							template={TEMPLATE}
							allowedBlocks={ALLOWED_BLOCKS}
						/>
					</div>
				</div>
			</>
		)
	},
	save : function ({ attributes, className }) {
		const {
			hidden,
			question,
			tagName
		} = attributes

		return (
			<div data-schema-only={hidden} className={className}>
				<RichText.Content
					tagName={tagName}
					className="aioseo-faq-block-question"
					value={question}
				/>
				<div className="aioseo-faq-block-answer">
					<InnerBlocks.Content />
				</div>
			</div>
		)
	}
}

registerBlock({
	name,
	settings
})