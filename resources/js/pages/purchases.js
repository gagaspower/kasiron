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

const bindSupplierPicker = root => {
    const supplierPicker = root.querySelector("[data-purchase-supplier-picker]");
    if (!supplierPicker || supplierPicker.dataset.bound === "true") return;
    supplierPicker.dataset.bound = "true";
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
};
bindSupplierPicker(document);
bindMoneyInputs(document);

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

const askConfirmation = (message, title = "Konfirmasi tindakan", acceptLabel = "Ya, lanjutkan") => {
    if (!confirmDialog) return Promise.resolve(false);
    confirmDialog.querySelector("[data-purchase-confirm-message]").textContent = message;
    const titleElement = confirmDialog.querySelector("[data-purchase-confirm-title]");
    const acceptButton = confirmDialog.querySelector("[data-purchase-confirm-accept]");
    if (titleElement) titleElement.textContent = title;
    if (acceptButton) acceptButton.textContent = acceptLabel;
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



document.querySelectorAll("[data-purchase-confirm-form]").forEach(confirmForm => {
    confirmForm.addEventListener("submit", async event => {
        event.preventDefault();
        const accepted = await askConfirmation(
            confirmForm.dataset.confirmMessage || "Lanjutkan tindakan ini?",
            confirmForm.dataset.confirmTitle || "Konfirmasi tindakan",
            confirmForm.dataset.confirmAccept || "Ya, lanjutkan"
        );
        if (accepted) confirmForm.submit();
    });
});


// Purchase transaction listing: AJAX search, filter, pagination, and loading feedback.
const purchaseResults = document.querySelector("[data-purchase-results]");
if (purchaseResults) {
    let purchaseSearchTimer;
    let purchaseRequest = 0;
    let purchaseLoadingOverlay = null;

    const setPurchaseLoading = loading => {
        if (loading) {
            if (purchaseLoadingOverlay) return;
            purchaseResults.classList.add("relative", "min-h-[160px]");
            purchaseResults.setAttribute("aria-busy", "true");
            purchaseLoadingOverlay = document.createElement("div");
            purchaseLoadingOverlay.className = "absolute inset-0 z-20 flex items-center justify-center rounded-2xl bg-white/75 backdrop-blur-[1px]";
            purchaseLoadingOverlay.innerHTML = '<div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-600 shadow-sm"><span class="size-5 animate-spin rounded-full border-2 border-emerald-100 border-t-emerald-600"></span><span>Memuat data...</span></div>';
            purchaseResults.append(purchaseLoadingOverlay);
        } else {
            purchaseLoadingOverlay?.remove();
            purchaseLoadingOverlay = null;
            purchaseResults.removeAttribute("aria-busy");
        }
    };

    const showPurchaseNotice = message => {
        let notice = document.querySelector("[data-purchase-list-notice]");
        if (!notice) {
            notice = document.createElement("div");
            notice.dataset.purchaseListNotice = "";
            notice.className = "mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700";
            purchaseResults.before(notice);
        }
        notice.textContent = message;
    };

    const refreshPurchaseResults = async (url, pushHistory = true) => {
        const requestId = ++purchaseRequest;
        const target = new URL(url, window.location.origin);
        setPurchaseLoading(true);
        try {
            const response = await fetch(target, {
                headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" }
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || "Tidak dapat memuat daftar pembelian.");
            if (requestId !== purchaseRequest) return;
            purchaseResults.innerHTML = payload.html;
            if (pushHistory) window.history.pushState({}, "", payload.url || target);
            document.querySelector("[data-purchase-list-notice]")?.remove();
        } finally {
            if (requestId === purchaseRequest) setPurchaseLoading(false);
        }
    };

    const purchaseFilterUrl = form => {
        const url = new URL(form.action, window.location.origin);
        for (const [key, value] of new FormData(form).entries()) {
            if (String(value).trim()) url.searchParams.set(key, value);
            else url.searchParams.delete(key);
        }
        url.searchParams.delete("page");
        return url;
    };

    document.addEventListener("submit", async event => {
        const form = event.target.closest("[data-purchase-search-form]");
        if (!form) return;
        event.preventDefault();
        window.clearTimeout(purchaseSearchTimer);
        try { await refreshPurchaseResults(purchaseFilterUrl(form)); }
        catch (error) { showPurchaseNotice(error.message); setPurchaseLoading(false); }
    });

    document.addEventListener("input", event => {
        if (!event.target.matches("[data-purchase-search]")) return;
        window.clearTimeout(purchaseSearchTimer);
        purchaseSearchTimer = window.setTimeout(async () => {
            const form = purchaseResults.querySelector("[data-purchase-search-form]");
            if (!form) return;
            try { await refreshPurchaseResults(purchaseFilterUrl(form)); }
            catch (error) { showPurchaseNotice(error.message); setPurchaseLoading(false); }
        }, 350);
    });

    document.addEventListener("click", async event => {
        const pageLink = event.target.closest("[data-purchase-results] nav a, [data-purchase-results] a[rel='next'], [data-purchase-results] a[rel='prev']");
        const resetLink = event.target.closest("[data-purchase-reset]");
        const link = pageLink || resetLink;
        if (!link?.href) return;
        event.preventDefault();
        window.clearTimeout(purchaseSearchTimer);
        try { await refreshPurchaseResults(link.href); }
        catch (error) { showPurchaseNotice(error.message); setPurchaseLoading(false); }
    });

    window.addEventListener("popstate", async () => {
        try { await refreshPurchaseResults(window.location.href, false); }
        catch (error) { showPurchaseNotice(error.message); setPurchaseLoading(false); }
    });
}


// Receipt listing: AJAX search, date/supplier filters, pagination, and loading feedback.
const receiptResults = document.querySelector("[data-receipt-results]");
if (receiptResults) {
    let receiptSearchTimer;
    let receiptRequest = 0;
    let receiptLoadingOverlay = null;

    const setReceiptLoading = loading => {
        if (loading) {
            if (receiptLoadingOverlay) return;
            receiptResults.classList.add("relative", "min-h-[160px]");
            receiptResults.setAttribute("aria-busy", "true");
            receiptLoadingOverlay = document.createElement("div");
            receiptLoadingOverlay.className = "absolute inset-0 z-20 flex items-center justify-center rounded-2xl bg-white/75 backdrop-blur-[1px]";
            receiptLoadingOverlay.innerHTML = '<div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-600 shadow-sm"><span class="size-5 animate-spin rounded-full border-2 border-emerald-100 border-t-emerald-600"></span><span>Memuat data...</span></div>';
            receiptResults.append(receiptLoadingOverlay);
        } else {
            receiptLoadingOverlay?.remove();
            receiptLoadingOverlay = null;
            receiptResults.removeAttribute("aria-busy");
        }
    };

    const showReceiptNotice = message => {
        let notice = document.querySelector("[data-receipt-list-notice]");
        if (!notice) {
            notice = document.createElement("div");
            notice.dataset.receiptListNotice = "";
            notice.className = "mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700";
            receiptResults.before(notice);
        }
        notice.textContent = message;
    };

    const refreshReceiptResults = async (url, pushHistory = true) => {
        const requestId = ++receiptRequest;
        const target = new URL(url, window.location.origin);
        setReceiptLoading(true);
        try {
            const response = await fetch(target, { headers: { "X-Requested-With": "XMLHttpRequest", Accept: "text/html" } });
            if (!response.ok) throw new Error("Tidak dapat memuat daftar penerimaan barang.");
            const html = await response.text();
            if (requestId !== receiptRequest) return;
            const parsed = new DOMParser().parseFromString(html, "text/html");
            const updated = parsed.querySelector("[data-receipt-results]");
            if (!updated) throw new Error("Struktur daftar penerimaan tidak ditemukan.");
            receiptResults.innerHTML = updated.innerHTML;
            bindSupplierPicker(receiptResults);
            if (pushHistory) window.history.pushState({}, "", target);
            document.querySelector("[data-receipt-list-notice]")?.remove();
        } finally {
            if (requestId === receiptRequest) setReceiptLoading(false);
        }
    };

    const receiptFormUrl = form => {
        const url = new URL(form.action, window.location.origin);
        for (const [key, value] of new FormData(form).entries()) {
            if (String(value).trim()) url.searchParams.set(key, value);
            else url.searchParams.delete(key);
        }
        url.searchParams.delete("menunggu_page");
        url.searchParams.delete("riwayat_page");
        return url;
    };

    document.addEventListener("submit", async event => {
        const form = event.target.closest("[data-receipt-search-form], [data-receipt-filter-form]");
        if (!form) return;
        event.preventDefault();
        window.clearTimeout(receiptSearchTimer);
        try { await refreshReceiptResults(receiptFormUrl(form)); }
        catch (error) { showReceiptNotice(error.message); setReceiptLoading(false); }
    });

    document.addEventListener("input", event => {
        if (!event.target.matches("[data-receipt-search]")) return;
        window.clearTimeout(receiptSearchTimer);
        receiptSearchTimer = window.setTimeout(async () => {
            const form = receiptResults.querySelector("[data-receipt-search-form]");
            if (!form) return;
            try { await refreshReceiptResults(receiptFormUrl(form)); }
            catch (error) { showReceiptNotice(error.message); setReceiptLoading(false); }
        }, 350);
    });

    document.addEventListener("click", async event => {
        const link = event.target.closest("[data-receipt-reset], [data-receipt-results] nav a, [data-receipt-results] a[rel='next'], [data-receipt-results] a[rel='prev']");
        if (!link?.href) return;
        event.preventDefault();
        window.clearTimeout(receiptSearchTimer);
        try { await refreshReceiptResults(link.href); }
        catch (error) { showReceiptNotice(error.message); setReceiptLoading(false); }
    });

    window.addEventListener("popstate", async () => {
        try { await refreshReceiptResults(window.location.href, false); }
        catch (error) { showReceiptNotice(error.message); setReceiptLoading(false); }
    });
}


// Supplier balances: AJAX filter, pagination, settlement, and deposit usage.
const balanceResults = document.querySelector("[data-balance-results]");
if (balanceResults) {
    let balanceRequest = 0;
    let balanceOverlay = null;

    const setBalanceLoading = loading => {
        if (loading) {
            if (balanceOverlay) return;
            balanceResults.classList.add("relative", "min-h-[180px]");
            balanceResults.setAttribute("aria-busy", "true");
            balanceOverlay = document.createElement("div");
            balanceOverlay.className = "absolute inset-0 z-30 flex items-center justify-center rounded-2xl bg-white/75 backdrop-blur-[1px]";
            balanceOverlay.innerHTML = '<div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-600 shadow-sm"><span class="size-5 animate-spin rounded-full border-2 border-emerald-100 border-t-emerald-600"></span><span>Memuat data...</span></div>';
            balanceResults.append(balanceOverlay);
        } else {
            balanceOverlay?.remove();
            balanceOverlay = null;
            balanceResults.removeAttribute("aria-busy");
        }
    };

    const showBalanceError = message => {
        let notice = balanceResults.querySelector("[data-balance-error]");
        if (!notice) {
            notice = document.createElement("div");
            notice.dataset.balanceError = "";
            notice.className = "mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700";
            balanceResults.prepend(notice);
        }
        notice.textContent = message;
    };

    const refreshBalances = async (url, options = {}) => {
        const requestId = ++balanceRequest;
        setBalanceLoading(true);
        try {
            const response = await fetch(url, {
                method: options.method || "GET",
                body: options.body || undefined,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "text/html",
                    ...(options.method === "POST" ? { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "" } : {})
                },
                credentials: "same-origin"
            });
            const html = await response.text();
            if (!response.ok) {
                const parsedError = new DOMParser().parseFromString(html, "text/html");
                const message = parsedError.querySelector("[data-balance-validation]")?.textContent?.trim();
                throw new Error(message || "Permintaan tidak berhasil diproses.");
            }
            const parsed = new DOMParser().parseFromString(html, "text/html");
            const updated = parsed.querySelector("[data-balance-results]");
            if (!updated) throw new Error("Data saldo tidak ditemukan pada respons server.");
            if (requestId !== balanceRequest) return;
            balanceResults.innerHTML = updated.innerHTML;
            if (options.pushHistory) window.history.pushState({}, "", response.url || url);
            document.querySelector("[data-balance-error]")?.remove();
        } finally {
            if (requestId === balanceRequest) setBalanceLoading(false);
        }
    };

    document.addEventListener("submit", async event => {
        const filter = event.target.closest("[data-balance-filter]");
        const action = event.target.closest("[data-balance-ajax]");
        if (!filter && !action) return;
        event.preventDefault();
        const form = filter || action;
        const submit = form.querySelector('button[type="submit"], button:not([type])');
        if (submit) submit.disabled = true;
        try {
            const url = form.action || window.location.href;
            const options = filter
                ? { pushHistory: true }
                : { method: "POST", body: new FormData(form), pushHistory: false };
            const target = filter ? new URL(url, window.location.origin) : url;
            if (filter) {
                const query = new URLSearchParams(new FormData(form));
                target.search = query.toString();
            }
            await refreshBalances(target, options);
        } catch (error) {
            showBalanceError(error.message || "Terjadi kesalahan saat memproses data.");
            setBalanceLoading(false);
        } finally {
            if (submit) submit.disabled = false;
        }
    });

    document.addEventListener("click", async event => {
        const link = event.target.closest("[data-balance-reset], [data-balance-results] nav a, [data-balance-results] a[rel='next'], [data-balance-results] a[rel='prev']");
        if (!link?.href) return;
        event.preventDefault();
        try { await refreshBalances(link.href, { pushHistory: true }); }
        catch (error) { showBalanceError(error.message || "Gagal memuat halaman."); setBalanceLoading(false); }
    });

    window.addEventListener("popstate", async () => {
        try { await refreshBalances(window.location.href); }
        catch (error) { showBalanceError(error.message || "Gagal memuat halaman."); setBalanceLoading(false); }
    });
}


// Supplier payment creation: AJAX invoice loading and Rupiah display-only input.
const paymentInvoiceFilter = document.querySelector("[data-payment-invoice-filter]");
const paymentInvoiceContent = document.querySelector("[data-payment-invoice-content]");
if (paymentInvoiceFilter && paymentInvoiceContent) {
    let invoiceRequest = 0;
    const showButton = paymentInvoiceFilter.querySelector("[data-payment-show-invoices]");
    const buttonLabel = showButton?.querySelector("[data-payment-button-label]");
    const buttonSpinner = showButton?.querySelector("[data-payment-button-spinner]");

    const loadPaymentInvoices = async (url, pushHistory = true) => {
        const requestId = ++invoiceRequest;
        showButton.disabled = true;
        buttonLabel.textContent = "Memuat nota...";
        buttonSpinner.classList.remove("hidden");
        paymentInvoiceContent.classList.add("relative", "min-h-[120px]");
        const overlay = document.createElement("div");
        overlay.className = "absolute inset-0 z-20 flex items-center justify-center rounded-2xl bg-white/75";
        overlay.innerHTML = '<div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-600 shadow-sm"><span class="size-5 animate-spin rounded-full border-2 border-emerald-100 border-t-emerald-600"></span><span>Memuat daftar nota...</span></div>';
        paymentInvoiceContent.append(overlay);
        try {
            const response = await fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest", Accept: "text/html" },
                credentials: "same-origin"
            });
            if (!response.ok) throw new Error("Gagal memuat nota supplier.");
            const html = await response.text();
            const parsed = new DOMParser().parseFromString(html, "text/html");
            const updated = parsed.querySelector("[data-payment-invoice-content]");
            if (!updated) throw new Error("Daftar nota tidak ditemukan.");
            if (requestId !== invoiceRequest) return;
            paymentInvoiceContent.innerHTML = updated.innerHTML;
            if (pushHistory) window.history.pushState({}, "", response.url || url);
            bindMoneyInputs(paymentInvoiceContent);
            bindPaymentTotalInput(paymentInvoiceContent);
        } catch (error) {
            let notice = paymentInvoiceContent.querySelector("[data-payment-load-error]");
            if (!notice) {
                notice = document.createElement("div");
                notice.dataset.paymentLoadError = "";
                notice.className = "rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700";
                paymentInvoiceContent.prepend(notice);
            }
            notice.textContent = error.message;
        } finally {
            if (requestId === invoiceRequest) {
                overlay.remove();
                paymentInvoiceContent.classList.remove("relative", "min-h-[120px]");
                showButton.disabled = false;
                buttonLabel.textContent = "Tampilkan Nota";
                buttonSpinner.classList.add("hidden");
            }
        }
    };

    paymentInvoiceFilter.addEventListener("submit", event => {
        event.preventDefault();
        const supplierId = paymentInvoiceFilter.querySelector("[data-purchase-supplier-value]")?.value;
        if (!supplierId) {
            paymentInvoiceFilter.querySelector("[data-purchase-supplier-search]")?.focus();
            return;
        }
        const url = new URL(paymentInvoiceFilter.action, window.location.origin);
        url.searchParams.set("supplier_id", supplierId);
        loadPaymentInvoices(url);
    });

    window.addEventListener("popstate", () => {
        if (window.location.pathname === paymentInvoiceFilter.action || window.location.pathname === new URL(paymentInvoiceFilter.action).pathname) {
            loadPaymentInvoices(window.location.href, false);
        }
    });
}

