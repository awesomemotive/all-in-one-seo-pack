export const MaxCounts = {
	methods : {
		maxRecommendedCount (current, max) {
			const error = current > max ? ' class="error"' : ''
			return this.$t.sprintf(
				// Translators: 1 - A number, 2 - A number.
				this.$t.__('%1$s out of %2$s max recommended characters.', this.$td),
				`<strong${error}>${current}</strong>`,
				`<strong>${max}</strong>`
			)
		},
		updateCount (count, option) {
			this[option] = count.length
		}
	}
}