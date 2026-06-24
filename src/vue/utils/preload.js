/**
 * Runs dynamic imports one at a time, yielding to the browser between each,
 * so a fan-out of imports doesn't coalesce into a single long task.
 *
 * @param   {Function[]} thunks Functions that each return a dynamic `import()`. Pass thunks (not Promises) so imports don't start eagerly.
 * @returns {void}
 */
export const preloadOnIdle = thunks => {
	const schedule = window.requestIdleCallback || (cb => setTimeout(cb, 1))
	const step     = i => {
		if (i >= thunks.length) {
			return
		}

		Promise.resolve(thunks[i]()).finally(() => schedule(() => step(i + 1)))
	}

	schedule(() => step(0))
}