<template>
    <div class="min-h-screen bg-gray-100">
        <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <!-- Selettore lingua -->
                <div class="mb-6">
                    <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ t('terms.select_language') }}
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
                    {{ t('terms.title') }}
                </h1>

                <!-- Contenuto termini e condizioni -->
                <div class="prose prose-indigo max-w-none mb-8">
                    <p class="whitespace-pre-line">{{ t('terms.content') }}</p>
                </div>

                <!-- Checkbox accettazione -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            v-model="termsAccepted"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        >
                        <span class="ml-2 text-sm text-gray-700">{{ t('terms.accept') }}</span>
                    </label>
                </div>

                <!-- Bottone Start -->
                <button
                    @click="startTour"
                    :disabled="!termsAccepted"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ t('terms.start') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();
const selectedLanguage = ref(navigator.language.split('-')[0] || 'it');
const termsAccepted = ref(false);

onMounted(() => {
    changeLanguage();
});

const changeLanguage = () => {
    locale.value = selectedLanguage.value;
};

const startTour = () => {
    if (!termsAccepted.value) return;

    const whatsappNumber = '1234567890'; // Sostituire con il numero effettivo
    const message = encodeURIComponent(
        `${t('terms.title')}\n\n${t('terms.content')}\n\n` +
        'Clicca qui per vedere la mappa dei monumenti: ' +
        'https://maps.google.com/?q=Roma,Italia'
    );

    window.open(`https://wa.me/${whatsappNumber}?text=${message}`, '_blank');
};
</script>