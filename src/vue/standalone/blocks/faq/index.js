import metadata from './block.json'
import edit from './edit'
import save from './save'
import icon from './icon'
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

export const settings = {
	title,
	description,
	category,
	attributes,
	icon,
	example : {
		attributes : {
			question : __('Who should use AIOSEO?', td)
		},
		innerBlocks : [
			{
				name       : 'core/paragraph',
				attributes : {
					content : __(
						'All in One SEO is perfect for business owners, bloggers, designers, developers, photographers, and basically everyone else. If you want to optimize your WordPress SEO, then you need to use All in One SEO Pack.', td
					)
				}
			}
		]
	},
	supports,
	edit,
	save
}