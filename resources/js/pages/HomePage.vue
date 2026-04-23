<template>
    <section class="card">
        <div class="section-head">
            <div>
                <h2>Calculator RCA</h2>
                <p class="muted">Completează datele și generează ofertele disponibile.</p>
            </div>
        </div>

        <div v-if="purchaseSuccess" class="success-box">
            <h3>Plata a fost confirmată</h3>
            <p>
                Asigurător: <strong>{{ selectedOffer?.insurer }}</strong>
            </p>
            <p>
                Cod ofertă: <strong>{{ selectedOffer?.details?.providerOfferCode || '-' }}</strong>
            </p>
            <p>
                Sumă: <strong>{{ selectedOffer?.price }} RON</strong>
            </p>

            <a
                v-if="selectedOffer?.details?.pid"
                class="primary-btn inline-btn"
                :href="selectedOffer.details.pid"
                target="_blank"
                rel="noopener noreferrer"
            >
                Descarcă PDF
            </a>

            <button class="secondary-btn inline-btn" @click="resetFlow">
                Calculează din nou
            </button>
        </div>

        <form v-else class="form-grid" @submit.prevent="submitForm">
            <div class="field">
                <label>Tip client</label>
                <select v-model="form.customerType">
                    <option value="pf">Persoană fizică</option>
                    <option value="pj">Persoană juridică</option>
                </select>
            </div>

            <div class="field">
                <label>Asigurător</label>
                <select v-model="form.selectedInsurer">
                    <option value="all">Toți asigurătorii</option>
                    <option value="asirom">Asirom</option>
                    <option value="grawe">Grawe</option>
                    <option value="axeria">Axeria</option>
                    <option value="hellas_autonom">Hellas Autonom</option>
                    <option v-if="form.customerType === 'pf'" value="eazy_insure">Eazy Insure</option>
                </select>
            </div>

            <template v-if="form.customerType === 'pf'">
                <div class="field">
                    <label>Prenume</label>
                    <input v-model="form.firstName" type="text" />
                </div>

                <div class="field">
                    <label>Nume</label>
                    <input v-model="form.lastName" type="text" />
                </div>

                <div class="field">
                    <label>CNP</label>
                    <input v-model="form.taxId" type="text" />
                </div>

                <div class="field">
                    <label>Data nașterii</label>
                    <input v-model="form.birthdate" type="date" />
                </div>

                <div class="field">
                    <label>Gen</label>
                    <select v-model="form.gender">
                        <option value="m">Masculin</option>
                        <option value="f">Feminin</option>
                    </select>
                </div>

                <div class="field">
                    <label>Serie/număr CI</label>
                    <input v-model="form.idNumber" type="text" />
                </div>
            </template>

            <template v-else>
                <div class="field">
                    <label>Denumire companie</label>
                    <input v-model="form.businessName" type="text" />
                </div>

                <div class="field">
                    <label>CUI</label>
                    <input v-model="form.taxId" type="text" />
                </div>

                <div class="field">
                    <label>Nr. Registrul Comerțului</label>
                    <input v-model="form.companyRegistryNumber" type="text" />
                </div>

                <div class="field">
                    <label>Cod CAEN</label>
                    <input v-model="form.caenCode" type="text" />
                </div>
            </template>

            <div class="field">
                <label>Email</label>
                <input v-model="form.email" type="email" />
            </div>

            <div class="field">
                <label>Telefon</label>
                <input v-model="form.mobileNumber" type="text" />
            </div>

            <div class="field">
                <label>Județ</label>
                <input v-model="form.county" type="text" />
            </div>

            <div class="field">
                <label>Localitate</label>
                <input v-model="form.city" type="text" />
            </div>

            <div class="field">
                <label>Stradă</label>
                <input v-model="form.street" type="text" />
            </div>

            <div class="field">
                <label>Număr</label>
                <input v-model="form.houseNumber" type="text" />
            </div>

            <div class="field">
                <label>VIN</label>
                <input v-model="form.vin" type="text" />
            </div>

            <div class="field">
                <label>Număr înmatriculare</label>
                <input v-model="form.licensePlate" type="text" />
            </div>

            <div class="field">
                <label>Marcă</label>
                <input v-model="form.brand" type="text" />
            </div>

            <div class="field">
                <label>Model</label>
                <input v-model="form.model" type="text" />
            </div>

            <div class="field">
                <label>An fabricație</label>
                <input v-model="form.yearOfConstruction" type="number" />
            </div>

            <div class="field">
                <label>Capacitate cilindrică</label>
                <input v-model="form.engineDisplacement" type="number" />
            </div>

            <div class="field">
                <label>Kilometraj</label>
                <input v-model="form.mileage" type="number" />
            </div>

            <div class="field">
                <label>Serie CIV</label>
                <input v-model="form.civNumber" type="text" />
            </div>

            <div class="form-actions">
                <button class="primary-btn" type="submit" :disabled="loading">
                    {{ loading ? 'Se generează ofertele...' : 'Generează oferte RCA' }}
                </button>
            </div>
        </form>

        <div v-if="apiError" class="error-box">
            {{ apiError }}
        </div>

        <div v-if="result?.offers?.length" class="offers-section">
            <div class="section-head">
                <h3>{{ result.offers.length > 1 ? 'Oferte disponibile' : 'Oferta disponibilă' }}</h3>
            </div>

            <div class="offers-grid">
                <OfferCard
                    v-for="offer in result.offers"
                    :key="offer.id || offer.insurer"
                    :offer="offer"
                    :loading="loading"
                    @buy="buyOffer"
                />
            </div>
        </div>
    </section>
