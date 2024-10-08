<template>
	<div class="aioseo-feature-card">
		<div
			class="feature-card-body"
			:class="{ static: staticCard }"
		>
			<div class="feature-card-header">
				<slot name="title" />
			</div>

			<div class="feature-card-description">
				<slot name="description" />

				<div
					v-if="(!addon.isActive || addon.requiresUpgrade) && !staticCard"
					class="learn-more"
				>
					<a
						:href="links.utmUrl('feature-manager-addon-link', addon.sku, addon.learnMoreUrl)"
						target="_blank"
					>{{ GLOBAL_STRINGS.learnMore }}</a>
					<a
						:href="links.utmUrl('feature-manager-addon-link', addon.sku, addon.learnMoreUrl)"
						class="no-underline"
						target="_blank"
					>&nbsp;&rarr;</a>
				</div>

				<div
					v-if="addon.manageUrl && ((addon.isActive && !addon.requiresUpgrade) || staticCard) && canManage"
					class="learn-more"
				>
					<a :href="getHref(addon.manageUrl)">{{ strings.manage }}</a>
					<a
						:href="getHref(addon.manageUrl)"
						class="no-underline"
					>
						&rarr;
					</a>
				</div>

				<core-alert
					class="install-failed"
					v-if="failed"
					type="red"
				>
					{{ strings.requestFailed }}
				</core-alert>
			</div>
		</div>

		<div
			v-if="canActivate"
			class="feature-card-footer"
			:class="{ 'upgrade-required': addon.requiresUpgrade || !licenseStore.license.isActive }"
		>
			<div
				v-if="!addon.requiresUpgrade && licenseStore.license.isActive && (!addon.installed || addon.hasMinimumVersion)"
				class="feature-card-install-activate"
			>
				<core-loader
					v-if="loading"
					dark
				/>
				<span
					v-if="!loading && addon.installedVersion"
					class="version"
				>
					{{ strings.version }} {{ addon.installedVersion }}
				</span>
				<span class="status">
					{{ statusLabel }}
				</span>
				<base-toggle
					v-if="addon.installed || addon.canInstall"
					:modelValue="addon.isActive"
					:disabled="loading"
					@update:modelValue="value => processStatusChange(value)"
				/>
			</div>

			<div
				v-if="addon.requiresUpgrade || !licenseStore.license.isActive"
				class="feature-card-upgrade-cta"
			>
				<base-button
					type="green"
					size="medium"
					tag="a"
					:href="links.getUpsellUrl('feature-manager-upgrade', addon.sku, rootStore.isPro ? 'pricing' : 'liteUpgrade')"
					target="_blank"
				>
					<span v-if="rootStore.isPro">{{ strings.upgradeYourPlan }}</span>
					<span v-if="!rootStore.isPro">{{ strings.upgradeToPro }}</span>
				</base-button>
			</div>

			<div
				v-if="rootStore.isPro && !addon.requiresUpgrade && addon.installed && !addon.hasMinimumVersion"
				class="feature-card-upgrade-cta"
			>
				<core-tooltip
					v-if="addon.isActive && !loading"
				>
					<span class="version">
						{{ strings.updateToVersion }} {{ addon.minimumVersion }}
					</span>

					<template #tooltip>
						{{ strings.updateRequired }}
						<strong
							v-if="!addons.userCanUpdate(addon.sku)"
						>
							{{ strings.permissionWarning }}
						</strong>
					</template>
				</core-tooltip>
				<base-button
					type="blue"
					size="medium"
					@click="processUpgradeFeature"
					:loading="featureUpgrading"
					:disabled="!addons.userCanUpdate(addon.sku)"
				>
					{{ strings.updateFeature }}
				</base-button>
			</div>
		</div>

		<core-modal
			:show="showNetworkModal"
			no-header
			@close="closeNetworkModal(false)"
			:classes="[ 'aioseo-feature-card-modal' ]"
		>
			<template #body>
				<div class="aioseo-modal-body">
					<button
						class="close"
						@click.stop="closeNetworkModal(false)"
					>
						<svg-close />
					</button>

					<h3>{{ strings.areYouSureNetworkChange }}</h3>

					<div class="reset-description">
						{{ networkChangeMessage }}
					</div>

					<base-button
						type="blue"
						size="medium"
						@click="closeNetworkModal(true)"
					>
						{{ strings.yesProcessNetworkChange }}
					</base-button>

					<base-button
						type="gray"
						size="medium"
						@click="closeNetworkModal(false)"
					>
						{{ strings.noChangedMind }}
					</base-button>
				</div>
			</template>
		</core-modal>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useAddonsStore,
	useLicenseStore,
	usePluginsStore,
	useRootStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import { getParams } from '@/vue/utils/params'

