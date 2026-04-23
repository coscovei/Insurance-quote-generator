import axios from 'axios'

const api = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
})

export const getRequests = () => api.get('/requests')
export const createOffer = (payload) => api.post('/offer', payload)
export const checkoutOffer = (offerId, insurerName, amount, pdfUrl = null) =>
    api.post('/checkout', {
        offer_id: offerId,
        insurer_name: insurerName,
        amount,
        pdf_url: pdfUrl,
    })

export default api
