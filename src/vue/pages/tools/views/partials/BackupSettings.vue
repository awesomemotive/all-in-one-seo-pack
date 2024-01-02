<template>
	<core-card
		id="aioseo-backup-settings"
		class="aioseo-backup-settings"
		slug="backupSettings"
		:toggles="false"
		no-slide
		:header-text="strings.backupSettings"
	>
		<template #header-icon>
			<svg-history />
		</template>

		<div
			class="aioseo-settings-row"
			v-if="rootStore.aioseo.data.isNetworkAdmin"
		>
			<div class="select-site">
				{{ strings.selectSite }}
			</div>

			<core-network-site-selector
				@selected-site="site = $event"
			/>
		</div>

		<core-alert
			v-if="backupsDeleteSuccess"
			type="green"
		>
			{{ strings.backupSuccessfullyDeleted }}
		</core-alert>

		<core-alert
			v-if="backupsRestoreSuccess"
			type="green"
		>
			{{ strings.backupSuccessfullyRestored }}
		</core-alert>

		<div
			v-if="!getBackups.length"
			class="aioseo-section-description"
		>
			{{ strings.noBackups }}
		</div>

		<template
			v-if="getBackups.length && !(rootStore.aioseo.data.isNetworkAdmin && !site)"
		>
			<div class="backups-table">
				<div class="backups-rows">
					<div
						class="backup-row"
						:class="{ even: 0 === index % 2 }"
						v-for="(backup, index) in getBackups"
						:key="index"
					>
						<div
							class="backup-name"
							v-html="getBackupName(backup)"
						/>

						<div class="backup-actions">
							<core-tooltip
								type="action"
							>
								<svg-refresh
									@click.native="maybeRestoreBackup(backup)"
								/>

								<template #tooltip>
									{{ strings.restore }}
								</template>
							</core-tooltip>

							<core-tooltip
								type="action"
							>
								<svg-trash
									@click.native="maybeDeleteBackup(backup)"
								/>

								<template #tooltip>
									{{ strings.delete }}
								</template>
							</core-tooltip>
						</div>
					</div>
				</div>
			</div>
		</template>

		<base-button
			type="blue"
			size="medium"
			@click="processCreateBackup"
			:loading="loading"
			:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
		>
			<svg-circle-plus />
			{{ strings.createBackup }}
		</base-button>

		<core-modal
			:show="showModal"
			no-header
			@close="showModal = false"
			:classes="[ 'aioseo-core-backup-settings-modal' ]"
		>
			<template #body >
				<div class="aioseo-modal-body">
					<button
						class="close"
						@click.stop="showModal = false"
					>
						<svg-close @click="showModal = false" />
					</button>

					<h3>{{ areYouSure }}</h3>
					<div class="reset-description"
						v-html="strings.actionCannotBeUndone"
					/>

					<base-button
						type="blue"
						size="medium"
						@click="processBackupAction"
					>
						{{ iAmSure }}
					</base-button>

					<base-button
						type="gray"
						size="medium"
						@click="showModal = false"
					>
						{{ strings.noChangedMind }}
					</base-button>
				</div>
			</template>
		</core-modal>
	</core-card>
</template>

<script>
import {
	useBackupsStore,
	useRootStore
} from '@/vue/stores'

