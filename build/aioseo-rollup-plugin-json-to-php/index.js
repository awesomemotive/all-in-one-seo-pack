import fs from 'fs'

export default function jsonToPhp (files = []) {
	const log = msg => console.log('\x1b[36m%s\x1b[0m', msg)

	return {
		name : 'aioseo-rollup-plugin-json-to-php',
		writeBundle () {
			files.forEach(file => {
				if (fs.existsSync(file.to)) {
					return
				}

				log(`Convert JSON to PHP: ${file.from} → ${file.to}`)

				let phpContents = '<?php\n/* THIS IS A GENERATED FILE. DO NOT EDIT DIRECTLY. */\n$manifestJson = \''

				// Add the PHP data.
				phpContents += fs.readFileSync(file.from)
				phpContents += '\';'

				// For Windows users we have to replace backslashes with forward slashes.
				// Otherwise the manifest JSON isn't valid.
				phpContents = phpContents.replace(/\\\\/g, '/')

				// First rename the file.
				fs.rename(file.from, file.to, () => {
					// Then overwrite it.
					log(`• Write PHP to file ${file.to}`)
					fs.writeFileSync(file.to, phpContents)

					log(`• Generated PHP file ${file.to}`)
				})
			})
		}
	}
}