import { generateUniqueSchemaBlockId } from '@/vue/standalone/blocks/utils'
import { render } from './AIOSEO_VERSION/sidebar.js'

const { useSelect }    = window.wp.data
const { useEffect }    = window.wp.element
const blockEditorStore = window.wp.blockEditor.store
const { serialize }    = window.wp.blocks
const hasInitialized   = []

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

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @param   {Array}     props The props.
 * @returns {WPElement}       Element to render.
 */
export default function edit (props) {
	const {
		attributes,
		className,
		clientId,
		setAttributes,
		isSelected
	} = props

	const {
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
			const {
				getAdjacentBlockClientId,
				getBlockAttributes,
				getBlockName,
				getClientIdsWithDescendants,
				getGlobalBlockCount
			} = select(blockEditorStore)

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
			const {
				getBlocks
			} = select(blockEditorStore)

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

	return render(className, attributes, setSchemaBlockAttributes)
}