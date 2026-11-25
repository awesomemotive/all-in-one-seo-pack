import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

// The built-in archives are not registered objects, so they have no entry in `postData`.
const builtInArchives = () => [
	{ name: 'author', label: __('Author Archives', td) },
	{ name: 'date', label: __('Date Archives', td) }
]

// Why an object cannot be in this sitemap. Each cause is resolved by a different control in a
// different place, so the notice needs one message and one destination per cause — pointing every
// cause at the object's own card sends people to a screen that cannot fix theirs.
export const exclusionCauses = {
	hidden        : 'hidden',
	objectNoindex : 'objectNoindex',
	globalNoindex : 'globalNoindex'
}

const searchAppearanceRoutes = {
	postTypes  : 'content-types',
	taxonomies : 'taxonomies',
	archives   : 'archives'
}

// Whether opting one object out of the site-wide robots defaults brings it back on its own.
// It does for post types and taxonomies: Sitemap\Helpers::includedPostTypes() and
// ::includedTaxonomies() only apply the global tier while the object still follows the defaults, and
// both the root index and the object's own sitemap read those same two helpers.
// It does NOT for the built-in archives, even though it looks like it does — Sitemap\Root:92-95
// applies the global tier unconditionally while Sitemap\Content:542-548 applies it only to archives
// following the defaults, so opting an archive out yields a sub-sitemap the root index never
// advertises. Verified against the served output. Do not offer that route for archives.
const isGlobalTierEscapableByObject = kind => 'archives' !== kind

/**
 * Answers "why can this object not be in this sitemap, and what resolves it?" for every sitemap tab.
 * This is the only place that question is implemented — see the sitemap generator's own gates in
 * Sitemap\Root, Sitemap\Helpers and Sitemap\Query, which this mirrors.
 */
