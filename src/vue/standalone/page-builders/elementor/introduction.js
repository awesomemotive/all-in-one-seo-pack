import { __, sprintf } from '@/vue/plugins/translations'

/**
 * Initializes the introduction.
 *
 * @returns {void}
 */
export default ({ elementor, elementorModules }) => {
	// Do nothing if the user already closed the introduction.
	if (true === elementor.config.user.introduction['aioseo-introduction']) {
		return
	}

	const introduction = new elementorModules.editor.utils.Introduction({
		introductionKey : 'aioseo-introduction',
		dialogType      : 'alert',
		dialogOptions   : {
			id            : 'aioseo-introduction',
			headerMessage : sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO"), 2 - The Elementor plugin name ("Elementor").
				__('New: %1$s %2$s integration',
					import.meta.env.VITE_TEXTDOMAIN),
				import.meta.env.VITE_SHORT_NAME,
				'Elementor'
			),
			message : sprintf(
				// Translators: 1 - The plugin name ("All in One SEO"), 2 - The Elementor plugin name ("Elementor").
				__('You can now manage your SEO settings inside of %1$s via %2$s before you publish your post!', import.meta.env.VITE_TEXTDOMAIN),
				'Elementor',
				import.meta.env.VITE_NAME
			),
			position : {
				my : 'center center',
				at : 'center center'
			},
			strings : {
				confirm : __('Got It!', import.meta.env.VITE_TEXTDOMAIN)
			},
			hide : {
				onButtonClick : false
			},
			onConfirm : () => {
				introduction.setViewed()
				introduction.getDialog().hide()
			}
		}
	})

	introduction.show()
}