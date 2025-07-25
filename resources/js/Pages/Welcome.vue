<template>
    <div class="min-h-screen bg-gray-100">
        <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <!-- Selettore lingua -->
                <div class="mb-6">
                    <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $page.props.translations.terms.select_language }}
                    </label>
                    <select
                        id="language"
                        v-model="selectedLanguage"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        @change="changeLanguage"
                    >
                        <option value="it">Italiano</option>
                        <option value="en">English</option>
                    </select>
                </div>

                <!-- Titolo -->
                <h1 class="text-3xl font-bold text-gray-900 mb-6">
                    {{ $page.props.translations.terms.title }}
                </h1>

                <!-- Contenuto termini e condizioni -->
                <div class="prose prose-indigo max-w-none mb-8">
                    <p class="whitespace-pre-line">{{ $page.props.translations.terms.content }}</p>
                </div>

                <!-- Checkbox accettazione -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            v-model="termsAccepted"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        >
                        <span class="ml-2 text-sm text-gray-700">{{ $page.props.translations.terms.accept }}</span>
                    </label>
                </div>

                <!-- Bottone Start -->
                <button
                    @click="startTour"
                    :disabled="!termsAccepted"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ $page.props.translations.terms.start }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const selectedLanguage = ref(navigator.language.split('-')[0] || 'it');
const termsAccepted = ref(false);
const pointsOfInterest = ref([]);

onMounted(async () => {
    // Recupera i punti di interesse dal database
    try {
        console.log('Tentativo di recupero dei punti di interesse...');
        const response = await fetch('/points');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        console.log('Punti di interesse recuperati:', data);
        pointsOfInterest.value = data;
    } catch (error) {
        console.error('Errore nel recupero dei punti di interesse:', error);
        pointsOfInterest.value = [];
    }
});

const changeLanguage = () => {
    router.get('/', { lang: selectedLanguage.value }, {
        preserveState: true,
        preserveScroll: true,
        only: ['translations']
    });
};

const startTour = () => {
    if (!termsAccepted.value) return;

    const twilioNumber = import.meta.env.VITE_TWILIO_WHATSAPP_NUMBER;
    if (!twilioNumber) {
        console.error('Numero WhatsApp non configurato');
        return;
    }

    const whatsappNumber = twilioNumber.replace('whatsapp:', '');
    
    // Costruisci l'URL della mappa con i punti di interesse
    const mapUrl = pointsOfInterest.value.length > 0
        ? `https://www.google.com/maps/dir/?api=1&origin=${pointsOfInterest.value[0].latitude},${pointsOfInterest.value[0].longitude}&destination=${pointsOfInterest.value[pointsOfInterest.value.length - 1].latitude},${pointsOfInterest.value[pointsOfInterest.value.length - 1].longitude}&waypoints=${pointsOfInterest.value.slice(1, -1).slice(0, 8).map(p => `${p.latitude},${p.longitude}`).join('|')}&travelmode=walking&hl=it`
        : 'https://maps.google.com/?q=Roma,Italia';

    const message = encodeURIComponent(
        'Benvenuto in City Tour! 🎉\n\n' +
        'Grazie per aver accettato i termini e condizioni. Ora puoi iniziare il tuo tour virtuale di Roma.\n\n' +
        'Clicca qui per aprire la mappa con tutti i punti di interesse: ' +
        mapUrl + '\n\n' +
        'Per iniziare la navigazione:\n' +
        '1. Apri il link da smartphone\n' +
        '2. Clicca su "Indicazioni"\n' +
        '3. Seleziona "A piedi"\n\n' +
        'Punti di interesse:\n' +
        pointsOfInterest.value.map((p, index) => {
            // Usiamo il formato più semplice che funziona meglio per punti personalizzati
            const pointUrl = `https://www.google.com/maps/search/?api=1&query=${p.latitude},${p.longitude}&query_place_id=${encodeURIComponent(p.name)}`;
            let pointInfo = `${index + 1}. *${p.name}*\n`;
            if (p.description) {
                pointInfo += `   ${p.description}\n`;
            }
            pointInfo += `   Clicca qui per vedere la posizione: ${pointUrl}`;
            return pointInfo;
        }).join('\n\n')
    );

    const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${message}`;
    window.open(whatsappUrl, '_blank');
};
</script>