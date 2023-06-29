<template>
	<div
		class="aioseo-details-column"
		:class="{
			editing: showEditTitle || showEditDescription
		}"
	>
		<div>
			<div class="edit-row edit-title">
				<a
					class="dashicons dashicons-edit aioseo-quickedit"
					:title="strings.edit"
					@click.prevent="editTitle"
				/>

				<strong>{{ strings.title }} </strong>

				<span :id="`aioseo-${columnName}-${termId}-value`">
					{{ truncate(titleParsed, 100) }}
				</span>
			</div>
			<div
				v-if="showEditTitle"
				class="edit-row"
			>
				<textarea
					v-model="title"
					class="aioseo-quickedit-input"
					rows="4"
					columns="32"
				/>
				<a
					class="dashicons aioseo-quickedit-input-save"
					@click.prevent="save"
					:title="strings.save"
				>
					<svg-circle-check width="20" />
				</a>
				<a
					class="dashicons aioseo-quickedit-input-cancel"
					@click.prevent="cancel"
					:title="strings.cancel"
				>
					<svg-circle-close width="20" />
				</a>
			</div>

			<div class="edit-row edit-description">
				<a
					class="dashicons dashicons-edit aioseo-quickedit"
					:title="strings.edit"
					@click.prevent="editDescription"
				/>

				<strong>{{ strings.description }}</strong>

				<span :id="`aioseo-${columnName}-${termId}-value`">
					{{ truncate(descriptionParsed) }}
				</span>
			</div>
			<div
				v-if="showEditDescription"
				class="edit-row"
			>
				<textarea
					v-model="termDescription"
					class="aioseo-quickedit-input"
					rows="4"
					columns="32"
				/>
				<a
					class="dashicons aioseo-quickedit-input-save"
					@click.prevent="save"
					:title="strings.save"
				>
					<svg-circle-check width="20" />
				</a>
				<a
					class="dashicons aioseo-quickedit-input-cancel"
					@click.prevent="cancel"
					:title="strings.cancel"
				>
					<svg-circle-close width="20" />
				</a>
			</div>
		</div>
	</div>
</template>

<script>
import http from '@/vue/utils/http'
import { merge } from 'lodash-es'
import { useTruSeoScore } from '@/vue/composables'
import { TruSeoScore } from '@/vue/mixins'
import { truncate } from '@/vue/utils/html'
import { truSeoShouldAnalyze } from '@/vue/plugins/tru-seo/components/helpers'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'

export default {
	setup () {
		const { strings } = useTruSeoScore()

		return {
			composableStrings : strings
		}
	},
	components : {
		SvgCircleCheck,
		SvgCircleClose
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
			strings             : merge(this.composableStrings, {
				title       : this.$t.__('Title:', this.$td),
				description : this.$t.__('Description:', this.$td),
				edit        : this.$t.__('Edit', this.$td),
				save        : this.$t.__('Save', this.$td),
				cancel      : this.$t.__('Cancel', this.$td),
				wait        : this.$t.__('Please wait...', this.$td)
			})
		}
	},
	methods : {
		save () {
			this.showEditTitle       = false
			this.showEditDescription = false
			this.term.title          = this.title
			this.term.description    = this.termDescription

			http.post(this.$links.restUrl('termscreen'))
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
	.column-name {
		width: auto !important;
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