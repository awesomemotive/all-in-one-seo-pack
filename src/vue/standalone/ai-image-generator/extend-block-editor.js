import {
	useAiStore,
	useAiImageGeneratorStore
} from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'
import { html } from '@/vue/standalone/blocks/utils'

import aiAssistantIcon from '@/vue/standalone/blocks/ai-assistant/icon'

const { addFilter } = window.wp.hooks
const { BlockControls } = window.wp.blockEditor
const { Button, ToolbarGroup, ToolbarButton } = window.wp.components
const { Fragment, render, unmountComponentAtNode } = window.wp.element
const { createHigherOrderComponent } = window.wp.compose
const { select, useSelect } = window.wp.data

const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	generateWithAI : __('Generate with AI', td),
	editWithAI     : __('Edit with AI', td)
}

let isImageBlockToolbarExtended = false

const clickHandler = ($el, options = {}) => {
	window.aioseoBus.$emit('do-post-settings-main-tab-change', { name: 'aiContent' })

	$el.classList.add('is-busy')
	$el.disabled = true

	const aiStore = useAiStore()
	const aiImageGeneratorStore = useAiImageGeneratorStore()

	// Delay the opening of the modal so its animation can be seen.
	setTimeout(() => {
		aiImageGeneratorStore.initiator = options?.initiator
		if (!aiImageGeneratorStore.initiator || !aiImageGeneratorStore.initiator.slug) {
			aiImageGeneratorStore.resetInitiator()
		}

		aiStore.isModalOpened = 'image-generator'

		$el.classList.remove('is-busy')
		$el.disabled = false
	}, 500)
}

export const extendImageBlockToolbar = () => {
	const aiImageGeneratorStore = useAiImageGeneratorStore()

	if (!aiImageGeneratorStore.extend.imageBlockToolbar) {
		return
	}

	// Prevent duplicate filter registrations.
	if (isImageBlockToolbarExtended) {
		return
	}

	addFilter(
		'editor.BlockEdit',
		'aioseo/extend-image-block-toolbar',
		createHigherOrderComponent(BlockEdit => {
			return props => {
				if (
					'core/image' !== props.name ||
					 !props.attributes.url
				) {
					return html`<${BlockEdit} ...${props} />`
				}

				const wpMedia = useSelect((select) => {
					return select('core').getMedia(props.attributes.id) || null
				}, [ `media-${props.attributes.id}` ])

				return html`
				<${Fragment}>
					<${BlockControls}>
						<${ToolbarGroup}>
							<${ToolbarButton}
								icon=${aiAssistantIcon}
								iconSize=${24}
								label=${strings.editWithAI}
								onClick=${event => { clickHandler(event.currentTarget, { initiator: { slug: 'image-block-toolbar', wpMedia } }) }}
								style=${{
									maxHeight : '90%',
									alignSelf : 'center',
									padding   : '0'
								}}
							/>
						</${ToolbarGroup}>
					</${BlockControls}>

					<${BlockEdit} ...${props} />
				</${Fragment}>`
			}
		}, 'extendImageBlockToolbar')
	)

	isImageBlockToolbarExtended = true
}

export const extendImageBlockPlaceholder = () => {
	const aiImageGeneratorStore = useAiImageGeneratorStore()

	if (!aiImageGeneratorStore.extend.imageBlockPlaceholder) {
		return
	}

	const selectedBlock = select('core/block-editor').getSelectedBlock()
	if (
		!selectedBlock ||
		'core/image' !== selectedBlock.name ||
		selectedBlock.attributes?.url
	) {
		return
	}

	const $block     = document.getElementById(`block-${selectedBlock.clientId}`)
	const $targetBtn = $block?.querySelector('.components-form-file-upload')
	if (
		!$targetBtn ||
		$block?.querySelector('.aioseo-ai-image-generator-btn')
	) {
		return
	}

	const $tempContainer = document.createElement('div')

	render(
		html`
			<${Button}
				className=${'aioseo-ai-image-generator-btn'}
				variant=${'secondary'}
				icon=${aiAssistantIcon}
				iconSize=${'20'}
				__next40pxDefaultSize=${true}
			>
				${strings.generateWithAI}
			</${Button}>`,
		$tempContainer
	)

	const $buttonElement = $tempContainer.firstChild?.cloneNode(true)
	if ($buttonElement) {
		// Place the button after the "Upload" button, inside the so called MediaPlaceholder component.
		$targetBtn.after($buttonElement)

		$buttonElement.addEventListener('click', () => {
			clickHandler($buttonElement, { initiator: { slug: 'image-block-placeholder' } })
		})
	}

	unmountComponentAtNode($tempContainer)

	$tempContainer.remove()
}

export const extendFeaturedImageButton = () => {
	const aiImageGeneratorStore = useAiImageGeneratorStore()

	if (!aiImageGeneratorStore.extend.featuredImageButton) {
		return
	}

	if ('edit-post/document' !== select('core/edit-post').getActiveGeneralSidebarName()) {
		return
	}

	const featuredImageId = select('core/editor').getEditedPostAttribute('featured_media')
	if (featuredImageId) {
		document.querySelector('.aioseo-ai-image-generator-btn-featured-image')?.remove()

		return
	}

	setTimeout(() => {
		const $featuredImageContainer = document.querySelector('.editor-post-featured-image__container')
		const $targetBtn              = $featuredImageContainer?.querySelector('button')
		if (
			!$targetBtn ||
			$featuredImageContainer?.querySelector('.aioseo-ai-image-generator-btn-featured-image')
		) {
			return
		}

		$featuredImageContainer.style.display = 'flex'
		$featuredImageContainer.style.gap     = '8px'

		const $tempContainer = document.createElement('div')

		render(
			html`
				<${Button}
					className=${'aioseo-ai-image-generator-btn-featured-image'}
					variant=${'secondary'}
					icon=${aiAssistantIcon}
					iconSize=${'20'}
					__next40pxDefaultSize=${true}
					title=${strings.generateWithAI}
				/>`,
			$tempContainer
		)

		const $buttonElement = $tempContainer.firstChild?.cloneNode(true)
		if ($buttonElement) {
			// Place the button after the "Set featured image" button.
			$targetBtn.after($buttonElement)

			$buttonElement.addEventListener('click', () => {
				clickHandler($buttonElement, { initiator: { slug: 'featured-image-btn' } })
			})
		}

		unmountComponentAtNode($tempContainer)

		$tempContainer.remove()
	})
}