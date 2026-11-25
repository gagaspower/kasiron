// Supplier management: AJAX search, pagination, CRUD and confirmation modals.
const supplierModal = document.querySelector("[data-supplier-modal]");
const supplierForm = document.querySelector("[data-supplier-form]");
const supplierName = document.querySelector("#supplier-name");
const supplierPhone = document.querySelector("#supplier-phone");
const supplierAddress = document.querySelector("#supplier-address");
const supplierMethod = document.querySelector("[data-supplier-method]");
const supplierTitle = document.querySelector("[data-supplier-modal-title]");
const supplierResults = document.querySelector("[data-supplier-results]");
const supplierStoreUrl = supplierForm?.action;
const supplierConfirmModal = document.querySelector("[data-supplier-confirm-modal]");
const supplierConfirmMessage = document.querySelector("[data-supplier-confirm-message]");
let supplierSearchTimer;
let supplierLoadingOverlay = null;
let resolveSupplierConfirmation = null;

const supplierContext = () => {
    const params = new URLSearchParams(window.location.search);
    return {
        status: params.get("status") || "active",
        search: params.get("search") || "",
        page: params.get("page") || "1",
    };
};

const setSupplierLoading = (loading) => {
    if (!supplierResults) return;
    if (loading) {
        if (supplierLoadingOverlay) return;
        supplierResults.classList.add("relative", "min-h-[160px]");
        supplierResults.setAttribute("aria-busy", "true");
        supplierLoadingOverlay = document.createElement("div");
        supplierLoadingOverlay.className = "absolute inset-0 z-20 flex items-center justify-center rounded-2xl bg-white/75 backdrop-blur-[1px]";
        supplierLoadingOverlay.innerHTML = '<div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-600 shadow-sm"><span class="size-5 animate-spin rounded-full border-2 border-emerald-100 border-t-emerald-600"></span><span>Memuat data...</span></div>';
        supplierResults.append(supplierLoadingOverlay);
    } else {
        supplierLoadingOverlay?.remove();
        supplierLoadingOverlay = null;
        supplierResults.removeAttribute("aria-busy");
    }
};

const showSupplierNotice = (message, isError = false) => {
    let notice = document.querySelector("[data-supplier-notice]");
    if (!notice) {
        notice = document.createElement("div");
        notice.dataset.supplierNotice = "";
        supplierResults?.before(notice);
    }
    notice.className = isError
        ? "mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
        : "mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800";
    notice.textContent = message;
};

const refreshSupplierResults = async (url = window.location.href, options = {}) => {
    if (!supplierResults) return;
    const target = new URL(url, window.location.origin);
    const focused = document.activeElement?.matches("[data-supplier-search]") === true;
    const selection = focused ? [document.activeElement.selectionStart, document.activeElement.selectionEnd] : null;
    setSupplierLoading(true);
    try {
        const response = await fetch(target, {
            headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
            ...options,
        });
        const payload = await response.json();
        if (!response.ok) throw new Error(payload.message || "Tidak dapat memuat data supplier.");
        supplierResults.innerHTML = payload.html;
        if (options.history !== false) window.history.pushState({}, "", payload.url || target);
        if (focused) {
            const next = supplierResults.querySelector("[data-supplier-search]");
            next?.focus({ preventScroll: true });
            if (next && selection) next.setSelectionRange(...selection);
        }
        return payload;
    } finally {
        setSupplierLoading(false);
    }
};

const ajaxSupplierForm = async (form) => {
    const data = new FormData(form);
    const context = supplierContext();
    data.set("status", context.status);
    data.set("search", context.search);
    data.set("page", context.page);
    setSupplierLoading(true);
    let response;
    let payload;
    try {
        response = await fetch(form.getAttribute("action"), {
            method: "POST",
            body: data,
            headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
        });
        payload = await response.json();
        if (!response.ok && response.status !== 422) throw new Error(payload.message || "Aksi supplier gagal.");
    } finally {
        setSupplierLoading(false);
    }

    if (response.status === 422) {
        if (supplierModal?.open) {
            const errors = payload.errors || {};
            const first = Object.values(errors).flat()[0] || payload.message || "Periksa kembali data yang dimasukkan.";
            const errorNode = supplierForm.querySelector("[data-supplier-field-error]");
            errorNode.textContent = first;
            errorNode.classList.remove("hidden");
        } else {
            showSupplierNotice(payload.message || "Aksi tidak dapat diproses.", true);
        }
        return;
    }

    if (payload.html) supplierResults.innerHTML = payload.html;
    if (supplierModal?.open) supplierModal.close();
    showSupplierNotice(payload.message || "Data supplier berhasil diperbarui.");
};

