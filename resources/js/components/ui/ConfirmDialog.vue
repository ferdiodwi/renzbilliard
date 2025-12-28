<template>
  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="isVisible" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="handleCancel">
      <div 
        class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden transform transition-all scale-100"
      >
        <div class="p-6">
          <div class="flex items-center gap-4 mb-4">
             <div 
              class="w-12 h-12 rounded-full flex items-center justify-center shrink-0"
              :class="{
                'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400': options.type === 'danger',
                'bg-warning-100 text-warning-600 dark:bg-warning-500/20 dark:text-warning-400': options.type === 'warning',
                'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400': options.type === 'info'
              }"
             >
                <svg v-if="options.type === 'danger'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <svg v-else-if="options.type === 'warning'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
             </div>
             <div>
               <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ options.title }}</h3>
               <p class="text-sm text-gray-500 dark:text-gray-400">{{ options.message }}</p>
             </div>
          </div>
          
          <div class="flex gap-3 justify-end mt-6">
            <button 
              @click="handleCancel"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"
            >
              {{ options.cancelText }}
            </button>
            <button 
              @click="handleConfirm"
              class="px-4 py-2 text-sm font-medium text-white rounded-lg transition shadow-sm"
              :class="{
                'bg-red-500 hover:bg-red-600': options.type === 'danger',
                'bg-warning-500 hover:bg-warning-600 text-white': options.type === 'warning',
                'bg-brand-500 hover:bg-brand-600': options.type === 'info'
              }"
            >
              {{ options.confirmText }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { useConfirmStore } from '@/stores/confirm'
import { storeToRefs } from 'pinia'

const store = useConfirmStore()
const { isVisible, options } = storeToRefs(store)
const { confirm, cancel } = store

const handleConfirm = () => {
    confirm()
}

const handleCancel = () => {
    cancel()
}
</script>