</template>

<script setup>
import { reactive, ref } from 'vue'
import OfferCard from '../components/OfferCard.vue'
import { checkoutOffer, createOffer } from '../services/api'

const loading = ref(false)
const result = ref(null)
const apiError = ref('')
const purchaseSuccess = ref(false)
const selectedOffer = ref(null)

const form = reactive({
    customerType: 'pf',
    selectedInsurer: 'all',

    firstName: 'VASILE',
    lastName: 'IONESCU',
    businessName: 'SC EXEMPLU SRL',
    taxId: '1900101010011',
    companyRegistryNumber: 'J12/123/2020',
    caenCode: '4511',

    birthdate: '1990-01-01',
    gender: 'm',
    email: 'test@email.ro',
    mobileNumber: '0722123456',
    idNumber: 'RR123456',

    county: 'CJ',
    city: 'Cluj-Napoca',
    street: 'Calea Turzii',
    houseNumber: '10',

    // vin: '3D7JV1EP7BG610' + Math.floor(100 + Math.random() * 899),
    vin: '3D7JV1EP7BG610352',
    licensePlate: 'CJ10ABC',
    brand: 'Toyota',
    model: 'Corolla',
    engineDisplacement: '1800',
    yearOfConstruction: '2018',
    civNumber: 'V123456',
    mileage: '120000',
})

