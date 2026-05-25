/**
 * admin.js
 * Lógica do painel de administração: modal de edição de equipamento.
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ── Modal de edição de equipamento ── */
    window.openEditEquipment = function (data) {
        document.getElementById('edit-eq-id').value        = data.id;
        document.getElementById('edit-eq-name').value      = data.name;
        document.getElementById('edit-eq-category').value  = data.category  || '';
        document.getElementById('edit-eq-quantity').value  = data.quantity;
        document.getElementById('edit-eq-available').value = data.available;
        document.getElementById('edit-eq-status').value    = data.status;
        document.getElementById('edit-eq-notes').value     = data.notes     || '';
        document.getElementById('modal-edit-equipment').style.display = 'flex';
    };

});
