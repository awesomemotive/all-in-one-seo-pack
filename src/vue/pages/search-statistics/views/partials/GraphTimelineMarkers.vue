<template>
	<div class="aioseo-graph-timeline-markers">
		<div class="aioseo-graph-timeline-markers-title">{{ title }}</div>

		<div
			v-if="showTypeSelector"
			class="aioseo-graph-timeline-markers-types"
		>
			<div
				v-for="(type, index) in types"
				:key="index"
				@click="() => back(type.name)"
				class="aioseo-graph-timeline-markers-types__item aioseo-graph-timeline-markers-types__item--clickable"
			>
				<div class="aioseo-graph-timeline-markers-types__item-icon">
					<img :alt="type.name" :src="type.icon" />
				</div>

				<div class="aioseo-graph-timeline-markers-types__item-title">{{ type.title }} ({{ type.count }})</div>

				<div class="aioseo-graph-timeline-markers-types__item-arrow">
					<svg-caret />
				</div>
			</div>
		</div>

		<div
			v-if="!showTypeSelector"
			class="aioseo-graph-timeline-markers-item"
		>
			<div class="aioseo-graph-timeline-markers-types__item">
				<div class="aioseo-graph-timeline-markers-types__item-icon">
					<img :alt="type.name" :src="type.icon" />
				</div>

				<div class="aioseo-graph-timeline-markers-types__item-title">{{ type.title }}</div>
			</div>

			<div v-if="'aioseoRevision' === item.type || 'wpRevision' === item.type">
				<div class="aioseo-graph-timeline-markers-item__title">
					{{ strings.theFollowingChanges }}
				</div>

				<div class="aioseo-graph-timeline-markers-item__content">
					{{ item.fields.join(', ') }}
				</div>

				<div class="aioseo-graph-timeline-markers-item__footer">
					<base-button
						type="gray"
						v-if="showBackButton"
						@click="() => back()"
						size="small"
					>
						<svg-caret />
					</base-button>

					<base-button
						v-if="'aioseoRevision' === item.type"
						type="blue"
						size="small"
						@click="() => modalOpen = true"
					>
						{{ strings.viewRevisions }}
					</base-button>

					<base-button
						v-if="'wpRevision' === item.type"
						:href="item.link"
						type="blue"
						tag="a"
						target="_blank"
						size="small"
					>
						{{ strings.viewRevisions }}
					</base-button>
				</div>
			</div>

			<div v-if="'googleUpdate' === item.type">
				<div class="aioseo-graph-timeline-markers-item__title">
					{{ item.title }}
				</div>

				<div class="aioseo-graph-timeline-markers-item__content">
					{{ item.description }}
				</div>

				<div class="aioseo-graph-timeline-markers-item__footer">
					<base-button
						type="gray"
						v-if="showBackButton"
						@click="() => back()"
						size="small"
					>
						<svg-caret />
					</base-button>

					<base-button
						v-if="item.link"
						type="blue"
						tag="a"
						:href="item.link"
						target="_blank"
						size="small"
					>
						{{ strings.readMore }}
					</base-button>

					<div
						class="aioseo-graph-timeline-markers-item__footer-status"
						:class="`aioseo-graph-timeline-markers-item__footer-status--${item.status}`"
					>
						{{ item.status }}
					</div>
				</div>
			</div>
		</div>

		<core-modal-portal
			:classes="[ 'aioseo-graph-timeline-markers-modal' ]"
			v-if="modalOpen"
			@close="() => this.modalOpen = false"
		>
			<template #headerTitle>
				{{ type.title }}
			</template>

			<template #body>
				<div class="aioseo-graph-timeline-markers-modal__body">
					<div
						v-if="'wpRevision' === item.type"
						class="aioseo-graph-timeline-markers__revisions aioseo-graph-timeline-markers__revisions--wordpress"
					>
						<div
							v-for="(diff, index) in item.diff"
							:key="index"
							class="aioseo-graph-timeline-markers__revisions-item"
						>
							<div class="aioseo-revision__title">{{ diff.name }}</div>
							<div class="aioseo-revision__content" v-html="diff.diff"></div>
						</div>
					</div>

					<div
						v-if="'aioseoRevision' === item.type"
						class="aioseo-graph-timeline-markers__revisions aioseo-graph-timeline-markers__revisions--aioseo"
					>
						<div class="aioseo-seo-revisions-list">
							<seo-revision-list-item
								v-for="(revision, index) in item.revisions"
								:key="'seo-revision-list-item-' + index"
								class="aioseo-graph-timeline-markers__revisions-item"
								:revision="revision"
								:compareTo="item.compareTo"
								:isCurrentVersion="revision.id === item.compareTo.id"
								context="metabox"
								@maybeDeleteSeoRevision="maybeDeleteSeoRevision"
							/>
						</div>
					</div>
				</div>
			</template>
		</core-modal-portal>

		<seo-revisions-delete-warning
			:modalOpenDeleteRevisionWarning="modalOpenDeleteRevisionWarning"
			:revision="itemToBeDeleted"
			v-if="itemToBeDeleted"
		/>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import { getAssetUrl } from '@/vue/utils/helpers'
