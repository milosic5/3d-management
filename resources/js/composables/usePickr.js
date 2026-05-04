import { ref, onMounted, onUnmounted } from 'vue'
import '@simonwep/pickr/dist/themes/classic.min.css'
import Pickr from '@simonwep/pickr'

export function usePickr(initialColor = '#ffffff') {
    const pickrEl = ref(null)
    const colorHex = ref(initialColor)
    let pickrInstance = null

    onMounted(() => {
        if (!pickrEl.value) return

        pickrInstance = Pickr.create({
            el: pickrEl.value,
            theme: 'classic',
            default: initialColor,
            components: {
                preview: true,
                opacity: false,
                hue: true,
                interaction: {
                    hex: true,
                    input: true,
                    save: true,
                },
            },
        })

        pickrInstance.on('save', (color) => {
            colorHex.value = color.toHEXA().toString()
            pickrInstance.hide() // optional, but good UX
        })
    })

    onUnmounted(() => {
        if (pickrInstance) {
            pickrInstance.destroyAndRemove()
        }
    })

    return { colorHex, pickrEl }
}
