const form = document.querySelector('#permesso-form');
const periodiScreen = document.querySelector('#periodi-screen');
const addPeriodoButton = document.querySelector('#add-periodo');
const causaliInputs = document.querySelectorAll('input[name="causale[]"]');
const causaliCount = document.querySelector('#causali-count');
const altroPermessoField = document.querySelector('#altro-permesso-field');
const presenzeScreen = document.querySelector('#presenze-screen');
const addPresenzaButton = document.querySelector('#add-presenza');
const causaliPresenzaInputs = document.querySelectorAll('input[name="causale_presenza[]"]');
const causaliPresenzaCount = document.querySelector('#causali-presenza-count');
const generatePdfButton = document.querySelector('#generate-pdf');
const pdfProgress = document.querySelector('#pdf-progress');
const privacyConsent = document.querySelector('#privacy-consent');
const privacyConsentCheckbox = document.querySelector('#privacy-consent-checkbox');
const privacyAcceptButton = document.querySelector('#privacy-accept');
const privacyConsentValue = document.querySelector('#privacy-consent-value');
const privacyConsentError = document.querySelector('#privacy-consent-error');
const errorSummary = document.querySelector('[data-error-summary]');
const recoveryBridge = document.querySelector('[data-recovery-bridge]');
const requestTypeButtons = document.querySelectorAll('[data-request-type]');
const requestDetails = document.querySelectorAll('[data-request-detail]');
const requestTypeCard = document.querySelector('#tipo-richiesta');
const requestSummaryTitle = document.querySelector('#request-summary-title');
const requestSummary = document.querySelector('#request-summary');
const formInlineError = document.querySelector('#form-inline-error');
const progressSteps = document.querySelectorAll('[data-progress-step]');

const requestTypeLabels = {
    absence: 'Assenza',
    presence: 'Presenza',
    missing: 'Omessa timbratura',
};

const selectedRequestTypes = new Set();

const hideInlineError = () => {
    if (formInlineError) {
        formInlineError.hidden = true;
        formInlineError.textContent = '';
    }
};

const showInlineError = (message, target = null) => {
    if (formInlineError) {
        formInlineError.textContent = message;
        formInlineError.hidden = false;
    }

    target?.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const updateRequestFlow = () => {
    requestTypeButtons.forEach((button) => {
        const isSelected = selectedRequestTypes.has(button.dataset.requestType);
        button.classList.toggle('is-selected', isSelected);
        button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
    });

    requestDetails.forEach((detail) => {
        const isActive = selectedRequestTypes.has(detail.dataset.requestDetail);
        detail.classList.toggle('is-hidden', !isActive);
        detail.querySelectorAll('input, select, textarea, button').forEach((control) => {
            control.disabled = !isActive;
        });
    });

    const selectedLabels = Array.from(selectedRequestTypes, (type) => requestTypeLabels[type]);
    const hasDetails = selectedLabels.length > 0;
    const hasPerson = Boolean(form?.elements.nome?.value.trim() && form?.elements.cognome?.value.trim());

    if (requestSummaryTitle && requestSummary) {
        requestSummaryTitle.textContent = hasDetails
            ? `${selectedLabels.length === 1 ? 'Richiesta selezionata' : 'Richieste selezionate'}: ${selectedLabels.join(', ')}`
            : 'Inizia scegliendo la richiesta';
        requestSummary.textContent = hasDetails
            ? 'Controlla i dati e genera il documento.'
            : 'Seleziona assenza, presenza o omessa timbratura.';
    }

    progressSteps.forEach((step) => {
        const key = step.dataset.progressStep;
        const isComplete = (key === 'details' && hasPerson) || (key === 'type' && hasDetails);
        step.classList.toggle('is-complete', isComplete);
        step.classList.toggle('is-current', (key === 'details' && !hasPerson)
            || (key === 'type' && hasPerson && !hasDetails)
            || (key === 'review' && hasPerson && hasDetails));
    });
};

requestTypeButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const scrollPosition = window.scrollY;
        const type = button.dataset.requestType;

        if (selectedRequestTypes.has(type)) {
            selectedRequestTypes.delete(type);
        } else {
            selectedRequestTypes.add(type);
        }

        hideInlineError();
        updateRequestFlow();

        if (window.matchMedia('(max-width: 760px)').matches) {
            window.scrollTo(0, scrollPosition);
            requestAnimationFrame(() => {
                window.scrollTo(0, scrollPosition);
            });
        }
    });
});

form?.querySelectorAll('[name="nome"], [name="cognome"]').forEach((input) => {
    input.addEventListener('input', updateRequestFlow);
});

errorSummary?.focus();

