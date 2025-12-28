import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useConfirmStore = defineStore('confirm', () => {
    const isVisible = ref(false)
    const options = ref({
        title: 'Konfirmasi',
        message: 'Apakah Anda yakin?',
        confirmText: 'Ya, Lanjutkan',
        cancelText: 'Batal',
        type: 'warning' // warning, danger, info
    })

    let resolvePromise = null
    let rejectPromise = null

    const show = (opts = {}) => {
        options.value = { ...options.value, ...opts }
        isVisible.value = true

        return new Promise((resolve, reject) => {
            resolvePromise = resolve
            rejectPromise = reject
        })
    }

    const confirm = () => {
        isVisible.value = false
        if (resolvePromise) resolvePromise(true)
    }

    const cancel = () => {
        isVisible.value = false
        if (resolvePromise) resolvePromise(false)
    }

    return {
        isVisible,
        options,
        show,
        confirm,
        cancel
    }
})
