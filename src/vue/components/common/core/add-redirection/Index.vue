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
					<div class="settings-name">
						<div class="name small-margin">
							{{ sourceUrl }}:
						</div>
					</div>

					<core-add-redirection-url
						v-for="(url, index) in sourceUrls"
						:key="index"
						:url="url"
						:allow-delete="1 < sourceUrls.length"
						@remove-url="removeUrl(index)"
						:target-url="targetUrl"
						:log404="log404"
						:disableSource="sourceDisabled"
					>
						<template
							#source-url-description
							v-if="edit && !sourceDisabled"
						>
							<div
								class="aioseo-description source-description"
								v-html="strings.sourceUrlDescription"
							/>
						</template>
					</core-add-redirection-url>

					<base-button
						v-if="!edit && !log404 && !sourceDisabled"
						size="small"
						type="gray"
						@click="addUrl"
					>
						{{ strings.addUrl }}
					</base-button>
				</div>
			</div>
			<div class="url-arrow" v-if="redirectTypeHasTarget()">
				<svg-right-arrow />
			</div>
			<div class="target" v-if="redirectTypeHasTarget()">
				<div class="aioseo-settings-row no-border no-margin small-padding">
					<div class="settings-name">
						<div class="name small-margin">
							{{ strings.targetUrl }}:
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
				v-if="!edit && !log404 && !sourceDisabled"
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

		<div
			class="settings"
			:class="{ advanced : showAdvancedSettings }"
		>
			<div class="all-settings">
				<div class="all-settings-content">
					<div class="redirect-type">
						{{ strings.redirectType }}
						<base-select
							:options="REDIRECT_TYPES"
							v-model="redirectType"
							size="medium"
						/>
					</div>

					<transition-slide
						class="advanced-settings"
						:active="showAdvancedSettings"
					>
						<div class="query-params">
							{{ strings.queryParams }}
							<base-select
								:options="redirectQueryParams"
								v-model="queryParam"
								size="medium"
							/>
						</div>
					</transition-slide>

					<a
						v-if="!showAdvancedSettings"
						class="advanced-settings-link"
						href="#"
						@click.prevent="showAdvancedSettings = !showAdvancedSettings"
					>{{ strings.advancedSettings }}</a>
				</div>
			</div>
			<transition-slide
				class="advanced-settings"
				:active="showAdvancedSettings"
			>
				<custom-rules
					:key="customRules"
					:edit-custom-rules="customRules"
					@redirects-custom-rule-error="value => customRulesError = value"
				/>
			</transition-slide>
			<div
				class="actions"
				:class="{ advanced : showAdvancedSettings }"
			>
				<base-button
					size="medium"
					type="blue"
					@click="edit ? saveChanges() : addRedirects()"
					:loading="addingRedirect"
					:disabled="saveIsDisabled"
				>
					{{ edit ? strings.saveChanges : addRedirect }}
				</base-button>

				<base-button
					v-if="edit"
					size="medium"
					type="gray"
					@click="$emit('cancel', true)"
					class="cancel-edit-row"
				>
					{{ strings.cancel }}
				</base-button>
			</div>
		</div>
	</div>
</template>

<script>
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

import { useJsonValues } from '@/vue/composables/JsonValues'
import { useRedirect } from '@/vue/composables/redirects/Redirect'
import { useUrl } from '@/vue/composables/Url'

