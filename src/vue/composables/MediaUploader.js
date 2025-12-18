import { nextTick } from 'vue'

import {
	useRootStore
} from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'
import { isOxygenEditor } from '@/vue/utils/context'

const td = import.meta.env.VITE_TEXTDOMAIN

/**
 * Get wp.media from current or parent window.
 *
 * @returns {Function|null} The wp.media function or null.
 */
const getWpMedia = () => {
	if ('function' === typeof window.wp?.media) {
		return window.wp.media
	}

	if ('function' === typeof window.parent?.wp?.media) {
		return window.parent.wp.media
	}

	return null
}

export const useMediaUploader = () => {
	let uploaderInstance = null

	/**
	 * Open the standard WordPress media uploader.
	 *
	 * @param {Object}   options            The options.
	 * @param {string}   options.title      The modal title.
	 * @param {string}   options.buttonText The button text.
	 * @param {boolean}  options.multiple   Whether to allow multiple selection.
	 * @param {string}   options.type       The library type filter (e.g., 'image').
	 * @param {Function} options.onSelect   Callback when media is selected.
	 * @returns {void}
	 */
	const openWpMedia = ({ title, buttonText, multiple = false, type = null, onSelect }) => {
		uploaderInstance = getWpMedia()({
			title,
			button  : { text: buttonText },
			multiple,
			library : type ? { type } : {}
		})

		uploaderInstance.on('select', () => {
			const selection = uploaderInstance.state().get('selection')
			const first     = selection.first()
			if (!first) {
				return
			}
			onSelect(multiple ? selection.toJSON() : first.toJSON())
		})

		uploaderInstance.on('close', () => uploaderInstance.detach())

		nextTick(() => {
			uploaderInstance.open()
		})
	}

	/**
	 * Open Oxygen's media chooser via iframe.
	 *
	 * @param {Object}   options          The options.
	 * @param {boolean}  options.multiple Whether to allow multiple selection.
	 * @param {string}   options.type     The library type filter (e.g., 'image').
	 * @param {Function} options.onSelect Callback when media is selected.
	 * @returns {void}
	 */
	const openOxygenMedia = ({ multiple = false, type = null, onSelect }) => {
		const rootStore = useRootStore()
		const homeUrl   = rootStore.aioseo?.urls?.home || window.location.origin
		const adminUrl  = homeUrl.replace(/\/$/, '') + '/wp-admin/'

		const params = new URLSearchParams({ breakdance_wpuiforbuilder_media: '1' })
		if (type) {
			params.set('types', type)
		}
		if (multiple) {
			params.set('multiple', '1')
		}

		const overlay     = document.createElement('div')
		overlay.className = 'aioseo-media-uploader-overlay'
		overlay.innerHTML = `
			<div class="aioseo-media-uploader-modal">
				<iframe src="${adminUrl}?${params.toString()}"></iframe>
			</div>
		`

		if (!document.getElementById('aioseo-media-uploader-styles')) {
			const style = document.createElement('style')
			style.id = 'aioseo-media-uploader-styles'
			style.textContent = `
				.aioseo-media-uploader-overlay {
					position: fixed;
					inset: 0;
					background: rgba(0, 0, 0, 0.7);
					z-index: 999999;
					display: flex;
					align-items: center;
					justify-content: center;
				}
				.aioseo-media-uploader-modal {
					background: #fff;
					width: 90%;
					height: 90%;
					max-width: 1200px;
					max-height: 800px;
					border-radius: 4px;
					overflow: hidden;
					display: flex;
					align-items: center;
					justify-content: center;
				}
				.aioseo-media-uploader-modal::before {
					content: '';
					width: 40px;
					height: 40px;
					border: 3px solid #e5e5e5;
					border-top-color: #005AE0;
					border-radius: 50%;
					animation: aioseoSpin 0.8s linear infinite;
					position: absolute;
				}
				.aioseo-media-uploader-modal iframe {
					width: 100%;
					height: 100%;
					border: none;
					background: #fff;
					opacity: 0;
					transition: opacity 0.15s ease-out;
				}
				.aioseo-media-uploader-modal.ready::before {
					display: none;
				}
				.aioseo-media-uploader-modal.ready iframe {
					opacity: 1;
				}
				@keyframes aioseoSpin {
					to { transform: rotate(360deg); }
				}
			`
			document.head.appendChild(style)
		}

		document.body.appendChild(overlay)

		const iframe = overlay.querySelector('iframe')
		const modal  = overlay.querySelector('.aioseo-media-uploader-modal')
		iframe.addEventListener('load', () => {
			setTimeout(() => modal.classList.add('ready'), 300)
		})

		const cleanup = () => {
			document.removeEventListener('breakdanceMediaChooserSelect', handleSelect)
			document.removeEventListener('breakdanceMediaChooserClose', handleClose)
			overlay.remove()
		}

		const handleClose = () => nextTick(cleanup)

		const handleSelect = (e) => {
			const attachments = e.detail
			const result      = multiple
				? (Array.isArray(attachments) ? attachments : [ attachments ])
				: (Array.isArray(attachments) ? attachments[0] : attachments)

			onSelect(result)
			cleanup()
		}

		overlay.addEventListener('click', (e) => e.target === overlay && cleanup())

		document.addEventListener('breakdanceMediaChooserSelect', handleSelect)
		document.addEventListener('breakdanceMediaChooserClose', handleClose)
	}

	/**
	 * Open the media library using the appropriate method for the current context.
	 *
	 * @param {Object}   options            The options.
	 * @param {string}   options.title      The modal title.
	 * @param {string}   options.buttonText The button text.
	 * @param {boolean}  options.multiple   Whether to allow multiple selection.
	 * @param {string}   options.type       The library type filter (e.g., 'image').
	 * @param {Function} options.onSelect   Callback when media is selected.
	 * @returns {void}
	 */
	const openMediaLibrary = ({
		title      = __('Choose Media', td),
		buttonText = __('Choose', td),
		multiple   = false,
		type       = null,
		onSelect
	}) => {
		if (getWpMedia()) {
			openWpMedia({ title, buttonText, multiple, type, onSelect })
			return
		}

		if (isOxygenEditor()) {
			openOxygenMedia({ multiple, type, onSelect })
			return
		}

		window.alert(__('The media uploader is not available. Please paste the image URL directly.', td))
	}

	return {
		openMediaLibrary
	}
}