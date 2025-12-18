import PageBuilderIntegration from '../PageBuilderIntegration'
import initWatcher from './watcher'
import initSeoButtonHandler from './seo-button-handler'

import Button from './components/Button'
import LimitModifiedDate from './components/LimitModifiedDate'
import Modal from './components/Modal'

const init = async () => {
	const slug = 'bricks'
	const integration = new PageBuilderIntegration({
		scoreBtn : {
			component : Button,
			appName   : `Standalone/${slug}/Button`,
			node      : {
				$wrapper   : document.querySelector('#bricks-toolbar *:first-child'),
				tag        : 'li',
				attributes : {
					id    : `aioseo-button-${slug}`,
					style : 'align-self: center; margin-left: 10px; cursor: pointer; width: auto',
					role  : 'button',
					class : 'aioseo-app'
				}
			}
		},
		limitModifiedDate : {
			component : LimitModifiedDate,
			appName   : `Standalone/${slug}/LimitModifiedDate`,
			node      : {
				$wrapper   : document.querySelector('#bricks-toolbar li.save')?.parentElement,
				tag        : 'li',
				attributes : {
					id    : `aioseo-limit-modified-date-wrapper-${slug}`,
					style : 'position: relative;'
				}
			}
		},
		metabox : {
			component : Modal,
			appName   : `Standalone/${slug}/Modal`
		}
	})

	await integration.mount()

	initWatcher(window)

	initSeoButtonHandler([
		{
			selector : 'li[data-control-group=\'seo\'] .control-group-title',
			tab      : 'general'
		},
		{
			selector : 'li[data-control-group=\'social-media\'] .control-group-title',
			tab      : 'social'
		}
	], document.getElementById('bricks-panel'))
}

document.addEventListener('DOMContentLoaded', () => {
	// The init function will run in a fresh call stack.
	setTimeout(init)
})