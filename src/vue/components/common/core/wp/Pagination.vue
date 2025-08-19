<template>
	<div class="tablenav-pages pagination">
		<span class="displaying-num">{{ numbers.numberFormat(totals.total) }} {{ strings.items }}</span>
		<span
			v-if="showLinks"
			class="pagination-links"
		>
			<component
				href="#"
				:is="pageNumber === 1 ? 'span' : 'a'"
				:class="pageNumber === 1 ? 'tablenav-pages-navspan button disabled' : 'first-page button'"
				@click.prevent="pageNumber === 1 ? null : toPage(1)"
			>
				&laquo;
			</component>

			<component
				href="#"
				:is="pageNumber === 1 ? 'span' : 'a'"
				:class="pageNumber === 1 ? 'tablenav-pages-navspan button disabled' : 'prev-page button'"
				@click.prevent="pageNumber === 1 ? null : toPage(pageNumber - 1)"
			>
				&lsaquo;
			</component>

			<span class="paging-input">
				<input
					class="current-page"
					type="number"
					name="paged"
					v-model="pageNumber"
					size="2"
					:min="1"
					:max="totals.pages || 1"
					:step="1"
					aria-describedby="table-paging"
					@keyup.enter="toPage(pageNumber)"
					:disabled="!totals.pages || disableTable"
				/>
				<span class="tablenav-paging-text">
					{{ strings.of }} {{ totals.pages || 0 }}
				</span>
			</span>
			<component
				href="#"
				:is="pageNumber === totals.pages || !totals.pages ? 'span' : 'a'"
				:class="pageNumber === totals.pages || !totals.pages ? 'tablenav-pages-navspan button disabled' : 'next-page button'"
				@click.prevent="pageNumber === totals.pages || !totals.pages ? null : toPage(pageNumber + 1)"
			>
				&rsaquo;
			</component>
			<component
				href="#"
				:is="pageNumber === totals.pages || !totals.pages ? 'span' : 'a'"
				:class="pageNumber === totals.pages || !totals.pages ? 'tablenav-pages-navspan button disabled' : 'last-page button'"
				@click.prevent="pageNumber === totals.pages || !totals.pages ? null : toPage(totals.pages)"
			>
				&raquo;
			</component>
		</span>
	</div>
</template>

<script>
import numbers from '@/vue/utils/numbers'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'paginate' ],
	props : {
		totals : {
			type     : Object,
			required : true
		},
		initialPageNumber : {
			type : Number,
			default () {
				return 1
			}
		},
		disableTable : Boolean,
		showLinks    : {
			type    : Boolean,
			default : true
		}
	},
	data () {
		return {
			numbers,
			pageNumber : 1,
			strings    : {
				of    : __('of', td),
				items : __('items', td)
			}
		}
	},
	watch : {
		initialPageNumber (newNumber) {
			if (newNumber !== this.pageNumber) {
				this.pageNumber = newNumber
			}
		}
	},
	methods : {
		toPage (page) {
			this.pageNumber = page
			this.$emit('paginate', parseInt(page))
		}
	},
	created () {
		this.pageNumber = this.initialPageNumber
	}
}
</script>