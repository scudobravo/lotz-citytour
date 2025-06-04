import { createI18n } from 'vue-i18n';

const messages = {
    it: {
        terms: {
            title: 'Termini e Condizioni',
            content: 'Benvenuto in City Tour Roma. Utilizzando questo servizio, accetti i seguenti termini e condizioni:\n\n1. Il servizio è fornito "così com\'è" e non garantiamo che sarà sempre disponibile o privo di errori.\n\n2. I contenuti forniti sono solo a scopo informativo e potrebbero non essere sempre accurati o aggiornati.\n\n3. Non siamo responsabili per eventuali danni derivanti dall\'uso del servizio.\n\n4. Ci riserviamo il diritto di modificare questi termini in qualsiasi momento.\n\n5. L\'uso del servizio implica l\'accettazione di questi termini.',
            accept: 'Ho letto e accetto i termini e condizioni',
            start: 'INIZIA',
            select_language: 'Seleziona lingua'
        }
    },
    en: {
        terms: {
            title: 'Terms and Conditions',
            content: 'Welcome to City Tour Rome. By using this service, you accept the following terms and conditions:\n\n1. The service is provided "as is" and we do not guarantee that it will always be available or error-free.\n\n2. The content provided is for informational purposes only and may not always be accurate or up to date.\n\n3. We are not responsible for any damages arising from the use of the service.\n\n4. We reserve the right to modify these terms at any time.\n\n5. Using the service implies acceptance of these terms.',
            accept: 'I have read and accept the terms and conditions',
            start: 'START',
            select_language: 'Select language'
        }
    }
};

const i18n = createI18n({
    legacy: false,
    locale: navigator.language.split('-')[0] || 'it',
    fallbackLocale: 'it',
    messages
});

export default i18n; 