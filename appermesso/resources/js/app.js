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
const errorSummary = document.querySelector('[data-error-summary]');

errorSummary?.focus();

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

privacyAcceptButton?.addEventListener('click', () => {
    if (!privacyConsentCheckbox.checked) {
        return;
    }

    privacyConsentValue.value = '1';
    privacyConsent.remove();
    document.body.classList.remove('privacy-locked');
});

window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        window.location.reload();
    }
});

form?.addEventListener('submit', async (event) => {
    const hasCausaleAssenza = Array.from(causaliInputs).some((input) => input.checked);
    const hasCausalePresenza = Array.from(causaliPresenzaInputs).some((input) => input.checked);
    const hasOmessaTimbratura = Array.from(form.querySelectorAll('[name^="omessa_"]'))
        .some((input) => input.value.trim() !== '');

    if (!hasCausaleAssenza && !hasCausalePresenza && !hasOmessaTimbratura) {
        event.preventDefault();
        window.alert('Compila almeno una causale di assenza, una causale di presenza o la sezione omessa timbratura prima di generare il PDF.');
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
    causaliCount.textContent = `${selected.length} ${selected.length === 1 ? 'selezionata' : 'selezionate'}`;
    const showOther = selected.some((input) => input.value === 'altro permesso');
    altroPermessoField.classList.toggle('is-hidden', !showOther);
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
    causaliPresenzaCount.textContent = `${selectedCount} ${selectedCount === 1 ? 'selezionata' : 'selezionate'}`;
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
