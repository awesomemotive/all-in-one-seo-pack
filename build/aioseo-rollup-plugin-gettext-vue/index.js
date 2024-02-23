import { createFilter } from '@rollup/pluginutils'
import { GettextExtractor } from 'gettext-extractor'
import pluralForm from 'plural-forms'
import { SourceMapGenerator } from 'source-map'
import fs from 'fs'

import callExpressionWithLiteral from './callExpressionWithLiteral'
import decorateJsParserWithReplacer from './decorateJsParserWithReplacer'
import config from './config'

const cacheFilesParse = {}

export default function i18n (options = {}) {
	const filter        = createFilter(options.include, options.exclude)
	const languageFiles = config.defaultLanguage
	const language      = languageFiles
	const extractor     = new GettextExtractor()
	const jsParser      = extractor.createJsParser(config.optionsArray.map(el => {
		return callExpressionWithLiteral(el.calleeNames, el.extractorOptions, options)
	}))
	const vueParser     = decorateJsParserWithReplacer(jsParser)

	let sourcemap

	return {
		name : 'rollup-plugin-gettext-vue',
		buildStart (bundleOpts) {
			sourcemap = 'sourcemap' in options
				? options.sourcemap
				: false !== bundleOpts.sourcemap
		},
		intro () {
			const pluralFunc = pluralForm.getPluralFunc(language)
			const nplurals = pluralForm.getNPlurals(language)
			let results = 'const PLURAL = ' + config.calleeFunctions.PLURAL

			// add glogal functions
			results = results.replace('PLURALFUNC', pluralFunc.toString()).replace('NPLURALS', nplurals)
			config.optionsArray.forEach(function (el) {
				results += '\n' + 'const ' + el.globalName + ' = ' + el.calleeFuncBody
			})

			return results
		},
		transform (code, id) {
			if (!filter(id)) {
				return
			}

			if (!cacheFilesParse[id]) {
				vueParser.parseSourceFile(code, id)
				cacheFilesParse[id] = vueParser
			}

			const map = sourcemap ? new SourceMapGenerator({ file: id }) : null
			return {
				code,
				map : map.toString()
			}
		},
		writeBundle () {
			if (!options.textDomains.length) {
				return
			}

			// This array will hold the output object for each text domain that corresponds with specific files that need to be written.
			const output = {}

			// Loop through the text domains and add an empty array to the output object.
			options.textDomains.forEach(textDomain => {
				output[textDomain.domain] = []
			})

			// First we need to loop through all the messages, read the references and sort them into the correct text domain based on the file path.
			extractor.getMessages().forEach(message => {
				// Keep track of which text domains this message is in.
				const textDomains = []

				// Loop through the references and sort them into the correct text domain.
				message.references.forEach(reference => {
					// Loop through the text domains and add the reference to the correct text domain.
					options.textDomains.forEach(textDomain => {
						// If the reference matches the text domain path and the text domain is not already in the array, add it.
						if (reference.match(textDomain.path) && !textDomains.includes(textDomain.domain)) {
							output[textDomain.domain].push(message)

							// Add to the local array so we don't duplicate this message in the same text domain.
							textDomains.push(textDomain.domain)
						}
					})
				})
			})

			// Loop through the output keys and write the files.
			Object.keys(output).forEach(tdKey => {
				const textDomain = options.textDomains.find(textDomain => textDomain.domain === tdKey)

				// If the text domain has no output path, skip it.
				if (!textDomain.output) {
					return
				}

				let phpContents = '<?php\n// phpcs:ignore Generic.Files.LineLength.MaxExceeded\n/* THIS IS A GENERATED FILE. DO NOT EDIT DIRECTLY. */\n$generated_i18n_strings = ['

				output[tdKey].forEach(message => {
					let newString    = '',
						commentFound = false,
						textDomains  = []

					// We sort the references so they don't keep moving around inside the final file causing a lot of git changes and noise.
					message.references
						.sort()
						// Filter out any references that don't match this text domain.
						.filter(reference => reference.match(textDomain.path))
						.forEach(reference => {
							reference    = reference = `/src/${reference.split('/src/').pop()}`
							newString += `\n\t// Reference: ${reference}`
						})

					message.comments
						.sort()
						.forEach(comment => {
							if ('string' !== typeof comment) {
								textDomains.push(comment)
								return
							}

							let duplicate = ''
							if (commentFound) {
								duplicate = ' [DUPLICATE]'
								console.error('Translation string has more than one translator comment!', message.text)
							}

							commentFound = true
							newString += `\n\t// Translators:${duplicate} ${comment}`
						})

					// Filter out any text domains that don't match this text domain.
					textDomains = textDomains.filter(td => {
						return textDomain.matches.includes(td.textDomain) || td.textDomain === textDomain.domain
					})

					// If there are no text domains, skip this message.
					if (!textDomains.length) {
						return
					}

					phpContents += newString

					const text       = message.text.replace(/'/g, '\\\'')
					const textPlural = message.textPlural ? message.textPlural.replace(/'/g, '\\\'') : null

					if (message.textPlural) {
						if (message.context) {
							phpContents += `\n\t_nx( '${text}', '${textPlural}', 0, '${message.context}', '${textDomain.domain}' ),\n`
							return
						}

						phpContents += `\n\t_n( '${text}', '${textPlural}', 0, '${textDomain.domain}' ),\n`
						return
					}

					if (!message.textPlural && message.context) {
						phpContents += `\n\t_x( '${text}', '${message.context}', '${textDomain.domain}' ),\n`
						return
					}

					phpContents += `\n\t__( '${text}', '${textDomain.domain}' ),\n`
				})

				phpContents += '];'

				fs.writeFileSync(textDomain.output, phpContents)
			})
		}
	}
}