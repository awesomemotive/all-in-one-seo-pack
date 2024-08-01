const { Fragment } = window.wp.element

const HeadlinePieChart = props => {
	const score = props.barScore
	const color = props.color

	return (
		<Fragment>
			<div
				className="aioseo-donut-container"
				style={{ flexDirection: 'column' }}
			>
				<svg
					className="aioseo-donut-score-svg"
					viewBox="0 0 33.83098862 33.83098862"
					xmlns="http://www.w3.org/2000/svg"
				>
					<circle
						className="aioseo-seo-headline-analyzer-score__background"
						stroke="#e8e8eb"
						strokeWidth="2"
						fill="none"
						cx="16.91549431"
						cy="16.91549431"
						r="15.91549431"
					/>
					<circle
						className="aioseo-seo-headline-analyzer-score__circle"
						stroke={color}
						strokeWidth="2"
						strokeDasharray={`${score}, 100`}
						strokeLinecap="round"
						fill="none"
						cx="16.91549431"
						cy="16.91549431"
						r="15.91549431"
					/>
				</svg>
			</div>
		</Fragment>
	)
}

export default HeadlinePieChart