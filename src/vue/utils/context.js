import {
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

if ('undefined' !== typeof window && !window.wp?.blockEditor && window.wp?.blocks && window.wp.oldEditor) {
	window.wp.blockEditor = window.wp.editor
}

export const isBlockEditor = () => {
	return document.body.classList.contains('block-editor-page') && window.wp.data && canLoadBlocks()
}

export const isClassicEditor = () => {
	return !!document.querySelector('#wp-content-wrap.tmce-active, #wp-content-wrap.html-active')
}

export const isClassicNoEditor = () => {
	return document.querySelector('#post input#title') && !document.querySelector('#wp-content-wrap')
}

export const isElementorEditor = () => {
	return !!(document.body.classList.contains('elementor-editor-active') && window.elementor)
}

export const isDiviEditor = () => {
	 const body = document.body
	 const hasBuilder = !!window.ET_Builder

	 if (!hasBuilder) {
		return false
	 }

	 return body.classList.contains('et_pb_pagebuilder_layout') ||
		(body.classList.contains('et_divi_builder') && body.classList.contains('et-fb'))
}

// Extra runs Divi's own builder, so isDiviEditor() is true there too. Highlighting and spelling
// are only supported under Divi itself, which is why the Divi adapters refuse an Extra page.
export const isExtraTheme = () => !!window.aioseo?.isExtraTheme

// Divi's major BUILDER version, which is not the theme's. `theme.templateVersion` is only a proxy
// and it is wrong for the packaging that matters here: the Divi Builder PLUGIN runs the builder
// under any theme, so a third-party theme at 5.x would read as Divi 5 while a Divi 4 builder is
// running -- the Divi 5 adapter would then resolve, claim the page, and find no `divi.data` to
// paint through.
//
// `ET_BUILDER_PRODUCT_VERSION` is defined by the theme and by the plugin alike, so it answers for
// the builder itself. The theme version stays as a fallback for a release that stops defining it.
const diviBuilderMajorVersion = () => {
	const builderVersion = window.aioseo?.diviBuilderVersion

	if (builderVersion) {
		return parseInt(builderVersion, 10) || 0
	}

	return parseInt(window.aioseo?.theme?.templateVersion, 10) || 0
}

// Divi 5's builder specifically, which is a different adapter from Divi 4's: only 5.x exposes the
// `divi.data` registry the adapter reads and writes through.
//
// OWNERSHIP, and asked of signals that exist at page load -- `ET_Builder` and the body classes are
// in the document, and `templateVersion` is localized by PHP. Deliberately NOT the registry's own
// readiness (`divi.data.select('divi/page-settings')`): adapter resolution is memoised and runs
// before the canvas iframe has booted, so a readiness test would answer "not mine" for the page's
// whole lifetime.
export const isDivi5Editor = () => {
	if (!isDiviEditor()) {
		return false
	}

	return 5 <= diviBuilderMajorVersion()
}

// Divi 4's builder, which is a different adapter from Divi 5's: 4.x has no `divi.data` registry
// at all, so its adapter reads React props and writes through a module's inline TinyMCE
// ({@see plugins/tru-seo/editors/divi4.js}).
//
// Expressed as "Divi, but not Divi 5" so the two are mutually exclusive by construction: a version
// this cannot read answers 0, which lands here rather than leaving the page unowned.
export const isDivi4Editor = () => {
	if (!isDiviEditor()) {
		return false
	}

	return !isDivi5Editor()
}

export const isSeedProdEditor = () => {
	return !!(document.body.classList.contains('seedprod-builder') && window.seedprod_data)
}

export const isWPBakeryEditor = () => {
	return !!(window.vc && window.vc_mode)
}

// Whether WPBakery's builder is the surface being edited, which is NOT what isWPBakeryEditor()
// answers: a switch to Classic Mode leaves `window.vc`, `vc_mode` and the builder's markup all in
// place -- the markup merely hidden -- while the user edits raw shortcodes in the classic
// textarea. `#wpb_vc_js_status` cannot break the tie either; it reports the post's builder
// setting and stays "true" throughout.
//
// Asked as "is the BUILDER on screen", never as "is the classic editor gone": the back-end
// builder is its own metabox (`#wpb_wpbakery`) rendered BESIDE the classic editor, so
// `#wp-content-editor-container` is visible in both modes and cannot break the tie either.
// Measured on 9.0.1 -- builder mode: builder container visible; Classic Mode: hidden, with the
// classic editor visible throughout both.
//
// A rename upstream costs us paint rather than correctness, which is the safe direction.
const WPBAKERY_BUILDER_CONTAINERS = [ '#wpbakery_content', '#vc_content-wrapper', '#wpb_wpbakery' ]

export const isWPBakeryBuilderActive = () => {
	if (!isWPBakeryEditor()) {
		return false
	}

	// The frontend editor replaces the whole screen; it has no such container and needs none.
	if ('admin_frontend_editor' === window.vc_mode) {
		return true
	}

	// OWNERSHIP, asked of a field that exists before the builder has rendered anything.
	//
	// The container test below cannot be the primary signal: adapter resolution is memoised and
	// runs EARLY, while WPBakery renders its metabox afterwards, so a visibility test answers "not
	// mine" for the whole page's lifetime and the builder falls through to the classic editor
	// underneath it. Readiness masquerading as ownership, and the trap this file has to stay out of.
	//
	// `#wpb_vc_editor_type` is rendered with the page and carries the post's persisted editor:
	// `backend` for the builder, `classic` for Classic Mode. WPBakery also updates it live when the
	// user switches, so a later probe stays correct. `#wpb_vc_js_status` is NOT usable -- it reports
	// whether the builder is enabled for the post and stays "true" in Classic Mode.
	const editorType = document.querySelector('#wpb_vc_editor_type')?.value

	if (editorType) {
		return 'classic' !== editorType
	}

	const visible = (el) => !!(el?.offsetWidth || el?.offsetHeight || el?.getClientRects().length)

	return WPBAKERY_BUILDER_CONTAINERS.some(selector => visible(document.querySelector(selector)))
}

export const isAvadaEditor = () => {
	return (window.FusionApp || window.FusionPageBuilderApp)?.builderActive
}

export const isThriveArchitectEditor = () => {
	return !!(window.TVE && window.TVE.Editor_Page)
}

export const isSiteOriginEditor = () => {
	const visible = (el) => !!(el?.offsetWidth || el?.offsetHeight || el?.getClientRects().length)

	const isBlockEditorPanelsEnabled   = document.querySelectorAll('.block-editor-page').length && 'undefined' !== typeof window.soPanelsBuilderView
	const isClassicEditorPanelsEnabled = visible(document.querySelector('#so-panels-panels.attached-to-editor'))

	return isBlockEditorPanelsEnabled || isClassicEditorPanelsEnabled
}

export const isWooCommerceProduct = () => {
	const postEditorStore = usePostEditorStore()
	const rootStore       = useRootStore()

	return (
		rootStore.aioseo.data.isWooCommerceActive &&
		postEditorStore.currentPost &&
		'product' === postEditorStore.currentPost.postType
	)
}

export const isBricksEditor = () => {
	try {
		const builderParam = window?.bricksData?.builderParam
		return !!builderParam && new URLSearchParams(window.location.search).has(builderParam)
	} catch {
		return false
	}
}

export const isOxygenEditor = () => {
	try {
		return !!(window?.OxygenFrontend?.utils?.isBuilder() || false)
	} catch {
		return false
	}
}

export const isPageBuilderEditor = () => {
	return (
		isElementorEditor() ||
		isDiviEditor() ||
		isSeedProdEditor() ||
		isWPBakeryEditor() ||
		isAvadaEditor() ||
		isSiteOriginEditor() ||
		isThriveArchitectEditor() ||
		isBricksEditor() ||
		isOxygenEditor()
	)
}

export const canLoadBlocks = () => {
	const wp = window.wp
	return ('undefined' !== typeof wp && 'undefined' !== typeof wp.blocks && 'undefined' !== typeof wp.blockEditor)
}

export const isBlockCodeEditor = () => {
	if (isBlockEditor()) {
		const selectEditPost = window.wp?.data?.select('core/edit-post')

		return 'text' === selectEditPost?.getEditorMode()
	}

	return false
}