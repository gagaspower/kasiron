// Product master: AJAX listing actions and dynamic sale-unit rows.
const results = document.querySelector("[data-product-results]");
let searchTimer;
let loadingOverlay = null;
let resolveConfirmation = null;
const confirmModal = document.querySelector("[data-product-confirm-modal]");

const context = () => {
    const params = new URLSearchParams(window.location.search);
    return { status: params.get("status") || "active", search: params.get("search") || "", page: params.get("page") || "1" };
};

const notice = (message, error = false) => {
    let node = document.querySelector("[data-product-notice]");
    if (!node) {
        node = document.createElement("div");
        node.dataset.productNotice = "";
        results?.before(node);
    }
    node.className = error
        ? "mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
        : "mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800";
    node.textContent = message;
};

const setLoading = (loading) => {
    if (!results) return;
    if (loading) {
        if (loadingOverlay) return;
        results.classList.add("relative", "min-h-[160px]");
        results.setAttribute("aria-busy", "true");
        loadingOverlay = document.createElement("div");
        loadingOverlay.className = "absolute inset-0 z-20 flex items-center justify-center rounded-2xl bg-white/75 backdrop-blur-[1px]";
        loadingOverlay.innerHTML = '<div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-600 shadow-sm"><span class="size-5 animate-spin rounded-full border-2 border-teal-100 border-t-teal-600"></span><span>Memuat data...</span></div>';
        results.append(loadingOverlay);
    } else {
        loadingOverlay?.remove();
        loadingOverlay = null;
        results.removeAttribute("aria-busy");
    }
};

