<template>
	<span style="display: none;" />
</template>

<script setup>
import { watch } from 'vue'

import { useTruSeoHighlighterStore } from '@/vue/stores'
import { useTruSeoHighlighter } from '@/vue/composables/TruSeoHighlighter'
import { createDebounce } from '@/vue/utils/debounce'

// Renders nothing. Owns the highlighter on a builder page, where the post-settings app mounts
// only when the AIOSEO panel opens, so highlights show on load. Running alongside the panel's own
// instance is supported, as the block editor's metabox and sidebar already do.
const truSeoHighlighterStore = useTruSeoHighlighterStore()

const { watchHighlightSentences } = useTruSeoHighlighter()
const debouncedWatchHighlightSentences = createDebounce((value, oldValue) => watchHighlightSentences(value, oldValue), 300)

watch(() => truSeoHighlighterStore.allHighlightSentences, (value, oldValue) => {
	debouncedWatchHighlightSentences(value, oldValue)
}, { deep: true, immediate: true })

// Both seeders are additive, idempotent and gated on the master toggle, so the panel re-running
// them later changes nothing. Spelling is picked up once its dictionary finishes loading.
watch(() => truSeoHighlighterStore.availableHighlightAnalyzers, () => {
	truSeoHighlighterStore.syncNewHighlightAnalyzers()
	truSeoHighlighterStore.ensureSpellingHighlightPainted()
}, { immediate: true })
</script>