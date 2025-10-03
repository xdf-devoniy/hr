(function () {
    const importInput = document.getElementById('transactionImport');
    if (importInput) {
        importInput.addEventListener('change', function () {
            const fileName = this.files?.[0]?.name || 'Choose CSV file';
            const label = document.querySelector('[for="transactionImport"]');
            if (label) {
                label.textContent = fileName;
            }
        });
    }
})();
