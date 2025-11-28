/**
 * Gets keyphrase panel elements.
 *
 * @returns {HTMLCollection} Collection of keyphrase panel elements.
 */
const getKeyphrasePanels = () => {
	return document.getElementsByClassName('keyphrase-name')
}

/**
 * Checks if an additional keyphrase already exists.
 *
 * @param {Object} options                 Options object.
 * @param {Object} options.postEditorStore Post editor Pinia store instance.
 * @param {string} options.keyphrase       Keyphrase to check.
 * @returns {boolean}                      True if keyphrase exists, false otherwise.
 */
export const hasAdditionalKeyphrase = ({ postEditorStore, keyphrase }) => {
	const { additional } = postEditorStore.currentPost.keyphrases
	if (!additional || !Array.isArray(additional)) {
		return false
	}

	return 0 < additional.filter(k => k.keyphrase.toLowerCase() === keyphrase.toLowerCase()).length
}

/**
 * Gets an additional keyphrase object by keyphrase value.
 *
 * @param {Object} options                 Options object.
 * @param {Object} options.postEditorStore Post editor Pinia store instance.
 * @param {string} options.keyphrase       Keyphrase to find.
 * @returns {Object|undefined}             Keyphrase object or undefined if not found.
 */
export const getAdditionalKeyphrase = ({ postEditorStore, keyphrase }) => {
	const { additional } = postEditorStore.currentPost.keyphrases
	if (!additional || !Array.isArray(additional)) {
		return undefined
	}

	return additional.find(k => k.keyphrase.toLowerCase() === keyphrase.toLowerCase())
}

/**
 * Updates an additional keyphrase value.
 *
 * @param {Object}   options                 Options object.
 * @param {Object}   options.postEditorStore Post editor Pinia store instance.
 * @param {Object}   options.truSeo          TruSEO instance for running analysis.
 * @param {number}   options.index           Index of the keyphrase to update.
 * @param {string}   options.value           New keyphrase value.
 * @param {Function} options.onSuccess       Callback when keyphrase is updated successfully.
 * @returns {void}
 */
export const updateAdditionalKeyphrase = ({ postEditorStore, truSeo, index, value, onSuccess }) => {
	if (!postEditorStore.currentPost.keyphrases.additional[index]) {
		return
	}

	postEditorStore.currentPost.keyphrases.additional[index].keyphrase = value
	postEditorStore.currentPost.keyphrases.additional[index].score     = 0
	postEditorStore.currentPost.loading.additional[index]              = true
	postEditorStore.isDirty                                            = true

	truSeo.runAnalysis({
		postId   : postEditorStore.currentPost.id,
		postData : postEditorStore.currentPost
	})

	if (onSuccess) {
		onSuccess(index)
	}
}

/**
 * Deletes an additional keyphrase by index.
 *
 * @param {Object}   options                 Options object.
 * @param {Object}   options.postEditorStore Post editor Pinia store instance.
 * @param {Object}   options.truSeo          TruSEO instance for running analysis.
 * @param {number}   options.index           Index of the keyphrase to delete.
 * @param {Function} options.onSuccess       Callback when keyphrase is deleted successfully.
 * @returns {void}
 */
export const deleteAdditionalKeyphraseByIndex = ({ postEditorStore, truSeo, index, onSuccess }) => {
	const additionalCopy = [ ...postEditorStore.currentPost.keyphrases.additional ]
	additionalCopy.splice(index, 1)
	postEditorStore.currentPost.keyphrases.additional = null

	setTimeout(() => {
		postEditorStore.currentPost.keyphrases.additional = additionalCopy
		postEditorStore.isDirty                           = true

		truSeo.runAnalysis({
			postId   : postEditorStore.currentPost.id,
			postData : postEditorStore.currentPost
		})

		if (onSuccess) {
			onSuccess()
		}
	}, 300)
}

/**
 * Adds an additional keyphrase from input.
 *
 * @param {Object}   options                 Options object.
 * @param {Object}   options.postEditorStore Post editor Pinia store instance.
 * @param {Object}   options.truSeo          TruSEO instance for running analysis.
 * @param {string}   options.screenContext   Screen context for finding the input.
 * @param {Function} options.onSuccess       Callback when keyphrase is added. Receives index.
 * @returns {number|null}                    Index of added keyphrase or null if failed.
 */
export const addAdditionalKeyphraseFromInput = ({ postEditorStore, truSeo, screenContext, onSuccess }) => {
	const maxKeyphrases       = postEditorStore.currentPost.maxAdditionalKeyphrases
	const currentKeyphrasesCount = postEditorStore.currentPost.keyphrases?.additional?.length || 0

	if (maxKeyphrases <= currentKeyphrasesCount) {
		return null
	}

	const keyphraseInputComponent = document.getElementsByClassName(`add-keyphrase-${screenContext}-input`)
	if (!keyphraseInputComponent || !keyphraseInputComponent[0]) {
		return null
	}

	const keyphraseInput      = keyphraseInputComponent[0].querySelector('.medium')
	const keyphraseInputValue = keyphraseInput?.value.trim()

	if (!keyphraseInputValue) {
		return null
	}

	const newKeyphrase      = { keyphrase: keyphraseInputValue, score: 0 }
	const newKeyphraseIndex = postEditorStore.currentPost.keyphrases.additional.push(newKeyphrase)
	const keyphrasePanel    = getKeyphrasePanels()

	postEditorStore.currentPost.loading.additional[0] = true
	keyphraseInput.value = ''
	keyphraseInput.blur()

	postEditorStore.isDirty = true

	if (keyphrasePanel[newKeyphraseIndex]) {
		keyphrasePanel[newKeyphraseIndex].click()
	}

	truSeo.runAnalysis({
		postId   : postEditorStore.currentPost.id,
		postData : postEditorStore.currentPost
	})

	const index = newKeyphraseIndex - 1
	if (onSuccess) {
		onSuccess(index)
	}

	return index
}