import dateFormat from '@/vue/utils/dateFormat'
import CoreModalPortal from '@/vue/components/common/core/modal/Portal'
import SeoRevisionsDeleteWarning from '@/vue/standalone/post-settings/views/pro/partials-seo-revisions/DeleteWarning'
import SeoRevisionListItem from '@/vue/standalone/post-settings/views/pro/partials-seo-revisions/ListItem'
import SvgAioseo from '@/vue/assets/images/logos/aioseo.svg'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgGoogle from '@/vue/assets/images/logos/google.svg'
import SvgWordPress from '@/vue/assets/images/logos/wordpress.svg'
export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	emits      : [ 'update' ],
	components : {
		CoreModalPortal,
		SeoRevisionsDeleteWarning,
		SeoRevisionListItem,
		SvgCaret
	},
	data () {
		return {
			item                           : {},
			itemToBeDeleted                : {},
			modalOpen                      : false,
			modalOpenDeleteRevisionWarning : false,
			strings                        : {
				theFollowingChanges : this.$t.__('The following changes were made that could affect your site rankings:', this.$td),
				readMore            : this.$t.__('Read More', this.$td),
				viewRevisions       : this.$t.__('View Revisions', this.$td)
			}
		}
	},
	watch : {
		items (newVal) {
			this.item = {}

			// If there's just one item, just display it.
			if (1 === newVal.length) {
				this.item = newVal[0]
			}
		}
	},
	props : {
		date : {
			type    : String,
			default : ''
		},
		timelineMarkers : {
			type    : Object,
			default : () => {}
		}
	},
	methods : {
		back (type) {
			this.item  = this.items.find(a => a.type === type) || {}
		},
		maybeDeleteSeoRevision (revision) {
			this.modalOpenDeleteRevisionWarning = true
			this.itemToBeDeleted = revision
		}
	},
	computed : {
		types () {
			let types = [
				{
					name  : 'wpRevision',
					title : this.$t.sprintf(
						// Translators: 1 - WordPress or the plugin name ("AIOSEO").
						this.$t.__('%1$s Revisions', this.$td),
						'WordPress'
					),
					icon : getAssetUrl(SvgWordPress)
				},
				{
					name  : 'aioseoRevision',
					title : this.$t.sprintf(
						// Translators: 1 - WordPress or the plugin name ("AIOSEO").
						this.$t.__('%1$s Revisions', this.$td),
						import.meta.env.VITE_SHORT_NAME
					),
					icon : getAssetUrl(SvgAioseo)
				},
				{
					name  : 'googleUpdate',
					title : this.$t.sprintf(
						// Translators: 1 - Google.
						this.$t.__('%1$s Updates', this.$td),
						'Google'
					),
					icon : getAssetUrl(SvgGoogle)
				}
			]

			types = types.map((type) => {
				return { ...type, count: this.items.filter(item => item.type === type.name).length }
			})

			// Only types with items.
			types = types.filter(type => 0 < type.count)

			return types
		},
		type () {
			return this.types.find(type => type.name === this.item.type) || {}
		},
		title () {
			if (!this.date) {
				return ''
			}

			return dateFormat(new Date(this.date + ' 00:00:00'), this.rootStore.aioseo.data.dateFormat)
		},
		items () {
			return this.timelineMarkers[this.date] || []
		},
		showTypeSelector () {
			return 1 < this.items.length && 0 === Object.keys(this.item).length
		},
		showBackButton () {
			return 1 < this.items.length
		}
	},
	mounted () {
		// If there's just one item, just display it.
		if (1 === this.items.length) {
			this.item = this.items[0]
		}
	},
	updated () {
		this.$emit('update', {
			item  : this.item,
			modal : this.modalOpen
		})
	}
}
</script>

