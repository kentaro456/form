document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    if (!form) return;

    const submitBtn = document.getElementById('submitBtn');
    const spinner = form.querySelector('.spinner');
    const btnText = form.querySelector('.btn-text');
    const responseMsg = document.getElementById('response-message');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        clearMessage();

        if (!form.reportValidity()) {
            return;
        }

        if (form.id === 'registerForm') {
            const password = form.querySelector('#password')?.value ?? '';
            const confirmPassword = form.querySelector('#password_confirm')?.value ?? '';
            if (password !== confirmPassword) {
                showMessage('error', 'Les mots de passe ne correspondent pas.');
                return;
            }
        }

        setLoading(true);

        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            if (data.success) {
                showMessage('success', data.message || 'Succès.');
                if (data.redirect) {
                    window.setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 500);
                }
            } else {
                showMessage('error', data.message || 'Une erreur est survenue.');
            }
        } catch (error) {
            showMessage('error', 'Erreur de connexion au serveur. Réessayez.');
            console.error('Network Error:', error);
        } finally {
            setLoading(false);
        }
    });

    function setLoading(isLoading) {
        submitBtn.disabled = isLoading;
        if (spinner) spinner.style.display = isLoading ? 'block' : 'none';
        if (btnText) btnText.style.display = isLoading ? 'none' : 'block';
    }

    function clearMessage() {
        responseMsg.style.display = 'none';
        responseMsg.className = '';
        responseMsg.textContent = '';
    }

    function showMessage(type, text) {
        responseMsg.textContent = text;
        responseMsg.className = type === 'success' ? 'message-success' : 'message-error';
        responseMsg.style.display = 'block';
    }
});
