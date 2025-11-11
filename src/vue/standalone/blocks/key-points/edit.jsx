const { InnerBlocks } = window.wp.blockEditor
const { useEffect } = window.wp.element
const { select, dispatch, subscribe } = window.wp.data

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @param   {Array}     props The props.
 * @returns {WPElement}       Element to render.
 */
export default function edit (props) {
	const {
		className,
		clientId
	} = props

	useEffect(() => {
		// Focus on the first list item when the block is inserted
		const unsubscribe = subscribe(() => {
			const innerBlocks = select('core/block-editor').getBlocks(clientId)
			const listBlock = innerBlocks.find((block) => 'core/list' === block.name)

			if (listBlock) {
				// Focus on the list block itself
				dispatch('core/block-editor').selectBlock(listBlock.clientId)

				// Optionally, place the cursor in the first list item
				const listItems = listBlock.attributes.values
				if (listItems) {
					const firstItemRange = listItems.indexOf('<li>') + 4 // Start of the first <li>
					const newSelection = {
						clientId : listBlock.clientId,
						start    : firstItemRange,
						end      : firstItemRange
					}

					dispatch('core/block-editor').selectBlock(listBlock.clientId, newSelection)
				}

				unsubscribe() // Ensure this runs only once
			}
		})

		// Cleanup subscription when the component is unmounted
		return () => unsubscribe()
	}, [ clientId ])

	return (
		<div className={className}>
			<div id={`aioseo-${clientId}`}>
				<InnerBlocks
					template={[ [ 'core/list' ] ]}
					templateLock="all"
				/>
			</div>
		</div>
	)
}