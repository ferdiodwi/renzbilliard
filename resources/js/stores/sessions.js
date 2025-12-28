import { defineStore } from 'pinia';
import axios from 'axios';
import { useTablesStore } from './tables';

export const useSessionsStore = defineStore('sessions', {
    state: () => ({
        activeSessions: [],
        loading: false,
        error: null,
    }),

    actions: {
        async fetchActiveSessions() {
            this.loading = true;
            this.error = null;
            try {
                const response = await axios.get('/api/sessions/active');
                if (response.data.success) {
                    this.activeSessions = response.data.data;
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Gagal memuat sesi aktif';
            } finally {
                this.loading = false;
            }
        },

        async startSession(tableId, rateId, durationMinutes, autoStop = true) {
            try {
                const response = await axios.post('/api/sessions/start', {
                    table_id: tableId,
                    rate_id: rateId,
                    duration_minutes: durationMinutes,
                    auto_stop: autoStop,
                });
                if (response.data.success) {
                    await this.fetchActiveSessions();
                    const tablesStore = useTablesStore();
                    await tablesStore.fetchTables();
                    return { success: true, data: response.data.data };
                }
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Gagal memulai sesi',
                };
            }
        },

        async stopSession(sessionId) {
            try {
                const response = await axios.post(`/api/sessions/${sessionId}/stop`);
                if (response.data.success) {
                    await this.fetchActiveSessions();
                    const tablesStore = useTablesStore();
                    await tablesStore.fetchTables();
                    return { success: true, data: response.data.data };
                }
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Gagal menghentikan sesi',
                };
            }
        },

        async extendSession(sessionId, additionalMinutes) {
            try {
                const response = await axios.post(`/api/sessions/${sessionId}/extend`, {
                    additional_minutes: additionalMinutes,
                });
                if (response.data.success) {
                    await this.fetchActiveSessions();
                    return { success: true, data: response.data.data };
                }
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Gagal memperpanjang sesi',
                };
            }
        },

        updateSessionTimer(sessionId, remainingSeconds) {
            const session = this.activeSessions.find(s => s.id === sessionId);
            if (session) {
                session.remaining_seconds = remainingSeconds;
            }
        },
    },
});