if (recoveryBridge && window.location.hash.length > 1) {
    const recoveryData = new URLSearchParams(window.location.hash.slice(1));
    const accessToken = recoveryData.get('access_token');
    const refreshToken = recoveryData.get('refresh_token');
    const type = recoveryData.get('type');

    window.history.replaceState(null, '', window.location.pathname + window.location.search);

    if (accessToken && refreshToken && type === 'recovery') {
        recoveryBridge.classList.remove('is-hidden');

        fetch(recoveryBridge.dataset.sessionUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify({
                access_token: accessToken,
                refresh_token: refreshToken,
                expires_at: Number(recoveryData.get('expires_at')) || null,
                expires_in: Number(recoveryData.get('expires_in')) || null,
                type,
            }),
        })
            .then(async (response) => {
                const payload = await response.json();

                if (!response.ok || !payload.redirect) {
                    throw new Error(payload.message ?? 'Link non valido.');
                }

                window.location.assign(payload.redirect);
            })
            .catch(() => {
                recoveryBridge.classList.remove('is-success');
                recoveryBridge.classList.add('is-error');
                recoveryBridge.textContent = 'Link di recupero non valido o scaduto. Richiedine uno nuovo.';
            });
    }
}

document.querySelectorAll('.password-toggle').forEach((button) => {
    button.addEventListener('click', () => {
        const input = button.closest('.password-field')?.querySelector('input');

        if (!input) {
            return;
        }

        const showPassword = input.type === 'password';
        input.type = showPassword ? 'text' : 'password';
        button.textContent = showPassword ? 'Nascondi' : 'Mostra';
        button.setAttribute('aria-label', showPassword ? 'Nascondi password' : 'Mostra password');
        button.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
    });
});

document.querySelectorAll('[data-loading-form]').forEach((loadingForm) => {
    loadingForm.addEventListener('submit', () => {
        const submitButton = loadingForm.querySelector('button[type="submit"]');

        if (!submitButton || !loadingForm.checkValidity()) {
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = submitButton.dataset.loadingLabel ?? 'Attendi…';
        submitButton.setAttribute('aria-busy', 'true');
    });
});

privacyConsentCheckbox?.addEventListener('change', () => {
    privacyAcceptButton.disabled = !privacyConsentCheckbox.checked;
});

privacyAcceptButton?.addEventListener('click', async () => {
    if (!privacyConsentCheckbox.checked) {
        return;
    }

    if (privacyConsent.dataset.acceptUrl) {
        privacyAcceptButton.disabled = true;
        privacyAcceptButton.textContent = 'Salvataggio…';
        privacyConsentError.hidden = true;

        try {
            const response = await fetch(privacyConsent.dataset.acceptUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
            });

            if (!response.ok) {
                throw new Error('Privacy consent could not be saved');
            }

            if (privacyConsent.dataset.persistsToProfile === 'true') {
                window.location.reload();
                return;
            }
        } catch {
            privacyConsentError.textContent = 'Impossibile memorizzare il consenso. Riprova.';
            privacyConsentError.hidden = false;
            privacyAcceptButton.disabled = false;
            privacyAcceptButton.textContent = 'Accetta e continua';
            return;
        }
    }

    if (privacyConsentValue) {
        privacyConsentValue.value = '1';
    }

    privacyConsent.remove();
    document.body.classList.remove('privacy-locked');
});

window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        window.location.reload();
    }
});

form?.addEventListener('submit', async (event) => {
    hideInlineError();

    if (!form.checkValidity()) {
        event.preventDefault();
        form.reportValidity();
        form.querySelector(':invalid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    if (selectedRequestTypes.size === 0) {
        event.preventDefault();
        showInlineError('Scegli almeno una tipologia di richiesta prima di generare il PDF.', requestTypeCard);
        return;
    }

    const hasCausaleAssenza = Array.from(causaliInputs).some((input) => input.checked);
    const hasCausalePresenza = Array.from(causaliPresenzaInputs).some((input) => input.checked);
    const hasOmessaTimbratura = Array.from(form.querySelectorAll('[name^="omessa_"]'))
        .some((input) => input.value.trim() !== '');

    if (selectedRequestTypes.has('absence') && !hasCausaleAssenza) {
        event.preventDefault();
        showInlineError('Seleziona almeno una causale di assenza.', document.querySelector('[data-request-detail="absence"]'));
        return;
    }

    if (selectedRequestTypes.has('presence') && !hasCausalePresenza) {
        event.preventDefault();
        showInlineError('Seleziona almeno una causale di presenza.', document.querySelector('[data-request-detail="presence"]'));
        return;
    }

    if (selectedRequestTypes.has('missing') && !hasOmessaTimbratura) {
        event.preventDefault();
        showInlineError('Inserisci almeno un dato dell’omessa timbratura.', document.querySelector('[data-request-detail="missing"]'));
        return;
    }

    event.preventDefault();
    generatePdfButton.disabled = true;
    pdfProgress.classList.add('is-active');
    pdfProgress.setAttribute('aria-hidden', 'false');

    try {
        const response = await fetch(form.action, {
            method: form.method,
            body: new FormData(form),
            headers: {
                Accept: 'application/pdf',
            },
        });

        if (!response.ok) {
            throw new Error('PDF generation failed');
        }

        const blobUrl = URL.createObjectURL(await response.blob());
        const disposition = response.headers.get('Content-Disposition') ?? '';
        const fileName = disposition.match(/filename="?([^";]+)"?/i)?.[1] ?? 'richiesta-assenza.pdf';
        const downloadLink = document.createElement('a');

        downloadLink.href = blobUrl;
        downloadLink.download = fileName;
        downloadLink.click();
        URL.revokeObjectURL(blobUrl);
        requestSummaryTitle.textContent = 'PDF generato correttamente';
        requestSummary.textContent = 'Il download è stato avviato sul tuo dispositivo.';
    } catch {
        showInlineError('Non è stato possibile generare il PDF. Controlla i dati e riprova.');
    } finally {
        generatePdfButton.disabled = false;
        pdfProgress.classList.remove('is-active');
        pdfProgress.setAttribute('aria-hidden', 'true');
    }
});

