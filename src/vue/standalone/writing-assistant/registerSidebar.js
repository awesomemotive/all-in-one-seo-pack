import { isBlockEditor } from '@/vue/utils/context'
import { __ } from '@wordpress/i18n'

(function (wp) {
	const td = import.meta.env.VITE_TEXTDOMAIN
	if (!isBlockEditor()) {
		return
	}
	const registerPlugin            = wp.plugins.registerPlugin
	const PluginSidebarMoreMenuItem = wp.editPost.PluginSidebarMoreMenuItem
	const PluginSidebar             = wp.editPost.PluginSidebar
	const Fragment                  = wp.element.Fragment
	const el                        = wp.element.createElement

	const PencilIcon = el('svg',
		{
			width   : 24,
			height  : 25,
			viewBox : '0 0 24 25',
			fill    : 'none',
			xmlns   : 'http://www.w3.org/2000/svg'
		},
		el('path',
			{
				d    : 'M5 19.4998H6.425L16.2 9.72482L14.775 8.29982L5 18.0748V19.4998ZM3 21.4998V17.2498L16.2 4.07482C16.4 3.89148 16.6208 3.74982 16.8625 3.64982C17.1042 3.54982 17.3583 3.49982 17.625 3.49982C17.8917 3.49982 18.15 3.54982 18.4 3.64982C18.65 3.74982 18.8667 3.89982 19.05 4.09982L20.425 5.49982C20.625 5.68315 20.7708 5.89982 20.8625 6.14982C20.9542 6.39982 21 6.64982 21 6.89982C21 7.16648 20.9542 7.42065 20.8625 7.66232C20.7708 7.90398 20.625 8.12482 20.425 8.32482L7.25 21.4998H3ZM15.475 9.02482L14.775 8.29982L16.2 9.72482L15.475 9.02482Z',
				fill : 'white'
			}
		),
		el('path',
			{
				d    : 'M5.83312 11.4994C5.83312 10.2604 6.26409 9.20986 7.12605 8.34791C7.988 7.48596 9.0385 7.05498 10.2776 7.05498C9.0385 7.05498 7.988 6.624 7.12605 5.76205C6.26409 4.9001 5.83312 3.84959 5.83312 2.61053C5.83312 3.84959 5.40214 4.9001 4.54019 5.76205C3.67823 6.624 2.62773 7.05498 1.38867 7.05498C2.62773 7.05498 3.67823 7.48596 4.54019 8.34791C5.40214 9.20986 5.83312 10.2604 5.83312 11.4994Z',
				fill : 'white'
			}
		),
		el('path',
			{
				d    : 'M11.3891 5.94383C11.3891 5.32431 11.1736 4.79905 10.7426 4.36808C10.3117 3.9371 9.78641 3.72161 9.16688 3.72161C9.78641 3.72161 10.3117 3.50612 10.7426 3.07515C11.1736 2.64417 11.3891 2.11892 11.3891 1.49939C11.3891 2.11892 11.6046 2.64417 12.0356 3.07515C12.4665 3.50612 12.9918 3.72161 13.6113 3.72161C12.9918 3.72161 12.4665 3.9371 12.0356 4.36808C11.6046 4.79905 11.3891 5.32431 11.3891 5.94383Z',
				fill : 'white'
			}
		),
		el('path',
			{
				d    : 'M16.5 21.5002C16.5 20.5245 16.8394 19.6972 17.5182 19.0184C18.197 18.3396 19.0242 18.0002 20 18.0002C19.0242 18.0002 18.197 17.6609 17.5182 16.9821C16.8394 16.3033 16.5 15.476 16.5 14.5002C16.5 15.476 16.1606 16.3033 15.4818 16.9821C14.803 17.6609 13.9758 18.0002 13 18.0002C13.9758 18.0002 14.803 18.3396 15.4818 19.0184C16.1606 19.6972 16.5 20.5245 16.5 21.5002Z',
				fill : 'white'
			}
		)
	)

	const WritingAssistantButton = el('div',
		{ id: 'aioseo-writing-assistant-sidebar-button' },
		PencilIcon
	)
	const user = window.aioseo.user

	registerPlugin('aioseo-writing-assistant-sidebar', {
		render : function () {
			if (!user.capabilities.aioseo_page_writing_assistant_settings) {
				return null
			}
			return el(Fragment, {},
				el(PluginSidebarMoreMenuItem,
					{
						target : 'aioseo-writing-assistant-sidebar',
						icon   : PencilIcon,
						id     : 'aioseo-writing-assistant-sidebar-button',
						title  : 'AIOSEO' // Don't translate this as we need to target it in CSS.
					},
					__('AIOSEO Writing Assistant', td)
				),
				el(PluginSidebar,
					{
						name  : 'aioseo-writing-assistant-sidebar',
						icon  : WritingAssistantButton,
						title : __('AIOSEO Writing Assistant', td)
					},
					el('div',
						{ id: 'aioseo-writing-assistant-sidebar' },
						el('div',
							el('div',
								{ className: 'aioseo-loading-spinner dark' },
								el('div',
									{ className: 'double-bounce1' },
									null
								),
								el('div',
									{ className: 'double-bounce2' },
									null
								)
							)
						)
					)
				)
			)
		}
	})
})(window.wp)