<template>
	<div
		class="aioseo-add-redirection"
		:class="{
			'edit-url' : edit,
			'log-404'  : log404
		}"
	>
		<core-alert
			class="generic-error"
			v-if="genericError"
			type="red"
		>
			{{ strings.genericErrorMessage }}
		</core-alert>

		<div class="urls">
			<div class="source">
				<div class="aioseo-settings-row no-border no-margin small-padding">
					<div class="settings-name source-url">
						<div class="name small-margin">
							{{ sourceUrlLabel }}
						</div>
					</div>

					<core-add-redirection-url
						v-for="(url, index) in sourceUrls"
						:key="index"
						ref="sourceUrlComponents"
						:url="url"
						:allow-delete="1 < sourceUrls.length"
						@remove-url="removeUrl(index)"
						:target-url="targetUrl"
						:log404="log404"
						:disableSource="sourceDisabled"
					/>

					<div class="aioseo-description source-description">
						<span v-html="strings.sourceUrlDescription" />

						{{ ' ' }}<span v-if="!edit && !log404 && !sourceDisabled">
							{{ strings.youCanAlso }}

							<a
								href="#"
								class="add-source-url"
								@click.prevent="addUrl"
							>
								<svg-circle-plus />

								{{ strings.addUrl }}
							</a>
						</span>
					</div>
				</div>
			</div>

			<div class="url-arrow" v-if="redirectTypeHasTarget()">
				<svg-right-arrow />
			</div>

			<div class="target" v-if="redirectTypeHasTarget()">
				<div class="aioseo-settings-row no-border no-margin small-padding">
					<div class="settings-name">
						<div class="name small-margin">
							{{ strings.targetUrl }}
						</div>
					</div>

					<div class="url">
						<core-add-redirection-target-url
							:url="decodeUrl(targetUrl)"
							:errors="targetUrlErrors"
							:warnings="targetUrlWarnings"
							@update:modelValue="updateTargetUrl"
						/>

						<div class="aioseo-description">
							{{ strings.targetUrlDescription }}
						</div>

						<transition-slide
							:active="!!targetUrlErrors.length"
						>
							<div>
								<core-alert
									v-for="(error, index) in targetUrlErrors"
									:key="index"
									class="target-url-error"
									type="red"
									size="small"
									v-html="error"
								/>
							</div>
						</transition-slide>

						<transition-slide
							:active="!!targetUrlWarnings.length"
						>
							<div>
								<core-alert
									v-for="(warning, index) in targetUrlWarnings"
									:key="index"
									class="target-url-warning"
									type="yellow"
									size="small"
									v-html="warning"
								/>
							</div>
						</transition-slide>
					</div>
				</div>
			</div>

			<template
				v-if="false"
			>
				<div class="break" />

				<div class="source">
					<div
						class="aioseo-description source-description"
						v-html="strings.sourceUrlDescription"
					/>
				</div>
				<div class="url-arrow" />
				<div class="target" />
			</template>
		</div>

		<div class="redirect-options">
			<div class="redirect-options__comment">
				<span class="redirect-options__label">{{ strings.comment }}</span>

				<base-input
					v-model="comment"
					maxlength="80"
					:placeholder="strings.commentPlaceholder"
					size="medium"
				/>
			</div>

			<div class="redirect-options__type">
				<span class="redirect-options__label">{{ strings.redirectType }}</span>

				<base-select
					float-dropdown
					:options="REDIRECT_TYPES"
					v-model="redirectType"
					size="medium"
				/>
			</div>

			<div class="redirect-options__query">
				<div class="redirect-options__label">
					<span>{{ strings.queryParams }}</span>

					<core-tooltip
						placement="left"
						flip
					>
						<svg-circle-question-mark />

						<template #tooltip>
							{{ strings.queryParamsTooltip }}
						</template>
					</core-tooltip>
				</div>

				<base-select
					float-dropdown
					:options="redirectQueryParams"
					v-model="queryParam"
					size="medium"
				/>
			</div>
		</div>

		<div class="custom-rules-toggle">
			<a
				href="#"
				class="custom-rules-toggle__link"
				:class="{ active: showCustomRules }"
				@click.prevent="showCustomRules = !showCustomRules"
			>
				<svg-circle-plus />

				{{ showCustomRules ? strings.hideCustomRules : strings.addCustomRules }}
			</a>
		</div>

		<transition-slide :active="showCustomRules">
			<custom-rules
				:edit-custom-rules="redirectCustomRules"
				@redirects-custom-rule-error="value => customRulesError = value"
			/>
		</transition-slide>

		<div class="aioseo-add-redirection__actions">
			<base-button
				size="medium"
				type="blue"
				@click="edit ? saveChanges() : addRedirects()"
				:loading="addingRedirect"
				:disabled="saveIsDisabled"
			>
				{{ edit ? strings.saveChanges : addRedirectLabel }}
			</base-button>

			<base-button
				v-if="edit"
				size="medium"
				type="gray"
				@click="emit('cancel', true)"
				class="cancel-edit-row"
			>
				{{ strings.cancel }}
			</base-button>
		</div>
	</div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'

