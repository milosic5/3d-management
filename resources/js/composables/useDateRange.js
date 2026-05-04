import { ref, watch } from 'vue';

export function useDateRange(initialFrom = '', initialTo = '') {
    const from = ref(initialFrom);
    const to = ref(initialTo);

    const setRange = (newFrom, newTo) => {
        from.value = newFrom;
        to.value = newTo;
    };

    return { from, to, setRange };
}
