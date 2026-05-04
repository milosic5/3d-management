import { ref } from 'vue';

export function useFileUpload() {
    const file = ref(null);
    const previewUrl = ref(null);
    
    const handleFileChange = (e) => {
        const selected = e.target.files[0];
        if (!selected) return;
        
        file.value = selected;
        
        if (selected.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewUrl.value = e.target.result;
            };
            reader.readAsDataURL(selected);
        } else {
            previewUrl.value = null;
        }
    };
    
    const clearFile = () => {
        file.value = null;
        previewUrl.value = null;
    };
    
    return { file, previewUrl, handleFileChange, clearFile };
}
