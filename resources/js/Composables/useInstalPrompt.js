// useInstallPrompt.js
import { ref } from 'vue';

export default function useInstallPrompt() {
    const deferredPrompt = ref(null);

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        console.log('beforeinstallprompt');
        deferredPrompt.value = e;
    });

    const showInstallPrompt = () => {
        console.log('showInstallPrompt');
        if (deferredPrompt.value) {
            deferredPrompt.value.prompt();
            deferredPrompt.value.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the install prompt');
                } else {
                    console.log('User dismissed the install prompt');
                }
                deferredPrompt.value = null;
            });
        }
    };

    const isAppInstalled = () => {
        if ('standalone' in navigator && navigator.standalone) {
            return true;
        }
        if (window.matchMedia('(display-mode: standalone)').matches) {
            return true;
        }
        return false;
    };

    const isInstallPromptSaved = () => {
        return deferredPrompt.value !== null;
    };

    return {
        deferredPrompt,
        showInstallPrompt,
        isAppInstalled,
        isInstallPromptSaved,
    };
}
