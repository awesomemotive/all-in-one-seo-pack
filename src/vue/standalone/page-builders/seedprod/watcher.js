import { handleEditorSave } from '@/vue/standalone/page-builders/helpers'

export default () => {
	// This hook will fire when the Save button is triggered.
	document.getElementById('seedprod-builder-save').addEventListener('click', handleEditorSave)
}