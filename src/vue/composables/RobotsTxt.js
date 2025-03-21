import { ref, watch, nextTick, computed } from 'vue'
import {
	useOptionsStore,
	useNetworkStore
} from '@/vue/stores'

export const useRobotsTxt = () => {
	const networkStore = useNetworkStore()
	const optionsStore = useOptionsStore()

	const botRules = ref([])

	const getOptions = computed(() => 'network' === networkStore.currentSite?.blog_id ? optionsStore.networkOptions : optionsStore.options)

	networkStore.networkRobots.rules = 'network' === networkStore.currentSite?.blog_id
		? networkStore.getNetworkRobots.rules
		: optionsStore.options.tools.robots.rules

	const updateUnwantedBotRules = (value, userAgent) => {
		const userAgentsOrder = [ 'AdsBot', 'Google-Extended', 'GPTBot', 'CCBot' ]
		if (value) {
			botRules.value.push(JSON.stringify({
				userAgent,
				directive  : 'disallow',
				fieldValue : '/',
				readOnly   : true,
				bot        : true
			}))
		}

		botRules.value = botRules.value.filter(rule => userAgent !== JSON.parse(rule).userAgent)

		if (value) {
			botRules.value.push(JSON.stringify({
				userAgent,
				directive  : 'disallow',
				fieldValue : '/',
				readOnly   : true,
				bot        : true
			}))
		}

		botRules.value.sort((a, b) => {
			const userAgentA = JSON.parse(a).userAgent
			const userAgentB = JSON.parse(b).userAgent
			return userAgentsOrder.indexOf(userAgentA) - userAgentsOrder.indexOf(userAgentB)
		})

		networkStore.networkRobots.rules = [
			...botRules.value,
			...networkStore.networkRobots.rules.filter(rule => !JSON.parse(rule).bot)
		]
	}

	const updatePreventCrawlingRules = (value) => {
		let rules = []
		if (value) {
			rules = [
				JSON.stringify({
					userAgent       : '*',
					directive       : 'disallow',
					fieldValue      : '/?s=',
					readOnly        : true,
					preventCrawling : true
				}),
				JSON.stringify({
					userAgent       : '*',
					directive       : 'disallow',
					fieldValue      : '/page/*/?s=',
					readOnly        : true,
					preventCrawling : true
				}),
				JSON.stringify({
					userAgent       : '*',
					directive       : 'disallow',
					fieldValue      : '/search/',
					readOnly        : true,
					preventCrawling : true
				})
			]
		}

		networkStore.networkRobots.rules = [
			...rules,
			...networkStore.networkRobots.rules.filter(rule => !JSON.parse(rule).preventCrawling)
		]
	}

	watch(() => networkStore.networkRobots.rules, async () => {
		if ('network' === networkStore.currentSite?.blog_id) {
			optionsStore.networkOptions.tools.robots.rules = networkStore.networkRobots.rules
		} else {
			optionsStore.options.tools.robots.rules = networkStore.networkRobots.rules
		}

		await nextTick()

		window.aioseoBus.$emit('validate-robots-txt')
	}, { deep: true })

	watch(() => getOptions.value.searchAppearance.advanced.unwantedBots.all, (value) => {
		const bots = [
			{ name: 'AdsBot', slug: 'googleAdsBot' },
			{ name: 'Google-Extended', slug: 'googleGeminiVertexAiBots' },
			{ name: 'GPTBot', slug: 'openAiGptBot' },
			{ name: 'CCBot', slug: 'commonCrawlCcBot' }
		]

		bots.forEach(bot => {
			updateUnwantedBotRules(value || getOptions.value.searchAppearance.advanced.unwantedBots.settings[bot.slug], bot.name)
		})
	})

	watch(() => getOptions.value.searchAppearance.advanced.unwantedBots.settings.googleAdsBot, (value) => {
		if (getOptions.value.searchAppearance.advanced.unwantedBots.all) return

		updateUnwantedBotRules(value, 'AdsBot')
	})

	watch(() => getOptions.value.searchAppearance.advanced.unwantedBots.settings.googleGeminiVertexAiBots, (value) => {
		if (getOptions.value.searchAppearance.advanced.unwantedBots.all) return

		updateUnwantedBotRules(value, 'Google-Extended')
	})

	watch(() => getOptions.value.searchAppearance.advanced.unwantedBots.settings.openAiGptBot, (value) => {
		if (getOptions.value.searchAppearance.advanced.unwantedBots.all) return

		updateUnwantedBotRules(value, 'GPTBot')
	})

	watch(() => getOptions.value.searchAppearance.advanced.unwantedBots.settings.commonCrawlCcBot, (value) => {
		if (getOptions.value.searchAppearance.advanced.unwantedBots.all) return

		updateUnwantedBotRules(value, 'CCBot')
	})

	watch(() => getOptions.value.searchAppearance.advanced.searchCleanup.settings.preventCrawling, (value) => {
		updatePreventCrawlingRules(value)
	})
}