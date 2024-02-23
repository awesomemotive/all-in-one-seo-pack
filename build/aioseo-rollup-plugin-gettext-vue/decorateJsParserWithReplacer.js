import ts from 'typescript'
import validate1 from 'gettext-extractor/dist/utils/validate'
import parser1 from 'gettext-extractor/dist/parser'

export default function (jsParser) {
	jsParser.parseSourceFile = function (source, fileName, options = {}) {
		validate1.Validate.required.string({ source })
		validate1.Validate.optional.nonEmptyString({ fileName })
		this.validateParseOptions(options)

		if (!this.extractors.length) {
			throw new Error('Missing extractor functions. Provide them when creating the parser or dynamically add extractors using \'addExtractor()\'')
		}
		if (options && options.transformSource) {
			source = options.transformSource(source)
		}

		const messages = jsParser.parseSource(source, fileName || jsParser.STRING_LITERAL_FILENAME, options)
		for (const message of messages) {
			this.builder.addMessage(message)
		}

		this.stats && this.stats.numberOfParsedFiles++
		if (messages.length) {
			this.stats && this.stats.numberOfParsedFilesWithMessages++
		}

		return source
	}

	jsParser.parseSource = function (source, fileName, options = {}) {
		const sourceFile = ts.createSourceFile(fileName, source, ts.ScriptTarget.Latest, true, options.scriptKind)
		return jsParser.parseNodeSource(sourceFile, sourceFile, options.lineNumberStart || 1)
	}

	jsParser.parseNodeSource = function (node, sourceFile, lineNumberStart) {
		let messages = []
		const addMessageCallback = parser1.Parser.createAddMessageCallback(messages, sourceFile.fileName, () => {
			const location = sourceFile.getLineAndCharacterOfPosition(node.getStart())
			return lineNumberStart + location.line
		})
		const replaceCallExpression = function (msg) {
			addMessageCallback(msg)
			jsParser.addReplaceMessageNode(msg, node, sourceFile.fileName)
		}
		for (const extractor of jsParser.extractors) {
			extractor(node, sourceFile, replaceCallExpression)
		}
		ts.forEachChild(node, n => {
			messages = messages.concat(jsParser.parseNodeSource(n, sourceFile, lineNumberStart))
		})

		// Parsing and enumeration of string literals.
		if (0 < node.getStart() && (ts.isStringLiteral(node) || ts.isRegularExpressionLiteral(node))) {
			const text = ts.isRegularExpressionLiteral(node) ? node.getText().slice(1) : node.text
			const srcText = ts.createSourceFile(sourceFile.fileName, text, ts.ScriptTarget.Latest, true)
			const lineNumberStartText = lineNumberStart + sourceFile.getLineAndCharacterOfPosition(node.getStart()).line
			ts.forEachChild(srcText, n => {
				messages = messages.concat(jsParser.parseNodeSource(n, srcText, lineNumberStartText))
			})
		}

		return messages
	}

	jsParser.addReplaceMessageNode = function (message, node, fileName) {
		if (!jsParser.replacements) { jsParser.replacements = [] }

		jsParser.replacements.push({ message, node, fileName })
	}

	return jsParser
}