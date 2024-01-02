<template>
	<div v-if="display"></div>
</template>

<script>
import '@/vue/assets/scss/app.scss'
import '@/vue/assets/scss/post-settings.scss'
export default {
	data () {
		return {
			display  : false,
			interval : null
		}
	},
	methods : {
		addMenuHighlight () {
			const menu = document.querySelector('#toplevel_page_aioseo')
			if (!menu) {
				return
			}

			const children = menu.querySelectorAll('.wp-submenu li')
			children.forEach(child => {
				const link = child.querySelector('a')
				if (!link) {
					return
				}

				const linkChild = link.querySelector('.aioseo-menu-highlight')
				if (linkChild) {
					child.classList.add('aioseo-submenu-highlight')

					if (linkChild.classList.contains('red')) {
						child.classList.add('red')
					}

					const link = child.querySelector('a')
					if (link && linkChild.classList.contains('lite')) {
						link.setAttribute('target', '_blank')
					}
				}
			})
		}
	},
	created () {
		this.addMenuHighlight()
	}
}
</script>