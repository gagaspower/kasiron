const form = document.querySelector("[data-purchase-form]");
const rowsContainer = document.querySelector("[data-purchase-rows]");
const rowTemplate = document.querySelector("[data-purchase-row-template]");
const confirmDialog = document.querySelector("[data-purchase-confirm]");
let resolveConfirmation = null;

const products = form ? JSON.parse(form.dataset.products || "[]") : [];
const money = value => new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(Number(value) || 0);
const field = (row, name) => row.querySelector(`[data-field="${name}"]`);

const productById = id => products.find(product => product.id === id);

const updateUnits = (row, selectedUnit = "") => {
    const product = productById(field(row, "produk_id").value);
    const unitSelect = field(row, "satuan_produk_id");
    unitSelect.innerHTML = '<option value="">Pilih satuan</option>';
    (product?.units || []).forEach(unit => {
        const option = document.createElement("option");
        option.value = unit.id;
        option.textContent = `${unit.nama} (1 = ${unit.konversi} satuan dasar)`;
        option.dataset.conversion = unit.konversi;
        option.selected = unit.id === selectedUnit;
        unitSelect.append(option);
    });
};

const renumber = () => {
    rowsContainer.querySelectorAll("[data-purchase-row]").forEach((row, index) => {
        row.querySelector("[data-purchase-row-number]").textContent = index + 1;
        ["produk_id", "satuan_produk_id", "jumlah", "harga_beli", "diskon", "catatan"].forEach(name => {
            field(row, name).name = `detail[${index}][${name}]`;
        });
    });
};

const calculate = () => {
    let subtotal = 0;
    rowsContainer.querySelectorAll("[data-purchase-row]").forEach(row => {
        const qty = Number(field(row, "jumlah").value) || 0;
        const price = Number(field(row, "harga_beli").value) || 0;
        const discount = Number(field(row, "diskon").value) || 0;
        const amount = Math.max(0, qty * price - discount);
        row.querySelector("[data-purchase-row-subtotal]").textContent = `Rp ${money(amount)}`;
        subtotal += amount;
    });
    const discount = Number(form.querySelector("[data-purchase-discount]").value) || 0;
    const extra = Number(form.querySelector("[data-purchase-extra]").value) || 0;
    form.querySelector("[data-purchase-subtotal]").textContent = `Rp ${money(subtotal)}`;
    form.querySelector("[data-purchase-total]").textContent = `Rp ${money(subtotal - discount + extra)}`;
};

const addRow = (values = {}) => {
    const fragment = rowTemplate.content.cloneNode(true);
    const row = fragment.querySelector("[data-purchase-row]");
    const productSelect = field(row, "produk_id");
    products.forEach(product => {
        const option = document.createElement("option");
        option.value = product.id;
        option.textContent = product.sku ? `${product.nama} · ${product.sku}` : product.nama;
        productSelect.append(option);
    });

    productSelect.value = values.produk_id || "";
    updateUnits(row, values.satuan_produk_id || "");
    ["jumlah", "harga_beli", "diskon", "catatan"].forEach(name => {
        if (values[name] !== undefined && values[name] !== null) field(row, name).value = values[name];
    });
    rowsContainer.append(fragment);
    renumber();
    calculate();
};

if (form) {
    const initialRows = Array.isArray(window.purchaseInitialRows) ? window.purchaseInitialRows : [];
    if (initialRows.length) initialRows.forEach(addRow);
    else addRow();
    form.querySelector("[data-purchase-add-row]").addEventListener("click", () => addRow());
    form.querySelector("[data-purchase-discount]").addEventListener("input", calculate);
    form.querySelector("[data-purchase-extra]").addEventListener("input", calculate);

    rowsContainer.addEventListener("change", event => {
        if (event.target.matches('[data-field="produk_id"]')) {
            updateUnits(event.target.closest("[data-purchase-row]"));
            calculate();
        }
    });
    rowsContainer.addEventListener("input", event => {
        if (event.target.matches('[data-field="jumlah"], [data-field="harga_beli"], [data-field="diskon"]')) calculate();
    });
    rowsContainer.addEventListener("click", event => {
        const remove = event.target.closest("[data-purchase-remove-row]");
        if (!remove) return;
        const row = remove.closest("[data-purchase-row]");
        if (rowsContainer.querySelectorAll("[data-purchase-row]").length === 1) {
            ["produk_id", "satuan_produk_id", "catatan"].forEach(name => field(row, name).value = "");
            field(row, "jumlah").value = "1";
            field(row, "harga_beli").value = "0";
            field(row, "diskon").value = "0";
        } else row.remove();
        renumber();
        calculate();
    });
}

const askConfirmation = message => {
    if (!confirmDialog) return Promise.resolve(false);
    confirmDialog.querySelector("[data-purchase-confirm-message]").textContent = message;
    confirmDialog.showModal();
    return new Promise(resolve => { resolveConfirmation = resolve; });
};

const finishConfirmation = accepted => {
    if (!confirmDialog?.open) return;
    confirmDialog.close();
    const resolve = resolveConfirmation;
    resolveConfirmation = null;
    resolve?.(accepted);
};

document.addEventListener("click", event => {
    if (event.target.closest("[data-purchase-confirm-cancel]")) finishConfirmation(false);
    if (event.target.closest("[data-purchase-confirm-accept]")) finishConfirmation(true);
});

document.querySelector("[data-purchase-approve-form]")?.addEventListener("submit", async event => {
    event.preventDefault();
    if (await askConfirmation("Setujui transaksi pembelian ini? Setelah disetujui, draft tidak dapat diedit. Persetujuan ini belum menambah stok.")) event.target.submit();
});

document.querySelector("[data-purchase-delete-form]")?.addEventListener("submit", async event => {
    event.preventDefault();
    if (await askConfirmation("Hapus draft pembelian ini secara permanen? Tindakan ini tidak dapat dibatalkan.")) event.target.submit();
});
