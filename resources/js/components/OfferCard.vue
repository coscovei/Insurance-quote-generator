<template>
    <div class="offer-card" :class="{ failed: !offer.success }">
        <div class="offer-header">
            <div>
                <h3>{{ offer.insurer }}</h3>
                <p class="muted" v-if="offer.success">
                    Clasa bonus-malus: {{ offer.details?.bonusMalusClass || 'B0' }}
                </p>
                <p class="muted error-text" v-else>
                    {{ prettyError }}
                </p>
            </div>

            <div v-if="offer.success" class="offer-price">
                {{ offer.price }} RON
            </div>
        </div>

        <button
            v-if="offer.success"
            class="primary-btn"
            :disabled="loading"
            @click="$emit('buy', offer)"
        >
            {{ loading ? 'Se procesează...' : 'Cumpără polița' }}
        </button>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    offer: {
        type: Object,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
})

defineEmits(['buy'])

const prettyError = computed(() => {
    const error = props.offer?.error || 'Oferta nu este disponibilă.'

    if (error.includes('RAF')) {
        return 'Oferta nu poate fi emisă deoarece lipsește codul RAF al emitentului.'
    }

    if (error.includes('incercari') || error.includes('24 ore')) {
        return 'A fost depășit numărul de încercări pentru ofertare în ultimele 24 de ore.'
    }

    if (error.includes('PTI') || error.includes('expirationDatePti')) {
        return 'Lipsește data expirării ITP.'
    }

    if (error.includes('provider') || error.includes('business name')) {
        return 'Identificatorul asigurătorului nu este valid.'
    }

    if (error.includes('DRPCIV') || error.includes('inmatriculare')) {
        return 'Vehiculul nu a fost validat în DRPCIV pentru acest tip de înmatriculare.'
    }

    return error
})
</script>