const askSupplierConfirmation = (message, label = "Ya, lanjutkan", destructive = false) => {
    if (!supplierConfirmModal) return Promise.resolve(false);
    supplierConfirmMessage.textContent = message;
    const button = supplierConfirmModal.querySelector("[data-supplier-confirm-accept]");
    button.textContent = label;
    button.className = destructive
        ? "rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700"
        : "rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700";
    supplierConfirmModal.showModal();
    return new Promise(resolve => { resolveSupplierConfirmation = resolve; });
};

const closeSupplierConfirmation = (accepted) => {
    if (!supplierConfirmModal?.open) return;
    supplierConfirmModal.close();
    const resolve = resolveSupplierConfirmation;
    resolveSupplierConfirmation = null;
    resolve?.(accepted);
};

const openSupplierModal = (title, action, method, values = {}) => {
    if (!supplierModal || !supplierForm) return;
    supplierTitle.textContent = title;
    supplierForm.action = action;
    supplierMethod.value = method;
    supplierName.value = values.nama || "";
    supplierPhone.value = values.telepon || "";
    supplierAddress.value = values.alamat || "";
    const errorNode = supplierForm.querySelector("[data-supplier-field-error]");
    errorNode?.classList.add("hidden");
    supplierModal.showModal();
    supplierName.focus();
};

document.addEventListener("click", async (event) => {
    if (event.target.closest("[data-supplier-create]")) {
        openSupplierModal("Tambah supplier", supplierStoreUrl, "POST");
        return;
    }

    const edit = event.target.closest("[data-supplier-edit]");
    if (edit) {
        openSupplierModal("Edit supplier", edit.dataset.url, "PUT", {
            nama: edit.dataset.name,
            telepon: edit.dataset.phone,
            alamat: edit.dataset.address,
        });
        return;
    }

    if (event.target.closest("[data-supplier-close]")) { supplierModal?.close(); return; }
    if (event.target.closest("[data-supplier-confirm-cancel]")) { closeSupplierConfirmation(false); return; }
    if (event.target.closest("[data-supplier-confirm-accept]")) { closeSupplierConfirmation(true); return; }

    const clear = event.target.closest("[data-supplier-search-clear]");
    if (clear) {
        const form = clear.closest("form");
        const url = new URL(form.action, window.location.origin);
        url.searchParams.set("status", supplierContext().status);
        url.searchParams.delete("search");
        url.searchParams.delete("page");
        try { await refreshSupplierResults(url); }
        catch (error) { showSupplierNotice(error.message, true); }
        return;
    }

    const pageLink = event.target.closest("[data-supplier-results] nav a, [data-supplier-results] a[rel='next'], [data-supplier-results] a[rel='prev']");
    if (pageLink?.href) {
        event.preventDefault();
        try { await refreshSupplierResults(pageLink.href); }
        catch (error) { showSupplierNotice(error.message, true); }
        return;
    }

    const bulkButton = event.target.closest("[data-supplier-bulk-submit]");
    if (bulkButton) {
        event.preventDefault();
        const form = document.querySelector("#supplier-bulk-form");
        const selected = [...document.querySelectorAll("[data-supplier-row-checkbox]")].some(box => box.checked);
        if (!selected) { showSupplierNotice("Pilih minimal satu supplier terlebih dahulu.", true); return; }
        const action = bulkButton.dataset.supplierBulkSubmit;
        const accepted = await askSupplierConfirmation(
            action === "force_delete" ? "Hapus permanen supplier yang dipilih? Tindakan ini tidak dapat dibatalkan." : action === "restore" ? "Pulihkan supplier yang dipilih?" : "Pindahkan supplier yang dipilih ke sampah?",
            action === "force_delete" ? "Hapus permanen" : action === "restore" ? "Pulihkan" : "Pindahkan ke sampah",
            action !== "restore"
        );
        if (!accepted) return;
        form.querySelector("[data-supplier-bulk-action]").value = action;
        try { await ajaxSupplierForm(form); }
        catch (error) { showSupplierNotice(error.message, true); }
        return;
    }

    const selectAll = event.target.closest("[data-supplier-select-all]");
    if (selectAll) {
        document.querySelectorAll("[data-supplier-row-checkbox]").forEach(box => { box.checked = selectAll.checked; });
    }
});

