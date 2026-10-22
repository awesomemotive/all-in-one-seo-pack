<template>
	<span style="display: none;" />
</template>

<script setup>
import { watch } from 'vue'

import { useTruSeoHighlighterStore } from '@/vue/stores'
import { useTruSeoHighlighter } from '@/vue/composables/TruSeoHighlighter'
import { createDebounce } from '@/vue/utils/debounce'

// Renders nothing. It exists to own the highlighter on a Divi 5 page, where the component that
// owns it everywhere else is not mounted.
//
// `Main.vue` hosts that bootstrap on the strength of "Main is always mounted" -- true in the
// metabox and the block sidebar, and false here: Divi's panel is a `<core-modal :show="modalOpen">`
// ({@see components/Modal.vue}), so `<post-settings />` MOUNTS ON OPEN rather than being rendered
// hidden. Measured on a fresh builder load: zero `.aioseo-post-settings` nodes, no analyzers
// seeded, and so no marks -- while the analysis those marks describe had already run and found
// five passive-voice sentences.
//
// WPBakery deliberately needs no such host: its modal renders the panel unconditionally and only
// toggles a CSS class, so Main is already mounted there. The difference is the modal, not the
// builder.
//
// Mounting this alongside the panel's own copy is a supported configuration rather than a hazard:
// the block editor already runs two instances and the machinery is built for it -- the delegated
// mark listener is registered once per document, and a click is claimed by the first instance to
// resolve it ({@see TruSeoHighlighter.js} `aioseoTruseoMarkClickHandled`).
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