<template>
	<core-alert
		v-if="notice"
		class="aioseo-page-builder-notice"
		type="yellow"
	>
		<span v-html="notice" />
	</core-alert>
</template>

<script setup>
import { computed } from 'vue'

import {
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import { __, sprintf } from '@/vue/plugins/translations'
import { isPageBuilderEditor } from '@/vue/utils/context'
import { escapeHtml, sanitizeUrl } from '@/vue/utils/strings'
import { resolveAdapter } from '@/vue/plugins/tru-seo/editors'

import CoreAlert from '@/vue/components/common/core/alert/Index'

const td = import.meta.env.VITE_TEXTDOMAIN

const postEditorStore = usePostEditorStore()
const rootStore       = useRootStore()

// "A, B and C" in the admin's language; a browser without Intl.ListFormat gets commas.
const formatList = (names) => {
	try {
		return new Intl.ListFormat(document.documentElement.lang || undefined, { type: 'conjunction' }).format(names)
	} catch (_e) {
		return names.join(', ')
	}
}

// Inside a builder, names the elements whose text this view can't fully analyze
// ({@see editors/types.js:getPartialAnalysisElements}). Shown only when the page has one, so a
// page both views analyze alike carries no warning.
const builderNotice = () => {
	const adapter = resolveAdapter()
	const names   = adapter?.getPartialAnalysisElements?.() || []

	if (!names.length) {
		return false
	}

	const url = adapter.getFullViewUrl?.()

	return sprintf(
		// Translators: 1 - A list of page builder element names, 2 - Opening anchor tag, 3 - Closing anchor tag.
		__('%1$s content is only fully analyzed in the %2$sFrontend Editor%3$s.', td),
		escapeHtml(formatList(names)),
		url ? '<a href="' + escapeHtml(sanitizeUrl(url)) + '">' : '',
		url ? '</a>' : ''
	)
}

// A page builder created this post, but it's being analyzed in a standard editor
// (block or classic), where the content won't match what the builder renders.
// Point the user back to the builder for an accurate analysis — but not inside the
// builder itself, since there's nothing to switch to there.
const notice = computed(() => {
	if (isPageBuilderEditor()) {
		// Gated on the results so the note follows each analysis: the elements come from the
		// builder's own models, which Vue can't watch, and every analysis assigns a new object.
		return !!postEditorStore.currentPost?.truseo && builderNotice()
	}

	const integration = rootStore.aioseo.integration
	const editLink    = postEditorStore.currentPost?.editlink

	if (!integration || !editLink) {
		return false
	}

	const pageBuilderName = integration.charAt(0).toUpperCase() + integration.slice(1)

	return sprintf(
		// Translators: 1 - The Page Builder name, 2 - Opening anchor tag, 3 - Closing anchor tag.
		__('We have detected that you are currently using the %1$s Page Builder. Please click %2$shere%3$s to use the %1$s editor for a most accurate result.', td),
		pageBuilderName,
		'<a href="' + editLink + '">',
		'</a>'
	)
})
</script>