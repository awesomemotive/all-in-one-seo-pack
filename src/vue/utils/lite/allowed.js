import {
	useRootStore
} from '@/vue/stores'

export const allowed = function (permission) {
	const rootStore = useRootStore()
	const user      = rootStore.aioseo.user
	return !!user.canManage || !!(user.capabilities && user.capabilities[permission])
}