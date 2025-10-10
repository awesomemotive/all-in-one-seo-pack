import { ref, onMounted } from 'vue'
import http from '@/vue/utils/http'
import { useRootStore, useOptionsStore } from '@/vue/stores'

export const useButtonAccessibility = (option) => {
	const rootStore = useRootStore()
	const optionsStore = useOptionsStore()

	const llmsButtonLocked  = ref(true)
	const llmsTxtAccessible = ref(false)

	const processChangesSaved = () => {
		llmsButtonLocked.value = false
		checkLlmsTxtAccessibility()
	}

	const checkLlmsTxtAccessibility = () => {
		if (!optionsStore.options.sitemap.llms[option]) {
			llmsTxtAccessible.value = false
			return
		}

		if ('enableFull' === option) {
			llmsTxtAccessible.value = rootStore.aioseo.urls.llmsFullUrl.isAccessible
		} else {
			llmsTxtAccessible.value = rootStore.aioseo.urls.llmsUrl.isAccessible
		}
	}

	onMounted(() => {
		window.aioseoBus.$on('changes-saved', async () => {
			processChangesSaved()
			if (optionsStore.options.sitemap.llms[option]) {
				try {
					const url = 'enableFull' === option
						? rootStore.aioseo.urls.llmsFullUrl.url
						: rootStore.aioseo.urls.llmsUrl.url

					// Try up to 10 times with 20 seconds delay between attempts
					let attempts      = 0
					const maxAttempts = 10
					const delay       = 20000

					while (attempts < maxAttempts) {
						try {
							const { status } = await http.get(url)
							if (200 === status) {
								llmsTxtAccessible.value = true
								break
							}
						} catch {
							// Continue to next attempt
						}

						attempts++
						if (attempts < maxAttempts) {
							await new Promise(resolve => setTimeout(resolve, delay))
						}
					}

					if (attempts >= maxAttempts) {
						llmsTxtAccessible.value = false
					}
				} catch {
					llmsTxtAccessible.value = false
				}
			}
		})

		// Check LLMs.txt accessibility on mount
		checkLlmsTxtAccessibility()
	})

	return {
		llmsButtonLocked,
		llmsTxtAccessible,
		processChangesSaved,
		checkLlmsTxtAccessibility
	}
}