const refreshResults = async (url, history = true) => {
    if (!results) return;
    const target = new URL(url, window.location.origin);
    const activeSearch = document.activeElement?.matches("[data-product-search]");
    const selection = activeSearch ? [document.activeElement.selectionStart, document.activeElement.selectionEnd] : null;
    setLoading(true);
    try {
        const response = await fetch(target, { headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" } });
        const payload = await response.json();
        if (!response.ok) throw new Error(payload.message || "Tidak dapat memuat data produk.");
        results.innerHTML = payload.html;
        if (history) window.history.pushState({}, "", payload.url || target);
        if (activeSearch) {
            const input = results.querySelector("[data-product-search]");
            input?.focus({ preventScroll: true });
            if (input && selection) input.setSelectionRange(...selection);
        }
        return payload;
    } finally {
        setLoading(false);
    }
};

const askConfirmation = (message, destructive = false) => {
    if (!confirmModal) return Promise.resolve(false);
    confirmModal.querySelector("[data-product-confirm-message]").textContent = message;
    const accept = confirmModal.querySelector("[data-product-confirm-accept]");
    accept.className = destructive
        ? "rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700"
        : "rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700";
    confirmModal.showModal();
    return new Promise(resolve => { resolveConfirmation = resolve; });
};

const closeConfirmation = (accepted) => {
    if (!confirmModal?.open) return;
    confirmModal.close();
    const resolve = resolveConfirmation;
    resolveConfirmation = null;
    resolve?.(accepted);
};

const submitAjaxForm = async (form) => {
    const data = new FormData(form);
    const current = context();
    data.set("status", current.status);
    data.set("search", current.search);
    data.set("page", current.page);
    setLoading(true);
    try {
        const response = await fetch(form.action, {
            method: "POST",
            body: data,
            headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
        });
        const payload = await response.json();
        if (!response.ok) throw new Error(payload.message || "Aksi produk gagal.");
        if (payload.html) results.innerHTML = payload.html;
        notice(payload.message || "Data produk berhasil diperbarui.");
    } finally {
        setLoading(false);
    }
};

document.addEventListener("click", async (event) => {
    if (event.target.closest("[data-product-confirm-cancel]")) { closeConfirmation(false); return; }
    if (event.target.closest("[data-product-confirm-accept]")) { closeConfirmation(true); return; }

    const clear = event.target.closest("[data-product-search-clear]");
    if (clear) {
        const form = clear.closest("form");
        const url = new URL(form.action, window.location.origin);
        url.searchParams.set("status", context().status);
        url.searchParams.delete("search");
        url.searchParams.delete("page");
        try { await refreshResults(url); } catch (error) { notice(error.message, true); }
        return;
    }

    const pageLink = event.target.closest("[data-product-results] nav a, [data-product-results] a[rel='next'], [data-product-results] a[rel='prev']");
    if (pageLink?.href) {
        event.preventDefault();
        try { await refreshResults(pageLink.href); } catch (error) { notice(error.message, true); }
        return;
    }

    const bulkButton = event.target.closest("[data-product-bulk-submit]");
    if (bulkButton) {
        event.preventDefault();
        const form = document.querySelector("#product-bulk-form");
        if (![...document.querySelectorAll("[data-product-row-checkbox]")].some(box => box.checked)) {
            notice("Pilih minimal satu produk terlebih dahulu.", true);
            return;
        }
        const action = bulkButton.dataset.productBulkSubmit;
        const accepted = await askConfirmation(
            action === "force_delete" ? "Hapus permanen produk yang dipilih? Produk dengan riwayat mutasi stok tidak dapat dihapus permanen." : action === "restore" ? "Pulihkan produk yang dipilih?" : "Pindahkan produk yang dipilih ke sampah?",
            action !== "restore",
        );
        if (!accepted) return;
        form.querySelector("[data-product-bulk-action]").value = action;
        try { await submitAjaxForm(form); } catch (error) { notice(error.message, true); }
        return;
    }

    const formButton = event.target.closest("form[data-product-ajax-form] button[type='submit']");
    if (formButton) {
        const form = formButton.closest("form");
        if (form.dataset.confirm) {
            event.preventDefault();
            if (!await askConfirmation(form.dataset.confirm, true)) return;
            try { await submitAjaxForm(form); } catch (error) { notice(error.message, true); }
        }
        return;
    }

    if (event.target.closest("[data-product-select-all]")) {
        const checked = event.target.closest("[data-product-select-all]").checked;
        document.querySelectorAll("[data-product-row-checkbox]").forEach(box => { box.checked = checked; });
    }
});

document.addEventListener("change", async (event) => {
    if (event.target.matches("[data-product-status-filter]")) {
        const form = results.querySelector("[data-product-search-form]");
        const url = new URL(form.action, window.location.origin);
        url.searchParams.set("status", event.target.value);
        url.searchParams.delete("search");
        url.searchParams.delete("page");
        try { await refreshResults(url); } catch (error) { notice(error.message, true); }
    }
    if (event.target.matches("[data-product-row-checkbox]")) {
        const boxes = [...document.querySelectorAll("[data-product-row-checkbox]")];
        const all = document.querySelector("[data-product-select-all]");
        if (all) all.checked = boxes.length > 0 && boxes.every(box => box.checked);
    }
});

document.addEventListener("input", (event) => {
    if (!event.target.matches("[data-product-search]")) return;
    const input = event.target;
    input.parentElement.querySelector("[data-product-search-clear]")?.classList.toggle("hidden", !input.value.length);
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(async () => {
        const form = input.closest("form");
        const url = new URL(form.action, window.location.origin);
        url.searchParams.set("status", form.querySelector('[name="status"]').value);
        if (input.value.trim()) url.searchParams.set("search", input.value.trim());
        else url.searchParams.delete("search");
        url.searchParams.delete("page");
        try { await refreshResults(url); } catch (error) { notice(error.message, true); }
    }, 350);
});

document.addEventListener("submit", async (event) => {
    const searchForm = event.target.closest("[data-product-search-form]");
    if (searchForm) {
        event.preventDefault();
        window.clearTimeout(searchTimer);
        const input = searchForm.querySelector("[data-product-search]");
        const url = new URL(searchForm.action, window.location.origin);
        url.searchParams.set("status", searchForm.querySelector('[name="status"]').value);
        if (input.value.trim()) url.searchParams.set("search", input.value.trim());
        else url.searchParams.delete("search");
        url.searchParams.delete("page");
        try { await refreshResults(url); } catch (error) { notice(error.message, true); }
    }
});

window.addEventListener("popstate", async () => {
    try { await refreshResults(window.location.href, false); } catch (error) { notice(error.message, true); }
});

// Product form: add/remove sale units, keep names indexed, and enforce one primary unit.
const productForm = document.querySelector("[data-product-form]");
if (productForm) {
    const rowsContainer = productForm.querySelector("[data-product-units]");
    const template = productForm.querySelector("[data-product-unit-template]");
    const baseSelect = productForm.querySelector("[data-product-base-unit]");

    const rows = () => [...rowsContainer.querySelectorAll("[data-product-unit-row]")];

    const reindex = () => {
        rows().forEach((row, index) => {
            row.querySelector("[data-product-unit-number]").textContent = index + 1;
            row.querySelectorAll("[data-field]").forEach(field => {
                field.name = `satuan_penjualan[${index}][${field.dataset.field}]`;
            });
            const id = row.querySelector('input[type="hidden"][name$="[id]"]');
            if (id) id.name = `satuan_penjualan[${index}][id]`;
        });
    };

    const applyBaseUnit = () => {
        const baseId = baseSelect.value;
        const baseRow = rows().find(row => row.querySelector("[data-product-unit-select]").value === baseId);
        rows().forEach(row => {
            const select = row.querySelector("[data-product-unit-select]");
            const conversion = row.querySelector("[data-product-conversion]");
            const isBase = Boolean(baseId && select.value === baseId);
            conversion.readOnly = isBase;
            if (isBase) conversion.value = "1";
        });
        if (baseId && !baseRow) {
            const row = template.content.firstElementChild.cloneNode(true);
            rowsContainer.append(row);
            row.querySelector("[data-product-unit-select]").value = baseId;
            row.querySelector("[data-product-conversion]").value = "1";
            row.querySelector("[data-product-primary]").checked = rows().length === 1;
            reindex();
        }
    };

    const addRow = () => {
        const row = template.content.firstElementChild.cloneNode(true);
        rowsContainer.append(row);
        reindex();
        row.querySelector("[data-product-unit-select]").focus();
    };

    rowsContainer.addEventListener("click", (event) => {
        if (!event.target.closest("[data-product-remove-unit]")) return;
        const row = event.target.closest("[data-product-unit-row]");
        const wasBase = row.querySelector("[data-product-unit-select]").value === baseSelect.value;
        if (rows().length <= 1) {
            notice("Produk harus memiliki minimal satu satuan penjualan.", true);
            return;
        }
        row.remove();
        reindex();
        if (wasBase) {
            baseSelect.value = "";
            rows().forEach(item => {
                const conversion = item.querySelector("[data-product-conversion]");
                conversion.readOnly = false;
            });
        }
    });

    rowsContainer.addEventListener("change", (event) => {
        if (event.target.matches("[data-product-unit-select]")) applyBaseUnit();
        if (event.target.matches("[data-product-primary]") && event.target.checked) {
            rows().forEach(row => {
                const checkbox = row.querySelector("[data-product-primary]");
                if (checkbox !== event.target) checkbox.checked = false;
            });
        }
    });

    baseSelect.addEventListener("change", applyBaseUnit);
    productForm.querySelector("[data-product-add-unit]")?.addEventListener("click", addRow);

    if (baseSelect.value) applyBaseUnit();
    reindex();
}


// Searchable category picker (server-side, limited results).
const categoryPicker = document.querySelector("[data-category-picker]");
if (categoryPicker) {
    const searchInput = categoryPicker.querySelector("[data-category-search]");
    const valueInput = categoryPicker.querySelector("[data-category-value]");
    const optionsBox = categoryPicker.querySelector("[data-category-options]");
    const clearButton = categoryPicker.querySelector("[data-category-clear]");
    let categoryTimer;
    let categoryRequest = 0;

    const closeCategoryOptions = () => {
        optionsBox.classList.add("hidden");
        searchInput.setAttribute("aria-expanded", "false");
    };

    const chooseCategory = (item) => {
        valueInput.value = item.id;
        searchInput.value = item.text;
        clearButton.classList.remove("hidden");
        clearButton.classList.add("flex");
        closeCategoryOptions();
    };

    const renderCategoryOptions = (items) => {
        optionsBox.replaceChildren();
        if (!items.length) {
            const empty = document.createElement("p");
            empty.className = "px-3 py-3 text-sm text-slate-500";
            empty.textContent = "Kategori tidak ditemukan.";
            optionsBox.append(empty);
        } else {
            items.forEach(item => {
                const option = document.createElement("button");
                option.type = "button";
                option.role = "option";
                option.className = "block w-full rounded-lg px-3 py-2.5 text-left text-sm text-slate-700 hover:bg-teal-50 hover:text-teal-800";
                option.textContent = item.text;
                option.addEventListener("click", () => chooseCategory(item));
                optionsBox.append(option);
            });
        }
        optionsBox.classList.remove("hidden");
        searchInput.setAttribute("aria-expanded", "true");
    };

    const searchCategories = async (query = "") => {
        const requestId = ++categoryRequest;
        const url = new URL(categoryPicker.dataset.categoryUrl, window.location.origin);
        if (query.trim()) url.searchParams.set("q", query.trim());
        try {
            const response = await fetch(url, { headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" } });
            if (!response.ok) throw new Error("Gagal memuat kategori.");
            const payload = await response.json();
            if (requestId === categoryRequest) renderCategoryOptions(payload.results || []);
        } catch (error) {
            if (requestId === categoryRequest) renderCategoryOptions([]);
        }
    };

    searchInput.addEventListener("focus", () => searchCategories(searchInput.value));
    searchInput.addEventListener("input", () => {
        valueInput.value = "";
        clearButton.classList.add("hidden");
        clearButton.classList.remove("flex");
        window.clearTimeout(categoryTimer);
        categoryTimer = window.setTimeout(() => searchCategories(searchInput.value), 250);
    });
    clearButton.addEventListener("click", () => {
        valueInput.value = "";
        searchInput.value = "";
        clearButton.classList.add("hidden");
        clearButton.classList.remove("flex");
        searchCategories("");
        searchInput.focus();
    });
    document.addEventListener("click", event => {
        if (!categoryPicker.contains(event.target)) closeCategoryOptions();
    });
    if (valueInput.value) {
        clearButton.classList.remove("hidden");
        clearButton.classList.add("flex");
    }
}
