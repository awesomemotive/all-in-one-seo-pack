import { useRootStore } from '@/vue/stores'
import links from '@/vue/utils/links'

export const getUtmLink = (url, content) => {
	const rootStore = useRootStore()
	const page      = rootStore.aioseo.page
	return links.utmUrl(`footer-${page}`, content, url)
}