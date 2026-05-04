import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';

export function useFilters(initialData) {
    const form = ref({
        search: initialData.search || '',
        status: initialData.status || '',
        material: initialData.material || '',
        category: initialData.category || '',
        from: initialData.from || '',
        to: initialData.to || '',
        ...initialData
    });

    watch(form, debounce((value) => {
        const queryParams = Object.keys(value).reduce((acc, key) => {
            if (value[key] !== '' && value[key] !== null && value[key] !== undefined) {
                acc[key] = value[key];
            }
            return acc;
        }, {});

        router.get(
            window.location.pathname,
            queryParams,
            { preserveState: true, replace: true }
        );
    }, 300), { deep: true });

    return { form };
}