import {
	REDIRECT_QUERY_PARAMS,
	REDIRECT_TYPES
} from '@/vue/plugins/constants'

import {
	useRedirectsStore
} from '@/vue/stores'

import links from '@/vue/utils/links'
import { debounce } from '@/vue/utils/debounce'
import { sanitizeString } from '@/vue/utils/strings'
import { isBlockEditor } from '@/vue/utils/context'

import { useJsonValues } from '@/vue/composables/JsonValues'
import { useRedirect } from '@/vue/composables/redirects/Redirect'
import { useUrl } from '@/vue/composables/Url'

import BaseButton from '@/vue/components/common/base/Button'
import BaseSelect from '@/vue/components/common/base/Select'
import BaseInput from '@/vue/components/common/base/Input'
import CoreAddRedirectionTargetUrl from '@/vue/components/common/core/add-redirection/TargetUrl'
import CoreAddRedirectionUrl from '@/vue/components/common/core/add-redirection/Url'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CustomRules from './CustomRules'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgRightArrow from '@/vue/components/common/svg/right-arrow/Index'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import TransitionSlide from '@/vue/components/common/transition/Slide'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	edit          : Boolean,
	log404        : Boolean,
	disableSource : Boolean,
	url           : Object,
	urls          : Array,
	target        : String,
	type          : Number,
	query         : String,
	slash         : Boolean,
	case          : Boolean,
	rules         : {
		type : Array,
		default () {
			return []
		}
	},
	postId          : Number,
	postStatus      : String,
	redirectComment : String
})

const emit = defineEmits([ 'cancel', 'added-redirect' ])

const { getJsonValue } = useJsonValues()
const { redirectHasUnPublishedPost } = useRedirect()
const { decodeUrl } = useUrl()

const redirectsStore = useRedirectsStore()

// Reactive state
const genericError      = ref(false)
const addingRedirect    = ref(false)
const targetUrlErrors   = ref([])
const targetUrlWarnings = ref([])
const targetUrlTouched  = ref(false)
const customRulesError  = ref(false)
const showCustomRules   = ref(false)
const sourceDisabled    = ref(false)
const editing           = ref(false)
const editingRedirect   = ref({
	sourceUrls   : [],
	targetUrl    : null,
	redirectType : null,
	queryParam   : null,
	customRules  : [],
	comment      : ''
})

// Template refs
const sourceUrlComponents = ref([])

