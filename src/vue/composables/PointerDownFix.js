import { computed, ref } from 'vue'

import { resolveAdapter } from '@/vue/plugins/tru-seo/editors'

// How long a press may wait for its click before the guard clears itself.
const CLICK_WAIT = 500

/**
 * Runs a fix on POINTER DOWN for editors that cannot be written to on click.
 *
 * WHEN a fix runs is not a detail for one editor. AIOSEO's fix surfaces -- the mark popover and
 * the Spelling tab -- live in the ADMIN document, and Divi 4 closes its inline editor from a
 * mousedown ANYWHERE in that document; measured, and `preventDefault()` does not stop it. Its
 * closed editor is read-only and its React commit handler is gone, so a fix that runs on `click`
 * gets far enough to change what the canvas SHOWS and commits nothing: the user reads it as fixed,
 * the analyser still reports the issue because it reads the module's stored props, and the edit
 * dies on the next render.
 *
 * Measured inside the mousedown dispatch, the editor is still open and a write still lands. So an
 * adapter that needs it applies there, and the click that follows must do nothing rather than
 * apply the same fix twice. Every other editor keeps applying on click, where a press the user
 * drags away from is correctly abandoned.
 *
 * @returns {Object} `onFixPointerDown` and `onFixClick`, each taking the fix to run.
 */
export const usePointerDownFix = () => {
	const fixesOnPointerDown = computed(() => true === resolveAdapter()?.fixesOnPointerDown)

	// Set for exactly one click: the one this gesture is about to deliver.
	const handledOnPointerDown = ref(false)

	const onFixPointerDown = (apply) => {
		if (!fixesOnPointerDown.value) {
			return
		}

		handledOnPointerDown.value = true

		// A press the user drags away from never delivers its click, and a guard left standing
		// would swallow the next real one.
		setTimeout(() => {
			handledOnPointerDown.value = false
		}, CLICK_WAIT)

		apply()
	}

	const onFixClick = (apply) => {
		if (handledOnPointerDown.value) {
			handledOnPointerDown.value = false

			return
		}

		apply()
	}

	return { onFixPointerDown, onFixClick }
}