export const useSitemapExclusions = () => {
	// Stores are resolved inside each function, never up here: addons bundle shared components that
	// call this without installing the main plugin's Pinia, so resolving them eagerly throws there.

	// Whether an individual post/term can still pull an object into a surface after the object itself
	// was excluded from search results. Sits in here rather than beside the other two tier predicates
	// because the answer differs between Lite and Pro.
	// Post types always can: Sitemap\Query::posts() keeps the row on every surface. Taxonomies can
	// only in Pro, whose Sitemap\Helpers::isTaxonomyIncludable() adds the checkForIndexedTerm() hatch
	// the Common one has no equivalent for — Lite drops a noindexed taxonomy outright. Every surface
	// applies that one predicate, llms.txt included, so the answer does not vary by sitemap type.
	// The built-in author/date archives have no per-object override at all.
	const isOverridablePerObject = kind => {
		if ('postTypes' === kind) {
			return true
		}

		return 'taxonomies' === kind && useRootStore().isPro
	}

	const getObjectOptions = (kind, name) => {
		if ('archives' === kind) {
			return useOptionsStore().options.searchAppearance.archives?.[name]
		}

		const group = useOptionsStore().dynamicOptions.searchAppearance?.[kind]
		if (!group) {
			return null
		}

		return group[name] || group[name.replace(/^_aioseo_/, '')]
	}

	// The three tiers the generator applies, returned as the cause rather than a boolean, because
	// each one is fixed somewhere else.
	const getExclusionCause = (kind, name) => {
		const objectOptions = getObjectOptions(kind, name)
		if (!objectOptions) {
			return null
		}

		if (!objectOptions.show) {
			return exclusionCauses.hidden
		}

		const robotsMeta = objectOptions.advanced?.robotsMeta
		if (robotsMeta && !robotsMeta.default && robotsMeta.noindex) {
			return exclusionCauses.objectNoindex
		}

		// Which kinds inherit a global noindex, as the generator behaves: the built-in archives always
		// do (Sitemap\Root), while post types and taxonomies do only while they still follow the
		// robots defaults (Sitemap\Helpers::includedPostTypes and ::includedTaxonomies, which read
		// that leaf directly). Every surface applies it, including llms.txt, which builds its own
		// object lists but runs them through the same Sitemap\Helpers::isTaxonomyIncludable().
		// All three then drop out of the sitemap.
		const globalRobotsMeta = useOptionsStore().options.searchAppearance.advanced.globalRobotsMeta
		const inheritsGlobal   = 'archives' === kind || !!robotsMeta?.default

		return inheritsGlobal && !globalRobotsMeta.default && globalRobotsMeta.noindex
			? exclusionCauses.globalNoindex
			: null
	}

	// Deep link straight to the object's own card, using the same params the rest of the admin uses.
	const getObjectUrl = (kind, name) => {
		const cardId = 'archives' === kind
			? `aioseo-card-${name}Archives`
			: `aioseo-card-${name}SA`

		// Attachments are the one object whose card does not live on its kind's route: Content Types
		// leaves it out and it sits on the Image SEO screen instead, beside the redirect control.
		const route = 'postTypes' === kind && 'attachment' === name
			? 'media'
			: searchAppearanceRoutes[kind]

		return `${useRootStore().aioseo.urls.aio.searchAppearance}&aioseo-scroll=${cardId}&aioseo-highlight=${cardId}#/${route}`
	}

	// The global robots meta lives on one row of one screen, so a global exclusion gets a single
	// link there rather than a link per object.
	const getGlobalRobotsMetaUrl = () => {
		const rowId = 'aioseo-global-robots-meta-row'

		return `${useRootStore().aioseo.urls.aio.searchAppearance}&aioseo-scroll=${rowId}&aioseo-highlight=${rowId}#/advanced`
	}

	const getRegisteredObjects = kind => {
		if ('archives' === kind) {
			return builtInArchives()
		}

		return useRootStore().aioseo.postData?.[kind] || []
	}

	/**
	 * The excluded objects of one kind on one sitemap tab, grouped by why they are excluded.
	 * `names` narrows the result to specific objects; `notOffered` drops the ones this tab never
	 * lists, so the notice cannot name something the user can't see.
	 *
	 * Each group carries everything its message needs: whether the objects link to their own card
	 * (the cause is on that card) or the group links to the global robots meta row instead, and
	 * whether the per-object opt-out is a real alternative for this kind.
	 */
	const getExclusions = (kind, names = null, notOffered = []) => {
		const overridable = isOverridablePerObject(kind)
		const groups      = []

		getRegisteredObjects(kind)
			.filter(object => !names || names.includes(object.name))
			.filter(object => !notOffered.includes(object.name))
			.forEach(object => {
				const cause = getExclusionCause(kind, object.name)
				if (!cause) {
					return
				}

				const isGlobal = exclusionCauses.globalNoindex === cause
				let group      = groups.find(candidate => candidate.cause === cause)
				if (!group) {
					group = {
						cause,
						overridable,
						objects           : [],
						groupUrl          : isGlobal ? getGlobalRobotsMetaUrl() : null,
						objectsAreLinks   : !isGlobal,
						escapableByObject : isGlobal && isGlobalTierEscapableByObject(kind)
					}
					groups.push(group)
				}

				group.objects.push({
					name  : object.name,
					label : object.label,
					url   : isGlobal ? null : getObjectUrl(kind, object.name)
				})
			})

		return { groups }
	}

	// Objects whose sitemap control can no longer change the output, so it can honestly be disabled.
	const getInertNames = (kind, notOffered = []) => {
		if (isOverridablePerObject(kind)) {
			return []
		}

		return getExclusions(kind, null, notOffered)
			.groups
			.flatMap(group => group.objects.map(object => object.name))
	}

	// Shared by the notice that carries the explanation and by the controls that point at it.
	// The cause is part of the id because one section can now render several notices, and a disabled
	// control has to describe itself with the one that explains *its* cause. `names` is part of it
	// too: a kind rendered as several narrowed notices — the Date and Author archive rows — would
	// otherwise emit duplicate ids and every aria-describedby would resolve to the first one.
	const getNoticeId = (sitemapType, kind, cause, names = null) => {
		const suffix = names ? `-${names.join('-')}` : ''

		return `aioseo-sitemap-exclusions-${sitemapType}-${kind}-${cause}${suffix}`
	}

	return {
		getExclusions,
		getExclusionCause,
		getInertNames,
		getNoticeId
	}
}