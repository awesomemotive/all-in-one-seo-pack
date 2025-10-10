import SvgAiContent from '@/vue/components/common/svg/ai/AiContent'

const el = window.wp.element.createElement
const render = SvgAiContent.render()
const children = render.children.map(child => {
	return el(
		child.type,
		{
			...child.props
		}
	)
})
const icon = el('svg',
	{ ...render.props },
	children
)

export default icon