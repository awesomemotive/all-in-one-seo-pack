<template>
	<draggable
		class="aioseo-toc-list"
		:class="[
			{ 'orderable' : allowReorder }
		]"
		v-bind="dragOptions"
		handle=".aioseo-drag-handle"
		:list="props.headings"
		@change="(value) => setReorder(value)"
		:item-key="(element) => element?.id || element.blockClientId"
	>
		<template #item="{element}">
			<li
				:class="[
					'aioseo-toc-list-item',
					{ 'heading-hidden' : element.hidden }
				]"
			>
				<div class="aioseo-toc-list-item__inner">
					<button
						class="aioseo-drag-handle has-icon"
						v-if="allowReorder"
					>
						<svg-drag />
					</button>

					<base-input
						class="row-input row-input--content"
						:modelValue="element.editedContent || element.content"
						@update:modelValue="value => setEditedContent(value, element)"
						:placeholder="element.content"
					>
						<template #append-icon>
							<div
								class="append-icon"
								v-if="!allowReorder"
								@click.native="handleAnchorInput"
							>
								<svg-toc-link />
							</div>
						</template>
					</base-input>

					<base-input
						v-if="!allowReorder"
						class="row-input row-input--anchor"
						:spellcheck=false
						:modelValue="element.anchor"
						@update:modelValue="value => setAnchor(value, element)"
					>
						<template #append-icon>
							<div class="append-icon">
								<core-tooltip>
									<div>
										<svg-info />
									</div>

									<template #tooltip>
										<p class="aioseo-tooltip__header">{{strings.tooltipHeader}}</p>

										<p>{{strings.tooltipDescription}}</p>
									</template>
								</core-tooltip>

								<div @click.native="handleAnchorInput">
									<svg-close />
								</div>
							</div>
						</template>
					</base-input>

					<button
						v-if="!allowReorder"
						:class="[
							{ 'active' : element.hidden },
							'aioseo-hide-heading-toggle',
							'has-icon'
						]"
						@click="setHiddenStatus(element)"
					>
						<svg-eye-off v-if="element.hidden" />
						<svg-eye v-else />
					</button>

					<List
						v-if="element.headings"
						class="aioseo-toc-list-nested"
						:headings="element.headings"
						:parent-block-id="element.blockClientId"
						:allowReorder="allowReorder"
						:client-id="clientId"
						:group="element.anchor"
					/>
				</div>
			</li>
		</template>
	</draggable>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import {
	useRootStore
} from '@/vue/stores'

import { cleanHtml, deepCopy } from '@/vue/standalone/blocks/utils'
import { cleanForSlug } from '@/vue/utils/cleanForSlug'
import { orderHeadings } from '../helpers'

import BaseInput from '@/vue/components/common/base/Input'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgClose from '@/vue/components/common/svg/Close'
import SvgDrag from '@/vue/components/common/svg/Drag'
import SvgEye from '@/vue/components/common/svg/Eye'
import SvgEyeOff from '@/vue/components/common/svg/EyeOff'
import SvgInfo from '@/vue/components/common/svg/Info'
import SvgTocLink from '@/vue/components/common/svg/Link'
import Draggable from 'vuedraggable'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	headings : {
		required : true,
		type     : Array
	},
	clientId : {
		required : true,
		type     : String
	},
	allowReorder : {
		required : false,
		type     : Boolean,
		default  : false
	},
	group : {
		required : false,
		type     : String,
		default  : 'description'
	},
	parentBlockId : {
		required : false,
		type     : String,
		default  : ''
	}
})

const rootStore = useRootStore()

const blockAttributes = ref(window.wp.data.select('core/block-editor').getBlockAttributes(props.clientId))

const strings = {
	tooltipHeader      : __('Edit HTML Anchor:', td),
	tooltipDescription : sprintf(
		// Translators: 1 - The plugin short name ("AIOSEO").
		__('The HTML anchor allows %1$s to link directly to your header from this table of contents block. Feel free to edit if you want, but an anchor is required. For headings without an anchor, %1$s will automatically generate them.', td),
		import.meta.env.VITE_SHORT_NAME
	)
}

