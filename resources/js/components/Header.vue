<template>
  <header class="sticky top-0 z-999 flex w-full bg-white drop-shadow-1 dark:bg-boxdark dark:drop-shadow-none">
    <div class="flex flex-grow items-center justify-between px-4 py-4 shadow-2 md:px-6 2xl:px-11">
      <div class="flex items-center gap-2 sm:gap-4 lg:hidden">
        <!-- Hamburger Toggle -->
        <button 
          class="z-99999 block rounded-sm border border-stroke bg-white p-1.5 shadow-sm dark:border-strokedark dark:bg-boxdark lg:hidden"
          @click.stop="$emit('toggle-sidebar')"
        >
          <span class="relative block h-5.5 w-5.5 cursor-pointer">
             <!-- Simple Menu Icon -->
             <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
             </svg>
          </span>
        </button>
      </div>

      <div class="hidden sm:block">
        <!-- Search placeholder -->
      </div>

      <div class="flex items-center gap-3 2xsm:gap-7">
        <!-- User Area -->
        <div class="relative flex items-center gap-2">
            <span class="hidden text-right lg:block">
                <span class="block text-sm font-medium text-black dark:text-white">{{ user?.name }}</span>
                <span class="block text-xs">{{ user?.role }}</span>
            </span>
             <!-- Logout Button -->
             <button @click="authStore.logout(); $router.push('/login')" class="text-sm text-red-500 hover:text-red-700">Logout</button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import { storeToRefs } from 'pinia';
import { useRouter } from 'vue-router';

const props = defineProps(['sidebarOpen']);
const emit = defineEmits(['toggle-sidebar']);

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
const router = useRouter(); 
</script>