/**
 * Adds an additional keyphrase.
 *
 * @param {Object}   options                    Options object.
 * @param {Object}   options.postEditorStore    Post editor Pinia store instance.
 * @param {Object}   options.truSeo             TruSEO instance for running analysis.
 * @param {string}   options.keyphrase          Keyphrase to add.
 * @param {Function} options.onStart            Callback when adding starts.
 * @param {Function} options.onSuccess          Callback when keyphrase is added. Receives keyphraseIndex.
 * @param {Function} options.onMaxReached       Callback when max keyphrases reached.
 * @param {Object}   options.nextTick           Vue's nextTick function.
 * @returns {Promise<number|null>}              Index of added keyphrase or null if failed.
 */
export const addAdditionalKeyphrase = async ({ postEditorStore, truSeo, keyphrase, onStart, onSuccess, onMaxReached, nextTick }) => {
	const maxKeyphrases       = postEditorStore.currentPost.maxAdditionalKeyphrases
	const currentKeyphrasesCount = postEditorStore.currentPost.keyphrases?.additional?.length || 0

	if (maxKeyphrases <= currentKeyphrasesCount) {
		if (onMaxReached) {
			onMaxReached()
		}
		return null
	}

	if (onStart) {
		onStart()
	}

	const { additional } = postEditorStore.currentPost.keyphrases
	const keyphraseIndex = additional.push({ keyphrase, score: 0 })
	const keyphrasePanel = getKeyphrasePanels()
	postEditorStore.currentPost.keyphrases.additional = additional
	postEditorStore.isDirty = true

	await truSeo.runAnalysis({
		postId   : postEditorStore.currentPost.id,
		postData : postEditorStore.currentPost
	})

	if (nextTick) {
		await nextTick()
	}

	if (keyphrasePanel[keyphraseIndex]) {
		keyphrasePanel[keyphraseIndex].click()
	}

	if (onSuccess) {
		onSuccess(keyphraseIndex)
	}

	return keyphraseIndex
}

/**
 * Navigates to an additional keyphrase panel.
 *
 * @param {Object}   options                 Options object.
 * @param {Object}   options.postEditorStore Post editor Pinia store instance.
 * @param {string}   options.keyphrase       Keyphrase to navigate to.
 * @param {Function} options.onSuccess       Callback when navigation succeeds.
 * @param {Function} options.onNotFound      Callback when keyphrase not found.
 * @returns {boolean}                        True if navigation succeeded, false otherwise.
 */
export const navigateToAdditionalKeyphrase = ({ postEditorStore, keyphrase, onSuccess, onNotFound }) => {
	const { additional }  = postEditorStore.currentPost.keyphrases
	const keyphraseIndex  = additional.findIndex(k => k.keyphrase.toLowerCase() === keyphrase.toLowerCase())

	if (-1 === keyphraseIndex) {
		if (onNotFound) {
			onNotFound()
		}
		return false
	}

	const keyphrasePanel = getKeyphrasePanels()
	if (keyphrasePanel[keyphraseIndex + 1]) {
		keyphrasePanel[keyphraseIndex + 1].click()
	}

	if (onSuccess) {
		onSuccess(keyphraseIndex)
	}

	return true
}

/**
 * Removes an additional keyphrase.
 *
 * @param {Object}   options                 Options object.
 * @param {Object}   options.postEditorStore Post editor Pinia store instance.
 * @param {string}   options.keyphrase       Keyphrase to remove.
 * @param {Function} options.onStart         Callback when removal starts.
 * @param {Function} options.onSuccess       Callback when keyphrase is removed. Receives keyphraseIndex.
 * @param {Function} options.onNotFound      Callback when keyphrase not found.
 * @param {Function} options.nextTick        Vue's nextTick function.
 * @returns {Promise<boolean>}               True if removal succeeded, false otherwise.
 */
export const removeAdditionalKeyphrase = async ({ postEditorStore, keyphrase, onStart, onSuccess, onNotFound, nextTick }) => {
	if (onStart) {
		onStart()
	}

	const { additional }  = postEditorStore.currentPost.keyphrases
	const keyphraseIndex  = additional.findIndex(k => k.keyphrase.toLowerCase() === keyphrase.toLowerCase())

	if (-1 === keyphraseIndex) {
		if (onNotFound) {
			onNotFound()
		}
		return false
	}

	additional.splice(keyphraseIndex, 1)
	postEditorStore.currentPost.keyphrases.additional = additional

	const keyphrasePanel = getKeyphrasePanels()
	if (keyphrasePanel[0]) {
		keyphrasePanel[0].click()
	}

	if (nextTick) {
		await nextTick()
	}

	if (onSuccess) {
		onSuccess(keyphraseIndex)
	}

	return true
}