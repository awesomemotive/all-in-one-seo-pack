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
					v-if="(!activated || feature.requiresUpgrade) && !staticCard"
					class="learn-more"
				>
					<a
						:href="$links.utmUrl('feature-manager-addon-link', feature.sku, feature.learnMoreUrl)"
						target="_blank"
					>{{ $constants.GLOBAL_STRINGS.learnMore }}</a>
					<a
						:href="$links.utmUrl('feature-manager-addon-link', feature.sku, feature.learnMoreUrl)"
						class="no-underline"
						target="_blank"
					>&nbsp;&rarr;</a>
				</div>

				<div
					v-if="feature.manageUrl && ((activated && !feature.requiresUpgrade) || staticCard) && canManage"
					class="learn-more"
				>
					<a :href="getHref(feature.manageUrl)">{{ strings.manage }}</a>
					<a
						:href="getHref(feature.manageUrl)"
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
					{{ strings.activateError }}
				</core-alert>
			</div>
		</div>

		<div
			v-if="canActivate"
			class="feature-card-footer"
			:class="{ 'upgrade-required': feature.requiresUpgrade || !licenseStore.license.isActive }"
		>
			<div
				v-if="!feature.requiresUpgrade && licenseStore.license.isActive && (!feature.installed || feature.hasMinimumVersion)"
				class="feature-card-install-activate"
			>
				<core-loader
					v-if="loading"
					dark
				/>
				<span
					v-if="!loading && feature.installedVersion"
					class="version"
				>
					{{ strings.version }} {{ feature.installedVersion }}
				</span>
				<span class="status">
					{{ activated ? strings.activated : (feature.installed || feature.canInstall ? strings.deactivated : strings.notInstalled) }}
				</span>
				<base-toggle
					v-if="feature.installed || feature.canInstall"
					:modelValue="activated"
					@update:modelValue="processStatusChange"
				/>
			</div>

			<div
				v-if="feature.requiresUpgrade || !licenseStore.license.isActive"
				class="feature-card-upgrade-cta"
			>
				<base-button
					type="green"
					size="medium"
					tag="a"
					:href="$links.getUpsellUrl('feature-manager-upgrade', feature.sku, $isPro ? 'pricing' : 'liteUpgrade')"
					target="_blank"
				>
					<span v-if="$isPro">{{ strings.upgradeYourPlan }}</span>
					<span v-if="!$isPro">{{ strings.upgradeToPro }}</span>
				</base-button>
			</div>

			<div
				v-if="$isPro && !feature.requiresUpgrade && feature.installed && !feature.hasMinimumVersion"
				class="feature-card-upgrade-cta"
			>
				<core-tooltip
					v-if="activated && !loading"
				>
					<span class="version">
						{{ strings.updateToVersion }} {{ feature.minimumVersion }}
					</span>

					<template #tooltip>
						{{ strings.updateRequired }}
						<strong
							v-if="!addons.userCanUpdate(feature.sku)"
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
					:disabled="!addons.userCanUpdate(feature.sku)"
				>
					{{ strings.updateFeature }}
				</base-button>
			</div>
		</div>

		<core-modal
			v-if="showNetworkModal"
			no-header
			@close="closeNetworkModal(false)"
		>
			<template #body>
				<div class="aioseo-modal-body">
					<button
						class="close"
						@click.stop="closeNetworkModal(false)"
					>
						<svg-close @click.stop="closeNetworkModal(false)" />
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
import {
	useAddonsStore,
	useLicenseStore,
	usePluginsStore,
	useRootStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import { getParams } from '@/vue/utils/params'
import { Url } from '@/vue/mixins/Url'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgClose from '@/vue/components/common/svg/Close'
export default {
	setup () {
		return {
			addonsStore  : useAddonsStore(),
			licenseStore : useLicenseStore(),
			pluginsStore : usePluginsStore(),
			rootStore    : useRootStore()
		}
	},
	mixins     : [ Url ],
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
			showNetworkModal : false,
			failed           : false,
			loading          : false,
			activated        : false,
			featureUpgrading : false,
			strings          : {
				version           : this.$t.__('Version', this.$td),
				updateToVersion   : this.$t.__('Update to version', this.$td),
				activated         : this.$t.__('Activated', this.$td),
				deactivated       : this.$t.__('Deactivated', this.$td),
				notInstalled      : this.$t.__('Not Installed', this.$td),
				upgradeToPro      : this.$t.__('Upgrade to Pro', this.$td),
				upgradeYourPlan   : this.$t.__('Upgrade Your Plan', this.$td),
				updateFeature     : this.$t.__('Update Addon', this.$td),
				permissionWarning : this.$t.__('You currently don\'t have permission to update this addon. Please ask a site administrator to update.', this.$td),
				manage            : this.$t.__('Manage', this.$td),
				activateError     : this.$t.__('An error occurred while activating the addon. Please upload it manually or contact support for more information.', this.$td),
				updateRequired    : this.$t.sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO"), 2 - Pro.
					this.$t.__('An update is required for this addon to continue to work with %1$s %2$s.', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					'Pro'
				),
				areYouSureNetworkChange : this.$t.__('This is a network-wide change.', this.$td),
				yesProcessNetworkChange : this.$t.__('Yes, process this network change', this.$td),
				noChangedMind           : this.$t.__('No, I changed my mind', this.$td)
			}
		}
	},
	computed : {
		networkChangeMessage () {
			// The logic is reversed here because the option has been toggled already.
			if (!this.activated) {
				return this.$t.__('Are you sure you want to deactivate this addon across the network?', this.$td)
			}

			return this.$t.__('Are you sure you want to activate this addon across the network?', this.$td)
		}
	},
	methods : {
		closeNetworkModal (changeStatus = false) {
			this.activated        = changeStatus ? this.activated : !this.activated
			this.showNetworkModal = false

			if (changeStatus) {
				this.actuallyProcessStatusChange(changeStatus)
			}
		},
		processStatusChange () {
			this.activated = !this.activated
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
			const action   = this.activated ? 'installPlugins' : 'deactivatePlugins'
			this.pluginsStore[action]([ { plugin: this.feature.basename } ])
				.then(response => {
					this.loading = false
					if (response.body.failed.length) {
						this.activated = !this.activated
						this.failed = true
					}
				})
				.catch(() => {
					this.loading   = false
					this.activated = !this.activated
				})
		},
		processUpgradeFeature () {
			this.failed           = false
			this.featureUpgrading = true
			const addon           = addons.getAddon(this.feature.sku)
			this.pluginsStore.upgradePlugins([ { plugin: this.feature.sku } ])
				.then(response => {
					this.featureUpgrading = false
					if (response.body.failed.length) {
						this.activated = false
						this.failed    = true
						return
					}

					this.activated          = true
					const updatedAddon      = response.body.completed[addon.sku]
					addon.hasMinimumVersion = true
					addon.isActive          = true
					addon.installedVersion  = updatedAddon.installedVersion
					this.addonsStore.updateAddon(addon)
				})
				.catch(() => {
					this.featureUpgrading = false
					this.activated        = false
				})
		}
	},
	mounted () {
		if (this.feature.isActive) {
			this.activated = true
		}

		const params = getParams()
		if (!this.activated && params['aioseo-activate'] && params['aioseo-activate'] === this.feature.sku) {
			this.loading   = true
			this.activated = true
			this.pluginsStore.installPlugins([ { plugin: this.feature.basename } ])
				.then(() => (this.loading = false))
				.catch(() => {
					this.loading   = false
					this.activated = !this.activated
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

	.aioseo-modal-body {
		padding: 20px 50px 50px;
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