document.addEventListener('DOMContentLoaded', function () {
    const qtyInput = document.getElementById('QtyMasuk');
    const hargaInput = document.getElementById('HargaSatuan');
    const subtotalInput = document.getElementById('SubTotal');

    function calculateSubtotal() {
        const qty = parseFloat(qtyInput.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        const subtotal = qty * harga;
        subtotalInput.value = subtotal.toFixed(0); // tanpa koma desimal
    }

    qtyInput.addEventListener('input', calculateSubtotal);
    hargaInput.addEventListener('input', calculateSubtotal);
});
