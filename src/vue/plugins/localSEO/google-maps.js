import links from '@/vue/utils/links'

export async function isApiKeyValid (apiKey) {
	return new Promise((resolve) => {
		const elem = document.createElement('div')
		elem.setAttribute('id', 'aioseoLocalSeoMapValidator')
		elem.setAttribute('style', 'display: none !important;')

		window.gm_authFailure = function () { // eslint-disable-line
			elem.remove()
			resolve(false)
		}

		new GoogleMapsLoader({
			apiKey  : apiKey,
			version : 'weekly'
		}).load().then((google) => {
			// Create a div for the map.
			document.body.appendChild(elem)

			// Trigger the map load.
			new google.maps.Map(elem, {
				center : { lat: 0, lng: 0 },
				zoom   : 16
			})

			// Wait for gm_authFailure not to trigger.
			setTimeout(function () {
				elem.remove()
				resolve(true)
			}, 10000)
		})
	})
}

export function isGoogleApiEnabled (apiKey, apiName, http) {
	if (!apiKey || !apiName) {
		return Promise.resolve(false)
	}

	switch (apiName) {
		case 'places/embed':
			return http.post(links.restUrl('local-business/maps/check-api-enabled'))
				.send({
					apiKey  : apiKey,
					apiName : apiName
				})
				.then(response => {
					if (200 === response.statusCode) {
						return true
					}
					return false
				}).catch(err => { // eslint-disable-line
					return false
				})
		default:
			return Promise.resolve(false)
	}
}

export async function isGoogleLibraryEnabled (google, library, query) {
	if (!google?.maps) {
		return false
	}

	let service = null

	switch (library) {
		case 'places':
			if (!google.maps.places) {
				return false
			}

			service = new google.maps.places.AutocompleteService()
			return new Promise((resolve) => {
				service.getQueryPredictions({ input: query || 'NYC' }, function (predictions, status) {
					resolve(status === google.maps.places.PlacesServiceStatus.OK)
				})
			})
		case 'geocoder':
			service = new google.maps.Geocoder()
			return new Promise((resolve) => {
				service.geocode({ address: query || 'NYC' }, function (results, status) {
					if (status === google.maps.GeocoderStatus.OK) {
						resolve({ enabled: true, result: results[0].geometry.location })
					}
					resolve({ enabled: false })
				})
			})
		default:
			return false
	}
}

export class GoogleMapsLoader {
	url = 'https://maps.googleapis.com/maps/api/js'
	scriptId = 'localseo-maps-script'
	loading = false
	googleMapCallback = 'aioseoGoogleMapsLoaded'
	callbacks = []

	constructor ({ apiKey, version, libraries }) {
		this.apiKey = apiKey
		this.version = version || 'weekly'
		this.libraries = libraries || []
	}

	createUrl () {
		let url = this.url

		url += `?callback=${this.googleMapCallback}`

		if (this.apiKey) {
			url += `&key=${this.apiKey}`
		}

		if (0 < this.libraries.length) {
			url += `&libraries=${this.libraries.join(',')}`
		}

		if (0 < this.libraries.length) {
			url += `&libraries=${this.libraries.join(',')}`
		}

		if (this.version) {
			url += `&v=${this.version}`
		}

		return url
	}

	async load () {
		return new Promise((resolve, reject) => {
			this.loadCallback((err) => {
				if (!err) {
					resolve(window.google)
				} else {
					reject(err)
				}
			})
		})
	}

	loadCallback (fn) {
		this.callbacks.push(fn)
		this.execute()
	}

	setCallback () {
		window.aioseoGoogleMapsLoaded = this.callback.bind(this)
	}

	callback () {
		this.loading = false

		this.callbacks.forEach((cb) => {
			cb(this.onerrorEvent)
		})

		this.callbacks = []
	}

	setScript () {
		this.reset()

		const url = this.createUrl()
		const script = document.createElement('script')
		script.id = this.scriptId
		script.type = 'text/javascript'
		script.src = url
		script.onerror = this.loadErrorCallback.bind(this)
		script.defer = true
		script.async = true

		if (this.nonce) {
			script.nonce = this.nonce
		}

		document.head.appendChild(script)
	}

	loadErrorCallback (e) {
		this.onerrorEvent = e
		this.callback()
	}

	reset () {
		this.deleteScript()
		if (window.google && window.google.maps) {
			window.google.maps = null
		}
	}

	deleteScript () {
		const script = document.getElementById(this.scriptId)
		if (script) {
			script.remove()
		}
	}

	execute () {
		this.setCallback()
		this.setScript()
	}
}