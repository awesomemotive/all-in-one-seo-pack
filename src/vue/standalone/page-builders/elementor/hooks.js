/**
 * A class that conforms to the Elementor hooks format,
 * so that we can register its instance as a callback.
 *
 * Specifically, this is a UI After hook.
 */
class ElementorUIHook extends window.$e.modules.hookUI.Base {
	/**
	 * Constructs this class.
	 *
	 * @param {string}   hook     The hook to register to.
	 * @param {string}   id       The id to register our callback behind.
	 * @param {Function} callback The function to call when the hook is fired.
	 */
	constructor (hook, id, callback) {
		super()
		this.hook = hook
		this.id = id
		this.callback = callback
	}

	/**
	 * A getter for the Elementor hook we want to register to.
	 *
	 * @returns {string} The hook to register to.
	 */
	getCommand () {
		return this.hook
	}

	/**
	 * A getter for the id we register our callback behind.
	 *
	 * @returns {string} The id to register behind.
	 */
	getId () {
		return this.id
	}

	/**
	 * This function is called when the hook is fired.
	 *
	 * @returns {boolean} True, always.
	 */
	apply () {
		this.callback()

		// Never the callback's own return value: Elementor's runCallbacks() logs a falsy hook result
		// as "Callback failed", and these callbacks have nothing to report.
		return true
	}
}

/**
 * A class that conforms to the Elementor hooks format,
 * so that we can register its instance as a callback.
 *
 * Specifically, this is a Data After hook.
 */
class ElementorDataHook extends window.$e.modules.hookData.Base {
	/**
	 * Constructs this class.
	 *
	 * @param {string}   hook     The hook to register to.
	 * @param {string}   id       The id to register our callback behind.
	 * @param {Function} callback The function to call when the hook is fired.
	 */
	constructor (hook, id, callback) {
		super()
		this.hook = hook
		this.id = id
		this.callback = callback
	}

	/**
	 * A getter for the Elementor hook we want to register to.
	 *
	 * @returns {string} The hook to register to.
	 */
	getCommand () {
		return this.hook
	}

	/**
	 * A getter for the id we register our callback behind.
	 *
	 * @returns {string} The id to register behind.
	 */
	getId () {
		return this.id
	}

	/**
	 * This function is called when the hook is fired.
	 *
	 * @returns {boolean} True, always.
	 */
	apply () {
		this.callback()

		// The UI hook's reasoning above applies unchanged: the save callback returns nothing
		// either, so every Update logged a failed data callback.
		return true
	}
}

/**
 * Initializes the Elementor UI hooks and registers them.
 *
 * @param {string}   hook     The hook to register to.
 * @param {string}   id       The id to register our callback behind.
 * @param {Function} callback The function to call when the hook is fired.
 *
 * @returns {void}
 */
export function registerElementorUIHookAfter (hook, id, callback) {
	window.$e.hooks.registerUIAfter(new ElementorUIHook(hook, id, callback))
}

/**
 * Initializes the Elementor Data hooks and registers them.
 *
 * @param {string}   hook     The hook to register to.
 * @param {string}   id       The id to register our callback behind.
 * @param {Function} callback The function to call when the hook is fired.
 *
 * @returns {void}
 */
export function registerElementorDataHookAfter (hook, id, callback) {
	window.$e.hooks.registerDataAfter(new ElementorDataHook(hook, id, callback))
}