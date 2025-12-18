<template>
	<span
		class="v-list-item__content"
		@click.prevent.exact.stop="initSave()"
	>
		<span class="v-list-item__title">
			{{ strings.buttonTitle }}
		</span>
	</span>
</template>

<script setup>
import { __ } from '@wordpress/i18n'
import { getSaveButton } from '../helpers'

const strings = {
	buttonTitle : __('Save (Don\'t Modify Date)', import.meta.env.VITE_TEXTDOMAIN)
}

const initSave = () => {
	if (!window.parent?.Breakdance?.ajaxurl) {
		return
	}

	try {
		const $button = getSaveButton()
		if ($button) {
			let url = new URL(window.parent.Breakdance.ajaxurl)
			url.searchParams.set('aioseo_limit_modified_date', true)
			window.parent.Breakdance.ajaxurl = url.toString()

			$button.click()

			setTimeout(() => {
				// This is a workaround to prevent the save button from being clicked twice.
				document.body.click()

				url = new URL(window.parent.Breakdance.ajaxurl)
				url.searchParams.delete('aioseo_limit_modified_date')
				window.parent.Breakdance.ajaxurl = url.toString()
			}, 100)
		}
	} catch (error) {
		console.error(error)
	}
}
</script>