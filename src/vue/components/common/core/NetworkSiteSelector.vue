<template>
	<div class="aioseo-network-site-selector">
		<base-select
			size="medium"
			v-model="site"
			:options="sites"
		/>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import { addParam, getParams, removeParam } from '@/vue/utils/params'
import { Network } from '@/vue/mixins/Network'

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	emits  : [ 'selected-site' ],
	mixins : [ Network ],
	props  : {
		followSelectedSite : Boolean,
		showNetwork        : Boolean
	},
	data () {
		return {
			site    : null,
			network : {
				value : 'network',
				label : this.$t.__('Network Admin (no site)', this.$td)
			}
		}
	},
	watch : {
		site (newVal) {
			let site = this.rootStore.aioseo.data.network.sites.sites.find(s => this.getUniqueSiteId(s) === newVal.value)
			if ('network' === newVal.value) {
				site = {
					blog_id : 'network'
				}
			}
			this.$emit('selected-site', site)

			if (this.followSelectedSite) {
				this.querySelectedSite()
			}
		}
	},
	computed : {
		sites () {
			const sites = this.getSites
				.filter(s => !s.parentDomain)
				.map(s => {
					return {
						value : this.getUniqueSiteId(s),
						label : `${s.domain}${s.path}`
					}
				})

			return this.showNetwork
				? [ this.network ].concat(sites)
				: sites
		}
	},
	methods : {
		querySelectedSite () {
			removeParam('aioseo-selected-site-value')
			if ('network' !== this.site.value) {
				addParam('aioseo-selected-site-value', this.site.value)
			}
		}
	},
	created () {
		const params = getParams()
		if (params['aioseo-selected-site-value']) {
			this.site = this.sites.find(s => s.value === decodeURIComponent(params['aioseo-selected-site-value']))

			removeParam('aioseo-selected-site-value')

			return false
		}

		if (this.showNetwork) {
			this.site = this.network
		}
	}
}
</script>