function getLocalTomorrow() {
    const tomorrow = new Date()
    tomorrow.setDate(tomorrow.getDate() + 1)

    const year = tomorrow.getFullYear()
    const month = String(tomorrow.getMonth() + 1).padStart(2, '0')
    const day = String(tomorrow.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`
}

function buildAddress(cityCode) {
    return {
        country: 'RO',
        county: form.county.toUpperCase(),
        city: form.city,
        cityCode,
        street: form.street,
        houseNumber: form.houseNumber,
        building: '1',
        floor: '0',
        postcode: '400356',
    }
}

function buildPolicyholder(cleanTaxId, cityCode) {
    if (form.customerType === 'pj') {
        return {
            businessName: form.businessName.toUpperCase(),
            taxId: cleanTaxId,
            companyRegistryNumber: form.companyRegistryNumber,
            caenCode: form.caenCode,
            email: form.email,
            mobileNumber: form.mobileNumber,
            isForeignPerson: false,
            address: buildAddress(cityCode),
        }
    }

    return {
        lastName: form.lastName.toUpperCase(),
        firstName: form.firstName.toUpperCase(),
        taxId: cleanTaxId,
        birthdate: form.birthdate,
        gender: form.gender,
        email: form.email,
        mobileNumber: form.mobileNumber,
        nationality: 'RO',
        citizenship: 'RO',
        isForeignPerson: false,
        drivingLicense: {
            issueDate: '2010-01-01',
        },
        identification: {
            idType: 'CI',
            idNumber: form.idNumber,
            issueAuthority: 'SPCLEP',
            issueDate: '2020-01-01',
        },
        address: buildAddress(cityCode),
        isRetired: false,
        hasDisability: false,
        occupation: 'SALARIAT',
    }
}

function buildOwner(cleanTaxId, cityCode) {
    if (form.customerType === 'pj') {
        return {
            businessName: form.businessName.toUpperCase(),
            taxId: cleanTaxId,
            companyRegistryNumber: form.companyRegistryNumber,
            caenCode: form.caenCode,
            email: form.email,
            mobileNumber: form.mobileNumber,
            isForeignPerson: false,
            address: buildAddress(cityCode),
        }
    }

    return {
        lastName: form.lastName.toUpperCase(),
        firstName: form.firstName.toUpperCase(),
        taxId: cleanTaxId,
        birthdate: form.birthdate,
        gender: form.gender,
        isForeignPerson: false,
        drivingLicense: {
            issueDate: '2010-01-01',
        },
        identification: {
            idType: 'CI',
            idNumber: form.idNumber,
        },
        address: buildAddress(cityCode),
    }
}

function buildPayload() {
    const cleanTaxId = form.taxId.replace(/\D/g, '')
    const cityCode = form.city.toLowerCase().includes('cluj') ? 54984 : 179132

    return {
        provider: {
            organization: {
                businessName: form.selectedInsurer,
            },
        },
        product: {
            motor: {
                startDate: getLocalTomorrow(),
                termTime: 12,
                installmentCount: 1,
                generatePaymentLink: false,
            },
            policyholder: buildPolicyholder(cleanTaxId, cityCode),
            vehicle: {
                owner: buildOwner(cleanTaxId, cityCode),
                licensePlate: form.licensePlate.toUpperCase(),
                vin: form.vin,
                brand: form.brand.toUpperCase(),
                model: form.model.toUpperCase(),
                yearOfConstruction: parseInt(form.yearOfConstruction),
                currentMileage: parseInt(form.mileage),
                firstRegistration: '2020-01-01',
                isLeased: false,
                fuelType: 'diesel',
                engineDisplacement: parseInt(form.engineDisplacement),
                enginePower: 110,
                totalWeight: 1950,
                seats: 5,
                registrationType: 'registered',
                vehicleType: 'M1',
                usageType: 'personal',
                identification: {
                    idNumber: form.civNumber,
                },
            },
            additionalData: {
                product: {
                    motor: {
                        hasCasco: false,
                        isAcquiredFromRomanianDealer: true,
                        coordinatorId: 76241,
                        protocolId: 222,
                        extraCoverage: false,
                        usageType: 'personal',
                        isLeased: false,
                    },
                },
            },
        },
    }
}

async function submitForm() {
    loading.value = true
    apiError.value = ''
    result.value = null

    try {
        const response = await createOffer(buildPayload())
        result.value = response.data
    } catch (error) {
        apiError.value =
            error.response?.data?.message ||
            'A apărut o eroare la generarea ofertelor.'
    } finally {
        loading.value = false
    }
}

async function buyOffer(offer) {
    const confirmed = window.confirm(
        `Confirmi cumpărarea ofertei ${offer.insurer.toUpperCase()} la prețul de ${offer.price} RON?`
    )

    if (!confirmed) return

    loading.value = true
    apiError.value = ''

    try {
        const response = await checkoutOffer(
            offer.id,
            offer.insurer,
            offer.price,
            offer.details?.pid || null
        )

        if (response.data.success) {
            selectedOffer.value = offer
            purchaseSuccess.value = true
            window.scrollTo({ top: 0, behavior: 'smooth' })
            return
        }

        apiError.value = response.data.message || 'Checkout-ul a eșuat.'
    } catch (error) {
        apiError.value =
            error.response?.data?.message ||
            'A apărut o eroare în timpul procesării plății.'
    } finally {
        loading.value = false
    }
}

function resetFlow() {
    purchaseSuccess.value = false
    selectedOffer.value = null
    result.value = null
    apiError.value = ''
}
</script>
