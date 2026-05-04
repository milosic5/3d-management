import { ref, watch, onMounted } from 'vue'

/**
 * Reusable composable for managing TanStack Table column visibility state.
 * Persists the visibility map to localStorage using the provided tableId.
 * 
 * @param {string} tableId - Unique identifier for the table (e.g., 'products_table')
 * @param {Array} columnDefs - The initial array of column definition objects
 * @param {Array} alwaysVisible - Array of column IDs to consistently keep visible (e.g. ['actions'])
 * @returns {Object} { columnVisibility, setColumnVisibility }
 */
export function useColumnVisibility(tableId, columnDefs, alwaysVisible = ['actions']) {
    const storageKey = `${tableId}_column_visibility`
    const columnVisibility = ref({})

    onMounted(() => {
        // Initialize default state
        const initialState = {}
        columnDefs.forEach(col => {
            const id = col.id || col.accessorKey || col.header
            if (id) {
                initialState[id] = true
            }
        })

        // Restore from localStorage
        const stored = localStorage.getItem(storageKey)
        if (stored) {
            try {
                const parsed = JSON.parse(stored)
                Object.assign(initialState, parsed)
            } catch (e) {
                console.error('Failed to parse column visibility configuration from localStorage', e)
            }
        }
        
        // Ensure strictly required fields are visible
        alwaysVisible.forEach(id => {
            if (id in initialState === false || initialState[id] === false) {
                initialState[id] = true
            }
        })

        columnVisibility.value = initialState
    })

    const setColumnVisibility = (updater) => {
        if (typeof updater === 'function') {
            columnVisibility.value = updater(columnVisibility.value)
        } else {
            columnVisibility.value = updater
        }
    }

    watch(columnVisibility, (newState) => {
        localStorage.setItem(storageKey, JSON.stringify(newState))
    }, { deep: true })

    return {
        columnVisibility,
        setColumnVisibility
    }
}
