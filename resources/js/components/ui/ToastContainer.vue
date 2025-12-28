<template>
  <div class="fixed top-16 right-4 z-[100] flex flex-col gap-2 pointer-events-none w-full max-w-sm">
    <TransitionGroup
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform translate-x-full opacity-0"
      enter-to-class="transform translate-x-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-x-0 opacity-100"
      leave-to-class="transform translate-x-full opacity-0"
      move-class="transition duration-300 ease-in-out"
    >
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="pointer-events-auto flex items-start gap-3 p-4 rounded-xl shadow-lg border backdrop-blur-sm"
        :class="{
          'bg-white/95 dark:bg-gray-800/95 border-success-200 dark:border-success-800 text-gray-800 dark:text-white': notification.type === 'success',
          'bg-white/95 dark:bg-gray-800/95 border-red-200 dark:border-red-800 text-gray-800 dark:text-white': notification.type === 'error',
          'bg-white/95 dark:bg-gray-800/95 border-blue-200 dark:border-blue-800 text-gray-800 dark:text-white': notification.type === 'info',
          'bg-white/95 dark:bg-gray-800/95 border-warning-200 dark:border-warning-800 text-gray-800 dark:text-white': notification.type === 'warning'
        }"
      >
        <!-- Icons -->
        <div class="shrink-0 mt-0.5">
          <svg v-if="notification.type === 'success'" class="w-5 h-5 text-success-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else-if="notification.type === 'error'" class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
           <svg v-else-if="notification.type === 'warning'" class="w-5 h-5 text-warning-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <svg v-else class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>

        <div class="flex-1 text-sm font-medium">
          {{ notification.message }}
        </div>

        <button @click="remove(notification.id)" class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
           <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useNotificationStore } from '@/stores/notification'
import { storeToRefs } from 'pinia'

const store = useNotificationStore()
const { notifications } = storeToRefs(store)
const { remove } = store
</script>
