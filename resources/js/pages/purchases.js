const form = document.querySelector("[data-purchase-form]");
const rowsContainer = document.querySelector("[data-purchase-rows]");
const rowTemplate = document.querySelector("[data-purchase-row-template]");
const confirmDialog = document.querySelector("[data-purchase-confirm]");
let resolveConfirmation = null;

const money = value => new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(Number(value) || 0);
const field = (row, name) => row.querySelector(`[data-field="${name}"]`);

const parseMoney = value => {
    const digits = String(value ?? "").replace(/[^0-9]/g, "");
    return digits === "" ? "" : String(Number(digits));
};

const formatMoneyInput = value => {
    const raw = parseMoney(value);
    return raw === "" ? "" : money(raw);
};

const bindMoneyInput = (displayInput, rawInput) => {
    const syncRaw = () => {
        rawInput.value = parseMoney(displayInput.value);
    };

    displayInput.addEventListener("focus", () => {
        const raw = parseMoney(displayInput.value);
        displayInput.value = raw === "0" ? "" : raw;
        displayInput.select();
    });

    displayInput.addEventListener("input", () => {
        const cursor = displayInput.selectionStart ?? displayInput.value.length;
        const digitsBeforeCursor = displayInput.value.slice(0, cursor).replace(/[^0-9]/g, "").length;
        const digits = displayInput.value.replace(/[^0-9]/g, "");
        rawInput.value = digits === "" ? "" : String(Number(digits));
        displayInput.value = digits === "" ? "" : money(digits);

        let nextCursor = 0;
        let countedDigits = 0;
        while (nextCursor < displayInput.value.length && countedDigits < digitsBeforeCursor) {
            if (/[0-9]/.test(displayInput.value[nextCursor])) countedDigits++;
            nextCursor++;
        }
        displayInput.setSelectionRange(nextCursor, nextCursor);
    });

    displayInput.addEventListener("blur", () => {
        syncRaw();
        displayInput.value = formatMoneyInput(rawInput.value || "0");
    });

    displayInput.value = formatMoneyInput(rawInput.value || displayInput.value || "0");
    syncRaw();
};

const bindMoneyInputs = root => {
    root.querySelectorAll("[data-money-display]").forEach(displayInput => {
        if (displayInput.dataset.moneyBound === "true") return;
        const rawInput = displayInput.previousElementSibling;
        if (!rawInput || rawInput.type !== "hidden") return;
        displayInput.dataset.moneyBound = "true";
        bindMoneyInput(displayInput, rawInput);
    });
};

const pickerRequests = new WeakMap();

const closePicker = (box, input) => {
    box.classList.add("hidden");
    input.setAttribute("aria-expanded", "false");
};

const renderPickerOptions = (box, input, items, emptyText, choose) => {
    box.replaceChildren();
    if (!items.length) {
        const empty = document.createElement("p");
        empty.className = "px-3 py-3 text-sm text-slate-500";
        empty.textContent = emptyText;
        box.append(empty);
    } else {
        items.forEach(item => {
            const option = document.createElement("button");
            option.type = "button";
            option.role = "option";
            option.className = "block w-full rounded-lg px-3 py-2.5 text-left text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-800";
            option.textContent = item.text;
            option.addEventListener("click", () => choose(item));
            box.append(option);
        });
    }
    box.classList.remove("hidden");
    input.setAttribute("aria-expanded", "true");
};

