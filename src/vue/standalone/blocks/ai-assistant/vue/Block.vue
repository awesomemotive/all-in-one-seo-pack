<template>
	<div class="aioseo-ai-assistant-block">
		<div class="aioseo-ai-assistant-block__head">
			<div
				ref="contentElement"
				class="aioseo-ai-assistant-block__content"
				v-html="parsedContent"
			/>
		</div>

		<div class="aioseo-ai-assistant-block__body">
			<div class="aioseo-ai-assistant-block__input-container">
				<div class="aioseo-ai-assistant-block__input-row">
					<div class="aioseo-ai-assistant-block__input-col aioseo-ai-assistant-block__input-col--grow">
						<textarea
							ref="inputElement"
							class="aioseo-ai-assistant-block__input"
							:placeholder="strings.placeholder"
							:disabled="$root.$data.isFetching"
							v-model="$root.$data.userPrompt"
							@input="resizeInput"
							rows="1"
							:maxlength="aiAssistantStore.options.input.userPrompt.maxlength"
						/>
					</div>

					<div class="aioseo-ai-assistant-block__input-col">
						<div class="aioseo-ai-assistant-block__controls">
							<base-button
								v-if="buttonStates.delete.show"
								size="small"
								type="gray"
								class="aioseo-ai-assistant-block__btn"
								@click.stop.exact="onBtnDeleteClick"
								:title="strings.delete"
							>
								<svg-trash
									width="18"
									height="18"
								/>
							</base-button>

							<base-button
								v-if="buttonStates.submit.show"
								:disabled="buttonStates.submit.disabled"
								:loading="buttonStates.submit.loading"
								:type="buttonStates.submit.type"
								:title="buttonStates.submit.title"
								size="small-table"
								class="aioseo-ai-assistant-block__btn"
								@click.stop.exact="onBtnSubmitClick"
							>
								<component
									v-if="'object' === typeof buttonStates.submit.text"
									:is="buttonStates.submit.text"
									width="18"
									height="18"
								 />

								 <span v-else>{{ buttonStates.submit.text }}</span>
							</base-button>

							<base-button
								v-if="buttonStates.insert.show"
								size="small-table"
								type="blue"
								class="aioseo-ai-assistant-block__btn"
								@click.stop.exact="onBtnInsertClick"
							>
								<span>{{ strings.insert }}</span>
							</base-button>

							<base-button
								v-if="buttonStates.stop.show"
								size="small-table"
								type="gray"
								class="aioseo-ai-assistant-block__btn"
								@click.stop.exact="onBtnStopClick"
							>
								<span>{{ strings.stop }}</span>
							</base-button>
						</div>
					</div>
				</div>

				<div class="aioseo-ai-assistant-block__input-row aioseo-ai-assistant-block__input-row--gray">
					<div class="aioseo-ai-assistant-block__input-col aioseo-ai-assistant-block__input-col--disclaimer">
						<span v-html="strings.disclaimer" />
					</div>
				</div>
			</div>

			<div class="aioseo-ai-assistant-block__error-container">
				<core-alert
					v-if="!!error"
					v-html="error"
					type="red"
					size="small"
					style="margin-top: 10px;"
				/>

				<core-alert
					v-if="!!userPromptLengthWarning"
					v-html="userPromptLengthWarning"
					type="yellow"
					size="small"
					style="margin-top: 10px;"
				/>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, computed, getCurrentInstance, onMounted, onBeforeUnmount, nextTick } from 'vue'

import {
	useAiAssistantStore,
	useAiStore,
	usePostEditorStore
} from '@/vue/stores'

import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import { fetchEventSource } from '@microsoft/fetch-event-source'
import { __, sprintf } from '@/vue/plugins/translations'
import { getPostEditedContent } from '@/vue/plugins/tru-seo/components/postContent'
import { getPostEditedTitle } from '@/vue/plugins/tru-seo/components/postTitle'
import { useAiContent } from '@/vue/composables/AiContent'

import links from '@/vue/utils/links'

import BaseButton from '@/vue/components/common/base/Button'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import SvgRenew from '@/vue/components/common/svg/Renew'
import SvgTrash from '@/vue/components/common/svg/Trash'

const td = import.meta.env.VITE_TEXTDOMAIN

const {
	toneOptions,
	audienceOptions,
	hasEnoughCredits
} = useAiContent()

const aiAssistantStore = useAiAssistantStore()
const aiStore          = useAiStore()
const postEditorStore  = usePostEditorStore()

