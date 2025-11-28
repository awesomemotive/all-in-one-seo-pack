<template>
	<div class="aioseo-add-redirection">
		<div class="urls">
			<div class="source">
				<div class="aioseo-settings-row no-border no-margin small-padding">
					<div class="settings-name">
						<div class="name small-margin">
							{{ strings.sourceUrl }}
						</div>
					</div>

					<core-add-redirection-url
						:url="sourceUrl"
						:allow-delete="false"
						@remove-url="() => {}"
						:disable-search="true"
					/>

					<base-button
						size="small"
						type="gray"
						@click="() => {}"
					>
						{{ strings.addUrl }}
					</base-button>
				</div>
			</div>

			<div class="url-arrow">
				<svg-right-arrow />
			</div>

			<div class="target">
				<div class="aioseo-settings-row no-border no-margin small-padding">
					<div class="settings-name">
						<div class="name small-margin">
							{{ strings.targetUrl }}
						</div>
					</div>

					<div class="url">
						<core-add-redirection-target-url
							url=""
							:errors="[]"
							:warnings="[]"
							@update:modelValue="() => {}"
							:disable-search="true"
						/>

						<div class="aioseo-description">
							{{ strings.targetUrlDescription }}
						</div>
					</div>
				</div>
			</div>

			<div class="break" />

			<div class="source">
				<div
					class="aioseo-description source-description"
					v-html="softSanitizeHtml(strings.sourceUrlDescription)"
				/>
			</div>
			<div class="url-arrow" />
			<div class="target" />
		</div>

		<hr class="aioseo-add-redirection__separator" />

		<div class="aioseo-add-redirection__settings">
			<base-toggle :modelValue="false" />

			<span>{{ strings.advancedSettings }}</span>
		</div>

		<hr class="aioseo-add-redirection__separator" />

		<div class="aioseo-add-redirection__actions">
			<base-button
				size="medium"
				type="blue"
				@click="() => {}"
				:disabled="true"
			>
				{{ strings.addRedirect }}
			</base-button>
		</div>
	</div>
</template>

<script>
import links from '@/vue/utils/links'
import license from '@/vue/utils/license'
import {
	useRedirectsStore,
	useRootStore,
	useLicenseStore
} from '@/vue/stores'

import {
	GLOBAL_STRINGS,
	REDIRECT_GROUPS,
	REDIRECT_QUERY_PARAMS,
	REDIRECT_TYPES
} from '@/vue/plugins/constants'

import BaseButton from '@/vue/components/common/base/Button'
import CoreAddRedirectionUrl from '@/vue/components/common/core/add-redirection/Url'
import CoreAddRedirectionTargetUrl from '@/vue/components/common/core/add-redirection/TargetUrl'
import SvgRightArrow from '@/vue/components/common/svg/right-arrow/Index'

import { softSanitizeHtml } from '@/vue/utils/strings'
import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			license,
			links,
			softSanitizeHtml,
			redirectsStore : useRedirectsStore(),
			rootStore      : useRootStore(),
			licenseStore   : useLicenseStore()
		}
	},
	components : {
		BaseButton,
		CoreAddRedirectionUrl,
		CoreAddRedirectionTargetUrl,
		SvgRightArrow
	},
	data () {
		return {
			strings : {
				sourceUrl            : __('Source URL', td),
				sourceUrlDescription : sprintf(
					// Translators: 1 - Oening link tag, 2 - Closing link tag.
					__('Enter a relative URL to redirect from or start by typing in page or post title, slug or ID. You can also use regex (%1$s)', td),
					links.getDocLink(__('what\'s this?', td), 'redirectManagerRegex')
				),
				targetUrl            : __('Target URL', td),
				targetUrlDescription : __('Enter a URL or start by typing a page or post title, slug or ID.', td),
				addUrl               : __('Add URL', td),
				addRedirect          : __('Add Redirect', td),
				advancedSettings     : __('Advanced Settings', td)
			},
			REDIRECT_TYPES,
			REDIRECT_QUERY_PARAMS
		}
	},
	computed : {
		sourceUrl () {
			return {
				id          : null,
				url         : null,
				regex       : false,
				ignoreSlash : false,
				ignoreCase  : false,
				errors      : [],
				warnings    : [],
				showOptions : true
			}
		},
		columns () {
			const columns = [
				{
					slug  : 'source_url',
					label : __('Source URL', td)
				},
				{
					slug  : 'target_url',
					label : __('Target URL', td)
				},
				{
					slug  : 'hits',
					label : __('Hits', td),
					width : '97px'
				},
				{
					slug  : 'type',
					label : __('Type', td),
					width : '100px'
				},
				{
					slug  : 'group',
					label : __('Group', td),
					width : '150px'
				},
				{
					slug  : 'enabled',
					label : GLOBAL_STRINGS.enabled,
					width : '80px'
				}
			]

			return columns
		},
		additionalFilters () {
			return [
				{
					label   : __('Filter by Group', td),
					name    : 'group',
					options : [ { label: __('All Groups', td), value: 'all' } ].concat(REDIRECT_GROUPS)
				}
			]
		}
	}
}
</script>

<style lang="scss" scoped>
@use '@/vue/assets/scss/redirects/table.scss' as *;

.aioseo-add-redirection {
	&__separator {
		width: 100%;
		margin: 20px 0;
		background-color: $border;
	}

	&__settings {
		width: 100%;
		display: flex;

		span {
			font-weight: bold;
		}
	}

	&__actions {
		display: flex;
		width: 100%;
		justify-content: flex-end;
		gap: 16px;
	}

	&.log-404 {
		.urls {
			.source {
				min-height: 103px;
				align-items: flex-start;
			}
		}
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

			.aioseo-description {
				margin: 8px 0;
			}
		}
	}

	.settings {
		display: flex;
		flex-direction: row;
		margin: 20px 0;

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

			&__label {
				display: flex;
				align-items: center;
				gap: 5px;
				font-weight: 600;
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
	}
}
</style>