const fetchOptions = async (picker, query, selectedId = "") => {
    const requestId = (pickerRequests.get(picker) || 0) + 1;
    pickerRequests.set(picker, requestId);
    const url = new URL(picker.dataset.optionsUrl, window.location.origin);
    if (selectedId) url.searchParams.set("id", selectedId);
    else if (query.trim()) url.searchParams.set("q", query.trim());
    const response = await fetch(url, { headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" } });
    if (!response.ok) throw new Error("Gagal memuat pilihan.");
    const payload = await response.json();
    return pickerRequests.get(picker) === requestId ? (payload.results || []) : null;
};

const bindSearchPicker = ({ picker, input, valueInput, box, clearButton, emptyText, onChoose, onClear }) => {
    let timer;
    const search = async query => {
        try {
            const items = await fetchOptions(picker, query);
            if (items) renderPickerOptions(box, input, items, emptyText, onChoose);
        } catch {
            renderPickerOptions(box, input, [], emptyText, onChoose);
        }
    };
    input.addEventListener("focus", () => search(input.value));
    input.addEventListener("input", () => {
        valueInput.value = "";
        clearButton.classList.add("hidden");
        clearButton.classList.remove("flex");
        onClear?.();
        window.clearTimeout(timer);
        timer = window.setTimeout(() => search(input.value), 250);
    });
    clearButton.addEventListener("click", () => {
        valueInput.value = "";
        input.value = "";
        clearButton.classList.add("hidden");
        clearButton.classList.remove("flex");
        onClear?.();
        search("");
        input.focus();
    });
    document.addEventListener("click", event => {
        if (!picker.contains(event.target)) closePicker(box, input);
    });
    if (valueInput.value) {
        clearButton.classList.remove("hidden");
        clearButton.classList.add("flex");
    }
    return { close: () => closePicker(box, input), search };
};

const supplierPicker = document.querySelector("[data-purchase-supplier-picker]");
if (supplierPicker) {
    const input = supplierPicker.querySelector("[data-purchase-supplier-search]");
    const valueInput = supplierPicker.querySelector("[data-purchase-supplier-value]");
    const box = supplierPicker.querySelector("[data-purchase-supplier-options]");
    const clearButton = supplierPicker.querySelector("[data-purchase-supplier-clear]");
    bindSearchPicker({
        picker: supplierPicker, input, valueInput, box, clearButton,
        emptyText: "Supplier tidak ditemukan.",
        onChoose: item => {
            valueInput.value = item.id;
            input.value = item.text;
            clearButton.classList.remove("hidden");
            clearButton.classList.add("flex");
            closePicker(box, input);
        },
    });
}

const updateUnits = (row, units = [], selectedUnit = "") => {
    const unitSelect = field(row, "satuan_produk_id");
    unitSelect.innerHTML = '<option value="">Pilih satuan</option>';
    units.forEach(unit => {
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
    rowsContainer.append(fragment);

    const picker = row.querySelector("[data-purchase-product-picker]");
    const input = picker.querySelector("[data-purchase-product-search]");
    const valueInput = field(row, "produk_id");
    const box = picker.querySelector("[data-purchase-product-options]");
    const clearButton = picker.querySelector("[data-purchase-product-clear]");
    const chooseProduct = product => {
        valueInput.value = product.id;
        input.value = product.text;
        row.dataset.units = JSON.stringify(product.units || []);
        updateUnits(row, product.units || []);
        clearButton.classList.remove("hidden");
        clearButton.classList.add("flex");
        closePicker(box, input);
    };
    const pickerApi = bindSearchPicker({
        picker, input, valueInput, box, clearButton,
        emptyText: "Produk tidak ditemukan.",
        onChoose: chooseProduct,
        onClear: () => {
            row.dataset.units = "[]";
            updateUnits(row, []);
        },
    });
    row._productPicker = { search: pickerApi.search };
    if (values.produk_id) {
        fetchOptions(picker, "", values.produk_id).then(items => {
            const selected = items?.find(item => item.id === values.produk_id);
            if (selected) chooseProduct(selected);
            else {
                valueInput.value = values.produk_id;
                input.value = values.nama_produk || "Produk tidak aktif";
                updateUnits(row, values.units || [], values.satuan_produk_id || "");
            }
            if (values.satuan_produk_id) field(row, "satuan_produk_id").value = values.satuan_produk_id;
        }).catch(() => {});
    }
    ["jumlah", "catatan"].forEach(name => {
        if (values[name] !== undefined && values[name] !== null) field(row, name).value = values[name];
    });
    ["harga_beli", "diskon"].forEach(name => {
        if (values[name] !== undefined && values[name] !== null) field(row, name).value = values[name];
    });
    bindMoneyInputs(row);
    renumber();
    calculate();
};

if (form) {
    const initialRows = Array.isArray(window.purchaseInitialRows) ? window.purchaseInitialRows : [];
    if (initialRows.length) initialRows.forEach(addRow);
    else addRow();
    form.querySelector("[data-purchase-add-row]").addEventListener("click", () => addRow());
    bindMoneyInputs(form);
    form.querySelectorAll("[data-money-display]").forEach(input => input.addEventListener("input", calculate));
    form.querySelector("[data-purchase-discount]").addEventListener("input", calculate);
    form.querySelector("[data-purchase-extra]").addEventListener("input", calculate);

    rowsContainer.addEventListener("change", event => {
        if (event.target.matches('[data-field="satuan_produk_id"]')) calculate();
    });
    rowsContainer.addEventListener("input", event => {
        if (event.target.matches('[data-field="jumlah"], [data-money-display]')) calculate();
    });
    rowsContainer.addEventListener("click", event => {
        const remove = event.target.closest("[data-purchase-remove-row]");
        if (!remove) return;
        const row = remove.closest("[data-purchase-row]");
        if (rowsContainer.querySelectorAll("[data-purchase-row]").length === 1) {
            ["produk_id", "satuan_produk_id", "catatan"].forEach(name => field(row, name).value = "");
            row.querySelector("[data-purchase-product-search]").value = "";
            row.querySelector("[data-purchase-product-clear]").classList.add("hidden");
            row.dataset.units = "[]";
            updateUnits(row, []);
            field(row, "jumlah").value = "1";
            field(row, "harga_beli").value = "0";
            field(row, "diskon").value = "0";
            row.querySelectorAll("[data-money-display]").forEach(input => {
                input.value = "0";
                input.dispatchEvent(new Event("blur"));
            });
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
