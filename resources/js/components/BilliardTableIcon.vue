<template>
    <svg
        :width="size"
        :height="size * 0.6"
        viewBox="0 0 100 60"
        xmlns="http://www.w3.org/2000/svg"
        class="billiard-table-svg"
    >
        <!-- Table Frame (outer border) -->
        <rect x="2" y="2" width="96" height="56" rx="4" fill="#4A4A5A" stroke="#3A3A48" stroke-width="2"/>
        
        <!-- Table Rail (inner border) -->
        <rect x="8" y="8" width="84" height="44" rx="2" fill="#2D5A3D" stroke="#1E4030" stroke-width="2"/>
        
        <!-- Playing Surface -->
        <rect x="12" y="12" width="76" height="36" rx="1" fill="#3A7650"/>
        
        <!-- Pockets -->
        <circle cx="10" cy="10" r="4" fill="#1A1A24"/>
        <circle cx="90" cy="10" r="4" fill="#1A1A24"/>
        <circle cx="10" cy="50" r="4" fill="#1A1A24"/>
        <circle cx="90" cy="50" r="4" fill="#1A1A24"/>
        <circle cx="50" cy="10" r="3" fill="#1A1A24"/>
        <circle cx="50" cy="50" r="3" fill="#1A1A24"/>
        
        <!-- Balls (triangle rack) - muted colors -->
        <g v-if="showBalls">
            <circle cx="70" cy="30" r="3" fill="#D4A853"/>
            <circle cx="75" cy="27" r="3" fill="#5B8CC4"/>
            <circle cx="75" cy="33" r="3" fill="#C45B5B"/>
            <circle cx="80" cy="24" r="3" fill="#8B5BB0"/>
            <circle cx="80" cy="30" r="3" fill="#D48B53"/>
            <circle cx="80" cy="36" r="3" fill="#5BB0A0"/>
            
            <!-- Cue ball -->
            <circle cx="30" cy="30" r="3.5" fill="#E8E8E8" stroke="#D0D0D0" stroke-width="0.5"/>
        </g>
        
        <!-- Status Indicator - more subtle -->
        <g v-if="status === 'playing'">
            <circle cx="50" cy="30" r="10" fill="rgba(99, 102, 241, 0.2)" stroke="rgba(99, 102, 241, 0.6)" stroke-width="1.5"/>
            <text x="50" y="33" text-anchor="middle" fill="rgba(99, 102, 241, 0.9)" font-size="9" font-weight="600">▶</text>
        </g>
        
        <g v-if="status === 'maintenance'">
            <circle cx="50" cy="30" r="10" fill="rgba(100, 116, 139, 0.2)" stroke="rgba(100, 116, 139, 0.5)" stroke-width="1.5"/>
            <text x="50" y="33" text-anchor="middle" fill="rgba(100, 116, 139, 0.8)" font-size="9" font-weight="600">⚙</text>
        </g>
    </svg>
</template>

<script setup>
defineProps({
    size: {
        type: Number,
        default: 120,
    },
    status: {
        type: String,
        default: 'available',
    },
    showBalls: {
        type: Boolean,
        default: true,
    },
});
</script>

<style scoped>
.billiard-table-svg {
    display: block;
}
</style>