const contentElement = ref(null)
const controller     = ref({})
const inputElement   = ref(null)
const parsedContent  = ref('')
const errors         = ref({
	api : null
})

// Store event handler references to properly remove only our specific listeners.
let onTranslateChange = null,
	onImproveChange   = null

const app = getCurrentInstance()

const strings = {
	aiTextGenerator     : __('AI Text Generator', td),
	insert              : __('Insert', td),
	placeholder         : __('Write a blog about... Generate a table for...', td),
	requestUnsuccessful : __('The request was unsuccessful. Status code:', td),
	generate            : __('Generate', td),
	stop                : __('Stop', td),
	delete              : __('Delete', td),
	regenerate          : __('Regenerate', td),
	noPermission        : __('You do not have permission to use the AI Assistant.', td),
	disclaimer          : sprintf(
		// Translators: 1 - The plugin name ("All in One SEO").
		__('AI-generated content could be inaccurate or biased. %1$s', td),
		links.getDocLink(GLOBAL_STRINGS.learnMore, 'aiDisclaimer', true)
	),
	userPromptMaxlength : __('You have reached the maximum number of allowed characters.', td)
}

const blockId = computed(() => {
	return inputElement.value?.closest('[data-block]').dataset.block
})

const buttonStates = computed(() => {
	const hasUserPrompt       = !!app.root.data.userPrompt?.trim().length
	const hasAssistantMessage = !!app.root.data.messages?.length && app.root.data.messages.find(m => 'assistant' === m.role)
	const isFetching          = !!app.root.data.isFetching
	const hasContent          = !!parsedContent.value.length
	const submitDisabled      = !hasUserPrompt || !aiAssistantStore.hasPermission || !hasEnoughCredits(aiAssistantStore.generationPrice)

	return {
		submit : {
			disabled : submitDisabled,
			show     : true,
			loading  : isFetching,
			text     : hasAssistantMessage ? SvgRenew : strings.generate,
			title    : hasAssistantMessage ? strings.regenerate : '',
			type     : hasAssistantMessage ? 'gray' : 'blue'
		},
		insert : {
			show : hasContent && !isFetching
		},
		stop : {
			show : isFetching
		},
		delete : {
			show : hasAssistantMessage && !isFetching
		}
	}
})

const error = computed(() => {
	if (!aiAssistantStore.hasPermission) {
		return strings.noPermission
	}

	if (errors.value.api) {
		return errors.value.api
	}

	return null
})

const userPromptLengthWarning = computed(() => {
	if (app.root.data.userPrompt.length >= aiAssistantStore.options.input.userPrompt.maxlength) {
		return strings.userPromptMaxlength
	}

	return null
})

const onBtnInsertClick = () => {
	let blocks = window.wp.blocks.pasteHandler({
		HTML      : parsedContent.value,
		plainText : app.root.data.content,
		mode      : 'AUTO'
	})

	if ('string' === typeof blocks) {
		blocks = window.wp.blocks.createBlock('core/paragraph', {
			content : blocks
		})
	}

	const { dispatch } = window.wp.data

	dispatch('core/block-editor').replaceBlock(blockId.value, blocks)
}

const onBtnSubmitClick = () => {
	initFetch()

	// Use nextTick to ensure the DOM has been updated before checking visibility.
	nextTick(() => {
		scrollToContentElement()
	})
}

const onBtnStopClick = () => {
	controller.value.abort()
}

const onBtnDeleteClick = () => {
	const { dispatch } = window.wp.data

	dispatch('core/block-editor').removeBlock(blockId.value)
}

const resizeInput = () => {
	const node = inputElement.value
	node.style.height = 'auto'
	node.style.height = node.scrollHeight + 'px'
}

const updateParsedContent = () => {
	parsedContent.value = aiAssistantStore.markdownConverter.render(app.root.data.content)

	resizeInput()
}

const updateMessages = (data) => {
	const currentMessages = app.root.data.messages || []
	app.root.data.messages = [ ...currentMessages, data ]
}

const getBlock = () => {
	return window.wp.data.select('core/block-editor').getBlock(blockId.value)
}

const isElementTopVisible = (element) => {
	if (!element) {
		return false
	}

	const rect = element.getBoundingClientRect()
	const viewportHeight = window.innerHeight || document.documentElement.clientHeight

	// Check if the top of the element is visible in the viewport.
	return 0 <= rect.top && rect.top <= viewportHeight
}

