import { defineStore } from 'pinia';
import axios from 'axios';

export const useTablesStore = defineStore('tables', {
    state: () => ({
        tables: [],
        loading: false,
        error: null,
    }),

    getters: {
        availableTables: (state) => state.tables.filter(t => t.status === 'available'),
        playingTables: (state) => state.tables.filter(t => t.status === 'playing'),
        maintenanceTables: (state) => state.tables.filter(t => t.status === 'maintenance'),
    },

    actions: {
        async fetchTables() {
            this.loading = true;
            this.error = null;
            try {
                const response = await axios.get('/api/tables');
                if (response.data.success) {
                    this.tables = response.data.data;
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Gagal memuat data meja';
            } finally {
                this.loading = false;
            }
        },

        async createTable(tableNumber) {
            try {
                const response = await axios.post('/api/tables', { table_number: tableNumber });
                if (response.data.success) {
                    this.tables.push(response.data.data);
                    return { success: true };
                }
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Gagal menambah meja',
                };
            }
        },

        async updateTable(id, data) {
            try {
                const response = await axios.put(`/api/tables/${id}`, data);
                if (response.data.success) {
                    const index = this.tables.findIndex(t => t.id === id);
                    if (index !== -1) {
                        this.tables[index] = { ...this.tables[index], ...response.data.data };
                    }
                    return { success: true };
                }
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Gagal update meja',
                };
            }
        },

        async deleteTable(id) {
            try {
                const response = await axios.delete(`/api/tables/${id}`);
                if (response.data.success) {
                    this.tables = this.tables.filter(t => t.id !== id);
                    return { success: true };
                }
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Gagal menghapus meja',
                };
            }
        },

        updateTableStatus(tableId, status, activeSession = null) {
            const table = this.tables.find(t => t.id === tableId);
            if (table) {
                table.status = status;
                table.active_session = activeSession;
            }
        },
    },
});
