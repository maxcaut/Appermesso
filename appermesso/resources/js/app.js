const form = document.querySelector('#permesso-form');
const periodiScreen = document.querySelector('#periodi-screen');
const addPeriodoButton = document.querySelector('#add-periodo');
const generatePdfButton = document.querySelector('#generate-pdf');
const pdfPeriodi = document.querySelector('#pdf-periodi');
const causaliInputs = document.querySelectorAll('input[name="causale[]"]');
const causaliCount = document.querySelector('#causali-count');
const altroPermessoField = document.querySelector('#altro-permesso-field');

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

const getValues = (name) => Array.from(form?.querySelectorAll(`[name="${name}"]`) ?? []).map((input) => input.value.trim());

const setPdfField = (name, value) => {
    document.querySelectorAll(`[data-pdf-field="${name}"]`).forEach((field) => {
        field.textContent = value ?? '';
    });
};

const setPdfChecks = (values) => {
    document.querySelectorAll('[data-pdf-check]').forEach((field) => {
        field.classList.toggle('checked-box', values.includes(field.dataset.pdfCheck));
    });
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

const renderPdfPeriodi = () => {
    const dalleOre = getValues('dalle_ore[]');
    const dalGiorno = getValues('dal_giorno[]');
    const alleOre = getValues('alle_ore[]');
    const alGiorno = getValues('al_giorno[]');
    const rows = Math.max(3, dalleOre.length);

    pdfPeriodi.innerHTML = '';

    for (let index = 0; index < rows; index += 1) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>Dalle ore ${dalleOre[index] ?? ''}</td>
            <td>del giorno ${dalGiorno[index] ?? ''}</td>
            <td>alle ore ${alleOre[index] ?? ''}</td>
            <td>del giorno ${alGiorno[index] ?? ''}</td>
        `;
        pdfPeriodi.append(row);
    }
};

const fillPdfTemplate = () => {
    const data = new FormData(form);
    const causali = data.getAll('causale[]');

    ['nome', 'cognome', 'matricola', 'centro_costo', 'livello', 'qualifica', 'ente'].forEach((name) => {
        setPdfField(name, data.get(name));
    });

    setPdfField('altro_permesso', causali.includes('altro permesso') ? data.get('altro_permesso') : '');
    setPdfChecks(causali);
    renderPdfPeriodi();
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

generatePdfButton?.addEventListener('click', () => {
    fillPdfTemplate();
    window.print();
});
