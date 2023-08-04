import { createPinia, setActivePinia } from 'pinia'

import { useAddonsStore } from '@/vue/stores/AddonsStore'
import { useAnalyzerStore } from '@/vue/stores/AnalyzerStore'
import { useBackupsStore } from '@/vue/stores/BackupsStore'
import { useConnectStore } from '@/vue/stores/ConnectStore'
import { useDirtyOptionsStore } from '@/vue/stores/DirtyOptionsStore'
import { useHelpPanelStore } from '@/vue/stores/HelpPanelStore'
import { useIndexNowStore } from '@/vue/stores/IndexNowStore'
import { useLicenseStore } from '@/vue/stores/LicenseStore'
import { useLinkAssistantStore } from '@/vue/stores/LinkAssistantStore'
import { useNetworkStore } from '@/vue/stores/NetworkStore'
import { useNotificationsStore } from '@/vue/stores/NotificationsStore'
import { useOptionsStore } from '@/vue/stores/OptionsStore'
import { usePluginsStore } from '@/vue/stores/PluginsStore'
import { usePostEditorStore } from '@/vue/stores/PostEditorStore'
import { useRedirectsStore } from '@/vue/stores/RedirectsStore'
import { useRootStore } from '@/vue/stores/RootStore'
import { useSchemaStore } from '@/vue/stores/SchemaStore'
import { useSearchStatisticsStore } from '@/vue/stores/SearchStatisticsStore'
import { useSeoRevisionsStore } from '@/vue/stores/SeoRevisionsStore'
import { useSettingsStore } from '@/vue/stores/SettingsStore'
import { useSetupWizardStore } from '@/vue/stores/SetupWizardStore'
import { useTagsStore } from '@/vue/stores/TagsStore'
import { useToolsStore } from '@/vue/stores/ToolsStore'

// Standolone stores.
import {
	useTableOfContentsStore
} from '@/vue/stores/standalones'

// Integration Stores.
import {
	useSemrushStore,
	useWpCodeStore
} from '@/vue/stores/integrations'

import { merge } from 'lodash-es'
import { __ } from '@wordpress/i18n'
import { markRaw } from 'vue'

const pinia = createPinia()