const dragOptions = computed(() => ({
	tag        : blockAttributes.value.listStyle,
	animation  : 300,
	group      : props.group,
	disabled   : !props.allowReorder,
	ghostClass : 'aioseo-drag-ghost',
	dragClass  : 'aioseo-dragging'
}))

const setEditedContent = (newValue, heading) => {
	heading.editedContent = newValue === heading.content ? '' : cleanHtml(newValue, true, false)

	window.wp.data.dispatch('core/block-editor').updateBlockAttributes(props.clientId, {
		...blockAttributes.value,
		headings : props.headings
	})
}

const replaceSortedHeadings = (heading) => {
	if (heading.blockClientId === props.parentBlockId) {
		heading.headings = orderHeadings(heading.headings, heading.editedOrder)
	} else {
		heading.headings.map(replaceSortedHeadings)
	}

	return heading
}

const setReorder = (value) => {
	blockAttributes.value.reOrdered = true

	const movedElement = value?.moved?.element
	if (!movedElement) {
		return
	}

	const copyHeadings = deepCopy(blockAttributes.value.headings)
	const newHeadings = orderHeadings(copyHeadings.map(replaceSortedHeadings))
	window.wp.data.dispatch('core/block-editor').updateBlockAttributes(props.clientId, {
		...blockAttributes.value,
		headings : newHeadings
	})
}

const setAnchor = (newValue, heading) => {
	const clientId = heading.blockClientId
	const block    = window.wp.data.select('core/block-editor').getBlock(clientId)
	if (!block) {
		return
	}

	heading.anchor = cleanForSlug(newValue)
	if (!newValue) {
		const blockIndex = window.wp.data.select('core/block-editor').getBlockIndex(clientId)
		heading.anchor   = rootStore.aioseo.data.blocks.toc.hashPrefix + cleanForSlug(`${heading.content}-${blockIndex}`)
	}

	window.wp.data.dispatch('core/block-editor').updateBlockAttributes(clientId, {
		anchor : heading.anchor
	})
}

const getHeadings = (headings, heading) => {
	return headings?.map((h) => {
		if (
			h.content === heading.content &&
			h.level === Number(heading.level) &&
			h.anchor === heading.anchor
		) {
			return heading
		} else {
			return h.headings ? getHeadings(h.headings, heading) : h
		}
	})
}

const setHiddenStatus = (heading) => {
	heading.hidden = !heading.hidden

	if ('nested' === props.group) {
		window.wp.data.dispatch('core/block-editor').updateBlockAttributes(props.clientId, blockAttributes.value)
		return
	}

	window.wp.data.dispatch('core/block-editor').updateBlockAttributes(props.clientId, getHeadings(blockAttributes.value.headings, heading))
}

const handleAnchorInput = (event) => {
	const inputRow = event.target.closest('.aioseo-toc-list-item')
	const anchorInput = inputRow?.querySelector('.row-input--anchor input')

	if (!anchorInput) {
		return
	}

	if (!inputRow.classList.contains('anchor-edit')) {
		anchorInput.focus({ preventScroll: true })
		inputRow.classList.add('anchor-edit', 'anchor-is-animating')

		anchorInput.addEventListener('animationend', function anchorIn () {
			inputRow.classList.remove('anchor-is-animating')
			inputRow.classList.add('done')

			anchorInput.removeEventListener('animationend', anchorIn, false)
		})
	} else {
		inputRow.classList.add('anchor-is-animating')
		inputRow.classList.remove('anchor-edit')
		inputRow.classList.remove('done')

		anchorInput.addEventListener('animationend', function anchorOut () {
			inputRow.classList.remove('anchor-is-animating')

			inputRow.removeEventListener('animationend', anchorOut, false)
		})
	}
}

onMounted(() => {
	nextTick(() => {
		window.aioseoBus.$on('updateToc' + props.clientId, () => {
			blockAttributes.value = window.wp.data.select('core/block-editor').getBlockAttributes(props.clientId)
		})
	})
})
</script>