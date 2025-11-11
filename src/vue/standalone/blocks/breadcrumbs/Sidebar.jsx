import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const wp = window.wp
const {
	PanelBody
} = wp.components

export const Sidebar = ({ clientId }) => {
	const sidebarSettingsId = `aioseo-${clientId}-settings`

	const openBreadcrumbConfig = () => {
		window.aioseoBus.$emit('do-post-settings-main-tab-change', { name: 'advanced', context: 'metabox' })
		wp.data.dispatch('core/edit-post').openGeneralSidebar('aioseo-post-settings-sidebar/aioseo-post-settings-sidebar')
		setTimeout(() => {
			window.aioseoBus.$emit('do-post-settings-main-tab-change', { name: 'advanced', context: 'sidebar' })
		}, 100)
	}

	return (
		<PanelBody
			title={__('Breadcrumb Settings', td)}
			initialOpen={true}
		>
			<div>
				<div id={sidebarSettingsId}>
					<p className="aioseo-breadcrumbs-sidebar-text">
						{__('You can customize your breadcrumb trail under ', td)}
						<a
							href="#"
							onClick={() => openBreadcrumbConfig()}
						>
							{__('Advanced > Breadcrumbs.', td)}
						</a>
					</p>
				</div>
			</div>
		</PanelBody>
	)
}