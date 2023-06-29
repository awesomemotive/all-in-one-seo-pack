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

import { Network } from '@/vue/mixins'
export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	emits  : [ 'selected-site' ],
	mixins : [ Network ],
	props  : {
		showNetwork : Boolean
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
	created () {
		if (this.showNetwork) {
			this.site = this.network
		}
	}
}
</script>