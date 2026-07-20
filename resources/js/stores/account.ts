import { defineStore } from 'pinia'
import { ref } from 'vue'
import { getAccount, updateAccount, changePassword, uploadAvatar } from '../services/api'
import { addToast } from '../composables/toast'

export const useAccountStore = defineStore('account', () => {
    const account = ref<any>(null)
    const loading = ref(false)
    const saving = ref(false)

    async function fetch() {
        loading.value = true
        try {
            const res = await getAccount()
            account.value = res.data
        } catch { }
        finally { loading.value = false }
    }

    async function update(data: any) {
        saving.value = true
        try {
            const res = await updateAccount(data)
            account.value = { ...account.value, ...res.data }
            addToast('success', 'Account updated successfully')
        } catch (e: any) {
            addToast('error', e?.response?.data?.message ?? 'Failed to update account')
            throw e
        } finally { saving.value = false }
    }

    async function updatePassword(data: any) {
        saving.value = true
        try {
            await changePassword(data)
            addToast('success', 'Password changed successfully')
        } catch (e: any) {
            addToast('error', e?.response?.data?.message ?? 'Failed to change password')
            throw e
        } finally { saving.value = false }
    }

    async function uploadAvatarFile(file: File) {
        const fd = new FormData()
        fd.append('avatar', file)
        try {
            const res = await uploadAvatar(fd)
            if (account.value?.profile) account.value.profile.avatar_url = res.data.avatar_url
            addToast('success', 'Avatar uploaded')
        } catch {
            addToast('error', 'Failed to upload avatar')
        }
    }

    return { account, loading, saving, fetch, update, updatePassword, uploadAvatarFile }
})
