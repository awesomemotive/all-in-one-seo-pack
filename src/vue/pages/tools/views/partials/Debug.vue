<template>
	<div class="aioseo-tools-debug">
		<core-card
			slug="debug"
			:header-text="strings.cardLabel"
		>
			<div
				class="aioseo-settings-row"
				v-if="rootStore.aioseo.data.isNetworkAdmin"
			>
				<div class="select-site">
					{{ strings.selectSite }}
				</div>

				<core-network-site-selector
					@selected-site="site = $event"
					show-network
				/>
			</div>

			<core-alert type="yellow">
				<div>{{ strings.alertWarning }}</div>

				<div v-html="alertLink" />
			</core-alert>

			<core-main-tabs
				internal
				:key="tabsKey"
				:tabs="tabs"
				:active="activeTab"
				:showSaveButton="false"
				@changed="value => activeTab = value"
			/>

			<core-settings-row
				v-for="(action, index) in activeTabObject.actions"
				:key="activeTab + index"
				:name="action.label"
				align
			>
				<template #content>
					<template
						v-if="action.component"
					>
						<component
							:is="action.component"
							@update="data => maybeDoAction(action, data)"
							:loading="doingAction[action.slug]"
							:disabled="isActionDisabled(action)"
						/>
					</template>

					<base-button
						v-else
						type="blue"
						size="medium"
						@click="maybeDoAction(action)"
						:loading="doingAction[action.slug]"
						:key="doingActionKey"
						:disabled="isActionDisabled(action)"
					>
						{{ strings.buttonLabel }}
					</base-button>

					<div class="aioseo-description"
						v-html="action.shortDescription"
					/>

					<div class="aioseo-description"
						v-html="action.longDescription"
					/>

					<template
						v-if="action.infoComponent"
					>
						<component :is="action.infoComponent"/>
					</template>
				</template>
			</core-settings-row>

			<core-modal
				:show="showAreYouSureModal"
				no-header
				@close="showAreYouSureModal = false"
				:classes="[ 'aioseo-debug-modal' ]"
			>
				<template #body >
					<div class="aioseo-modal-body">
						<button
							class="close"
							@click.stop="showAreYouSureModal = false"
						>
							<svg-close @click="showAreYouSureModal = false" />
						</button>

						<h3>{{ areYouSureTitle }}</h3>

						<div
							class="description"
							v-html="strings.cannotBeUndone"
						/>

						<base-button
							type="blue"
							size="medium"
							@click="doAction"
						>
							{{ strings.yesDoAction }}
						</base-button>

						<base-button
							type="gray"
							size="medium"
							@click="showAreYouSureModal = false"
						>
							{{ strings.noChangedMind }}
						</base-button>
					</div>
				</template>
			</core-modal>
		</core-card>
	</div>
</template>