import BaseButton from '@/vue/components/common/base/Button'
import BaseSelect from '@/vue/components/common/base/Select'
import CoreAddRedirectionTargetUrl from '@/vue/components/common/core/add-redirection/TargetUrl'
import CoreAddRedirectionUrl from '@/vue/components/common/core/add-redirection/Url'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CustomRules from './CustomRules'
import SvgRightArrow from '@/vue/components/common/svg/right-arrow/Index'
import TransitionSlide from '@/vue/components/common/transition/Slide'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'cancel', 'added-redirect' ],
	setup () {
		const {
			getJsonValue
		} = useJsonValues()

		const {
			redirectHasUnPublishedPost
		} = useRedirect()

		const {
			decodeUrl
		} = useUrl()

		return {
			getJsonValue,
			redirectHasUnPublishedPost,
			decodeUrl,
			redirectsStore : useRedirectsStore()
		}
	},
	components : {
		BaseButton,
		BaseSelect,
		CoreAddRedirectionTargetUrl,
		CoreAddRedirectionUrl,
		CoreAlert,
		CustomRules,
		SvgRightArrow,
		TransitionSlide
	},
	props : {
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
		postId     : Number,
		postStatus : String
	},
	data () {
		return {
			REDIRECT_TYPES,
			genericError      : false,
			addingRedirect    : false,
			targetUrlErrors   : [],
			targetUrlWarnings : [],
			customRulesError  : false,
			strings           : {
				redirectType         : __('Redirect Type:', td),
				targetUrl            : __('Target URL', td),
				targetUrlDescription : __('Enter a URL or start by typing a page or post title, slug or ID.', td),
				addUrl               : __('Add URL', td),
				sourceUrlDescription : sprintf(
					// Translators: 1 - Oening link tag, 2 - Closing link tag.
					__('Enter a relative URL to redirect from or start by typing in page or post title, slug or ID. You can also use regex (%1$s)', td),
					links.getDocLink(__('what\'s this?', td), 'redirectManagerRegex')
				),
				advancedSettings          : __('Advanced Settings', td),
				queryParams               : __('Query Parameters:', td),
				saveChanges               : __('Save Changes', td),
				cancel                    : __('Cancel', td),
				genericErrorMessage       : __('An error occurred while adding your redirects. Please try again later.', td),
				sourceUrlSetOncePublished : __('source url set once post is published', td)
			},
			sourceDisabled  : false,
			editing         : false,
			editingRedirect : {
				sourceUrls           : [],
				targetUrl            : null,
				redirectType         : null,
				queryParam           : null,
				customRules          : [],
				showAdvancedSettings : false
			}
		}
	},
	watch : {
		sourceUrls : {
			deep : true,
			handler () {
				debounce(() => this.checkForDuplicates(), 500)
			}
		}
	},
	computed : {
		sourceUrls : {
			get () {
				return this.editing ? this.editingRedirect.sourceUrls : this.redirectsStore.addNewRedirect.sourceUrls
			},
			set (value) {
				this.editing ? this.editingRedirect.sourceUrls = value : this.redirectsStore.addNewRedirect.sourceUrls = value
			}
		},
		targetUrl : {
			get () {
				return this.editing ? this.editingRedirect.targetUrl : this.redirectsStore.addNewRedirect.targetUrl
			},
			set (value) {
				this.editing ? this.editingRedirect.targetUrl = value : this.redirectsStore.addNewRedirect.targetUrl = value
			}
		},
		redirectType : {
			get () {
				return this.editing ? this.editingRedirect.redirectType : this.redirectsStore.addNewRedirect.redirectType
			},
			set (value) {
				this.editing ? this.editingRedirect.redirectType = value : this.redirectsStore.addNewRedirect.redirectType = value
			}
		},
		queryParam : {
			get () {
				return this.editing ? this.editingRedirect.queryParam : this.redirectsStore.addNewRedirect.queryParam
			},
			set (value) {
				this.editing ? this.editingRedirect.queryParam = value : this.redirectsStore.addNewRedirect.queryParam = value
			}
		},
		customRules : {
			get () {
				return this.editing ? this.editingRedirect.customRules : this.redirectsStore.addNewRedirect.customRules
			},
			set (value) {
				this.editing ? this.editingRedirect.customRules = value : this.redirectsStore.addNewRedirect.customRules = value
			}
		},
		showAdvancedSettings : {
			get () {
				return this.editing ? this.editingRedirect.showAdvancedSettings : this.redirectsStore.addNewRedirect.showAdvancedSettings
			},
			set (value) {
				this.editing ? this.editingRedirect.showAdvancedSettings = value : this.redirectsStore.addNewRedirect.showAdvancedSettings = value
			}
		},
		saveIsDisabled () {
			return !!this.sourceUrls.filter(url => !url.url).length ||
				!!this.sourceUrls.filter(url => 0 < url.errors.length).length ||
				(this.redirectTypeHasTarget() && !this.targetUrl) ||
				this.customRulesError
		},
		getRelativeAbsolute () {
			const matched = this.targetUrl.match(/^\/([a-zA-Z0-9_\-%]*\..*)\//)

			if (matched) {
				return matched[0]
			}

			return null
		},
		sourceUrl () {
			return 1 < this.sourceUrls.length ? __('Source URLs', td) : __('Source URL', td)
		},
		addRedirect () {
			return 1 < this.sourceUrls.length ? __('Add Redirects', td) : __('Add Redirect', td)
		},
		hasTargetUrlErrors () {
			if (!this.targetUrl) {
				return []
			}

			const errors = []
			const sanitizedTargetUrl = sanitizeString(this.targetUrl)

			if (!sanitizedTargetUrl) {
				errors.push(__('Your target URL is not valid.', td))
				return errors
			}

			if (
				this.targetUrl &&
				!this.beginsWith(this.targetUrl, 'https://') &&
				!this.beginsWith(this.targetUrl, 'http://') &&
				'/' !== this.targetUrl.substr(0, 1)
			) {
				errors.push(sprintf(
					// Translators: 1 - Adds a html tag with an option like: <code>^</code>, 2 - Adds a html tag with an option like: <code>^</code>.
					__('Your target URL should be an absolute URL like %1$s or start with a slash %2$s.', td),
					'<code>https://domain.com/' + sanitizedTargetUrl + '</code>',
					'<code>/' + sanitizedTargetUrl + '</code>'
				))
			}

			const matches = this.targetUrl.match(/[|\\$]/g)
			if (null !== matches) {
				// Let's make sure that all URLs have regex enabled or else we fail.
				const regex = this.sourceUrls.map(u => u.regex)
				if (!regex.every(a => a)) {
					errors.push(sprintf(
						// Translators: 1 - Adds a html tag with an option like: <code>^</code>.
						__('Your target URL contains the invalid character(s) %1$s', td),
						'<code>' + matches + '</code>'
					))
				}
			}

			return errors
		},
		hasTargetUrlWarnings () {
			if (!sanitizeString(this.targetUrl)) {
				return []
			}

			const warnings = []
			if (this.getRelativeAbsolute) {
				warnings.push(sprintf(
					// Translators: 1 - Domain URL, 2 - Domain URL.
					__('Your URL appears to contain a domain inside the path: %1$s. Did you mean to use %2$s instead?', td),
					'<code>' + this.getRelativeAbsolute + '</code>',
					'<code>https:/' + this.getRelativeAbsolute + '</code>'
				))
			}

			return warnings
		},
		getDefaultRedirectType () {
			let option = this.getJsonValue(this.redirectsStore.options.redirectDefaults.redirectType)
			const defaultRedirect = REDIRECT_TYPES.find(t => parseInt(t.value) === parseInt(option?.value))

			if (!option) {
				option = REDIRECT_TYPES[0]
			}

			return defaultRedirect || option
		},
		getDefaultQueryParam () {
			let option = this.getJsonValue(this.redirectsStore.options.redirectDefaults.queryParam)
			const defaultQueryParam = REDIRECT_QUERY_PARAMS.find(t => t.value === option?.value)

			if (!option) {
				option = REDIRECT_QUERY_PARAMS[0]
			}

			return defaultQueryParam || option
		},
		getDefaultSlash () {
			return this.redirectsStore.options.redirectDefaults.ignoreSlash
		},
		getDefaultCase () {
			return this.redirectsStore.options.redirectDefaults.ignoreCase
		},
		getDefaultSourceUrls () {
			return [ JSON.parse(JSON.stringify(this.getDefaultSourceUrl)) ]
		},
		getDefaultSourceUrl () {
			return {
				id          : null,
				url         : null,
				regex       : false,
				ignoreSlash : this.slash || this.getDefaultSlash || false,
				ignoreCase  : this.case || this.getDefaultCase || false,
				errors      : [],
				warnings    : []
			}
		},
		redirectQueryParams () {
			return 0 < this.sourceUrls.filter(u => u.regex).length
				? REDIRECT_QUERY_PARAMS.map(param => {
					param.$isDisabled = false
					if ('exact' === param.value) {
						param.$isDisabled = true
						// Let's also reset the selected queryParam.
						if ('exact' === this.queryParam.value) {
							this.queryParam = REDIRECT_QUERY_PARAMS.find(option => !option.$isDisabled)
						}
					}
					return param
				})
				: REDIRECT_QUERY_PARAMS.map(param => {
					param.$isDisabled = false
					return param
				})
		},
		unPublishedPost () {
			return this.redirectHasUnPublishedPost({ post_id: this.postId, postStatus: this.postStatus })
		}
	},
	methods : {
		beginsWith (str, match) {
			return 0 === match.indexOf(str) || str.substr(0, match.length) === match
		},
		addUrl () {
			this.sourceUrls.push(JSON.parse(JSON.stringify(this.getDefaultSourceUrl)))
		},
		removeUrl (index) {
			this.sourceUrls.splice(index, 1)
		},
		addRedirects () {
			this.genericError   = false
			this.addingRedirect = true

			this.sourceUrls.map(url => {
				if ('http' !== url.url.substr(0, 4) && '/' !== url.url.substr(0, 1) && 0 < url.url.length && !url.regex) {
					url.url = '/' + url.url
				}

				return url
			})

			this.redirectsStore.create({
				sourceUrls            : this.sourceUrls,
				targetUrl             : this.targetUrl,
				queryParam            : this.queryParam.value,
				customRules           : this.customRules,
				redirectType          : this.redirectType.value,
				redirectTypeHasTarget : this.redirectTypeHasTarget(),
				group                 : this.log404 ? '404' : 'manual',
				postId                : this.postId
			})
				.then(() => {
					this.$emit('added-redirect')
					window.aioseoBus.$emit('added-redirect')
					this.reset()
				})
				.catch(error => {
					this.handleError(error)
				})
		},
		saveChanges () {
			this.genericError   = false
			this.addingRedirect = true

			if ('http' !== this.sourceUrls[0].url.substr(0, 4) && '/' !== this.sourceUrls[0].url.substr(0, 1) && 0 < this.sourceUrls[0].url.length && !this.sourceUrls[0].regex) {
				this.sourceUrls[0].url = '/' + this.sourceUrls[0].url
			}

			this.redirectsStore.update({
				id      : this.sourceUrls[0].id,
				payload : {
					sourceUrls            : this.sourceUrls,
					targetUrl             : this.targetUrl,
					queryParam            : this.queryParam.value,
					customRules           : this.customRules,
					redirectType          : this.redirectType.value,
					redirectTypeHasTarget : this.redirectTypeHasTarget(),
					postId                : this.postId
				}
			})
				.then(() => {
					this.$emit('added-redirect')
					this.reset()
				})
				.catch(error => {
					console.error(error)
					this.handleError(error)
				})
		},
		handleError (error) {
			if (409 !== error.response.status || !error.response.body.failed || !error.response.body.failed.length) {
				this.genericError   = true
				this.addingRedirect = false
				return
			}

			const urlIndexes          = []
			const failed              = error.response.body.failed
			const genericErrorMessage = __('A redirect already exists for this source URL. To make changes, edit the original instead.', td)
			failed.forEach(f => {
				const urlIndex = this.sourceUrls.findIndex(u => u.url === f.url || f)
				if (-1 !== urlIndex) {
					if (!this.sourceUrls[urlIndex].errors.find(error => error === f.error || error === genericErrorMessage)) {
						this.sourceUrls[urlIndex].errors.push(f.error || genericErrorMessage)
					}
					urlIndexes.push(urlIndex)
				}
			})

			for (let i = this.sourceUrls.length - 1; 0 <= i; i--) {
				if (urlIndexes.includes(i)) {
					continue
				}

				this.sourceUrls.splice(i, 1)
			}

			this.addingRedirect = false
		},
		updateTargetUrl (value) {
			this.targetUrl         = value
			this.targetUrlErrors   = this.hasTargetUrlErrors
			this.targetUrlWarnings = this.hasTargetUrlWarnings
		},
		reset () {
			this.showAdvancedSettings = false
			this.addingRedirect       = false

			// Don't reset an edited URL.
			if (this.edit) {
				return
			}

			const redirectType = REDIRECT_TYPES.find(t => t.value === this.type) || this.getDefaultRedirectType
			const queryParam   = REDIRECT_QUERY_PARAMS.find(t => t.value === this.query) || this.getDefaultQueryParam

			this.sourceUrls        = [ JSON.parse(JSON.stringify(this.getDefaultSourceUrl)) ]
			this.targetUrl         = null
			this.targetUrlErrors   = []
			this.targetUrlWarnings = []
			this.redirectType      = redirectType || { label: '301 ' + __('Moved Permanently', td), value: 301 }
			this.queryParam        = queryParam || { label: __('Ignore all parameters', td), value: 'ignore' }
			this.customRules       = []
		},
		checkForDuplicates () {
			const urls = []
			this.sourceUrls.forEach((u, i) => {
				// Prevent endless loop with the error message.
				if (!u.url || u.errors.length) {
					return
				}

				if (
					urls.includes(u.url.replace(/\/$/, ''))
				) {
					this.sourceUrls[i].errors.push(__('This is a duplicate of a URL you are already adding. You can only add unique source URLs.', td))
					return
				}

				urls.push(u.url.replace(/\/$/, ''))
			})

			this.updateTargetUrl(this.targetUrl)
		},
		redirectTypeHasTarget () {
			return this.redirectType && ('undefined' === typeof this.redirectType.noTarget || !this.redirectType.noTarget)
		}
	},
	mounted () {
		if (0 <= this.sourceUrls?.length) {
			this.sourceUrls = this.getDefaultSourceUrls
		}

		if (this.url) {
			this.editing = true
			this.sourceUrls = [ { ...this.getDefaultSourceUrl, ...this.url } ]
		}

		if (this.urls && this.urls.length) {
			this.editing = true
			this.sourceUrls = this.urls.map(url => ({ ...this.getDefaultSourceUrl, ...url }))
		}

		this.sourceDisabled = this.disableSource

		// We don't have an url to work with yet. Let's set it as a warning string.
		if (this.unPublishedPost) {
			this.sourceUrls = this.sourceUrls.map(sourceUrl => {
				sourceUrl.url =  '(' + this.strings.sourceUrlSetOncePublished + ')'
				return sourceUrl
			})

			this.sourceDisabled = true
		}

		if (this.target) {
			this.targetUrl = this.target
		}

		if (this.rules && 0 !== this.rules.length) {
			this.customRules = this.rules
		}

		this.redirectType = REDIRECT_TYPES.find(t => t.value === this.type) || this.redirectType || this.getDefaultRedirectType
		this.queryParam = REDIRECT_QUERY_PARAMS.find(t => t.value === this.query) || this.queryParam || this.getDefaultQueryParam
	}
}
</script>

<style lang="scss">
@use '@/vue/assets/scss/redirects/table.scss' as *;

.aioseo-redirects.aioseo-modal {
	.bd {
		padding: 20px;
	}
}
.aioseo-add-redirection {
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
				margin-bottom: 5px;
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
			margin-top: 12px;

			+ .source-url-options {
				margin-top: 12px;
			}
		}

		.right-arrow,
		.url-arrow {
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 36px 30px;

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
			align-items: center;

			> * {
				flex: 1;
			}

			.aioseo-input {
				margin-bottom: 12px;
			}
		}

		.target {
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
		margin-top: 20px;

		&.advanced {
			flex-direction: column;
		}

		.all-settings {
			flex-grow: 1;

			.all-settings-content {
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
		}

		.query-params {
			width: 340px;
		}

		.redirect-type {
			width: 300px;
			margin-right: 24px;
			font-weight: $font-bold;

			> * {
				font-weight: 400;
			}
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

	.aioseo-modal & {
		.aioseo-select {
			.multiselect__content-wrapper {
				max-height: 200px !important;
			}
		}
	}
}
</style>