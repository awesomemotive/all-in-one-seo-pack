<template>
	<div class="aioseo-app">
		<div
			class="aioseo-details-column"
			:class="{
				editing: showEditTitle || showEditDescription
			}"
		>
			<div>
				<div>
					<core-tooltip class="aioseo-details-column__tooltip">
						<div class="edit-row edit-title">
							<strong>{{ strings.title }} </strong>

							<span v-if="!showEditTitle">
								<strong>:</strong>
								{{ truncate(titleParsed, 100) }}
							</span>

							<core-loader v-if="termLoading" dark />

							<svg-pencil
								v-if="!showEditTitle"
								class="pencil-icon"
								@click.prevent="editTitle"
							/>
						</div>

						<template #tooltip>
							<strong>{{ strings.title }}:</strong>
							{{ titleParsed }}
						</template>
					</core-tooltip>
				</div>

				<div
					v-if="showEditTitle"
					class="edit-row"
				>
					<core-html-tags-editor
						v-model="title"
						:line-numbers="false"
						single
						tags-context="taxonomyTitle"
						defaultMenuOrientation="bottom"
						tagsDescription=''
						:default-tags="[ 'taxonomy_title' ]"
					/>

					<base-button
						type="gray"
						size="small"
						@click.prevent="cancel"
					>
						{{ strings.discardChanges }}
					</base-button>

					<base-button
						type="blue"
						size="small"
						@click.prevent="save"
					>
						{{ strings.saveChanges }}
					</base-button>
				</div>

				<div>
					<core-tooltip class="aioseo-details-column__tooltip">
						<div class="edit-row edit-description">
							<strong>{{ strings.description }}</strong>

							<span v-if="!showEditDescription">
								<strong>:</strong>
								{{ truncate(descriptionParsed) }}
							</span>

							<core-loader v-if="termLoading" dark />

							<svg-pencil
								v-if="!showEditDescription"
								class="pencil-icon"
								@click.prevent="editDescription"
							/>
						</div>

						<template #tooltip>
							<strong>{{ strings.description }}:</strong>
							{{ descriptionParsed }}
						</template>
					</core-tooltip>
				</div>

				<div
					v-if="showEditDescription"
					class="edit-row"
				>
					<core-html-tags-editor
						v-model="termDescription"
						:line-numbers="false"
						tags-context="taxonomyDescription"
						defaultMenuOrientation="bottom"
						tagsDescription=''
						:default-tags="[ 'taxonomy_description' ]"
					/>

					<base-button
						type="gray"
						size="small"
						@click.prevent="cancel"
					>
						{{ strings.discardChanges }}
					</base-button>

					<base-button
						type="blue"
						size="small"
						@click.prevent="save"
					>
						{{ strings.saveChanges }}
					</base-button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import http from '@/vue/utils/http'
import { merge } from 'lodash-es'
import { useTruSeoScore } from '@/vue/composables'
import { TruSeoScore } from '@/vue/mixins/TruSeoScore'
import { truncate } from '@/vue/utils/html'
import { truSeoShouldAnalyze } from '@/vue/plugins/tru-seo/components/helpers'
import BaseButton from '@/vue/components/common/base/Button'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgPencil from '@/vue/components/common/svg/Pencil'
import '@/vue/assets/scss/main.scss'

export default {
	setup () {
		const { strings } = useTruSeoScore()

		return {
			composableStrings : strings
		}
	},
	components : {
		BaseButton,
		CoreHtmlTagsEditor,
		CoreLoader,
		CoreTooltip,
		SvgPencil
	},
	mixins : [ TruSeoScore ],
	props  : {
		term  : Object,
		terms : Array,
		index : Number
	},
	data () {
		return {
			termId              : null,
			columnName          : null,
			title               : null,
			titleParsed         : null,
			termDescription     : null,
			descriptionParsed   : null,
			showEditTitle       : false,
			showEditDescription : false,
			showTruSeo          : false,
			termLoading         : false,
			strings             : merge(this.composableStrings, {
				title          : this.$t.__('Title', this.$td),
				description    : this.$t.__('Description', this.$td),
				saveChanges    : this.$t.__('Save Changes', this.$td),
				discardChanges : this.$t.__('Discard Changes', this.$td)
			})
		}
	},
	methods : {
		save () {
			this.showEditTitle       = false
			this.showEditDescription = false
			this.term.title          = this.title
			this.term.description    = this.termDescription
			this.termLoading         = true

			http.post(this.$links.restUrl('terms-list/update-details-column'))
				.send({
					termId      : this.term.id,
					title       : this.term.title,
					description : this.term.description
				})
				.then(response => {
					this.titleParsed       = response.body.title
					this.descriptionParsed = response.body.description

					this.term.titleParsed       = response.body.title
					this.term.descriptionParsed = response.body.description
				})
				.catch(error => {
					console.error(`Unable to update term with ID ${this.term.id}: ${error}`)
				})
				.finally(() => {
					this.termLoading = false
				})
		},
		cancel () {
			this.showEditTitle = false
			this.showEditDescription = false
		},
		editTitle () {
			this.showEditTitle = true
		},
		editDescription () {
			this.showEditDescription  = true
		},
		truncate
	},
	mounted () {
		this.termId            = this.term.id
		this.columnName        = this.term.columnName
		this.title             = this.term.title
		this.titleParsed       = this.term.titleParsed
		this.termDescription   = this.term.description
		this.descriptionParsed = this.term.descriptionParsed

		// If the term data changed, we need to parse the title and description again.
		// This can happen after using the quick-edit feature.
		if (this.term.reload) {
			this.save()
		}
	},
	async created () {
		this.showTruSeo = truSeoShouldAnalyze()
	}
}
</script>

<style lang="scss">
.aioseo-details-column {
	float: left;
	display: block;
	opacity: 1;
	overflow: hidden;
	width: 100%;

	&.editing {
		max-height: initial;
		overflow: visible;
	}

	.dashicons {
		cursor: pointer;
	}

	.aioseo-quickedit {
		margin-right: 5px;
		color: #72777c;

		&:hover {
			color: #0073aa;
			outline: 0;
		}
	}

	.aioseo-quickedit-input {
		float:left;
		position:relative;
		margin-bottom: 10px;
		font-size:13px;
		width:100%;
		z-index:1;
	}

	.aioseo-quickedit-input-save {
		margin-right: 5px;
		color: rgb(22, 204, 22);
	}

	.aioseo-quickedit-input-cancel {
		color: red;
	}

	.aioseo-quickedit:focus,
	.aioseo-quickedit-input-save:focus,
	.aioseo-quickedit-input-cancel:focus  {
		box-shadow: none;
	}

	.aioseo-quickedit-spinner {
		float:left;
		width:20px;
		margin-right:5px;
	}

	.edit-row {
		margin-bottom: 10px;
		&.edit-title,
		&.edit-description,
		&.edit-image-title,
		&.edit-image-alt {
			max-height: 70px;
			overflow: hidden;
		}
	}
}

table.wp-list-table {
	&.tags {
		.aioseo-html-tags-editor {
			.add-tags {
				flex-direction: column;
				align-items: start;
			}
		}
	}
}

td.seotitle.column-seotitle,
td.seodesc.column-seodesc,
td.seokeywords.column-seokeywords {
	overflow: visible;
}

@media screen and (max-width: 782px) {
	body.wp-admin {
		th.column-seotitle,
		th.column-seodesc,
		th.column-seokeywords,
		td.seotitle.column-seotitle,
		td.seodesc.column-seodesc,
		td.seokeywords.column-seokeywords {
			display: none;
		}
	}
}
</style>