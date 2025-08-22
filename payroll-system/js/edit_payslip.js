document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.hours, .rate').forEach(input => {
        input.addEventListener('input', () => {
            const target = input.dataset.target;
            const hourInput = document.querySelector(`input[name='${input.dataset.hours || input.name}']`);
            const rateInput = document.querySelector(`input[name='${input.dataset.rate || input.name}']`);
            const hours = parseFloat(hourInput.value) || 0;
            const rate = parseFloat(rateInput.value) || 0;
            const total = hours * rate;

            const display = document.getElementById(`${target}_display`);
            if (display) {
                display.textContent = '₱' + total.toFixed(2);
            }
        });
    });
});