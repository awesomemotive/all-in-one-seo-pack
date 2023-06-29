<template>
	<div class="content">
		<template
			v-for="archive in archives"
			:key="archive.slug"
		>
			<component
				:is="archive.slug"
				:active="archive.slug"
			/>
		</template>
	</div>
</template>

<script>
import DateArchives from './DateArchives'
import PostTypeArchives from './PostTypeArchives'
import SearchArchives from './SearchArchives'
import NotFound from './NotFound'
import AuthorArchives from './AuthorArchives'
import BlogArchives from './BlogArchives'

export default {
	components : {
		DateArchives,
		PostTypeArchives,
		SearchArchives,
		NotFound,
		AuthorArchives,
		BlogArchives
	},
	data () {
		return {
			archives :
				[
					{
						slug   : 'DateArchives',
						name   : this.$t.__('Date Archives', this.$td),
						access : 'aioseo_general_settings',
						pro    : true
					},
					{
						slug   : 'SearchArchives',
						name   : this.$t.__('Search Archives', this.$td),
						access : 'aioseo_general_settings',
						pro    : true
					},
					{
						slug   : 'NotFound',
						name   : this.$t.__('404', this.$td),
						access : 'aioseo_general_settings',
						pro    : true
					},
					{
						slug   : 'AuthorArchives',
						name   : this.$t.__('Author Archives', this.$td),
						access : 'aioseo_general_settings',
						pro    : true
					},
					{
						slug   : 'BlogArchives',
						name   : this.$t.__('Blog Archive', this.$td),
						access : 'aioseo_general_settings',
						pro    : true
					},
					{
						slug   : 'PostTypeArchives',
						name   : this.$t.__('Post Type Archives', this.$td),
						access : 'aioseo_general_settings',
						pro    : true
					}
				]
		}
	}
}
</script>

<style lang="scss">
.content[active="Archives"]:has(> :last-child:empty) > :nth-last-child(2) {
	margin-bottom: 0;
	border-bottom: none;
	padding-bottom: 0;
}
</style>