import { DateTime } from 'luxon'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreNetworkSiteSelector from '@/vue/components/common/core/NetworkSiteSelector'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'
import SvgClose from '@/vue/components/common/svg/Close'
import SvgHistory from '@/vue/components/common/svg/History'
import SvgRefresh from '@/vue/components/common/svg/Refresh'
import SvgTrash from '@/vue/components/common/svg/Trash'
export default {
	setup () {
		return {
			backupsStore : useBackupsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		CoreAlert,
		CoreCard,
		CoreModal,
		CoreNetworkSiteSelector,
		CoreTooltip,
		SvgCirclePlus,
		SvgClose,
		SvgHistory,
		SvgRefresh,
		SvgTrash
	},
	data () {
		return {
			site                  : null,
			timeout               : null,
			backupToDelete        : null,
			backupToRestore       : null,
			backupsDeleteSuccess  : false,
			showModal             : false,
			backupsRestoreSuccess : false,
			loading               : false,
			strings               : {
				backupSettings             : this.$t.__('Backup Settings', this.$td),
				areYouSureDeleteBackup     : this.$t.__('Are you sure you want to delete this backup?', this.$td),
				areYouSureRestoreBackup    : this.$t.__('Are you sure you want to restore this backup?', this.$td),
				yesDeleteBackup            : this.$t.__('Yes, I want to delete this backup', this.$td),
				yesRestoreBackup           : this.$t.__('Yes, I want to restore this backup', this.$td),
				noChangedMind              : this.$t.__('No, I changed my mind', this.$td),
				actionCannotBeUndone       : this.$t.__('This action cannot be undone.', this.$td),
				noBackups                  : this.$t.__('You have no saved backups.', this.$td),
				createBackup               : this.$t.__('Create Backup', this.$td),
				restore                    : this.$t.__('Restore', this.$td),
				delete                     : this.$t.__('Delete', this.$td),
				backupSuccessfullyDeleted  : this.$t.__('Success! The backup was deleted.', this.$td),
				backupSuccessfullyRestored : this.$t.__('Success! The backup was restored.', this.$td)
			}
		}
	},
	computed : {
		getBackups () {
			if (this.site) {
				return this.backupsStore.networkBackups[this.site.blog_id] || []
			}
			return this.backupsStore.backups
		},
		areYouSure () {
			return this.backupToDelete ? this.strings.areYouSureDeleteBackup : this.strings.areYouSureRestoreBackup
		},
		iAmSure () {
			return this.backupToDelete ? this.strings.yesDeleteBackup : this.strings.yesRestoreBackup
		}
	},
	methods : {
		processCreateBackup () {
			this.loading = true
			this.backupsStore.createBackup({
				siteId : this.site ? this.site.blog_id : null
			})
				.then(() => {
					this.loading = false
				})
		},
		maybeDeleteBackup (backup) {
			this.showModal       = true
			this.backupToRestore = null
			this.backupToDelete  = backup
		},
		maybeRestoreBackup (backup) {
			this.showModal       = true
			this.backupToDelete  = null
			this.backupToRestore = backup
		},
		processDeleteBackup () {
			this.loading = true
			this.backupsStore.deleteBackup({
				backup : this.backupToDelete,
				siteId : this.site ? this.site.blog_id : null
			})
				.then(() => {
					clearTimeout(this.timeout)
					this.loading              = false
					this.showModal            = false
					this.backupToDelete       = null
					this.backupsDeleteSuccess = true
					this.timeout = setTimeout(() => {
						this.backupsDeleteSuccess  = false
						this.backupsRestoreSuccess = false
					}, 3000)
				})
		},
		processRestoreBackup () {
			this.loading = true
			this.backupsStore.restoreBackup({
				backup : this.backupToRestore,
				siteId : this.site ? this.site.blog_id : null
			})
				.then(() => {
					clearTimeout(this.timeout)
					this.loading              = false
					this.showModal            = false
					this.backupToRestore      = null
					this.backupsRestoreSuccess = true
					this.timeout = setTimeout(() => {
						this.backupsDeleteSuccess  = false
						this.backupsRestoreSuccess = false
					}, 3000)
				})
		},
		getBackupName (backup) {
			const date = DateTime.fromMillis(backup * 1000).setZone(DateTime.local().zoneName)
			return this.$t.sprintf(
				// Translators: 1 - Date, 2 - Timestamp.
				this.$t.__('%1$s at %2$s', this.$td),
				'<strong>' + date.toFormat('MMMM d, yyyy') + '</strong>',
				'<strong>' + date.toFormat('h:mma ZZZZ') + '</strong>'
			)
		},
		processBackupAction () {
			return this.backupToDelete ? this.processDeleteBackup() : this.processRestoreBackup()
		}
	}
}
</script>

<style lang="scss">
.aioseo-backup-settings {
	.aioseo-alert {
		margin-bottom: 20px;
	}

	.backups-table {
		border: 1px solid $input-border;
		border-radius: 3px;
		margin-bottom: 20px;
		font-size: 16px;

		.backup-actions {
			max-width: 50px;

			.aioseo-tooltip {
				display: inline-block;
				margin: 0;
			}
		}

		.backups-rows {
			font-size: 14px;

			.backup-row {
				background-color: #fff;
				height: 56px;
				display: flex;
				align-items: center;
				padding: 0 16px;

				&:last-of-type {
					border-radius: 0 0 3px 3px;
				}

				&.even {
					background-color: $box-background;
				}

				> div {
					flex: 1 0 auto;
					padding-right: 30px;

					&:last-child {
						padding-right: 0;
					}
				}

				.backup-actions {
					svg {
						width: 20px;
						height: 20px;
						color: $placeholder-color;
						cursor: pointer;
						transition: color 0.1s ease;
					}

					svg.aioseo-trash {
						&:hover {
							color: $red;
						}
					}

					svg.aioseo-refresh {
						margin-right: 10px;

						&:hover {
							color: $green;
						}
					}
				}
			}
		}
	}

	svg.aioseo-circle-plus {
		width: 14px;
		height: 14px;
		margin-right: 10px;
	}
}

.aioseo-core-backup-settings-modal {
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

		.aioseo-description {
			max-width: 510px;
			text-align: center;
		}

		.aioseo-button:not(.close) {
			margin-top: 16px;
		}
	}
}
</style>