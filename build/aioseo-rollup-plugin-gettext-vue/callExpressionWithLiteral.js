import ts from 'typescript'
import validate1 from 'gettext-extractor/dist/utils/validate'
import content1 from 'gettext-extractor/dist/utils/content'
import common1 from 'gettext-extractor/dist/js/extractors/common'
import utils1 from 'gettext-extractor/dist/js/utils'
import comments1 from 'gettext-extractor/dist/js/extractors/comments'

export default function (calleeName, options, globalOptions) {
	validate1.Validate.required.argument({ calleeName })

	const calleeNames = [].concat(calleeName)

	for (const name of calleeNames) {
		if ('string' !== typeof name || 0 === name.length) {
			throw new TypeError('Argument \'calleeName\' must be a non-empty string or an array containing non-empty strings')
		}
	}

	common1.validateOptions(options)
	content1.validateContentOptions(options)

	const contentOptions = {
		trimWhiteSpace      : false,
		preserveIndentation : true,
		replaceNewLines     : false
	}

	if (options.content) {
		if (options.content.trimWhiteSpace !== undefined) {
			contentOptions.trimWhiteSpace = options.content.trimWhiteSpace
		}
		if (options.content.preserveIndentation !== undefined) {
			contentOptions.preserveIndentation = options.content.preserveIndentation
		}
		if (options.content.replaceNewLines !== undefined) {
			contentOptions.replaceNewLines = options.content.replaceNewLines
		}
	}

	// const loggedBadPath = false
	return (node, sourceFile, addMessage) => {
		if (node.kind !== ts.SyntaxKind.CallExpression) {
			return
		}

		const callExpression = node
		const matches = calleeNames.reduce((matchFound, name) => (matchFound || utils1.JsUtils.calleeNameMatchesCallExpression(name, callExpression)), false)
		if (!matches) {
			return
		}

		const message = extractArguments(callExpression, options.arguments, contentOptions)
		if (!message) {
			return
		}

		// Get all the paths from the globalOptions.textDomains.
		const paths = []
		for (let i = 0; i < globalOptions.textDomains.length; i++) {
			paths.push(globalOptions.textDomains[i].path)
		}

		// Check if the file is in the path for the current textdomain.
		if (!paths.some(path => sourceFile.fileName.match(path))) {
			return
		}

		if (!message.textDomain) {
			console.error('\n', 'Missing a textdomain: ', message)
			return
		}

		// Use a for loop to loop through the text domains array.
		let valid   = false,
			domains = []

		for (let i = 0; i < globalOptions.textDomains.length; i++) {
			const textDomain = globalOptions.textDomains[i]

			domains.push(textDomain.domain)
			domains = [
				...domains,
				...textDomain.matches
			]

			if (
				textDomain.matches.includes(message.textDomain) ||
				message.textDomain === textDomain.domain
			) {
				valid = true
			}
		}

		if (!valid) {
			const lineInfo = sourceFile.getLineAndCharacterOfPosition(node.getStart())
			console.error('\n', 'Invalid textdomain: ', message, `${sourceFile.fileName}:${lineInfo.line}:${lineInfo.character}`, `Expected: ${domains.join(' or ')}. Found: "${message.textDomain}"`)
			return
		}

		message.comments = comments1.JsCommentUtils.extractComments(callExpression, sourceFile, options.comments)

		// Since the original gettext-extractor doesn't support textdomains,
		// we want to push it to the comments here and extract it later.
		message.comments.push({ textDomain: message.textDomain })

		addMessage(message)
	}
}

function extractArguments (callExpression, argumentMapping, contentOptions) {
	const callArguments    = callExpression.arguments
	const contextArgument  = callArguments[argumentMapping.context]
	let textArgument       = callArguments[argumentMapping.text],
		textPluralArgument = callArguments[argumentMapping.textPlural],
		textDomainArgument = callArguments[argumentMapping.textDomain],
		textNumberArgument = callArguments[argumentMapping.textNumber]

	// For literal.
	if (textArgument && !isTextLiteral(textArgument)) {
		textArgument = ts.factory.createStringLiteral(textArgument.getText())
	}

	if (textPluralArgument && !isTextLiteral(textPluralArgument)) {
		textPluralArgument = ts.factory.createStringLiteral(textPluralArgument.getText())
	}

	if (textDomainArgument && !isTextLiteral(textDomainArgument)) {
		textDomainArgument = ts.factory.createStringLiteral(textDomainArgument.getText())
	}

	if (textNumberArgument && !isTextLiteral(textNumberArgument)) {
		textNumberArgument = ts.factory.createStringLiteral(textNumberArgument.getText())
	}

	textArgument       = checkAndConcatenateStrings(textArgument)
	textPluralArgument = checkAndConcatenateStrings(textPluralArgument)
	textDomainArgument = checkAndConcatenateStrings(textDomainArgument)
	textNumberArgument = checkAndConcatenateStrings(textNumberArgument)
	const textPluralValid = 'number' !== typeof argumentMapping.textPlural || isTextLiteral(textPluralArgument)
	if (isTextLiteral(textArgument) && textPluralValid) {
		const message = {
			text : content1.normalizeContent(textArgument.text, contentOptions)
		}
		if (isTextLiteral(textDomainArgument)) {
			message.textDomain = content1.normalizeContent(textDomainArgument.text, contentOptions)
		}
		if (isTextLiteral(textNumberArgument)) {
			message.textNumber = content1.normalizeContent(textNumberArgument.text, contentOptions)
		}
		if (isTextLiteral(textPluralArgument)) {
			message.textPlural = content1.normalizeContent(textPluralArgument.text, contentOptions)
		}
		if (isTextLiteral(contextArgument)) {
			message.context = content1.normalizeContent(contextArgument.text, contentOptions)
		}
		return message
	}
	return null
}
function isTextLiteral (expression) {
	return expression && (expression.kind === ts.SyntaxKind.StringLiteral || expression.kind === ts.SyntaxKind.NoSubstitutionTemplateLiteral)
}
function isParenthesizedExpression (expression) {
	return expression && expression.kind === ts.SyntaxKind.ParenthesizedExpression
}
function isBinaryExpression (expression) {
	return expression && expression.kind === ts.SyntaxKind.BinaryExpression
}
function getAdditionExpression (expression) {
	while (isParenthesizedExpression(expression)) {
		expression = expression.expression
	}
	if (isBinaryExpression(expression) && expression.operatorToken.kind === ts.SyntaxKind.PlusToken) {
		return expression
	}
	return null
}
function checkAndConcatenateStrings (expression) {
	let addition
	if (!expression || !(addition = getAdditionExpression(expression))) {
		return expression
	}
	const concatenated = ts.factory.createStringLiteral('')
	if (processStringAddition(addition, concatenated)) {
		return concatenated
	}
	return expression
}
function processStringAddition (expression, concatenated) {
	const leftAddition  = getAdditionExpression(expression.left)
	const rightAddition = getAdditionExpression(expression.right)
	if (isTextLiteral(expression.left)) {
		concatenated.text += expression.left.text
	} else if (leftAddition) {
		if (!processStringAddition(leftAddition, concatenated)) {
			return false
		}
	} else {
		return false
	}
	if (isTextLiteral(expression.right)) {
		concatenated.text += expression.right.text
		return true
	} else if (rightAddition) {
		return processStringAddition(rightAddition, concatenated)
	} else {
		return false
	}
}