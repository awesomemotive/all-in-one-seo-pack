/**
 * Gets the ACF field value.
 *
 * @param 	{string} 	  fieldKey The ACF field key.
 * @returns {Object|null} 		   The field value if found, null otherwise.
 */
export const getFieldValue = (fieldKey) => {
	if ('object' !== typeof window?.acf || 'function' !== typeof window.acf?.getField) {
		return null
	}

	const field = window.acf.getField(fieldKey) || {}
	if ('function' !== typeof field?.val) {
		return null
	}

	return field.val() || {}
}