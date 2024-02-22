const tinyMceActive = () => {
	const contentWrap = document.querySelector('#wp-content-wrap')
	const hasEditor = contentWrap && contentWrap.classList.contains('tmce-active')

	return hasEditor && window.tinyMCE
}

const textViewActive = () => {
	const contentWrap = document.querySelector('#wp-content-wrap')
	return (contentWrap && contentWrap.classList.contains('html-active'))
}

const getClassicEditorContent = () => {
	const editor = window.tinyMCE.get('content')
	if (editor && tinyMceActive()) {
		return editor.getContent({ format: 'raw' })
	}

	// No tinyMCE. Let's see if there's a proper #content textarea.
	const textEditor = document.querySelector('textarea#content')
	return textEditor ? textEditor.value : ''
}

const getClassicEditorSlug = () => {
	const link = document.querySelector('#editable-post-name')
	if (!link || !link.parentElement.href) {
		return ''
	}
	return link.parentElement.href
}

const isTinyMceEmpty = (editor) => {
	// When tinyMce is empty the raw content seems to be: `<p><br data-mce-bogus="1"></p>`.
	const body = editor.getBody()
	if (1 < body.childNodes.length) {
		return false
	} else if (0 === body.childNodes.length) {
		return true
	}
	if (1 < body.childNodes[0].childNodes.length) {
		return false
	}
	return /^\n?$/.test(body.innerText || body.textContent)
}

export {
	tinyMceActive,
	textViewActive,
	getClassicEditorContent,
	getClassicEditorSlug,
	isTinyMceEmpty
}