export const SeoSiteScore = {
	computed : {
		connectWithAioseo () {
			return this.$t.sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				this.$t.__('Connect with %1$s', this.$td),
				import.meta.env.VITE_SHORT_NAME
			)
		},
		description () {
			return 25 >= this.score
				? this.strings.weveGotWorkToDo
				: (
					50 >= this.score
						? this.strings.needsImprovement
						: (
							75 >= this.score
								? this.strings.veryGood
								: this.strings.excellent
						)
				)
		}
	}
}