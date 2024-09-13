<template>
	<div id="aioseo-help-modal" class="aioseo-help">
		<core-upgrade-bar
			v-if="!rootStore.isPro && settingsStore.settings.showUpgradeBar && rootStore.pong"
		/>

		<core-license-key-bar
			v-if="rootStore.isPro && licenseStore.isUnlicensed && rootStore.pong"
		/>

		<core-api-bar
			v-if="!rootStore.pong"
		/>

		<div class="aioseo-help-header">
			<div class="logo">
				<a
					v-if="licenseStore.isUnlicensed"
					:href="links.utmUrl('header-logo')"
					target="_blank"

				>
					<svg-aioseo-logo
						id="aioseo-help-logo"
					/>
				</a>
				<svg-aioseo-logo
					v-if="!licenseStore.isUnlicensed"
					id="aioseo-help-logo"
				/>
			</div>
			<div
				id="aioseo-help-close"
				:title="strings.close"
				@click.stop="toggleModal"
			>
				<svg-close />
			</div>
		</div>
		<div class="help-content">
			<div id="aioseo-help-search">
				<base-input
					type="text"
					size="medium"
					:placeholder="strings.search"
					@update:modelValue="value => inputSearch(value)"
				/>
			</div>
			<div
				id="aioseo-help-result"
			>
				<ul class="aioseo-help-docs">
					<li
						v-for="(doc, index) in filteredDocs"
						:key="index"
					>
						<span class="icon"><svg-description /></span>
						<a :href="links.utmUrl('help-panel-doc', '', doc.url)" rel="noopener noreferrer" target="_blank">{{ doc.title }}</a>
					</li>
				</ul>
			</div>
			<div id="aioseo-help-categories">
				<ul class="aioseo-help-categories-toggle">
					<li
						v-for="(category, index) in helpPanelStore.categories"
						:key="index"
						class="aioseo-help-category"
						:class="{ opened: 'getting-started' === index }"
					>
						<header @click.stop="toggleSection($event)">
							<span class="folder-open"><svg-folder-open /></span>
							<span class="title">{{category}}</span>
							<span class="dashicons dashicons-arrow-right-alt2" />
						</header>
						<ul class="aioseo-help-docs">
							<li
								v-for="(doc, index) in getCategoryDocs(index).slice(0, 5)"
								:key="index"
							>
								<span class="icon"><svg-description /></span>
								<a :href="links.utmUrl('help-panel-doc', '', doc.url)" rel="noopener noreferrer" target="_blank">{{ doc.title }}</a>
							</li>
							<div class="aioseo-help-additional-docs">
								<li
									v-for="(doc, index) in getCategoryDocs(index).slice(5, getCategoryDocs(index).length)"
									:key="index"
								>
									<span class="icon"><svg-description /></span>
									<a :href="links.utmUrl('help-panel-doc', '', doc.url)" rel="noopener noreferrer" target="_blank">{{ doc.title }}</a>
								</li>
							</div>
							<base-button
								v-if="getCategoryDocs(index).length >= 5"
								class="aioseo-help-docs-viewall gray medium"
								@click.stop="toggleDocs($event)"
							>
								{{ strings.viewAll }} {{ category }} {{ strings.docs }}
							</base-button>
						</ul>
					</li>
				</ul>
			</div>
			<div id="aioseo-help-footer">
				<div class="aioseo-help-footer-block">
					<a :href="links.utmUrl('help-panel-all-docs', '', 'https://aioseo.com/docs/')" rel="noopener noreferrer" target="_blank">
						<svg-description />
						<h3>{{ strings.viewDocumentation }}</h3>
						<p>{{ strings.browseDocumentation }}</p>
						<base-button
							class="aioseo-help-docs-viewall gray small"
						>
							{{ strings.viewAllDocumentation }}
						</base-button>
					</a>
				</div>

				<div class="aioseo-help-footer-block">
					<a
						:href="!rootStore.isPro || !licenseStore.license.isActive
							? links.getUpsellUrl('help-panel', 'get-support', 'liteUpgrade')
							: links.utmUrl('help-panel-support', '', 'https://aioseo.com/account/support/')
						"
						rel="noopener noreferrer"
						target="_blank"
					>
						<svg-support />
						<h3>{{ strings.getSupport }}</h3>
						<p>{{ strings.submitTicket }}</p>
						<base-button
							class="aioseo-help-docs-support blue small"
							v-if="rootStore.isPro && licenseStore.license.isActive"
						>
							{{ strings.submitSupportTicket }}
						</base-button>
						<base-button
							class="aioseo-help-docs-support green small"
							v-if="!rootStore.isPro || !licenseStore.license.isActive"
						>
							{{ strings.upgradeToPro }}
						</base-button>
					</a>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import links from '@/vue/utils/links'
