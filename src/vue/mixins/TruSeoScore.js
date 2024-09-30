export const TruSeoScore = {
	computed : {
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
	},
	methods : {
		getErrorDisplay (amountOfErrors) {
			if (0 < amountOfErrors) {
				return this.$t.sprintf(
					// Translators: 1 - The amount of errors.
					this.$t._n('%1$s Error', '%1$s Errors', amountOfErrors, this.$td),
					amountOfErrors
				)
			}
			return this.strings.allGood
		},
		getErrorClass (errors) {
			if (5 < errors) {
				return 'red'
			}
			if (0 < errors) {
				return 'orange'
			}
			return 'green'
		},
		getScoreClass (score) {
			return 80 < score ? 'green' : 50 < score ? 'orange' : 1 < score ? 'red' : 'none'
		},
		runAnalysis (postId) {
			this.$truSeo.runAnalysis(postId)
		}
	}
}