document.addEventListener("change", async (event) => {
    if (event.target.matches("[data-supplier-status-filter]")) {
        const action = supplierResults.querySelector("[data-supplier-search-form]")?.action || window.location.pathname;
        const url = new URL(action, window.location.origin);
        url.searchParams.set("status", event.target.value);
        url.searchParams.delete("search");
        url.searchParams.delete("page");
        try { await refreshSupplierResults(url); }
        catch (error) { showSupplierNotice(error.message, true); }
        return;
    }

    if (event.target.matches("[data-supplier-row-checkbox]")) {
        const boxes = [...document.querySelectorAll("[data-supplier-row-checkbox]")];
        const all = document.querySelector("[data-supplier-select-all]");
        if (all) all.checked = boxes.length > 0 && boxes.every(box => box.checked);
    }
});

document.addEventListener("input", (event) => {
    if (!event.target.matches("[data-supplier-search]")) return;
    const input = event.target;
    input.parentElement.querySelector("[data-supplier-search-clear]")?.classList.toggle("hidden", !input.value.length);
    window.clearTimeout(supplierSearchTimer);
    supplierSearchTimer = window.setTimeout(async () => {
        const form = input.closest("form");
        const url = new URL(form.action, window.location.origin);
        url.searchParams.set("status", form.querySelector('[name="status"]').value);
        if (input.value.trim()) url.searchParams.set("search", input.value.trim());
        else url.searchParams.delete("search");
        url.searchParams.delete("page");
        try { await refreshSupplierResults(url); }
        catch (error) { showSupplierNotice(error.message, true); }
    }, 350);
});

document.addEventListener("submit", async (event) => {
    const searchForm = event.target.closest("[data-supplier-search-form]");
    if (searchForm) {
        event.preventDefault();
        window.clearTimeout(supplierSearchTimer);
        const input = searchForm.querySelector("[data-supplier-search]");
        const url = new URL(searchForm.action, window.location.origin);
        url.searchParams.set("status", searchForm.querySelector('[name="status"]').value);
        if (input.value.trim()) url.searchParams.set("search", input.value.trim());
        else url.searchParams.delete("search");
        url.searchParams.delete("page");
        try { await refreshSupplierResults(url); }
        catch (error) { showSupplierNotice(error.message, true); }
        return;
    }

    const form = event.target.closest("[data-supplier-ajax-form], [data-supplier-form]");
    if (!form) return;
    event.preventDefault();
    if (form.dataset.confirm) {
        const accepted = await askSupplierConfirmation(
            form.dataset.confirm,
            form.getAttribute("action").includes("force-delete") ? "Hapus permanen" :
                form.getAttribute("action").includes("restore") ? "Pulihkan" : "Ya, lanjutkan",
            !form.getAttribute("action").includes("restore")
        );
        if (!accepted) return;
    }
    try { await ajaxSupplierForm(form); }
    catch (error) { showSupplierNotice(error.message, true); }
});

window.addEventListener("popstate", async () => {
    try { await refreshSupplierResults(window.location.href, { history: false }); }
    catch (error) { showSupplierNotice(error.message, true); }
});

if (supplierModal && document.querySelector("[data-supplier-validation-error]")) supplierModal.showModal();
