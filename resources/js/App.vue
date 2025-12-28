<template>
  <ThemeProvider>
    <SidebarProvider>
      <RouterView />
      <ToastContainer />
      <ConfirmDialog />
    </SidebarProvider>
  </ThemeProvider>
</template>

<script setup>
import ThemeProvider from '@/components/layout/ThemeProvider.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import ToastContainer from '@/components/ui/ToastContainer.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
</script>
