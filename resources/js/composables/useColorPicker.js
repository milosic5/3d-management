import { ref } from 'vue';

export function useColorPicker(initialHex = '#ffffff') {
    const colorHex = ref(initialHex);
    
    const updateFromPicker = (e) => {
        colorHex.value = e.target.value;
    };
    
    const updateFromText = (e) => {
        let val = e.target.value;
        if (!val.startsWith('#')) val = '#' + val;
        // basic regex check
        if (/^#[0-9A-Fa-f]{6}$/i.test(val)) {
            colorHex.value = val;
        }
    };

    return { colorHex, updateFromPicker, updateFromText };
}