import {
	useHelpPanelStore,
	useLicenseStore,
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import { debounce } from '@/vue/utils/debounce'
import CoreApiBar from '@/vue/components/common/core/ApiBar'
import CoreLicenseKeyBar from '@/vue/components/AIOSEO_VERSION/core/LicenseKeyBar'
import CoreUpgradeBar from '@/vue/components/lite/core/UpgradeBar'
import SvgAioseoLogo from '@/vue/components/common/svg/aioseo/Logo'
import SvgClose from '@/vue/components/common/svg/Close'
import SvgDescription from '@/vue/components/common/svg/Description'
import SvgFolderOpen from '@/vue/components/common/svg/FolderOpen'
import SvgSupport from '@/vue/components/common/svg/Support'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			licenseStore   : useLicenseStore(),
			rootStore      : useRootStore(),
			helpPanelStore : useHelpPanelStore(),
			settingsStore  : useSettingsStore(),
			links
		}
	},
	components : {
		CoreApiBar,
		CoreLicenseKeyBar,
		CoreUpgradeBar,
		SvgAioseoLogo,
		SvgClose,
		SvgDescription,
		SvgFolderOpen,
		SvgSupport
	},
	data () {
		return {
			searchItem : null,
			strings    : {
				close               : __('Close', td),
				search              : __('Search', td),
				viewAll             : __('View All', td),
				docs                : __('Docs', td),
				viewDocumentation   : __('View Documentation', td),
				browseDocumentation : sprintf(
					// Translators: 1 - The plugin short name ("AIOEO").
					__('Browse documentation, reference material, and tutorials for %1$s.', td),
					import.meta.env.VITE_SHORT_NAME
				),
				viewAllDocumentation : __('View All Documentation', td),
				getSupport           : __('Get Support', td),
				submitTicket         : __('Submit a ticket and our world class support team will be in touch soon.', td),
				submitSupportTicket  : __('Submit a Support Ticket', td),
				upgradeToPro         : __('Upgrade to Pro', td)
			}
		}
	},
	computed : {
		filteredDocs () {
			if ('' !== this.searchItem) {
				return Object.values(this.helpPanelStore.docs).filter(post => {
					return null !== this.searchItem ? post.title.toLowerCase().includes(this.searchItem.toLowerCase()) : null
				})
			}
			return null
		}
	},
	methods : {
		inputSearch : function (value) {
			debounce(() => {
				this.searchItem = value
			}, 1000)
		},
		toggleSection : function (event) {
			const category = event.target.parentNode.parentNode
			category.classList.toggle('opened')
		},
		toggleDocs : function (event) {
			const docs = event.target.previousSibling
			docs.classList.toggle('opened')
			event.target.style.display = 'none'
		},
		toggleModal () {
			const modal = document.getElementById('aioseo-help-modal')
			modal.classList.toggle('visible')
			document.body.classList.toggle('modal-open')
		},
		getCategoryDocs (category) {
			return Object.values(this.helpPanelStore.docs).filter(post => {
				return post.categories.flat().includes(category) ? post : null
			})
		}
	}
}
</script>

