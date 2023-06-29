import {
	useRootStore
} from '@/vue/stores'

const lang = () => {
	const rootStore = useRootStore()

	return rootStore.aioseo.user.locale.replace('_', '-') || 'en-US'
}

// Format tokens and functions.
const tokens = {
	// day of month, pad to 2 digits.
	d : d => pad(d.getDate()),
	// Day name, first 3 letters.
	D : d => getDayName(d).substr(0, 3),
	// Day of month, no padding.
	j : d => d.getDate(),
	// Full day name.
	l : d => getDayName(d),
	// ISO weekday number (1 = Monday ... 7 = Sunday).
	N : d => d.getDay() || 7,
	// Ordinal suffix for day of the month.
	S : d => getOrdinal(d.getDate()),
	// Weekday number (0 = Sunday, 6 = Saturday).
	w : d => d.getDay(),
	// Day of year, 1 Jan is 0.
	z : d => {
		const Y = d.getFullYear()
		const M = d.getMonth()
		const D = d.getDate()
		return Math.floor((Date.UTC(Y, M, D) - Date.UTC(Y, 0, 1)) / 8.64e7)
	},
	// ISO week number of year.
	W : d => getWeekNumber(d)[1],
	// Full month name.
	F : d => getMonthName(d),
	// Month number, padded.
	m : d => pad(d.getMonth() + 1),
	// 3 letter month name.
	M : d => getMonthName(d).substr(0, 3),
	// Month number, no pading.
	n : d => d.getMonth() + 1,
	// Days in month.
	t : d => new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate(),
	// Return 1 if d is a leap year, otherwise 0.
	L : d => 29 === new Date(d.getFullYear(), 1, 29).getDate() ? 1 : 0,
	// ISO week numbering year.
	o : d => getWeekNumber(d)[0],
	// 4 digit year.
	Y : d => {
		let year = d.getFullYear()
		if (0 > year) {
			year = '-' + ('000' + Math.abs(year)).slice(-4)
		}
		return year
	},
	// 2 digit year.
	y : d => {
		let year = d.getFullYear()
		if (0 <= year) {
			return ('0' + year).slice(-2)
		} else {
			year = Math.abs(year)
			return -+('0' + year).slice(-2)
		}
	},
	// Lowercase am or pm.
	a : d => 12 > d.getHours() ? 'am' : 'pm',
	// Uppercase AM or PM.
	A : d => 12 > d.getHours() ? 'AM' : 'PM',
	// Swatch internet time.
	B : d => (((+d + 3.6e6) % 8.64e7) / 8.64e4).toFixed(0),
	// 12 hour hour no padding.
	g : d => (d.getHours() % 12) || 12,
	// 24 hour hour no padding.
	G : d => d.getHours(),
	// 12 hour hour padded.
	h : d => pad((d.getHours() % 12) || 12),
	// 24 hour hour padded.
	H : d => pad(d.getHours()),
	// Minutes padded.
	i : d => pad(d.getMinutes()),
	// Seconds padded.
	s : d => pad(d.getSeconds()),
	// Microseconds padded - always returns 000000.
	u : () => '000000',
	// Milliseconds.
	v : d => padd(d.getMilliseconds()),
	// Timezone identifier: UTC, GMT or IANA Tz database identifier - Not supported.
	e : () => 0,
	// If in daylight saving: 1 yes, 0 no.
	I : d => d.getTimezoneOffset() === getOffsets(d)[0] ? 0 : 1,
	// Difference to GMT in hours, e.g. +0200.
	O : d => minsToHours(-d.getTimezoneOffset(), false),
	// Difference to GMT in hours with colon, e.g. +02:00.
	P : d => minsToHours(-d.getTimezoneOffset(), true),
	// Timezone abbreviation, e.g. AEST. Dodgy but may work….
	T : d => d.toLocaleString('en', { year: 'numeric', timeZoneName: 'long' }).replace(/[^A-Z]/g, ''),
	// Timezone offset in seconds, +ve east.
	Z : d => d.getTimezoneOffset() * -60,

	// ISO 8601 format - UTC.
	// c: d => d.getUTCFullYear() + '-' + pad(d.getUTCMonth() + 1) + '-' + pad(d.getUTCDate()) +
	//        'T' + pad(d.getUTCHours()) + ':' + pad(d.getUTCMinutes()) + ':' + pad(d.getUTCSeconds()) +
	//        '+00:00',

	// ISO 8601 format - local.
	c : d => format(d, 'Y-m-d\\TH:i:sP'),
	// RFC 2822 formatted date, local timezone.
	r : d => format(d, 'D, d M Y H:i:s O'),
	// Seconds since UNIX epoch (same as ECMAScript epoch).
	U : d => d.getTime() / 1000 | 0
}

// Helpers.
// Return day name for date.
const getDayName = d => d.toLocaleString(lang(), { weekday: 'long' })

// Return month name for date.
const getMonthName = d => d.toLocaleString(lang(), { month: 'long' })

// Return [std offest, DST offset]. If no DST, same offset for both.
const getOffsets = d => {
	const y = d.getFullYear()
	const offsets = [ 0, 2, 5, 9 ].map(m => new Date(y, m).getTimezoneOffset())
	return [ Math.max(...offsets), Math.min(...offsets) ]
}

// Return ordinal for positive integer.
const getOrdinal = n => {
	n = n % 100
	const ords = [ 'th', 'st', 'nd', 'rd' ]
	return (10 > n || 13 < n) ? ords[n % 10] || 'th' : 'th'
}

// Return ISO week number and year.
const getWeekNumber = d => {
	const e = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()))
	e.setUTCDate(e.getUTCDate() + 4 - (e.getUTCDay() || 7))
	const yearStart = new Date(Date.UTC(e.getUTCFullYear(), 0, 1))
	const weekNo = Math.ceil((((e - yearStart) / 86400000) + 1) / 7)
	return [ e.getUTCFullYear(), weekNo ]
}

// Return true if o is a Date, otherwise false.
const isDate = o => '[object Date]' === Object.prototype.toString.call(o)

// Convert numeric minutes to +/-HHMM or +/-HH:MM.
const minsToHours = (mins, colon) => {
	const sign = 0 > mins ? '-' : '+'
	mins = Math.abs(mins)
	const H = pad(mins / 60 | 0)
	const M = pad(mins % 60)
	return sign + H + (colon ? ':' : '') + M
}

// Pad single digits with a leading zero.
const pad = n => (10 > n ? '0' : '') + n

// Pad single digits with two leading zeros, double digits with one leading zero.
const padd = n => (10 > n ? '00' : 100 > n ? '0' : '') + n

// Format date using token string s.
const format = (date, s) => {
	// Minimal input validation.
	if (!isDate(date) || 'string' !== typeof s) {
		return
	}

	return s.split('').reduce((acc, c, i, chars) => {
		// Add quoted characters to output.
		if ('\\' === c) {
			acc += chars.splice(i + 1, 1)
			// If character matches a token, use it.
		} else if (c in tokens) {
			acc += tokens[c](date)
			// Otherwise, just add character to output.
		} else {
			acc += c
		}

		return acc
	}, '')
}

export default format