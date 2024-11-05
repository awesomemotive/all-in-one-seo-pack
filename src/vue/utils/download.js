export const downloadFile = (content, fileName) => {
	const blob = new Blob([ content ])
	const link = document.createElement('a')

	link.href = URL.createObjectURL(blob)
	link.download = fileName
	link.click()
	URL.revokeObjectURL(link.href)
}