const scrollToContentElement = () => {
	if (!contentElement.value) {
		return
	}

	// Check if the top of the content is visible.
	if (!isElementTopVisible(contentElement.value)) {
		contentElement.value.scrollIntoView({
			behavior : 'smooth',
			block    : 'start',
			inline   : 'nearest'
		})
	}
}

const getPostContent = () => {
	let postContent = ''
	try {
		// Hide blocks temporarily to prevent their content from being included in the post content.
		document.querySelectorAll('.aioseo-ai-assistant-block').forEach($block => {
			$block.style.visibility = 'hidden'
		})

		postContent = getPostEditedContent(true)

		// Make sure the current block's content is not included in the post content.
		postContent = postContent.replace(new RegExp(`<!-- wp:${getBlock().name}.*?/-->`, 'gs'), '')

		// Limit post content length to prevent API payload issues (max ~10k characters).
		if (postContent && 10000 < postContent.length) {
			postContent = postContent.substring(0, 10000)
		}
	} catch (error) {
		console.warn('Could not retrieve post content for context:', error)
		postContent = ''
	} finally {
		document.querySelectorAll('.aioseo-ai-assistant-block').forEach($block => {
			$block.style.visibility = 'visible'
		})
	}

	return postContent.trim()
}

const initFetch = (args = {}) => {
	controller.value = new AbortController()
	errors.value.api = null

	app.root.data.isFetching = true
	app.root.data.content = ''

	updateParsedContent()

	const newMessage = {
		role : 'user'
	}

	if (args?.translate) {
		newMessage.translate = args.translate
	} else if (args?.improve) {
		newMessage.improve = args.improve
	} else {
		newMessage.content = app.root.data.userPrompt
	}

	updateMessages(newMessage)

	fetchEventSource(links.restUrl('ai/assistant'), {
		headers : {
			'Content-Type' : 'application/json',
			'X-WP-Nonce'   : window.aioseo.nonce
		},
		method : 'POST',
		body   : JSON.stringify({
			messages     : app.root.data.messages,
			tone         : toneOptions.find(t => t.value === getBlock().attributes.tone).label,
			audience     : audienceOptions.find(a => a.value === getBlock().attributes.audience).label,
			postContent  : getPostContent(),
			postTitle    : (getPostEditedTitle() || '').trim(),
			focusKeyword : (postEditorStore.currentPost?.keyphrases?.focus?.keyphrase || '').trim()
		}),
		cache          : 'no-store',
		openWhenHidden : true,
		onclose () {
			updateParsedContent()
		},
		onerror (err) {
			// Rethrow to stop the operation otherwise it will retry forever.
			throw err
		},
		onmessage (ev) {
			const json = JSON.parse(ev.data)
			if (json?.error) {
				throw new Error(`${json.error}`)
			}

			app.root.data.content += json?.content ?? ''

			updateParsedContent()
		},
		onopen (response) {
			if (response.ok) {
				return
			}

			// Throwing an error here fires the `onerror` event.
			throw new Error(`${strings.requestUnsuccessful.trim()} ${response.status}`)
		},
		signal : controller.value.signal
	})
		.catch((err) => {
			console.error(err)

			errors.value.api = err.message
		})
		.then(() => {
			if (!app.root.data.content.trim().length) {
				// At this point if the content is empty, we'll remove the last user message because the user might have aborted the request.
				app.root.data.messages.pop()

				return
			}

			updateMessages({
				role    : 'assistant',
				content : app.root.data.content
			})
		})
		.finally(async () => {
			await aiStore.getCredits(true)

			app.root.data.isFetching = false

			await nextTick()

			updateParsedContent()
		})
}

onMounted(() => {
	updateParsedContent()

	onTranslateChange = (payload) => {
		if (!payload || payload.clientId !== blockId.value || !payload.translate) {
			return
		}

		initFetch({ translate: payload.translate })
	}

	onImproveChange = (payload) => {
		if (!payload || payload.clientId !== blockId.value || !payload.improve) {
			return
		}

		initFetch({ improve: payload.improve })
	}

	window.aioseoBus.$on('aiAssistantTranslateChange', onTranslateChange)
	window.aioseoBus.$on('aiAssistantImproveChange', onImproveChange)

	nextTick(() => {
		inputElement.value?.focus()
	})
})

onBeforeUnmount(() => {
	window.aioseoBus.$off('aiAssistantTranslateChange', onTranslateChange)
	window.aioseoBus.$off('aiAssistantImproveChange', onImproveChange)
})
</script>