function bindPaymentTotalInput(root = document) {
    const display = root.querySelector("[data-payment-total-display]");
    const raw = root.querySelector("[data-payment-total-raw]");
    if (!display || !raw || display.dataset.bound === "true") return;
    display.dataset.bound = "true";
    const digitsOnly = value => String(value || "").replace(/[^0-9]/g, "");
    const format = value => {
        const digits = digitsOnly(value);
        return digits ? new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(Number(digits)) : "";
    };
    display.addEventListener("focus", () => {
        if (raw.value === "0" || display.value === "0") display.value = "";
        else display.value = digitsOnly(display.value);
        display.select();
    });
    display.addEventListener("input", () => {
        const cursor = display.selectionStart ?? display.value.length;
        const before = digitsOnly(display.value.slice(0, cursor)).length;
        const digits = digitsOnly(display.value);
        raw.value = digits;
        display.value = format(digits);
        let position = 0;
        let count = 0;
        while (position < display.value.length && count < before) {
            if (/\d/.test(display.value[position])) count++;
            position++;
        }
        display.setSelectionRange(position, position);
    });
    display.addEventListener("blur", () => {
        raw.value = digitsOnly(display.value);
        display.value = format(raw.value || "0");
    });
    raw.value = digitsOnly(raw.value || display.value);
    display.value = format(raw.value);
}
bindPaymentTotalInput();


