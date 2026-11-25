import { escapeRegex } from '@/vue/utils/regex'
import { getJsonValue } from '@/vue/utils/json'

// Mirrors RobotsService::MAX_CLEAN_PARAM_LENGTH.
const MAX_CLEAN_PARAM_LENGTH = 500

/**
 * Decodes and cleans a rule value the way the server does, leaving undecodable sequences as typed.
 *
 * NOTE: "%25" stays encoded, so decoding twice gives the same value, as on the server.
 *
 * @param   {string} value The rule value.
 * @returns {string}       The decoded value.
 */
export const decodeRuleValue = value => String(value)
	.replace(/%3[ce]/gi, '')
	.replace(/(%(?!25)[0-9a-f]{2})+/gi, sequence => {
		try {
			return decodeURIComponent(sequence)
		} catch {
			return sequence
		}
	})
	// eslint-disable-next-line no-control-regex
	.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '')
	.replace(/[\r\n\t ]+/g, ' ')
	.trim()

/**
 * Whether decoding a rule value left nothing but slashes and wildcards of it, as with "/%00".
 *
 * NOTE: must classify exactly like `sanitizeRuleValue()` in `Common\Tools\RobotsTxt`, which rejects these values.
 *
 * @param   {string}  value The rule value, as typed.
 * @returns {boolean}       Whether the decoded value is emptied.
 */
export const isEmptiedByDecoding = value => {
	const onlySlashes = v => /^[/*]*$/.test(v)

	return onlySlashes(decodeRuleValue(value)) && !onlySlashes(String(value).trim())
}

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
			// Split on the first colon only: a value such as `/page:2/` holds colons of its own.
			const rule      = ruleset[userAgent][index]
			const separator = rule.indexOf(':')
			const directive = rule.slice(0, separator).trim()
			const value     = rule.slice(separator + 1).trim()
			rules.push(`${directive.charAt(0).toUpperCase() + directive.slice(1)}: ${value}`)
		})

		rules.push('')
	})

	return rules.join('\r\n')
}

/**
 * Whether a rule field holds a usable value.
 *
 * NOTE: not truthiness ('0' and 0 count); must classify exactly like the PHP mirror in `Common\Tools\RobotsTxt`.
 *
 * @param 	{*} 	  value The field value to test.
 * @returns {boolean} 		Whether the value is a non-empty string or a number.
 */
const isRuleValueFilled = value => ('string' === typeof value || 'number' === typeof value) && '' !== String(value)

/**
 * Groups robots.txt rules by User-agent.
 *
 * @param 	{Array}  rules An array of rules.
 * @returns {Object} 	   An object containing the rules grouped by User-agent.
 */
export const groupRulesByUserAgent = rules => {
	const groups = {}
	rules.forEach(rule => {
		const r = getJsonValue(rule)
		if (!r?.userAgent) {
			return
		}

		// Mirror of the PHP grouping: it skips a rule the served file can't print, so the preview must too.
		if (!isRuleValueFilled(r.userAgent) || !isRuleValueFilled(r.directive) || !isRuleValueFilled(r.fieldValue)) {
			return
		}

		// A line break inside a field would print the rest of it as a separate, forged directive line.
		const [ userAgent, directive, fieldValue ] = [ r.userAgent, r.directive, r.fieldValue ].map(v => String(v).replace(/[\r\n]/g, ''))
		if (!userAgent || !directive || !fieldValue) {
			return
		}

		if (!groups[userAgent]) {
			groups[userAgent] = [ `${directive}: ${fieldValue}` ]

			return
		}

		groups[userAgent].push(`${directive}: ${fieldValue}`)
	})

	return groups
}

/**
 * Whether an entry has the shape of a custom robots rule: an object carrying every rule field.
 *
 * NOTE: keys, not values — the editor stores a row before it is filled in, and that row must survive a reload.
 * Must classify exactly like the PHP mirror in `Common\Tools\RobotsTxt`.
 *
 * @param 	{*} 	  rule The entry to test.
 * @returns {boolean} 	   Whether the entry is a rule record.
 */
const isRuleShaped = rule => !!rule && 'object' === typeof rule &&
	[ 'userAgent', 'directive', 'fieldValue' ].every(field => field in rule)

/**
 * Normalizes stored robots rules into canonical JSON strings.
 *
 * NOTE: classifies the decoded entry whatever its encoding; a non-rule string would render as a blank editor row.
 *
 * @param 	{Array} rules The raw rules, which may contain malformed entries.
 * @returns {Array} 	  Canonical JSON-string rules.
 */
export const normalizeRules = rules => {
	if (!Array.isArray(rules)) {
		return []
	}

	return rules.reduce((normalized, rule) => {
		const isEncoded = 'string' === typeof rule
		const decoded   = isEncoded ? getJsonValue(rule) : rule
		if (isRuleShaped(decoded)) {
			normalized.push(isEncoded ? rule : JSON.stringify(decoded))
		}

		return normalized
	}, [])
}

/**
 * Validates the robots.txt rules.
 *
 * @param 	{Object[]} 		 ruleset An object of robots.txt rules grouped by User-agent.
 * @returns {boolean|Object}   		 True if all rules are valid. Throws an object filled with errors otherwise.
 */
export const validateRuleset = (ruleset) => {
	const overriddenIndexes = []

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
			// An import can store any JSON type; a rule the preview skips has nothing to validate.
			if (!isRuleValueFilled(rule.directive) || !isRuleValueFilled(rule.fieldValue)) {
				continue
			}

			// Rules from other plugins are shown percent-encoded, as they are served, so values are compared decoded.
			const [ directive, value ] = [ String(rule.directive), decodeRuleValue(rule.fieldValue) ]

			let savePreviewIndex = previewIndex
			// Compare all rules with each other, with the benefit of not making a comparison twice.
			for (let nextIndex = parseInt(index) + 1; nextIndex < ruleset[userAgent].length; nextIndex++) {
				const nextRule = ruleset[userAgent][nextIndex]
				if (!isRuleValueFilled(nextRule.directive) || !isRuleValueFilled(nextRule.fieldValue)) {
					continue
				}

				const [ nextDirective, nextValue ] = [ String(nextRule.directive), decodeRuleValue(nextRule.fieldValue) ]

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
					const equivalentPattern = new RegExp(`^${escapeRegex(nextValue.replace(/\*+$/g, ''))}$`)
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
				// Yandex's syntax is `p0[&p1&…&pn] [path]`: non-empty params joined by "&", then at most one path.
				const cleanParam = value.trim()
				if (MAX_CLEAN_PARAM_LENGTH < cleanParam.length || !cleanParam.match(/^(?!\/)[^&\s]+(&[^&\s]+)*(\s+\/\S*)?$/)) {
					errors = fillErrors(errors, 'red', 'invalidCleanParam', rule, { previewIndex })
				}
			}

			// The server drops these values on save; other plugins' rules are served as they wrote them.
			if (!rule.default && isEmptiedByDecoding(rule.fieldValue)) {
				errors = fillErrors(errors, 'red', 'emptiedByDecoding', rule, { previewIndex })
			} else if (!rule.default && /%(?!25)[0-9a-f]{2}/i.test(value)) {
				errors = fillErrors(errors, 'red', 'invalidEncoding', rule, { previewIndex })
			}

			if (directive.match(/^crawl-delay/i)) {
				// A plain decimal only: Number() also takes exponents and hex, which crawlers don't read.
				const delay = value.trim().match(/^\d+(\.\d+)?$/) ? Number(value) : NaN
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