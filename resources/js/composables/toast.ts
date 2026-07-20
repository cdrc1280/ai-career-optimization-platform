// Singleton toast store — importable anywhere without Pinia or provide/inject
import { reactive } from 'vue'

interface Toast {
    id: number
    type: 'success' | 'error' | 'info' | 'warning'
    title?: string
    message: string
}

let _id = 0
export const toastState = reactive<{ items: Toast[] }>({ items: [] })

export function addToast(
    type: Toast['type'],
    message: string,
    title?: string,
    duration = 4500,
) {
    const id = ++_id
    toastState.items.push({ id, type, message, title })
    setTimeout(() => {
        toastState.items = toastState.items.filter(t => t.id !== id)
    }, duration)
}

export function removeToast(id: number) {
    toastState.items = toastState.items.filter(t => t.id !== id)
}
