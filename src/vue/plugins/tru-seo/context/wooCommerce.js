import {
	useLicenseStore,
	useRootStore,
	useTagsStore
} from '@/vue/stores'

export const watchWooCommerce = () => {
	const licenseStore = useLicenseStore()
	if (licenseStore.isUnlicensed) {
		return
	}
	let productSku   = '',
		productPrice = '',
		productBrand = ''

	const tagsStore = useTagsStore()

	window.addEventListener('change', (event) => {
		if ('INPUT' !== event.target.tagName) {
			return
		}

		const sku = document.getElementById('_sku')
		if (sku) {
			productSku = sku.value
		}
		tagsStore.updateWooCommerceSku(productSku)

		const salePrice = document.getElementById('_sale_price')
		const price     = document.getElementById('_regular_price')

		if (salePrice) {
			productPrice = salePrice.value
		}
		if (!productPrice && price) {
			productPrice = price.value
		}

		const rootStore = useRootStore()
		const parsedProductPrice = rootStore.aioseo.data.wooCommerce.currencySymbol + parseFloat(productPrice || 0).toFixed(2)
		tagsStore.updateWooCommercePrice(parsedProductPrice)

		let brands = document.querySelectorAll('#post input[name="tax_input[product_brand][]"]:checked')
		if (!brands.length) {
			brands = document.querySelectorAll('#post input[name="tax_input[pwb-brand][]"]:checked')
		}
		if (brands.length) {
			if (productBrand !== brands[0].parentNode.innerText) {
				productBrand = brands[0].parentNode.innerText
				tagsStore.updateWooCommerceBrand(brands[0].parentNode.innerText)
			}
		} else {
			if ('' !== productBrand) {
				productBrand = ''
				tagsStore.updateWooCommerceBrand('')
			}
		}
	})
}