<style lang="scss">
.aioseo-graph-timeline-markers {
	width: 310px;

	&-title {
		font-size: 14px;
		line-height: 22px;
		color: $black2;
		margin-bottom: 12px;
	}

	&-types {
		&__item {
			display: flex;
			align-items: center;

			&-icon {
				width: 24px;
				height: 24px;
				border: 1px solid $input-border;
				border-radius: 50%;
				margin-right: 8px;
				text-align: center;
				display: flex;
				align-items: center;
				justify-content: center;

				img {
					width: 16px;
				}
			}

			&-title {
				font-size: 16px;
				font-weight: 700;
				line-height: 24px;
				color: $black;
			}

			&-arrow {
				margin-left: auto;

				svg.aioseo-caret {
					width: 20px;
					height: 20px;
					transform: rotate(-90deg);
					color: $placeholder-color;
				}
			}

			&:not(:first-child) {
				margin-top: 12px;
				padding-top: 12px;
				border-top: 1px solid $gray;
			}

			&--clickable {
				cursor: pointer;
			}
		}
	}

	&-item {
		.aioseo-graph-timeline-markers-types__item {
			margin-bottom: 12px;
		}

		&__title {
			font-weight: 700;
			font-size: 14px;
			line-height: 22px;
			color: $black;
			margin-bottom: 12px;
		}

		&__content {
			font-weight: 400;
			font-size: 14px;
			line-height: 22px;
			margin-bottom: 12px;
			color: $black;
		}

		&__footer {
			display: flex;
			align-items: center;

			.aioseo-caret {
				margin: 0;
				height: 14px;
				width: 14px;
				transform: rotate(90deg);
				color: $black2-hover;
			}

			.aioseo-button {
				margin-right: 8px;
			}

			&-status {
				margin-left: auto;
				border: 1px solid $gray;
				border-radius: 2px;
				padding: 2px 8px;
				text-transform: uppercase;

				&--confirmed {
					background: rgba( $green, 0.1 );
					border: 1px solid $green;
					color: $green;
				}

				&--unconfirmed {
					background: rgba( $red, 0.1 );
					border: 1px solid $red;
					color: $red;
				}
			}
		}
	}

	&-modal {
		&__body {
			padding: 24px;

			.aioseo-graph-timeline-markers__revisions {
				&--wordpress {
					&:not(:last-child) {
						margin-bottom: 12px;
						padding-bottom: 12px;
						border-bottom: 1px solid $border;
					}

					.aioseo-revision__title {
						color: $black;
						font-weight: 600;
						font-size: 16px;
						line-height: 22px;
						margin-bottom: 16px;
					}

					.aioseo-revision__content {
						margin: -20px;

						.diff {
							border-spacing: 20px;

							&-sub-title {
								display: none;
							}
						}
					}
				}

				&--aioseo {
					--bullet-ms: 12px;
					--item-p: 10px;

					.aioseo-seo-revision-list-item {
						padding: var(--item-p);
					}

					.aioseo-seo-revisions-list {
						--border-size: 2px;
						--bullet-size: 12px;
						position: relative;

						&:before {
							content: '';
							background-color: $input-border;
							height: calc(100% - var(--item-p));
							left: calc(var(--border-size) / 2);
							position: absolute;
							top: var(--item-p);
							width: var(--border-size);
						}

						.aioseo-seo-revision-list-item {
							margin-left: calc(var(--bullet-ms) * 2);

							&:before {
								content: '';
								background-color: $input-border;
								border-radius: 50%;
								height: var(--bullet-size);
								left: calc((var(--bullet-ms) + var(--border-size)) * -2);
								position: absolute;
								top: var(--item-p);
								width: var(--bullet-size);
							}

							&:first-of-type {
								&:before {
									background-color: $blue;
								}
							}

							&:last-of-type {
								&:after {
									content: '';
									background-color: #fff;
									height: 100%;
									left: calc(var(--bullet-ms) * -2 + var(--border-size) / 2);
									position: absolute;
									top: calc(var(--item-p) + var(--bullet-size));
									width: var(--border-size);
								}
							}
						}
					}
				}
			}
		}
	}
}
</style>