<style lang="scss">
	body.modal-open {
		overflow: hidden;
	}

	.aioseo-help {
		display: block;
		position: fixed;
		top: 0;
		bottom: 0;
		left: 0;
		right: 0;
		height: 100%;
		width: 100vw;
		background-color: #fff;
		color: $black;
		opacity: 0;
		max-height: 100vh;
		overflow-y: auto;
		transition: opacity 300ms ease-in 0s;
		z-index: -999;

		&.visible {
			opacity: 1;
			z-index: 100000;
		}

		.aioseo-help-header {
			background: #fff;
			width: 100%;
			height: 60px;
			z-index: 1;
			padding: 20px;
			display: flex;

			> .logo {
				flex: 1;
			}
		}

		.aioseo-help-docs {
			margin-bottom: 25px;
			display: none;

			li {
				padding: 0 0 14px 4px;
				margin: 0;
			}

			.aioseo-help-docs-viewall {
				margin: 10px 0 0 0;
			}

			.aioseo-help-additional-docs {
				display: none;
				&.opened {
					display: block;
				}
			}

			.icon .aioseo-description {
				width: 20px;
				margin-top: 0;
				position: relative;
				top: 5px;
				left: -5px;
				color: $placeholder-color;
			}
		}

		.help-content {
			background-color: #fff;
			width: 100%;
			max-width: 740px;
			margin: 0 auto 50px auto;
			padding: 0 20px;
			box-sizing: border-box;
			z-index: 1;

			.aioseo-help-category {
				border-top: 1px solid $border;
				margin: 0;

				&:last-child {
					border-bottom: 1px solid $border;
				}

				header {
					display: block;
					position: relative;
					cursor: pointer;
					width: 100%;
					height: 68px;

					.title {
						display: block;
						font-size: 16px;
						color: $black;
						font-weight: 600;
						padding: 23px 11px 23px 30px;
					}
				}

				.folder-open {
					position: absolute;
					top: 24px;
					width: 20px;
					height: 20px;
					color: $black;
				}

				.dashicons-arrow-right-alt2 {
					position: absolute;
					top: 20px;
					right: 0;
					transition: transform 0.3s ease-out;
				}

				&.opened {
					.aioseo-help-docs {
						display: block;
					}
					.dashicons-arrow-right-alt2 {
						transform: rotate(90deg);
					}
				}
			}

			#aioseo-help-search {
				position: relative;
				background-color: #fff;
				text-align: center;
				top: 0;
				padding: 20px 0 50px 0;
			}

			#aioseo-help-result {
				.aioseo-help-docs {
					display: block;
				}
			}

			#aioseo-help-footer {
				display: flex;
				flex-wrap: nowrap;
				justify-content: space-between;
				align-items: center;
				margin: 50px 0 0 0;

				@media screen and (max-width: 750px) {
					display: block;
				}

				.aioseo-help-footer-block {
					box-sizing: border-box;
					max-width: 325px;
					border: 1px solid $placeholder-color;
					border-radius: 6px;
					text-align: center;

					@media screen and (max-width: 750px) {
						max-width: 100%;
					}

					&:first-child {
						margin-right: 20px;

						@media screen and (max-width: 750px) {
							margin: 0 0 20px 0;
						}
					}

					a {
						display: block;
						padding: 25px;
						text-decoration: none;
						color: $black;

						h3 {
							color: $black;
						}

						span {
							font-size: 16px;
							color: $blue;
							text-decoration: underline;
						}

						&:hover span {
							text-decoration: none;
						}
					}
				}

				.aioseo-description,
				.aioseo-support {
					width: 48px;
					margin-top: 0;
					color: $black;
				}
			}
		}
	}

	#aioseo-help-logo {
		width: 132px;
		height: 26px;
		z-index: 2;
	}

	#aioseo-help-close {
		color: $black;
		width: 20px;
		height: 20px;
		cursor: pointer;
		opacity: 0.7;
		transition: all 0.05s;
		z-index: 2;

		@media screen and (max-width: 750px) {
			top: 20px;
			right: 20px;
		}
	}
</style>