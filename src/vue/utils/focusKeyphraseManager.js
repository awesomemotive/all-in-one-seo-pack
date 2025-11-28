/**
 * Gets the focus keyphrase input element.
 *
 * @param   {string} screenContext The screen context (sidebar, metabox, etc).
 * @returns {HTMLElement|null}     The input element or null.
 */
const getFocusKeyphraseInput = (screenContext) => {
	const keyphraseInputComponent = document.getElementsByClassName(`add-focus-keyphrase-${screenContext}-input`)
	if (!keyphraseInputComponent || !keyphraseInputComponent[0]) {
		return null
	}

	return keyphraseInputComponent[0].querySelector('.medium')
}

/**
 * Adds a focus keyphrase from the input field.
 *
 * @param {Object}   options                  Options object.
 * @param {Object}   options.postEditorStore  Post editor Pinia store instance.
 * @param {Object}   options.truSeo           TruSEO instance for running analysis.
 * @param {string}   options.screenContext    Screen context for finding the input.
 * @param {Function} options.onSuccess        Callback when keyphrase is added successfully.
 * @returns {boolean}                         True if keyphrase was added, false otherwise.
 */
export const addFocusKeyphrase = ({ postEditorStore, truSeo, screenContext, onSuccess }) => {
	const keyphraseInput = getFocusKeyphraseInput(screenContext)
	if (!keyphraseInput) {
		return false
	}

	const keyphraseInputValue = keyphraseInput.value.trim()
	if (!keyphraseInputValue) {
		return false
	}

	postEditorStore.currentPost.keyphrases.focus = {
		keyphrase : keyphraseInputValue,
		score     : 0,
		analysis  : {}
	}
	postEditorStore.currentPost.loading.focus = true

	keyphraseInput.value = ''
	keyphraseInput.blur()

	postEditorStore.isDirty = true
	truSeo.runAnalysis({
		postId   : postEditorStore.currentPost.id,
		postData : postEditorStore.currentPost
	})

	if (onSuccess) {
		onSuccess(keyphraseInputValue)
	}

	return true
}

/**
 * Updates the focus keyphrase value.
 *
 * @param {Object}   options                 Options object.
 * @param {Object}   options.postEditorStore Post editor Pinia store instance.
 * @param {Object}   options.truSeo          TruSEO instance for running analysis.
 * @param {string}   options.value           New keyphrase value.
 * @param {Function} options.onSuccess       Callback when keyphrase is updated successfully.
 * @returns {void}
 */
export const updateFocusKeyphrase = ({ postEditorStore, truSeo, value, onSuccess }) => {
	postEditorStore.currentPost.keyphrases.focus.keyphrase = value
	postEditorStore.currentPost.loading.focus              = true
	postEditorStore.isDirty                                = true

	truSeo.runAnalysis({
		postId   : postEditorStore.currentPost.id,
		postData : postEditorStore.currentPost
	})

	if (onSuccess) {
		onSuccess(value)
	}
}

/**
 * Deletes the focus keyphrase.
 *
 * @param {Object}   options                 Options object.
 * @param {Object}   options.postEditorStore Post editor Pinia store instance.
 * @param {Object}   options.truSeo          TruSEO instance for running analysis.
 * @param {Function} options.onSuccess       Callback when keyphrase is deleted successfully.
 * @returns {void}
 */
export const deleteFocusKeyphrase = ({ postEditorStore, truSeo, onSuccess }) => {
	postEditorStore.currentPost.keyphrases.focus.keyphrase = ''
	postEditorStore.isDirty                                = true

	truSeo.runAnalysis({
		postId   : postEditorStore.currentPost.id,
		postData : postEditorStore.currentPost
	})

	if (onSuccess) {
		onSuccess()
	}
}