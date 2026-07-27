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

form?.addEventListener('submit', (event) => {
    const hasCausaleAssenza = Array.from(causaliInputs).some((input) => input.checked);
    const hasCausalePresenza = Array.from(causaliPresenzaInputs).some((input) => input.checked);
    const hasOmessaTimbratura = Array.from(form.querySelectorAll('[name^="omessa_"]'))
        .some((input) => input.value.trim() !== '');

    if (!hasCausaleAssenza && !hasCausalePresenza && !hasOmessaTimbratura) {
        event.preventDefault();
        window.alert('Compila almeno una causale di assenza, una causale di presenza o la sezione omessa timbratura prima di generare il PDF.');
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
