import { ref, computed, onMounted, onUnmounted } from 'vue'

// Session alert manager for client-side calculation
export function useSessionAlerts() {
    const sessions = ref([])
    const alertedSessions = ref(new Set())
    const acknowledgedSessions = ref([])
    const checkInterval = ref(null)

    // Load acknowledged sessions from localStorage
    const loadAcknowledgedSessions = () => {
        const saved = localStorage.getItem('acknowledgedExpiredSessions')
        if (saved) {
            acknowledgedSessions.value = JSON.parse(saved)
        }
    }

    // Calculate remaining minutes for a session
    const calculateRemainingMinutes = (session) => {
        if (!session.end_time) return null

        const now = new Date()
        const endTime = new Date(session.end_time)
        const diffMs = endTime - now
        const diffMinutes = Math.floor(diffMs / (1000 * 60))

        return diffMinutes
    }

    // Check if session needs alert
    const checkSessionAlert = (session) => {
        const remaining = calculateRemainingMinutes(session)

        if (remaining === null) return null

        // Skip if already acknowledged
        if (remaining < 0 && acknowledgedSessions.value.includes(session.id)) {
            return null
        }

        const isExpired = remaining < 0
        const isExpiring = remaining >= 0 && remaining < 5

        if (!isExpired && !isExpiring) return null

        // Create alert key
        const alertKey = isExpired ? `${session.id}-expired` : `${session.id}-warning`

        // Check if already alerted
        if (alertedSessions.value.has(alertKey)) return null

        return {
            session,
            remaining,
            isExpired,
            isExpiring,
            alertKey
        }
    }

    // Update sessions data
    const updateSessions = (tablesData) => {
        const activeSessions = tablesData
            .filter(table => table.status === 'occupied' && table.active_session)
            .map(table => ({
                ...table.active_session,
                table_number: table.table_number,
                table_id: table.id
            }))

        sessions.value = activeSessions
    }

    // Check all sessions for alerts
    const checkAlerts = (callback) => {
        const alerts = []

        sessions.value.forEach(session => {
            const alert = checkSessionAlert(session)
            if (alert) {
                alertedSessions.value.add(alert.alertKey)
                alerts.push(alert)
            }
        })

        if (alerts.length > 0 && callback) {
            callback(alerts)
        }
    }

    // Start checking (every 1 second for accurate countdown)
    const startChecking = (callback) => {
        loadAcknowledgedSessions()

        checkInterval.value = setInterval(() => {
            checkAlerts(callback)
        }, 1000) // Check every second
    }

    // Stop checking
    const stopChecking = () => {
        if (checkInterval.value) {
            clearInterval(checkInterval.value)
            checkInterval.value = null
        }
    }

    // Mark session as acknowledged
    const acknowledgeSession = (sessionId) => {
        if (!acknowledgedSessions.value.includes(sessionId)) {
            acknowledgedSessions.value.push(sessionId)
            localStorage.setItem('acknowledgedExpiredSessions', JSON.stringify(acknowledgedSessions.value))
        }
    }

    // Clear acknowledged session (after payment)
    const clearAcknowledged = (sessionId) => {
        acknowledgedSessions.value = acknowledgedSessions.value.filter(id => id !== sessionId)
        localStorage.setItem('acknowledgedExpiredSessions', JSON.stringify(acknowledgedSessions.value))
    }

    // Cleanup
    onUnmounted(() => {
        stopChecking()
    })

    return {
        sessions,
        updateSessions,
        startChecking,
        stopChecking,
        checkAlerts,
        acknowledgeSession,
        clearAcknowledged,
        calculateRemainingMinutes
    }
}
