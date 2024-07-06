import Quill from 'quill'

const Clipboard = Quill.import('modules/clipboard')
const Delta     = Quill.import('delta')
class CustomClipboard extends Clipboard {
	onPaste (range, { html, text }) {
		const options     = this.options
		const formats     = this.quill.getFormat(range.index)
		const pastedDelta = this.convert({ text, html }, formats)

		const delta = new Delta()
			.retain(range.index)
			.delete(range.length)
			.concat(pastedDelta)

		if (false === options.newLines) {
			delta.ops.map(function (op) {
				if ('string' === typeof op.insert) {
					op.insert = op.insert.replace(/(\r\n|\n|\r)/gm, ' ')
				}
				return op
			})
		}

		this.quill.updateContents(delta, Quill.sources.USER)
		// range.length contributes to delta.length()
		this.quill.setSelection(
			delta.length() - range.length,
			Quill.sources.SILENT
		)
		this.quill.scrollSelectionIntoView()
	}

	convert ({ html, text }) {
		if ('string' === typeof html) {
			// Remove spaces between tags
			// html = html.replace(/>\s+<(?!\s*(?:b|big|i|small|tt|abbr|acronym|cite|code|dfn|em|kbd|strong|samp|var|a|bd|br|img|map|object|q|script|span|sub|sub|sup|button|input|label|select|textarea))/g, '><')
			html = html.replace(/>\r?\n +</g, '><')
		}

		return super.convert({ html, text })
	}
}

Quill.register('modules/clipboard', CustomClipboard, true)