<script>
import links from '@/vue/utils/links'
import {
	useRootStore,
	useToolsStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreNetworkSiteSelector from '@/vue/components/common/core/NetworkSiteSelector'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import DeprecatedOptions from './debug/DeprecatedOptions'
import MigrationInfo from './debug/MigrationInfo'
import SvgClose from '@/vue/components/common/svg/Close'

import { sprintf } from '@/vue/plugins/translations'

export default {
	setup () {
		return {
			rootStore  : useRootStore(),
			toolsStore : useToolsStore()
		}
	},
	components : {
		CoreAlert,
		CoreCard,
		CoreMainTabs,
		CoreModal,
		CoreNetworkSiteSelector,
		CoreSettingsRow,
		DeprecatedOptions,
		MigrationInfo,
		SvgClose
	},
	props : {
		extraActions : {
			type     : Array,
			required : false
		}
	},
	data () {
		return {
			site                : {},
			tabsKey             : 0,
			doingActionKey      : 0,
			activeTab           : 'general',
			currentAction       : '',
			showAreYouSureModal : false,
			doingAction         : [],
			strings             : {
				selectSite     : 'Select Site',
				cardLabel      : 'Debug',
				selectLabel    : 'Select a Debug Action:',
				buttonLabel    : 'Run Action',
				alertWarning   : 'Before you run any action, please make sure that you have fully read the description and understand the consequences as these cannot be reverted.',
				cannotBeUndone : 'This action cannot be undone.',
				yesDoAction    : 'Yes, run this action',
				noChangedMind  : 'No, I changed my mind'
			},
			alertLink : links.getPlainLink('Click here to open to the Scheduled Actions panel', this.rootStore.aioseo.urls.admin.scheduledActions, true)
		}
	},
	computed : {
		areYouSureTitle () {
			return `Are you sure you want to run the "${this.currentAction.label}" action?`
		},
		tabs () {
			const scheduledActionsLink          = sprintf('<a href="%1$s" target="_blank">Scheduled Actions</a>', this.rootStore.aioseo.urls.admin.scheduledActions)
			const networkClearCache             = this.rootStore.aioseo.data.isNetworkAdmin ? '<br><strong>NOTE: If no site is selected, this will clear the network cache.</strong>' : ''
			const networkPluginUpdatesTransient = this.rootStore.aioseo.data.isNetworkAdmin ? '<br><strong>NOTE: If no site is selected, this will clear the network plugin updates transient.</strong>' : ''
			return [
				{
					slug    : 'general',
					name    : 'General',
					actions : [
						{
							label            : 'Clear Cache',
							slug             : 'clear-cache',
							shortDescription : `This action deletes all records of the <code>aioseo_cache</code> table in the database.${networkClearCache}`,
							longDescription  : '',
							showModal        : false,
							network          : true
						},
						{
							label            : 'Clear Plugin Updates Transient',
							slug             : 'clear-plugin-updates-transient',
							shortDescription : `This action clears the plugin updates transient, which forces WordPress Core to check for plugin updates.${networkPluginUpdatesTransient}`,
							longDescription  : '',
							showModal        : false,
							network          : true
						},
						{
							label            : 'Readd Capabilities',
							slug             : 'readd-capabilities',
							shortDescription : 'This action will readd our capabilities (access permissions) for all users.',
							longDescription  : '',
							showModal        : false
						},
						{
							label            : 'Reset Data',
							slug             : 'reset-data',
							shortDescription : 'This action will <strong>delete</strong> all our custom tables and options.',
							longDescription  : '',
							showModal        : true
						}
					]
				},
				{
					slug    : 'sitemap',
					name    : 'Sitemap',
					actions : [
						{
							label            : 'Clear Image Data',
							slug             : 'clear-image-data',
							shortDescription : 'This action removes all image data from the database, forcing a site-wide rescan via Action Scheduler.',
							longDescription  : sprintf(
								'To speed up the image scan, go to %1$s and run the <code>aioseo_image_sitemap_scan</code> action.',
								scheduledActionsLink
							),
							showModal : false
						}
					]
				},
				{
					slug    : 'migrations',
					name    : 'Migrations',
					actions : [
						{
							label            : 'Rerun V4+ Migrations',
							slug             : 'rerun-migrations',
							shortDescription : 'This action will rerun all update migrations since 4.0.0, excluding the V3 migration.',
							longDescription  : '',
							showModal        : true
						}
					]
				},
				{
					slug    : 'old-issues',
					name    : 'Old Issues',
					actions : [
						{
							label            : 'Remove Duplicates',
							slug             : 'remove-duplicates',
							shortDescription : 'This action will delete any duplicate records that are found in the <code>aioseo_posts</code> and <code>aioseo_terms</code> tables.',
							longDescription  : '',
							showModal        : false
						},
						{
							label            : 'Unescape Data',
							slug             : 'unescape-data',
							shortDescription : 'This action will clean <code>aioseo_posts</code> and <code>aioseo_term</code> records whose data is corrupted.',
							longDescription  : sprintf(
								'The action will trigger a routine which runs in batches via Action Scheduler. It may take some time for this routine to complete, To speed up this process, go to %1$s and run the <code>aioseo_unslash_escaped_data_posts</code> or <code>aioseo_unslash_escaped_data_terms</code> action.',
								scheduledActionsLink
							),
							showModal : false
						}
					]
				},
				{
					slug    : 'deprecated-options',
					name    : 'Deprecated Options',
					actions : [
						{
							label            : 'Deprecated Options',
							slug             : 'deprecated-options',
							shortDescription : 'Enable or disable any options that have been deprecated in AIOSEO.',
							longDescription  : '<strong>These options are not guaranteed to work and all support has been dropped.</strong>',
							showModal        : false,
							component        : 'deprecated-options'
						}
					]
				}
			]
		},
		activeTabObject () {
			return this.tabs.find(x => x.slug === this.activeTab)
		}
	},
	methods : {
		isActionDisabled (action) {
			if (!this.rootStore.aioseo.data.isNetworkAdmin) {
				return false
			}

			if (!this.site.blog_id) {
				return true
			}

			if ('network' === this.site.blog_id && action.network) {
				return false
			}

			if ('network' === this.site.blog_id && !action.network) {
				return true
			}

			return false
		},
		isLoading (action) {
			return !!this.doingAction[action.slug]
		},
		getSelectedActionObject (savedOption) {
			let option = null
			this.actions.forEach(group => {
				const localOption = group.options.find(o => o.value === savedOption)
				if (localOption) {
					option = localOption
				}
			})

			return option
		},
		maybeDoAction (action, data) {
			this.currentAction = action
			if (action.showModal) {
				this.showAreYouSureModal = true
				return
			}

			this.doAction(data)
		},
		doAction (data) {
			this.doingAction[this.currentAction.slug] = true
			this.showAreYouSureModal                  = false

			this.doingActionKey++
			this.toolsStore.doTask({
				action : this.currentAction.slug,
				siteId : this.site.blog_id || this.rootStore.aioseo.data.currentBlogId,
				data
			}).then(() => {
				console.log(`Action "${this.currentAction.label}" has been completed.`)
			}).catch((error) => {
				console.error(`Action "${this.currentAction.label}" could not be completed: `, error)
			}).finally(() => {
				this.doingAction[this.currentAction.slug] = false
				this.doingActionKey++
			})
		}
	},
	beforeMount () {
		let existingTabIndex = -1
		if (this.rootStore.aioseo.data.v3Options) {
			existingTabIndex = this.tabs.findIndex(existingTab => 'migrations' === existingTab.slug.toLowerCase())
			if (-1 !== existingTabIndex) {
				const scheduledActionsLink = sprintf('<a href="%1$s" target="_blank">Scheduled Actions</a>', this.rootStore.aioseo.urls.admin.scheduledActions)
				this.tabs[existingTabIndex].actions.push({
					label            : 'Rerun V3 Migration',
					slug             : 'restart-v3-migration',
					shortDescription : 'This action restarts the migration from V3 to V4.',
					longDescription  : sprintf(
						'All settings will be migrated immediately. However, the post/term meta needs to be migrated via a routine which runs in batches via Action Scheduler. To speed up the post/term meta migration, go to %1$s and run the <code>aioseo_migrate_post_meta</code> or <code>aioseo_migrate_term_meta</code> action.',
						scheduledActionsLink
					),
					infoComponent : 'MigrationInfo',
					showModal     : true
				})
			}
		}

		if (!this.extraActions?.length) {
			return
		}

		this.extraActions.forEach(tab => {
			existingTabIndex = this.tabs.findIndex(existingTab => existingTab.slug.toLowerCase() === tab.slug.toLowerCase())
			if (-1 !== existingTabIndex) {
				this.tabs[existingTabIndex].actions = this.tabs[existingTabIndex].actions.concat(tab.actions)
				return
			}
			this.tabs.push(tab)
		})
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-tools-debug {

	.select-site {
		font-size: 16px;
		font-weight: bold;
		margin-bottom: 5px;
	}

	.aioseo-alert {
		div:first-of-type {
			margin-bottom: 10px;
		}
	}

	.aioseo-tabs.internal {
		margin: 8px 0 var(--aioseo-gutter) 0;
	}
}

.aioseo-debug-modal {
	.aioseo-description:empty {
		display: none;
	}

	.aioseo-modal-body {
		padding: 20px 40px 40px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		position: relative;

		.description {
			font-size: 16px;
			color: $black;
			margin-bottom: 16px;
		}

		button {
			&.aioseo-button {
				margin-top: 16px;
			}

			&.close {
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
		}
	}
}
</style>