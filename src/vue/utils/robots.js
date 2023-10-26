/**
 * Converts an array of robots.txt rules into a string.
 *
 * @param 	{Object[]} ruleset An object of robots.txt rules grouped by User-agent.
 * @returns {string} 		   A string representation of the ruleset in the format specified by the robots.txt documentation.
 */
export const stringifyRuleset = ruleset => {
	const rules = []
	Object.keys(ruleset).forEach(userAgent => {
		if (!userAgent) {
			return
		}

		rules.push(`User-agent: ${userAgent}`)

		Object.keys(ruleset[userAgent]).forEach(index => {
			const [ directive, value ] = ruleset[userAgent][index].split(':').map(v => v.trim())
			rules.push(`${directive.charAt(0).toUpperCase() + directive.slice(1)}: ${value}`)
		})

		rules.push('')
	})

	return rules.join('\r\n')
}

/**
 * Groups robots.txt rules by User-agent.
 *
 * @param 	{Array}  rules An array of rules.
 * @returns {Object} 	   An object containing the rules grouped by User-agent.
 */
export const groupRulesByUserAgent = rules => {
	const groups = {}
	rules.forEach(rule => {
		const r = JSON.parse(rule)
		if (!r.userAgent || !r.fieldValue) {
			return
		}

		if (!groups[r.userAgent]) {
			groups[r.userAgent] = [ `${r.directive}: ${r.fieldValue}` ]

			return
		}

		groups[r.userAgent].push(`${r.directive}: ${r.fieldValue}`)
	})

	return groups
}

/**
 * Validates the robots.txt rules.
 *
 * @param 	{Object[]} 		 ruleset An object of robots.txt rules grouped by User-agent.
 * @returns {boolean|Object}   		 True if all rules are valid. Throws an object filled with errors otherwise.
 */
export const validateRuleset = (ruleset) => {
	const overriddenIndexes = []
	const pathPattern = /^\/.*$/

	const fillErrors = (previousErrors, type, message, rule, args = {}) => {
		const hash = `${rule.tableIndex}${rule.userAgent}${rule.directive}${rule.fieldValue}`
		if (previousErrors.find(e => 'defaultRuleOverride' === message && e.hash === hash && e.message === message)) {
			return previousErrors
		}

		previousErrors.push({
			type,
			message,
			hash,
			isNetworkIndex     : args.isNetworkIndex,
			previewIndex       : args.previewIndex,
			sourcePreviewIndex : args?.sourcePreviewIndex || null,
			conflictingIndex   : args?.conflictingIndex || null,
			duplicateIndex     : args?.duplicateIndex || null,
			equivalentIndex    : args?.equivalentIndex || null,
			overriddenIndex    : args?.overriddenIndex || null
		})

		overriddenIndexes.push(args?.overriddenIndex || null)

		return previousErrors
	}

	const incPreviewIndex = (previousPreviewIndex, value) => previousPreviewIndex + value

	let errors = [],
		previewIndex = 0
	for (const userAgent in ruleset) {
		previewIndex = incPreviewIndex(previewIndex, 2)

		for (const [ index, rule ] of Object.entries(ruleset[userAgent])) {
			const [ directive, value ] = [ rule.directive, rule.fieldValue ]
			if (!directive || !value) {
				continue
			}

			let savePreviewIndex = previewIndex
			// Compare all rules with each other, with the benefit of not making a comparison twice.
			for (let nextIndex = parseInt(index) + 1; nextIndex < ruleset[userAgent].length; nextIndex++) {
				const [ nextDirective, nextValue ] = [ ruleset[userAgent][nextIndex].directive, ruleset[userAgent][nextIndex].fieldValue ]
				if (!nextDirective || !nextValue) {
					continue
				}

				// Valueless or directiveless rules are not previewed, so we increment the `previewIndex` only if they are not empty.
				previewIndex = incPreviewIndex(previewIndex, 1)

				// Check for duplicates.
				if (`${directive}${value}` === `${nextDirective}${nextValue}`) {
					errors = fillErrors(errors, 'red', 'duplicateRule', ruleset[userAgent][nextIndex], {
						previewIndex,
						sourcePreviewIndex : savePreviewIndex,
						isNetworkIndex     : rule.networkLevel,
						duplicateIndex     : rule.tableIndex
					})
				}

				if (directive.match(/disallow|allow/i) && nextDirective.match(/disallow|allow/i)) {
					// Check for Allow/Disallow conflicts.
					if (directive !== nextDirective && value === nextValue) {
						if (rule.default) {
							if (!overriddenIndexes.includes(rule.tableIndex)) {
								// Make sure the `previewIndex` is decreased by one, because the overridden rule is not shown on the preview.
								savePreviewIndex--
							}
							errors = fillErrors(errors, 'yellow', 'defaultRuleOverride', ruleset[userAgent][nextIndex], {
								previewIndex,
								isNetworkIndex  : rule.networkLevel,
								overriddenIndex : rule.tableIndex
							})
						} else {
							errors = fillErrors(errors, rule.networkLevel ? 'yellow' : 'red', 'conflictingPath', ruleset[userAgent][nextIndex], {
								previewIndex,
								sourcePreviewIndex : rule.networkLevel ? null : savePreviewIndex,
								isNetworkIndex     : rule.networkLevel,
								conflictingIndex   : rule.tableIndex
							})
						}
					}

					// Check for equivalent path matches.
					const equivalentPattern = new RegExp(`^${nextValue.replace(/\*+$/g, '')}$`)
					if (-1 !== nextValue.indexOf('*') && value.match(equivalentPattern)) {
						errors = fillErrors(errors, 'red', 'equivalentPath', ruleset[userAgent][nextIndex], {
							previewIndex,
							sourcePreviewIndex : savePreviewIndex,
							isNetworkIndex     : rule.networkLevel,
							equivalentIndex    : rule.tableIndex
						})
					}
				}

				// Each user-agent must have only one "Crawl-delay".
				if ('crawl-delay' === directive && 'crawl-delay' === nextDirective && value !== nextValue) {
					errors = fillErrors(errors, 'red', 'conflictingCrawlDelay', ruleset[userAgent][nextIndex], {
						previewIndex,
						sourcePreviewIndex : savePreviewIndex,
						isNetworkIndex     : rule.networkLevel,
						conflictingIndex   : rule.tableIndex
					})
				}
			}

			previewIndex = savePreviewIndex

			if (directive.match(/^clean-param/i)) {
				// Clean-param needs to have at least one param, path is optional.
				const [ param, path ] = value.split(/\s+/).map(v => v.trim())
				if (!param || param.match(pathPattern) || (path && !path.match(pathPattern))) {
					errors = fillErrors(errors, 'red', 'invalidCleanParam', rule, { previewIndex })
				}
			}

			if (directive.match(/^crawl-delay/i)) {
				// Crawl-delay must be a number greater than 0.
				const delay = Number(value)
				if (isNaN(delay) || 1 > delay) {
					errors = fillErrors(errors, 'red', 'invalidCrawlDelay', rule, { previewIndex })
				}
			}

			previewIndex = incPreviewIndex(previewIndex, 1)
		}
	}

	if (errors.length) {
		throw errors
	}

	return true
}