import { useUrl } from '@/vue/composables/Url'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgClose from '@/vue/components/common/svg/Close'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			getHref
		} = useUrl()

		return {
			GLOBAL_STRINGS,
			addonsStore  : useAddonsStore(),
			getHref,
			licenseStore : useLicenseStore(),
			links,
			pluginsStore : usePluginsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		CoreAlert,
		CoreLoader,
		CoreModal,
		CoreTooltip,
		SvgClose
	},
	props : {
		feature : {
			type     : Object,
			required : true
		},
		canActivate : {
			type : Boolean,
			default () {
				return true
			}
		},
		canManage : {
			type : Boolean,
			default () {
				return false
			}
		},
		staticCard : Boolean
	},
	data () {
		return {
			addons,
			addon                 : {},
			showNetworkModal      : false,
			changeStatusOnNetwork : false,
			failed                : false,
			loading               : false,
			featureUpgrading      : false,
			strings               : {
				version           : __('Version', td),
				updateToVersion   : __('Update to version', td),
				activated         : __('Activated', td),
				networkActivated  : __('Network Activated', td),
				deactivated       : __('Deactivated', td),
				notInstalled      : __('Not Installed', td),
				upgradeToPro      : __('Upgrade to Pro', td),
				upgradeYourPlan   : __('Upgrade Your Plan', td),
				updateFeature     : __('Update Addon', td),
				permissionWarning : __('You currently don\'t have permission to update this addon. Please ask a site administrator to update.', td),
				manage            : __('Manage', td),
				requestFailed     : __('An error occurred while changing the addon status. Please try again or contact support for more information.', td),
				updateRequired    : sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO"), 2 - Pro.
					__('An update is required for this addon to continue to work with %1$s %2$s.', td),
					import.meta.env.VITE_SHORT_NAME,
					'Pro'
				),
				areYouSureNetworkChange : __('This is a network-wide change.', td),
				yesProcessNetworkChange : __('Yes, process this network change', td),
				noChangedMind           : __('No, I changed my mind', td)
			}
		}
	},
	watch : {
		showNetworkModal (value) {
			if (value) {
				this.changeStatusOnNetwork = false
				return
			}

			if (!this.changeStatusOnNetwork) {
				this.addon.isActive = !this.addon.isActive
			}

			if (this.changeStatusOnNetwork) {
				this.actuallyProcessStatusChange()
			}
		}
	},
	computed : {
		networkChangeMessage () {
			// The logic is reversed here because the option has been toggled already.
			if (!this.addon.isActive) {
				return __('Are you sure you want to deactivate this addon across the network?', td)
			}

			return __('Are you sure you want to activate this addon across the network?', td)
		},
		statusLabel () {
			if (this.addon.isActive) {
				return !this.rootStore.aioseo.data.isNetworkAdmin && this.addon.isNetworkActive
					? this.strings.networkActivated
					: this.strings.activated
			}

			return this.addon.installed || this.addon.canInstall
				? this.strings.deactivated
				: this.strings.notInstalled
		}
	},
	methods : {
		closeNetworkModal (changeStatus) {
			this.changeStatusOnNetwork = changeStatus
			this.showNetworkModal = false
		},
		processStatusChange (activated) {
			this.addon.isActive = activated
			if (this.rootStore.aioseo.data.isNetworkAdmin) {
				this.showNetworkModal = true
				return
			}

			this.actuallyProcessStatusChange()
		},
		actuallyProcessStatusChange () {
			this.failed  = false
			this.loading = true

			// The action is reversed because we already swapped it earlier.
			const action = this.addon.isActive ? 'installPlugins' : 'deactivatePlugins'
			this.pluginsStore[action]([ { plugin: this.addon.basename } ])
				.then(response => {
					this.loading = false
					if (response.body.failed.length) {
						this.addon.isActive = !this.addon.isActive
						this.failed = true
					}
				})
				.catch(() => {
					this.loading = false
					this.addon.isActive = !this.addon.isActive
				})
		},
		processUpgradeFeature () {
			this.failed           = false
			this.featureUpgrading = true
			this.pluginsStore.upgradePlugins([ { plugin: this.addon.sku } ])
				.then(response => {
					this.featureUpgrading = false
					if (response.body.failed.length) {
						this.addon.isActive = false
						this.failed    = true
						return
					}

					this.addon = this.addons.getAddon(this.addon.sku)
				})
				.catch(() => {
					this.featureUpgrading = false
					this.addon.isActive = false
				})
		}
	},
	mounted () {
		this.addon = this.addons.getAddon(this.feature.sku)
		const params = getParams()
		if (!this.addon.isActive && params['aioseo-activate'] && params['aioseo-activate'] === this.addon.sku) {
			this.loading   = true
			this.addon.isActive = true
			this.pluginsStore.installPlugins([ { plugin: this.addon.basename } ])
				.then(() => (this.loading = false))
				.catch(() => {
					this.loading   = false
					this.addon.isActive = !this.addon.isActive
				})
		}
	}
}
</script>

