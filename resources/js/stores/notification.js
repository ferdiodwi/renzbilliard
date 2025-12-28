import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationStore = defineStore('notification', () => {
    const notifications = ref([])
    let nextId = 1

    const add = (message, type = 'success', duration = 3000) => {
        const id = nextId++
        notifications.value.push({
            id,
            message,
            type,
        })

        if (duration > 0) {
            setTimeout(() => {
                remove(id)
            }, duration)
        }
    }

    const remove = (id) => {
        const index = notifications.value.findIndex(n => n.id === id)
        if (index !== -1) {
            notifications.value.splice(index, 1)
        }
    }

    const success = (message, duration) => add(message, 'success', duration)
    const error = (message, duration) => add(message, 'error', duration)
    const info = (message, duration) => add(message, 'info', duration)
    const warning = (message, duration) => add(message, 'warning', duration)

    return {
        notifications,
        add,
        remove,
        success,
        error,
        info,
        warning
    }
})
