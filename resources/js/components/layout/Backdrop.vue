<template>
  <!-- Backdrop overlay for mobile sidebar -->
  <div
    v-if="isMobileOpen"
    @click="toggleMobileSidebar"
    class="fixed inset-0 bg-black/50 transition-opacity duration-300 z-30"
  ></div>
</template>

<script setup>
import { useSidebar } from '@/composables/useSidebar'

const { isMobileOpen, toggleMobileSidebar } = useSidebar()
</script>