const strings = {
	redirectType         : __('Redirect Type', td),
	targetUrl            : __('Target URL', td),
	targetUrlDescription : __('Enter a URL or search for a page.', td),
	addUrl               : __('add extra source URLs.', td),
	youCanAlso           : __('You can also', td),
	sourceUrlDescription : sprintf(
		// Translators: 1 - Opening link tag, 2 - Closing link tag.
		__('Enter a relative URL or search for a page. The gear icon enables advanced options such as regex (%1$s).', td),
		links.getDocLink(__('what\'s this?', td), 'redirectManagerRegex')
	),
	queryParams               : __('Query Parameters', td),
	queryParamsTooltip        : __('Query parameters are the parameters that are passed to the target URL. You can ignore all parameters, ignore exact parameters, or include all parameters.', td),
	saveChanges               : __('Save Changes', td),
	cancel                    : __('Cancel', td),
	genericErrorMessage       : __('An error occurred while adding your redirects. Please try again later.', td),
	sourceUrlSetOncePublished : __('source url set once post is published', td),
	comment                   : __('Comment', td),
	commentPlaceholder        : __('I added this redirect to...', td),
	advancedSettings          : __('Advanced Settings', td),
	addCustomRules            : __('Add Custom Rules', td),
	hideCustomRules           : __('Hide Custom Rules', td)
}

// Computed properties with getters/setters
const sourceUrls = computed({
	get () {
		return editing.value ? editingRedirect.value.sourceUrls : redirectsStore.addNewRedirect.sourceUrls
	},
	set (value) {
		if (editing.value) {
			editingRedirect.value.sourceUrls = value
		} else {
			redirectsStore.addNewRedirect.sourceUrls = value
		}
	}
})

const targetUrl = computed({
	get () {
		return editing.value ? editingRedirect.value.targetUrl : redirectsStore.addNewRedirect.targetUrl
	},
	set (value) {
		if (editing.value) {
			editingRedirect.value.targetUrl = value
		} else {
			redirectsStore.addNewRedirect.targetUrl = value
		}
	}
})

const redirectType = computed({
	get () {
		return editing.value ? editingRedirect.value.redirectType : redirectsStore.addNewRedirect.redirectType
	},
	set (value) {
		if (editing.value) {
			editingRedirect.value.redirectType = value
		} else {
			redirectsStore.addNewRedirect.redirectType = value
		}
	}
})

const queryParam = computed({
	get () {
		return editing.value ? editingRedirect.value.queryParam : redirectsStore.addNewRedirect.queryParam
	},
	set (value) {
		if (editing.value) {
			editingRedirect.value.queryParam = value
		} else {
			redirectsStore.addNewRedirect.queryParam = value
		}
	}
})

const redirectCustomRules = computed({
	get () {
		return editing.value ? editingRedirect.value.customRules : redirectsStore.addNewRedirect.customRules
	},
	set (value) {
		if (editing.value) {
			editingRedirect.value.customRules = value
		} else {
			redirectsStore.addNewRedirect.customRules = value
		}
	}
})

const comment = computed({
	get () {
		return editing.value ? editingRedirect.value.comment : redirectsStore.addNewRedirect.comment
	},
	set (value) {
		if (editing.value) {
			editingRedirect.value.comment = value
		} else {
			redirectsStore.addNewRedirect.comment = value
		}
	}
})

const saveIsDisabled = computed(() => {
	return !!sourceUrls.value.filter(url => !url.url).length ||
		!!sourceUrls.value.filter(url => 0 < url.errors.length).length ||
		(redirectTypeHasTarget() && !targetUrl.value) ||
		customRulesError.value
})

