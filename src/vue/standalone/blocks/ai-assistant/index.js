import metadata from './block.json'
import edit from './edit'
import save from './save'
import icon from './icon'

const {
	name,
	title,
	category,
	description,
	supports,
	attributes
} = metadata

export { name }

export const settings = {
	title,
	category,
	description,
	supports,
	attributes,
	icon,
	edit,
	save
}