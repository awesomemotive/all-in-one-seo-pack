import numbers from '@/vue/utils/numbers'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useStatistic = () => {
	const formatStatistic = (name, number) => {
		if ('ctr' === name) {
			return `${Math.abs(number)}%`
		}

		if ('position' === name) {
			return Math.abs(number).toFixed(0)
		}

		if ('decay' === name) {
			return sprintf(
				// Translators: 1 - The number of points.
				__('%1$s Points', td),
				numbers.numberFormat(number, 0)
			)
		}

		if ('decayPercent' === name) {
			return `${number}%`
		}

		number = Math.abs(number)
		return numbers.compactNumber(number)
	}

	return {
		formatStatistic
	}
}