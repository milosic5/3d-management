import { computed } from 'vue';

export function usePrintTime(minutesRef) {
    const formattedPrintTime = computed(() => {
        if (!minutesRef.value && minutesRef.value !== 0) return '—';
        const h = Math.floor(minutesRef.value / 60);
        const m = minutesRef.value % 60;
        if (h > 0 && m > 0) return `${h}h ${m}m`;
        if (h > 0) return `${h}h`;
        return `${m}m`;
    });

    return { formattedPrintTime };
}
