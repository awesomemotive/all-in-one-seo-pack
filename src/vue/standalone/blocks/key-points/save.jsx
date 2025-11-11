const {
	InnerBlocks
} = window.wp.blockEditor

export default function save ({ className }) {
	return (
		<div className={className}>
			<div className="aioseo-key-points-block-content">
				<InnerBlocks.Content />
			</div>
		</div>
	)
}