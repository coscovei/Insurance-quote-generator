<template>
    <article class="offer-card" :class="{ failed: !offer.success }">
        <div v-if="offer.success" class="offer-top">
            <div>
                <p class="offer-label">Asigurător</p>
                <h3>{{ formatInsurerName(offer.insurer) }}</h3>
            </div>

            <div class="offer-price-box">
                <span class="offer-price-label">Preț</span>
                <strong class="offer-price">{{ formatPrice(offer.price) }} RON</strong>
            </div>
        </div>

        <div v-if="offer.success" class="offer-meta">
            <div class="offer-meta-item">
                <span>Bonus-malus</span>
                <strong>{{ offer.details?.bonusMalusClass || 'B0' }}</strong>
            </div>

            <div class="offer-meta-item">
                <span>Valabil până la</span>
                <strong>{{ formatDate(offer.details?.offerExpiryDate) }}</strong>
            </div>

            <div class="offer-meta-item">
                <span>Compensare directă</span>
                <strong>
                    {{ offer.details?.directCompensation?.premiumAmount ? 'Da' : 'Nu' }}
                </strong>
            </div>
        </div>

        <div v-if="offer.success" class="offer-notes" v-show="offer.details?.notes">
            {{ offer.details?.notes }}
        </div>

        <div v-if="offer.success" class="offer-actions">
            <a
                v-if="offer.details?.pid"
                :href="offer.details.pid"
                target="_blank"
                rel="noopener noreferrer"
                class="secondary-btn"
            >
                Vezi PDF
            </a>

            <button
                class="primary-btn"
                :disabled="loading"
                @click="$emit('buy', offer)"
            >
                {{ loading ? 'Se procesează...' : 'Cumpără polița' }}
            </button>
        </div>

        <div v-else class="offer-error-wrap">
            <div>
                <p class="offer-label">Asigurător</p>
                <h3>{{ formatInsurerName(offer.insurer) }}</h3>
            </div>

            <div class="offer-error-box">
                {{ prettyError }}
            </div>
        </div>
    </article>
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

    if (error.includes('DRPCIV') || error.includes('inmatriculare')) {
        return 'Vehiculul nu a fost validat pentru tipul de înmatriculare selectat.'
    }

    return error
})

function formatInsurerName(name) {
    if (!name) return '-'
    return name.replaceAll('_', ' ')
}

function formatPrice(value) {
    return Number(value).toFixed(2)
}

function formatDate(value) {
    if (!value) return '-'

    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return value

    return date.toLocaleDateString('ro-RO')
}
</script>
