import initFeaturedImageWatcher from './featuredImage'
import initMetaBoxSaveWatcher from './metaBoxSave'
import initSeoRevisionsWatcher from './seoRevisions'

export default () => {
	initFeaturedImageWatcher()
	initMetaBoxSaveWatcher()
	initSeoRevisionsWatcher()
}