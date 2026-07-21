const form = document.querySelector('#permesso-form');
const periodiScreen = document.querySelector('#periodi-screen');
const addPeriodoButton = document.querySelector('#add-periodo');
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