<style lang="scss">
.aioseo-feature-card {
	height: 100%;
	border: 1px solid $border;
	background: #fff;
	box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);
	color: $black;
	display: flex;
	flex-direction: column;

	.feature-card-body {
		line-height: 22px;
		padding: $gutter;
		flex: 1;

		&.static {
			padding: $gutter;
		}

		.feature-card-header {
			display: flex;
			align-items: center;
			font-size: $font-md;
			font-weight: $font-bold;
			margin-bottom: 12px;

			img,
			svg {
				width: 24px;
				height: 24px;
				margin-right: 10px;
			}
		}

		.feature-card-description {
			color: $black2;
			font-size: $font-md;

			.learn-more {
				margin-top: 12px;
				font-size: $font-md;
			}
		}
	}

	.feature-card-footer {
		padding: 15px;

		&:not(.upgrade-required) {
			border: 2px solid #fff;
			background-color: $box-background;
			padding: 12px 20px;
			min-height: 43px;
		}

		.feature-card-install-activate {
			display: flex;
			align-items: center;
			justify-content: flex-end;
			height: 30px;
			position: relative;

			.aioseo-loading-spinner {
				position: absolute;
				left: 0;
			}

			.version {
				flex: 1;
			}

			.status {
				font-weight: $font-bold;
				font-size: 14px;
			}

			.aioseo-toggle {
				.toggle-content {
					margin-right: 0;
					margin-left: 10px;
				}
			}
		}

		.feature-card-upgrade-cta {
			display: flex;
			align-items: center;
			justify-content: flex-end;

			.aioseo-tooltip {
				margin: 0;
				display: inline-block;
				flex: 1;

				.version {
					cursor: pointer;
					color: $blue;
					font-weight: $font-bold;
					text-decoration: underline;
				}
			}
		}

		&.installed {
			.feature-card-install-activate {
				.status {
					color: $placeholder-color;
				}
			}
		}
	}
}

.aioseo-feature-card-modal {
	.aioseo-modal-body {
		padding: 20px 40px 40px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		position: relative;

		h3 {
			font-size: 20px;
			margin-bottom: 16px;
		}

		.reset-description {
			font-size: 16px;
			color: $black;
			margin-bottom: 16px;
			text-align: center;
			max-width: 515px;
		}

		button.close {
			position: absolute;
			right: 11px;
			top: 11px;
			width: 24px;
			height: 24px;
			background-color: #fff;
			border: none;
			display: flex;
			align-items: center;
			svg.aioseo-close {
				cursor: pointer;
				width: 14px;
				height: 14px;
			}
		}

		.aioseo-button:not(.close) {
			margin-top: 16px;
		}
	}
}
</style>