// Supplier payment listing: AJAX filters, reset, pagination, and loading state.
const paymentListResults = document.querySelector("[data-payment-list-results]");
if (paymentListResults) {
    let paymentListRequest = 0;
    let paymentListOverlay = null;

    const setPaymentListLoading = loading => {
        if (loading) {
            if (paymentListOverlay) return;
            paymentListResults.classList.add("relative", "min-h-[160px]");
            paymentListResults.setAttribute("aria-busy", "true");
            paymentListOverlay = document.createElement("div");
            paymentListOverlay.className = "absolute inset-0 z-30 flex items-center justify-center rounded-2xl bg-white/75 backdrop-blur-[1px]";
            paymentListOverlay.innerHTML = '<div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-600 shadow-sm"><span class="size-5 animate-spin rounded-full border-2 border-emerald-100 border-t-emerald-600"></span><span>Memuat data...</span></div>';
            paymentListResults.append(paymentListOverlay);
        } else {
            paymentListOverlay?.remove();
            paymentListOverlay = null;
            paymentListResults.removeAttribute("aria-busy");
            paymentListResults.classList.remove("relative", "min-h-[160px]");
        }
    };

    const showPaymentListError = message => {
        let notice = document.querySelector("[data-payment-list-error]");
        if (!notice) {
            notice = document.createElement("div");
            notice.dataset.paymentListError = "";
            notice.className = "mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700";
            paymentListResults.before(notice);
        }
        notice.textContent = message;
    };

    const refreshPaymentList = async (url, pushHistory = true) => {
        const requestId = ++paymentListRequest;
        const target = new URL(url, window.location.origin);
        setPaymentListLoading(true);
        try {
            const response = await fetch(target, {
                headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
                credentials: "same-origin"
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || "Tidak dapat memuat daftar pembayaran.");
            if (requestId !== paymentListRequest) return;
            const parsed = new DOMParser().parseFromString(payload.html, "text/html");
            const updated = parsed.querySelector("[data-payment-list-results]");
            if (!updated) throw new Error("Struktur daftar pembayaran tidak ditemukan.");
            paymentListResults.innerHTML = updated.innerHTML;
            bindSupplierPicker(paymentListResults);
            if (pushHistory) window.history.pushState({}, "", payload.url || target);
            document.querySelector("[data-payment-list-error]")?.remove();
        } finally {
            if (requestId === paymentListRequest) setPaymentListLoading(false);
        }
    };

    const paymentListFilterUrl = form => {
        const url = new URL(form.action, window.location.origin);
        for (const [key, value] of new FormData(form).entries()) {
            if (String(value).trim()) url.searchParams.set(key, value);
            else url.searchParams.delete(key);
        }
        url.searchParams.delete("page");
        return url;
    };

    document.addEventListener("submit", async event => {
        const filter = event.target.closest("[data-payment-list-filter]");
        if (!filter) return;
        event.preventDefault();
        try { await refreshPaymentList(paymentListFilterUrl(filter)); }
        catch (error) { showPaymentListError(error.message || "Gagal memuat daftar pembayaran."); setPaymentListLoading(false); }
    });

    document.addEventListener("click", async event => {
        const reset = event.target.closest("[data-payment-list-reset]");
        const page = event.target.closest("[data-payment-list-results] nav a, [data-payment-list-results] a[rel='next'], [data-payment-list-results] a[rel='prev']");
        const link = reset || page;
        if (!link?.href) return;
        event.preventDefault();
        try { await refreshPaymentList(link.href); }
        catch (error) { showPaymentListError(error.message || "Gagal memuat daftar pembayaran."); setPaymentListLoading(false); }
    });

    window.addEventListener("popstate", async () => {
        const resetUrl = paymentListResults.querySelector("[data-payment-list-reset]")?.href;
        if (resetUrl && window.location.pathname !== new URL(resetUrl).pathname) return;
        try { await refreshPaymentList(window.location.href, false); }
        catch (error) { showPaymentListError(error.message || "Gagal memuat daftar pembayaran."); setPaymentListLoading(false); }
    });
}


// Payment date range: adjust the end date only when the start date is
// today or later, and earlier than the current end date.
let paymentEndDateManuallyChanged = false;

document.addEventListener("change", event => {
    if (event.target.matches("[data-payment-date-from]")) {
        const dateFrom = event.target;
        const dateTo = document.querySelector("[data-payment-date-to]");
        const today = new Date();
        const todayLocal = [
            today.getFullYear(),
            String(today.getMonth() + 1).padStart(2, "0"),
            String(today.getDate()).padStart(2, "0")
        ].join("-");

        if (
            dateTo &&
            !paymentEndDateManuallyChanged &&
            dateFrom.value >= todayLocal &&
            dateTo.value &&
            dateFrom.value < dateTo.value
        ) {
            dateTo.value = dateFrom.value;
        }
    }

    if (event.target.matches("[data-payment-date-to]")) {
        paymentEndDateManuallyChanged = true;
    }
});
