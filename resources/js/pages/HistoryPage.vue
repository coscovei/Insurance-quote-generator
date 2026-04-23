<template>
    <section class="card">
        <div class="section-head">
            <div>
                <h2>Istoric cereri</h2>
                <p class="muted">Ultimele cereri generate în aplicație.</p>
            </div>

            <button class="secondary-btn" @click="loadRequests" :disabled="loading">
                {{ loading ? 'Se actualizează...' : 'Reîncarcă' }}
            </button>
        </div>

        <div v-if="loading" class="placeholder">
            Se încarcă istoricul...
        </div>

        <div v-else-if="error" class="error-box">
            {{ error }}
        </div>

        <div v-else-if="!requests.length" class="placeholder">
            Nu există cereri salvate încă.
        </div>

        <div v-else class="history-list">
            <article v-for="req in requests" :key="req.id" class="history-card">
                <div class="history-row">
                    <div>
                        <h3>{{ getClientName(req) }}</h3>
                        <p class="muted">
                            CNP/CUI: {{ req.policyholder?.personable?.tax_id || '-' }}
                        </p>
                        <p class="muted">
                            Tip cerere:
                            <strong>{{ formatTargetProvider(req.target_provider) }}</strong>
                        </p>
                    </div>

                    <div class="price-badge" v-if="getBestOffer(req)">
                        {{ getBestOffer(req).price }} RON
                    </div>
                </div>

                <div class="history-grid">
                    <div>
                        <strong>Vehicul</strong>
                        <p>{{ req.vehicle?.brand || '-' }} {{ req.vehicle?.model || '-' }}</p>
                    </div>

                    <div>
                        <strong>Număr</strong>
                        <p>{{ req.vehicle?.license_plate || '-' }}</p>
                    </div>

                    <div>
                        <strong>VIN</strong>
                        <p>{{ req.vehicle?.vin || '-' }}</p>
                    </div>

                    <div>
                        <strong>Nr. oferte</strong>
                        <p>{{ req.offers?.length || 0 }}</p>
                    </div>
                </div>

                <div v-if="req.offers?.length" class="offers-inline">
                    <div
                        v-for="offer in sortedOffers(req.offers)"
                        :key="offer.id"
                        class="offer-chip"
                    >
                        <span class="offer-chip-name">{{ offer.insurer_name }}</span>
                        <span class="offer-chip-price">{{ offer.price }} RON</span>

                        <a
                            v-if="offer.pdf_url"
                            :href="offer.pdf_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="offer-chip-link"
                        >
                            Descarcă PDF
                        </a>
                    </div>
                </div>

                <p class="muted">
                    Creat la: {{ formatDate(req.created_at) }}
                </p>
            </article>
        </div>
    </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { getRequests } from '../services/api'

const requests = ref([])
const loading = ref(true)
const error = ref('')

async function loadRequests() {
    loading.value = true
    error.value = ''

    try {
        const response = await getRequests()
        requests.value = response.data
    } catch (err) {
        error.value = 'Istoricul nu a putut fi încărcat.'
    } finally {
        loading.value = false
    }
}

function getClientName(req) {
    const personable = req.policyholder?.personable

    if (!personable) return 'Client necunoscut'
    if (personable.business_name) return personable.business_name

    return `${personable.first_name || ''} ${personable.last_name || ''}`.trim() || 'Client necunoscut'
}

function formatTargetProvider(targetProvider) {
    if (!targetProvider) return '-'
    if (targetProvider === 'all') return 'Toți asigurătorii'
    return targetProvider
}

function sortedOffers(offers = []) {
    return [...offers].sort((a, b) => Number(a.price) - Number(b.price))
}

function getBestOffer(req) {
    if (!req.offers?.length) return null
    return sortedOffers(req.offers)[0]
}



function formatDate(value) {
    if (!value) return '-'
    return new Date(value).toLocaleString('ro-RO')
}

onMounted(loadRequests)
</script>
