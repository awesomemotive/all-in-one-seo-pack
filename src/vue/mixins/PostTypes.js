export default {
	methods : {
		viewPost (singular) {
			return this.$t.sprintf(
				// Translators: 1 - The singular label for the current post type.
				this.$t.__('View %1$s', this.$td),
				singular || this.$t.__('Post', this.$td)
			)
		},
		editPost (singular) {
			return this.$t.sprintf(
				// Translators: 1 - A noun for something that's being edited ("Post", "Page", "Article", "Product", etc.).
				this.$t.__('Edit %1$s', this.$td),
				singular || this.$t.__('Post', this.$td)
			)
		}
	}
}