const getRelativeAbsolute = computed(() => {
	const matched = targetUrl.value?.match(/^\/([a-zA-Z0-9_\-%]*\..*)\//)

	if (matched) {
		return matched[0]
	}

	return null
})

const sourceUrlLabel = computed(() => {
	return 1 < sourceUrls.value.length ? __('Source URLs', td) : __('Source URL', td)
})

const addRedirectLabel = computed(() => {
	return 1 < sourceUrls.value.length ? __('Add Redirects', td) : __('Add Redirect', td)
})

const isRegexRedirect = computed(() => sourceUrls.value.every(url => url.regex))

const hasTargetUrlErrors = computed(() => {
	if (!targetUrl.value) {
		return []
	}

	const errors = []
	const sanitizedTargetUrl = sanitizeString(targetUrl.value)

	if (!sanitizedTargetUrl) {
		errors.push(__('Your target URL is not valid.', td))
		return errors
	}

	if (
		targetUrl.value &&
		!/^https?:\/\//i.test(targetUrl.value) &&
		'/' !== targetUrl.value.substr(0, 1)
	) {
		errors.push(sprintf(
			// Translators: 1 - Adds a html tag with an option like: <code>^</code>, 2 - Adds a html tag with an option like: <code>^</code>.
			__('Your target URL should be an absolute URL like %1$s or start with a slash %2$s.', td),
			'<code>https://domain.com/' + sanitizedTargetUrl + '</code>',
			'<code>/' + sanitizedTargetUrl + '</code>'
		))
	}

	const matches = targetUrl.value.match(/[|\\$]/g)
	if (null !== matches && !isRegexRedirect.value) {
		errors.push(sprintf(
			// Translators: 1 - Adds a html tag with an option like: <code>^</code>.
			__('Your target URL contains the invalid character(s) %1$s', td),
			'<code>' + matches + '</code>'
		))
	}

	return errors
})

const hasTargetUrlWarnings = computed(() => {
	if (!sanitizeString(targetUrl.value)) {
		return []
	}

	const warnings = []
	if (getRelativeAbsolute.value) {
		warnings.push(sprintf(
			// Translators: 1 - Domain URL, 2 - Domain URL.
			__('Your URL appears to contain a domain inside the path: %1$s. Did you mean to use %2$s instead?', td),
			'<code>' + getRelativeAbsolute.value + '</code>',
			'<code>https:/' + getRelativeAbsolute.value + '</code>'
		))
	}

	const domainWarning = getDomainTypoWarning(targetUrl.value)
	if (domainWarning) {
		warnings.push(domainWarning)
	}

	return warnings
})

const getDefaultRedirectType = computed(() => {
	let option = getJsonValue(redirectsStore.options.redirectDefaults?.redirectType)
	const defaultRedirect = REDIRECT_TYPES.find(t => parseInt(t.value) === parseInt(option?.value))

	if (!option) {
		option = REDIRECT_TYPES[0]
	}

	return defaultRedirect || option
})

const getDefaultQueryParam = computed(() => {
	let option = getJsonValue(redirectsStore.options.redirectDefaults?.queryParam)
	const defaultQueryParam = REDIRECT_QUERY_PARAMS.find(t => t.value === option?.value)

	if (!option) {
		option = REDIRECT_QUERY_PARAMS[0]
	}

	return defaultQueryParam || option
})

const getDefaultSlash = computed(() => {
	return redirectsStore.options.redirectDefaults?.ignoreSlash
})

const getDefaultCase = computed(() => {
	return redirectsStore.options.redirectDefaults?.ignoreCase
})

const getDefaultSourceUrl = computed(() => {
	return {
		id          : null,
		url         : null,
		regex       : false,
		ignoreSlash : props.slash || getDefaultSlash.value || false,
		ignoreCase  : props.case || getDefaultCase.value || false,
		errors      : [],
		warnings    : []
	}
})

const getDefaultSourceUrls = computed(() => {
	return [ JSON.parse(JSON.stringify(getDefaultSourceUrl.value)) ]
})

const redirectQueryParams = computed(() => {
	return 0 < sourceUrls.value.filter(u => u.regex).length
		? REDIRECT_QUERY_PARAMS.map(param => {
			param.$isDisabled = false
			if ('exact' === param.value) {
				param.$isDisabled = true
				// Let's also reset the selected queryParam.
				if ('exact' === queryParam.value?.value) {
					queryParam.value = REDIRECT_QUERY_PARAMS.find(option => !option.$isDisabled)
				}
			}
			return param
		})
		: REDIRECT_QUERY_PARAMS.map(param => {
			param.$isDisabled = false
			return param
		})
})

const unPublishedPost = computed(() => {
	return redirectHasUnPublishedPost({ post_id: props.postId, postStatus: props.postStatus })
})

// Watchers
watch(() => props.redirectComment, (value) => {
	if (editing.value && value) {
		editingRedirect.value.comment = value
	}
})

watch(sourceUrls, () => {
	debounce(() => checkForDuplicates(), 500)
}, { deep: true })

// The target checks are only refreshed while typing, but the regex toggle changes whether
// `$1` placeholders are valid, so a stale message would otherwise stay on screen. An untouched
// field stays silent, since mounting an edit row populates the source URLs and flips this too.
watch(isRegexRedirect, () => {
	if (!targetUrlTouched.value || !targetUrl.value) {
		return
	}

	targetUrlErrors.value   = hasTargetUrlErrors.value
	targetUrlWarnings.value = hasTargetUrlWarnings.value
})

// Methods
function addUrl () {
	sourceUrls.value.push(JSON.parse(JSON.stringify(getDefaultSourceUrl.value)))
}

function removeUrl (index) {
	sourceUrls.value.splice(index, 1)
}

function getDomainTypoWarning (url) {
	if (!url) {
		return null
	}

	const domainMatch = url.match(/^https?:\/\/([^/?#]+)/i)
	if (!domainMatch) {
		return null
	}

	const domain = domainMatch[1]

	// A regex redirect substitutes `$1`-style placeholders anywhere in the target, host included.
	const checkedDomain = isRegexRedirect.value ? domain.replace(/\$\d+/g, '') : domain
	const invalidChars  = checkedDomain.match(/[^a-zA-Z0-9.\-:]/g)
	if (!invalidChars) {
		return null
	}

	const fixDomainPart = part => part.replace(/[,;_]/g, '.')
		.replace(/\s+/g, '')
		.replace(/[^a-zA-Z0-9.\-:]/g, '')

	// Splitting on the placeholders keeps them out of the suggestion's cleanup.
	const fixedDomain = (isRegexRedirect.value ? domain.split(/(\$\d+)/) : [ domain ])
		.map(part => /^\$\d+$/.test(part) ? part : fixDomainPart(part))
		.join('')
		.replace(/\.{2,}/g, '.')
		.replace(/^\.+|\.+$/g, '')

	// If we can't produce a plausible domain (needs at least a dot-separated TLD), warn without a suggestion.
	if (!fixedDomain || !/\.[a-zA-Z]{2,}$/.test(fixedDomain)) {
		return sprintf(
			// Translators: 1 - The entered domain with invalid characters.
			__('Your URL appears to contain invalid characters in the domain %1$s. Please enter a valid domain.', td),
			'<code>' + sanitizeString(domain) + '</code>'
		)
	}

	return sprintf(
		// Translators: 1 - The entered domain with invalid characters, 2 - The suggested corrected domain.
		__('Your URL appears to contain invalid characters in the domain %1$s. Did you mean %2$s?', td),
		'<code>' + sanitizeString(domain) + '</code>',
		'<code>' + sanitizeString(fixedDomain) + '</code>'
	)
}

function normalizeTargetUrlScheme (value) {
	if (!value) {
		return value
	}

	return value.replace(/^(https?:\/\/)/i, match => match.toLowerCase())
}

function addRedirects () {
	genericError.value   = false
	addingRedirect.value = true

	if (isBlockEditor()) {
		const slug = props.urls?.[0]?.url ?? window.wp.data.select('core/editor').getCurrentPostAttribute('slug')

		if (slug) {
			sourceUrls.value.map(url => {
				url.url = slug.startsWith('/') ? slug : `/${slug}`

				return url
			})
		}
	}

	sourceUrls.value.map(url => {
		// Trim before adding the leading slash, or leading spaces end up inside the path.
		if (!url.regex && 'string' === typeof url.url) {
			url.url = url.url.trim()
		}

		if ('http' !== url.url.substr(0, 4) && '/' !== url.url.substr(0, 1) && 0 < url.url.length && !url.regex) {
			url.url = '/' + url.url
		}

		return url
	})

	redirectsStore.create({
		sourceUrls            : sourceUrls.value,
		targetUrl             : normalizeTargetUrlScheme(targetUrl.value),
		queryParam            : queryParam.value.value,
		customRules           : redirectCustomRules.value,
		redirectType          : redirectType.value.value,
		redirectTypeHasTarget : redirectTypeHasTarget(),
		group                 : props.log404 ? '404' : 'manual',
		postId                : props.postId,
		comment               : comment.value
	})
		.then(() => {
			emit('added-redirect')
			window.aioseoBus.$emit('added-redirect')
			reset()
		})
		.catch(error => {
			handleError(error)
		})
}

function saveChanges () {
	genericError.value   = false
	addingRedirect.value = true

	// Trim before adding the leading slash, or leading spaces end up inside the path.
	if (!sourceUrls.value[0].regex && 'string' === typeof sourceUrls.value[0].url) {
		sourceUrls.value[0].url = sourceUrls.value[0].url.trim()
	}

	if ('http' !== sourceUrls.value[0].url.substr(0, 4) && '/' !== sourceUrls.value[0].url.substr(0, 1) && 0 < sourceUrls.value[0].url.length && !sourceUrls.value[0].regex) {
		sourceUrls.value[0].url = '/' + sourceUrls.value[0].url
	}

	redirectsStore.update({
		id      : sourceUrls.value[0].id,
		payload : {
			sourceUrls            : sourceUrls.value,
			targetUrl             : normalizeTargetUrlScheme(targetUrl.value),
			queryParam            : queryParam.value.value,
			customRules           : redirectCustomRules.value,
			redirectType          : redirectType.value.value,
			redirectTypeHasTarget : redirectTypeHasTarget(),
			postId                : props.postId,
			comment               : comment.value
		}
	})
		.then(() => {
			emit('added-redirect')
			reset()
		})
		.catch(error => {
			console.error(error)
			handleError(error)
		})
}

function handleError (error) {
	if (409 !== error.response.status || !error.response.body.failed || !error.response.body.failed.length) {
		genericError.value   = true
		addingRedirect.value = false
		return
	}

	const urlIndexes          = []
	const failed              = error.response.body.failed
	const genericErrorMessage = __('A redirect already exists for this source URL. To make changes, edit the original instead.', td)
	failed.forEach(f => {
		const urlIndex = sourceUrls.value.findIndex(u => u.url === f.url || f)
		if (-1 !== urlIndex) {
			if (!sourceUrls.value[urlIndex].errors.find(error => error === f.error || error === genericErrorMessage)) {
				sourceUrls.value[urlIndex].errors.push(f.error || genericErrorMessage)
			}
			urlIndexes.push(urlIndex)
		}
	})

	for (let i = sourceUrls.value.length - 1; 0 <= i; i--) {
		if (urlIndexes.includes(i)) {
			continue
		}

		sourceUrls.value.splice(i, 1)
	}

	addingRedirect.value = false
}

function updateTargetUrl (value) {
	targetUrl.value         = value
	targetUrlTouched.value  = true
	targetUrlErrors.value   = hasTargetUrlErrors.value
	targetUrlWarnings.value = hasTargetUrlWarnings.value
}

function reset () {
	addingRedirect.value = false

	// Don't reset an edited URL.
	if (props.edit) {
		return
	}

	const newRedirectType = REDIRECT_TYPES.find(t => t.value === props.type) || getDefaultRedirectType.value
	const newQueryParam   = REDIRECT_QUERY_PARAMS.find(t => t.value === props.query) || getDefaultQueryParam.value

	// The advanced options panel is state inside each source URL row, and those rows are keyed
	// by index, so replacing the URLs below never remounts them into a collapsed state.
	sourceUrlComponents.value.forEach(sourceUrl => sourceUrl?.collapseOptions())

	sourceUrls.value        = [ JSON.parse(JSON.stringify(getDefaultSourceUrl.value)) ]
	targetUrl.value         = null
	targetUrlErrors.value   = []
	targetUrlWarnings.value = []
	targetUrlTouched.value  = false
	redirectType.value      = newRedirectType || { label: '301 ' + __('Moved Permanently', td), value: 301 }
	queryParam.value        = newQueryParam || { label: __('Ignore all parameters', td), value: 'ignore' }
	redirectCustomRules.value = []
	customRulesError.value    = false
	showCustomRules.value     = false
	comment.value           = ''
}

function checkForDuplicates () {
	const urls = []
	sourceUrls.value.forEach((u, i) => {
		// Prevent endless loop with the error message.
		if (!u.url || u.errors.length) {
			return
		}

		if (
			urls.includes(u.url.replace(/\/$/, ''))
		) {
			sourceUrls.value[i].errors.push(__('This is a duplicate of a URL you are already adding. You can only add unique source URLs.', td))
			return
		}

		urls.push(u.url.replace(/\/$/, ''))
	})

	updateTargetUrl(targetUrl.value)
}

function redirectTypeHasTarget () {
	return redirectType.value && ('undefined' === typeof redirectType.value.noTarget || !redirectType.value.noTarget)
}

// Lifecycle
onMounted(() => {
	if (0 <= sourceUrls.value?.length) {
		sourceUrls.value = getDefaultSourceUrls.value
	}

	if (props.url) {
		editing.value = true
		sourceUrls.value = [ { ...getDefaultSourceUrl.value, ...props.url } ]
	}

	if (props.urls && props.urls.length) {
		editing.value = true
		sourceUrls.value = props.urls.map(url => ({ ...getDefaultSourceUrl.value, ...url }))
	}

	sourceDisabled.value = props.disableSource

	// We don't have an url to work with yet. Let's set it as a warning string.
	if (unPublishedPost.value) {
		sourceUrls.value = sourceUrls.value.map(sourceUrl => {
			sourceUrl.url = '(' + strings.sourceUrlSetOncePublished + ')'
			return sourceUrl
		})

		sourceDisabled.value = true
	}

	if (props.target) {
		targetUrl.value = props.target
	}

	if (props.rules && 0 !== props.rules.length) {
		redirectCustomRules.value = props.rules
		showCustomRules.value = true
	}

	if (props.redirectComment) {
		comment.value = props.redirectComment
	}

	redirectType.value = REDIRECT_TYPES.find(t => t.value === props.type) || redirectType.value || getDefaultRedirectType.value
	queryParam.value = REDIRECT_QUERY_PARAMS.find(t => t.value === props.query) || queryParam.value || getDefaultQueryParam.value
})
</script>

<style lang="scss">
@use '@/vue/assets/scss/redirects/table.scss' as *;

.aioseo-redirects.aioseo-modal {
	.bd {
		padding: 20px;
	}
}

.aioseo-add-redirection {
	&__separator {
		width: 100%;
		margin: 20px 0;
		background-color: $border;
	}

	.aioseo-tooltip > div:nth-of-type(2) {
		width: 17px;
		height: 17px;

		svg.aioseo-circle-question-mark {
			width: 17px;
			height: 17px;
			color: $placeholder-color;
		}
	}

	&__section-title {
		display: block;
		font-size: 14px;
		font-weight: 600;
		margin-bottom: 12px;
	}

	.source-url {
		display: flex;
		gap: 12px;
		align-items: center;
		margin-bottom: 2px;

		button {
			padding: 0 6px;

			svg {
				width: 14px;
				height: 14px;
				margin-right: 6px;
			}
		}
	}

	.add-source-url {
		display: inline;
		color: $blue;
		text-decoration: none;
		cursor: pointer;

		svg {
			width: 12px;
			height: 12px;
			vertical-align: -1px;
			margin-right: 2px;
		}

		&:hover {
			text-decoration: underline;
		}
	}

	.redirect-options {
		display: flex;
		width: 100%;
		gap: 16px;
		margin-top: 20px;
		padding-top: 20px;
		border-top: 1px solid $border;

		&__comment {
			flex: 2;
			min-width: 0;
		}

		&__type,
		&__query {
			flex: 1;
			min-width: 0;
		}

		&__label {
			display: flex;
			align-items: center;
			gap: 5px;
			font-weight: 600;
			margin-bottom: 5px;
		}
	}

	.custom-rules-toggle {
		margin-top: 16px;

		&__link {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			font-size: 14px;
			color: $blue;
			text-decoration: none;
			cursor: pointer;
			white-space: nowrap;

			svg {
				width: 14px;
				height: 14px;
				transition: transform 0.2s ease;
			}

			&.active svg {
				transform: rotate(45deg);
			}

			&:hover {
				text-decoration: underline;
			}
		}
	}

	&__settings {
		width: 100%;
		display: flex;

		span {
			font-weight: 600;
		}
	}

	&__actions {
		display: flex;
		align-items: center;
		gap: 12px;
		margin-top: 12px;
	}

	&.edit-url {
		.urls {
			align-items: flex-start;
		}
	}

	.advanced-settings-link {
		text-decoration: underline !important;
	}

	&.log-404 {
		.urls {
			.source {
				min-height: 103px;
				align-items: flex-start;
			}
		}
	}

	.generic-error {
		margin-bottom: 20px;
	}

	.aioseo-settings-row {
		.settings-name {

			.name {
				line-height: 1.4;
				font-size: 14px;
				font-weight: 600;
			}
		}
	}

	.urls {
		display: flex;
		flex-direction: row;
		align-items: flex-start;
		flex-wrap: wrap;

		.break {
			flex-basis: 100%;
			height: 0;
		}

		.aioseo-description.source-description {
			margin-top: 0;

			+ .source-url-options {
				margin-top: 12px;
			}
		}

		.right-arrow,
		.url-arrow {
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 39px 30px 0;

			&:empty {
				margin-block: 0;
			}

			svg.aioseo-right-arrow {
				color: $blue;
				max-width: 20px;
			}
		}

		.source,
		.target {
			flex: 1;
			display: flex;
			align-items: flex-start;

			> * {
				flex: 1;
			}

			.aioseo-input {
				margin-bottom: 12px;
			}
		}

		.target {
			.settings-name {
				min-height: 25px;
				display: flex;
				align-items: center;
				margin-bottom: 2px;
			}

			.name {
				margin-top: 0;
				margin-bottom: 0;
			}

			input {
				padding-right: 30px;
			}

			.append-icon {
				width: 30px;
				justify-content: flex-end;

				svg {
					max-width: 16px;
					margin-right: 5px;

					&:last-of-type {
						margin-right: 0;
					}

					&.aioseo-circle-check {
						color: $green;
					}

					&.aioseo-circle-close {
						color: $red;
					}
				}
			}

			.aioseo-description {
				margin: 8px 0;
			}

			.target-url-warning,
			.target-url-error {
				margin-bottom: 10px;
			}
		}
	}

	.settings {
		display: flex;
		flex-direction: row;
		margin: 0;

		&.advanced {
			flex-direction: column;
		}

		.all-settings {
			flex-grow: 1;

			.all-aioseo-settings-content {
				display: flex;
				align-items: center;
				flex-wrap: wrap;
				margin-right: 10px;

				.advanced-settings-link {
					margin: 16px 0 0 0;
					color: $placeholder-color;
				}

				@media (max-width: 767px) {
					align-items: start;
				}
			}
		}

		> .actions {
			margin-top: 13px;
			flex-grow: 1;
			text-align: right;
			align-self: center;

			.postbox & {
				@media (max-width: 1071px) {
					margin-top: 20px;
				}
			}

			@media (max-width: 767px) {
				margin-top: 20px;
			}

			button:not(:first-child) {
				margin-top: 6px;
			}

			&.advanced {
				margin-top: 18px;
				align-self: flex-end;
			}
		}

		.redirect-type,
		.query-params {
			flex: 0 1 auto;

			.aioseo-select {
				margin-top: 5px;
			}

			&__label {
				display: flex;
				align-items: center;
				gap: 5px;
				font-weight: 600;

				svg {
					color: $placeholder-color !important;
				}
			}
		}

		.query-params {
			width: 340px;
		}

		.redirect-type {
			width: 300px;
			margin-right: 16px;
		}

		.aioseo-button {
			align-self: flex-end;
		}

		.cancel-edit-row {
			margin-left: 10px;
			@media(min-width: 1200px) {
				margin-left: 16px;
			}
		}
	}
}
</style>