import { MARK_ATTRIBUTE } from './domDecorator'

// Editors already guarded, so `PreProcess` is bound once per instance. A WeakSet because the
// builder's editors outlive our repaints and must not be kept alive by this.
const guardedEditors = new WeakSet()

/**
 * Makes our decorations invisible to every serialization of a builder's inline TinyMCE editors.
 *
 * `editor.getContent()` serializes the editor's LIVE DOM, so a mark painted into an open editor
 * would otherwise be readable by the commit that rides on its blur -- measured on Divi 4.27.8,
 * where an injected `<mark>` came straight back out of getContent(), and on Divi 5.15, where
 * ending an inline edit wrote the painted marks into the module's stored content. TinyMCE's
 * serializer fires `PreProcess` on a CLONE of the content root before writing HTML, so unwrapping
 * there removes our marks from the output while leaving the painted ones on screen.
 *
 * The alternative is refusing to paint any module whose editor is open -- which is what Elementor
 * V3 must do for its pen -- and on Divi that would take the mark away on the very click meant to
 * open its popover, because Divi opens the editor on a click it cannot be talked out of.
 *
 * @param {Window} canvasWindow The window whose `tinymce.editors` to guard.
 * @returns {void}
 */
export const ensureSerializerGuards = (canvasWindow) => {
	try {
		for (const editor of (canvasWindow?.tinymce?.editors || [])) {
			if (!editor || guardedEditors.has(editor)) {
				continue
			}

			guardedEditors.add(editor)

			editor.on('PreProcess', (event) => {
				try {
					for (const mark of (event?.node?.querySelectorAll?.(`mark[${MARK_ATTRIBUTE}]`) || [])) {
						while (mark.firstChild) {
							mark.parentNode.insertBefore(mark.firstChild, mark)
						}

						mark.remove()
					}
				} catch (_e) {
					// A failed strip must not break the builder's own save.
				}
			})
		}
	} catch (_e) {
		// No editors yet; nothing can serialize a mark either.
	}
}