const createPeriodoRow = () => {
    const row = document.createElement('div');
    row.className = 'periodo-row';
    row.innerHTML = `
        <div class="period-index"></div>
        <label><span>Dalle ore</span><input type="time" name="dalle_ore[]"></label>
        <label><span>Dal giorno</span><input type="date" name="dal_giorno[]"></label>
        <label><span>Alle ore</span><input type="time" name="alle_ore[]"></label>
        <label><span>Al giorno</span><input type="date" name="al_giorno[]"></label>
        <button type="button" class="remove-periodo" aria-label="Rimuovi periodo">×</button>
    `;

    return row;
};

const updatePeriodoIndexes = () => {
    periodiScreen?.querySelectorAll('.periodo-row').forEach((row, index) => {
        row.querySelector('.period-index').textContent = index + 1;
    });
};

const updateCausaliState = () => {
    const selected = Array.from(causaliInputs).filter((input) => input.checked);
    if (causaliCount) {
        causaliCount.textContent = `${selected.length} ${selected.length === 1 ? 'selezionata' : 'selezionate'}`;
    }
    const showOther = selected.some((input) => input.value === 'altro permesso');
    altroPermessoField?.classList.toggle('is-hidden', !showOther);
};

const createPresenzaRow = () => {
    const row = document.createElement('div');
    row.className = 'periodo-row presenza-row';
    row.innerHTML = `
        <div class="period-index"></div>
        <label><span>Dalle ore</span><input type="time" name="presenza_dalle_ore[]"></label>
        <label><span>Alle ore</span><input type="time" name="presenza_alle_ore[]"></label>
        <label><span>Giorno</span><input type="date" name="presenza_giorno[]"></label>
        <label><span>Motivo / N. commessa</span><input type="text" name="presenza_motivo[]" maxlength="255" placeholder="Motivo o commessa"></label>
        <button type="button" class="remove-periodo" aria-label="Rimuovi periodo di presenza">×</button>
    `;

    return row;
};

const updatePresenzaIndexes = () => {
    presenzeScreen?.querySelectorAll('.presenza-row').forEach((row, index) => {
        row.querySelector('.period-index').textContent = index + 1;
    });
};

const updateCausaliPresenzaState = () => {
    const selectedCount = Array.from(causaliPresenzaInputs).filter((input) => input.checked).length;
    if (causaliPresenzaCount) {
        causaliPresenzaCount.textContent = `${selectedCount} ${selectedCount === 1 ? 'selezionata' : 'selezionate'}`;
    }
};

addPeriodoButton?.addEventListener('click', () => {
    periodiScreen?.append(createPeriodoRow());
    updatePeriodoIndexes();
});

periodiScreen?.addEventListener('click', (event) => {
    const button = event.target.closest('.remove-periodo');

    if (!button || periodiScreen.querySelectorAll('.periodo-row').length <= 1) {
        return;
    }

    button.closest('.periodo-row')?.remove();
    updatePeriodoIndexes();
});

causaliInputs.forEach((input) => input.addEventListener('change', updateCausaliState));
causaliPresenzaInputs.forEach((input) => input.addEventListener('change', updateCausaliPresenzaState));

addPresenzaButton?.addEventListener('click', () => {
    presenzeScreen?.append(createPresenzaRow());
    updatePresenzaIndexes();
});

presenzeScreen?.addEventListener('click', (event) => {
    const button = event.target.closest('.remove-periodo');

    if (!button || presenzeScreen.querySelectorAll('.presenza-row').length <= 1) {
        return;
    }

    button.closest('.presenza-row')?.remove();
    updatePresenzaIndexes();
});

updatePeriodoIndexes();
updatePresenzaIndexes();
updateCausaliState();
updateCausaliPresenzaState();
updateRequestFlow();