let isDirtyWatcher = false
const loadPiniaStores = (app, router = null) => {
	loadPinia(app, router)

	const rootStore = useRootStore()

	// If the stores have already been loaded, bail.
	if (rootStore.loaded) {
		return pinia
	}

	const aioseo = JSON.parse(JSON.stringify(window.aioseo || {}))

	// Pinia stores.
	const addonsStore           = useAddonsStore()
	const backupsStore          = useBackupsStore()
	const dirtyOptionsStore     = useDirtyOptionsStore()
	const helpPanelStore        = useHelpPanelStore()
	const indexNowStore         = useIndexNowStore()
	const licenseStore          = useLicenseStore()
	const linkAssistantStore    = useLinkAssistantStore()
	const networkStore          = useNetworkStore()
	const notificationsStore    = useNotificationsStore()
	const optionsStore          = useOptionsStore()
	const pluginsStore          = usePluginsStore()
	const postEditorStore       = usePostEditorStore()
	const redirectsStore        = useRedirectsStore()
	const searchStatisticsStore = useSearchStatisticsStore()
	const seoRevisionsStore     = useSeoRevisionsStore()
	const settingsStore         = useSettingsStore()
	const tagsStore             = useTagsStore()
	const wpCodeStore           = useWpCodeStore()

	// Initial network data.
	const networkData = {
		sites       : aioseo.data?.network?.sites,
		activeSites : aioseo.data?.network?.activeSites
	}

	// Options stores.
	optionsStore.dynamicOptions         = merge({ ...optionsStore.dynamicOptions }, { ...aioseo.dynamicOptions || {} })
	optionsStore.internalOptions        = merge({ ...optionsStore.internalOptions }, { ...aioseo.internalOptions || {} })
	optionsStore.options                = merge({ ...optionsStore.options }, { ...aioseo.options || {} })
	optionsStore.internalNetworkOptions = merge({ ...optionsStore.internalNetworkOptions }, { ...aioseo.internalNetworkOptions || {} })
	optionsStore.networkOptions         = merge({ ...optionsStore.networkOptions }, { ...aioseo.networkOptions || {} })

	// Other stores.
	addonsStore.addons           = merge([ ...addonsStore.addons ], [ ...aioseo.addons || [] ])
	backupsStore.backups         = merge([ ...backupsStore.backups ], [ ...aioseo.backups || [] ])
	backupsStore.networkBackups  = merge({ ...backupsStore.networkBackups }, { ...aioseo.data?.network?.backups || {} })
	helpPanelStore.$state        = merge({ ...helpPanelStore.$state }, { ...aioseo.helpPanel || {} })
	indexNowStore.$state         = merge({ ...indexNowStore.$state }, { ...aioseo.indexNow || {} })
	licenseStore.license         = merge({ ...licenseStore.license }, { ...aioseo.license || {} })
	linkAssistantStore.$state    = merge({ ...linkAssistantStore.$state }, { ...aioseo.linkAssistant || {} })
	networkStore.networkData     = merge({ ...networkStore.networkData }, { ...networkData })
	notificationsStore.$state    = merge({ ...notificationsStore.$state }, { ...aioseo.notifications || {} })
	pluginsStore.plugins         = merge({ ...pluginsStore.plugins }, { ...aioseo.plugins || {} })
	postEditorStore.currentPost  = merge({ ...postEditorStore.currentPost }, { ...aioseo.currentPost || {} })
	redirectsStore.$state        = merge({ ...redirectsStore.$state }, { ...aioseo.redirects || {} })
	searchStatisticsStore.$state = merge({ ...searchStatisticsStore.$state }, { ...aioseo.searchStatistics || {} })
	seoRevisionsStore.$state     = merge({ ...seoRevisionsStore.$state }, { ...aioseo.seoRevisions || {} })
	settingsStore.settings       = merge({ ...settingsStore.settings }, { ...aioseo.settings || {} })
	settingsStore.userProfile    = merge({ ...settingsStore.userProfile }, { ...aioseo.userProfile || {} })
	tagsStore.$state             = merge({ ...tagsStore.$state }, { ...aioseo.tags || {} })

	// Integration stores.
	if (aioseo.integrations?.wpcode) {
		wpCodeStore.$state = merge({ ...wpCodeStore.$state }, { ...aioseo.integrations.wpcode || {} })
	}

	// Default AIOSEO root store without the above data.
	delete aioseo.addons
	delete aioseo.backups
	delete aioseo.currentPost
	delete aioseo.dynamicOptions
	delete aioseo.helpPanel
	delete aioseo.indexNow
	delete aioseo.internalNetworkOptions
	delete aioseo.internalOptions
	delete aioseo.license
	delete aioseo.linkAssistant
	delete aioseo.networkOptions
	delete aioseo.notifications
	delete aioseo.options
	delete aioseo.plugins
	delete aioseo.redirects
	delete aioseo.searchStatistics
	delete aioseo.seoRevisions
	delete aioseo.settings
	delete aioseo.tags
	delete aioseo.userProfile

	// Add additional properties.
	aioseo.publicPath   = '/'
	aioseo.translations = {}

	rootStore.aioseo = merge({ ...rootStore.aioseo }, { ...aioseo || {} })

	// We clone the state as it is right now so we can compare for changes later.
	dirtyOptionsStore.updateOriginalOptions('options', optionsStore.options)
	dirtyOptionsStore.updateOriginalOptions('dynamicOptions', optionsStore.dynamicOptions)
	dirtyOptionsStore.updateOriginalOptions('networkOptions', optionsStore.networkOptions)

	if (redirectsStore?.options) {
		dirtyOptionsStore.updateOriginalOptions('redirectOptions', redirectsStore.options)
	}

	dirtyOptionsStore.updateOriginalOptions('indexNowOptions', indexNowStore.options)
	if (!indexNowStore.options.indexNow.apiKey) {
		dirtyOptionsStore.disableDirtyCheck('indexNowOptions')
	}

	// Finally, set the stores to loaded.
	rootStore.loaded = true

	if (isDirtyWatcher) {
		return pinia
	}

	isDirtyWatcher = true

	window.addEventListener('beforeunload', event => {
		if (!dirtyOptionsStore.isDirty) {
			return undefined
		}

		const message = __('Are you sure you want to leave? you have unsaved changes!', import.meta.env.VITE_TEXTDOMAIN);
		(event || window.event).returnValue = message
		return message
	})

	return pinia
}

const loadPinia = (app, router = null) => {
	if (router) {
		pinia.use(({ store }) => {
			store.$router = markRaw(router)
		})
	}

	if (!app) {
		setActivePinia(pinia)
		return pinia
	}

	app.use(pinia)

	return pinia
}

export {
	pinia,
	loadPinia,
	loadPiniaStores,
	// All the stores.
	useAddonsStore,
	useAnalyzerStore,
	useBackupsStore,
	useConnectStore,
	useDirtyOptionsStore,
	useHelpPanelStore,
	useIndexNowStore,
	useLicenseStore,
	useLinkAssistantStore,
	useNetworkStore,
	useNotificationsStore,
	useOptionsStore,
	usePluginsStore,
	usePostEditorStore,
	useRedirectsStore,
	useRootStore,
	useSchemaStore,
	useSearchStatisticsStore,
	useSeoRevisionsStore,
	useSemrushStore,
	useSettingsStore,
	useSetupWizardStore,
	useTableOfContentsStore,
	useTagsStore,
	useToolsStore,
	useWpCodeStore
}