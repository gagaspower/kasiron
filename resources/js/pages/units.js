// Unit management: AJAX search, pagination, actions and modal.
const unitModal = document.querySelector("[data-unit-modal]");
const unitForm = document.querySelector("[data-unit-form]");
const unitCode = document.querySelector("#unit-code");
const unitName = document.querySelector("#unit-name");
const unitDimension = document.querySelector("#unit-dimension");
const unitMethod = document.querySelector("[data-unit-method]");
const unitTitle = document.querySelector("[data-unit-modal-title]");
const unitResults = document.querySelector("[data-unit-results]");
const unitStoreUrl = unitForm?.action;
let unitSearchTimer;
let unitLoadingOverlay = null;
let resolveUnitConfirmation = null;
const unitConfirmModal = document.querySelector("[data-unit-confirm-modal]");
const unitConfirmMessage = document.querySelector("[data-unit-confirm-message]");

const unitContext = () => {
    const params = new URLSearchParams(window.location.search);
    return { status: params.get("status") || "active", search: params.get("search") || "", page: params.get("page") || "1" };
};

const setUnitLoading = (loading) => {
    if (!unitResults) return;
    if (loading) {
        if (unitLoadingOverlay) return;
        unitResults.classList.add("relative", "min-h-[160px]");
        unitResults.setAttribute("aria-busy", "true");
        unitLoadingOverlay = document.createElement("div");
        unitLoadingOverlay.className = "absolute inset-0 z-20 flex items-center justify-center rounded-2xl bg-white/75 backdrop-blur-[1px]";
        unitLoadingOverlay.innerHTML = '<div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-600 shadow-sm"><span class="size-5 animate-spin rounded-full border-2 border-violet-100 border-t-violet-600"></span><span>Memuat data...</span></div>';
        unitResults.append(unitLoadingOverlay);
    } else {
        unitLoadingOverlay?.remove();
        unitLoadingOverlay = null;
        unitResults.removeAttribute("aria-busy");
    }
};

const showUnitNotice = (message, isError = false) => {
    let notice = document.querySelector("[data-unit-notice]");
    if (!notice) {
        notice = document.createElement("div");
        notice.dataset.unitNotice = "";
        unitResults?.before(notice);
    }
    notice.className = isError ? "mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" : "mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800";
    notice.textContent = message;
};

const refreshUnitResults = async (url = window.location.href, options = {}) => {
    if (!unitResults) return;
    const target = new URL(url, window.location.origin);
    const focused = document.activeElement?.matches("[data-unit-search]") === true;
    const selection = focused ? [document.activeElement.selectionStart, document.activeElement.selectionEnd] : null;
    setUnitLoading(true);
    try {
        const response = await fetch(target, { headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" }, ...options });
        const payload = await response.json();
        if (!response.ok) throw new Error(payload.message || "Tidak dapat memuat data satuan.");
        unitResults.innerHTML = payload.html;
        if (options.history !== false) window.history.pushState({}, "", payload.url || target);
        if (focused) {
            const next = unitResults.querySelector("[data-unit-search]");
            next?.focus({ preventScroll: true });
            if (next && selection) next.setSelectionRange(...selection);
        }
        return payload;
    } finally { setUnitLoading(false); }
};

const ajaxUnitForm = async (form) => {
    const data = new FormData(form);
    const context = unitContext();
    data.set("status", context.status);
    data.set("search", context.search);
    data.set("page", context.page);
    setUnitLoading(true);
    let response;
    let payload;
    try {
        response = await fetch(form.action, { method: "POST", body: data, headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" } });
        payload = await response.json();
        if (!response.ok && response.status !== 422) throw new Error(payload.message || "Aksi satuan gagal.");
    } finally { setUnitLoading(false); }
    if (response.status === 422) {
        if (unitModal?.open) {
            const errors = payload.errors || {};
            const first = Object.values(errors).flat()[0] || payload.message || "Periksa kembali data yang dimasukkan.";
            const errorNode = unitForm.querySelector("[data-unit-field-error]");
            errorNode.textContent = first;
            errorNode.classList.remove("hidden");
        } else showUnitNotice(payload.message || "Aksi tidak dapat diproses.", true);
        return;
    }
    if (payload.html) unitResults.innerHTML = payload.html;
    if (unitModal?.open) unitModal.close();
    showUnitNotice(payload.message || "Data satuan berhasil diperbarui.");
};

const askUnitConfirmation = (message, label = "Ya, lanjutkan", destructive = false) => {
    if (!unitConfirmModal) return Promise.resolve(false);
    unitConfirmMessage.textContent = message;
    const button = unitConfirmModal.querySelector("[data-unit-confirm-accept]");
    button.textContent = label;
    button.className = destructive ? "rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700" : "rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-700";
    unitConfirmModal.showModal();
    return new Promise(resolve => { resolveUnitConfirmation = resolve; });
};

const closeUnitConfirmation = (accepted) => {
    if (!unitConfirmModal?.open) return;
    unitConfirmModal.close();
    const resolve = resolveUnitConfirmation;
    resolveUnitConfirmation = null;
    resolve?.(accepted);
};

const openUnitModal = (title, action, method, values = {}) => {
    unitTitle.textContent = title;
    unitForm.action = action;
    unitMethod.value = method;
    unitCode.value = values.kode || "";
    unitName.value = values.nama || "";
    unitDimension.value = values.dimensi || "jumlah";
    unitForm.querySelector("[data-unit-field-error]")?.classList.add("hidden");
    unitModal.showModal();
    unitCode.focus();
};

document.addEventListener("click", async (event) => {
    if (event.target.closest("[data-unit-create]")) { openUnitModal("Tambah satuan", unitStoreUrl, "POST"); return; }
    const edit = event.target.closest("[data-unit-edit]");
    if (edit) { openUnitModal("Edit satuan", edit.dataset.url, "PUT", { kode: edit.dataset.kode, nama: edit.dataset.name, dimensi: edit.dataset.dimensi }); return; }
    if (event.target.closest("[data-unit-close]")) { unitModal?.close(); return; }
    if (event.target.closest("[data-unit-confirm-cancel]")) { closeUnitConfirmation(false); return; }
    if (event.target.closest("[data-unit-confirm-accept]")) { closeUnitConfirmation(true); return; }

    const clear = event.target.closest("[data-unit-search-clear]");
    if (clear) {
        const form = clear.closest("form");
        const url = new URL(form.action, window.location.origin);
        url.searchParams.set("status", unitContext().status);
        url.searchParams.delete("search");
        try { await refreshUnitResults(url); } catch (error) { showUnitNotice(error.message, true); }
        return;
    }

    const pageLink = event.target.closest("[data-unit-results] nav a, [data-unit-results] a[rel='next'], [data-unit-results] a[rel='prev']");
    if (pageLink?.href) { event.preventDefault(); try { await refreshUnitResults(pageLink.href); } catch (error) { showUnitNotice(error.message, true); } return; }

    const bulk = event.target.closest("[data-unit-bulk-submit]");
    if (bulk) {
        event.preventDefault();
        const form = document.querySelector("#unit-bulk-form");
        const selected = [...document.querySelectorAll("[data-unit-row-checkbox]")].some(box => box.checked);
        if (!selected) { showUnitNotice("Pilih minimal satu satuan terlebih dahulu.", true); return; }
        const action = bulk.dataset.unitBulkSubmit;
        const accepted = await askUnitConfirmation(action === "force_delete" ? "Hapus permanen satuan yang dipilih? Tindakan ini tidak dapat dibatalkan." : action === "restore" ? "Pulihkan satuan yang dipilih?" : "Pindahkan satuan yang dipilih ke sampah?", action === "force_delete" ? "Hapus permanen" : action === "restore" ? "Pulihkan" : "Pindahkan ke sampah", action !== "restore");
        if (!accepted) return;
        form.querySelector("[data-unit-bulk-action]").value = action;
        try { await ajaxUnitForm(form); } catch (error) { showUnitNotice(error.message, true); }
        return;
    }

    if (event.target.closest("[data-unit-select-all]")) {
        const checked = event.target.closest("[data-unit-select-all]").checked;
        document.querySelectorAll("[data-unit-row-checkbox]").forEach(box => { box.checked = checked; });
    }
});

document.addEventListener("change", async (event) => {
    if (event.target.matches("[data-unit-status-filter]")) {
        const action = unitResults.querySelector("[data-unit-search-form]")?.action || window.location.pathname;
        const url = new URL(action, window.location.origin);
        url.searchParams.set("status", event.target.value);
        url.searchParams.delete("search");
        url.searchParams.delete("page");
        try { await refreshUnitResults(url); } catch (error) { showUnitNotice(error.message, true); }
    }
    if (event.target.matches("[data-unit-row-checkbox]")) {
        const boxes = [...document.querySelectorAll("[data-unit-row-checkbox]")];
        const all = document.querySelector("[data-unit-select-all]");
        if (all) all.checked = boxes.length > 0 && boxes.every(box => box.checked);
    }
});

document.addEventListener("input", (event) => {
    if (!event.target.matches("[data-unit-search]")) return;
    const input = event.target;
    input.parentElement.querySelector("[data-unit-search-clear]")?.classList.toggle("hidden", !input.value.length);
    window.clearTimeout(unitSearchTimer);
    unitSearchTimer = window.setTimeout(async () => {
        const form = input.closest("form");
        const url = new URL(form.action, window.location.origin);
        url.searchParams.set("status", form.querySelector('[name="status"]').value);
        if (input.value.trim()) url.searchParams.set("search", input.value.trim()); else url.searchParams.delete("search");
        url.searchParams.delete("page");
        try { await refreshUnitResults(url); } catch (error) { showUnitNotice(error.message, true); }
    }, 350);
});

document.addEventListener("submit", async (event) => {
    const searchForm = event.target.closest("[data-unit-search-form]");
    if (searchForm) {
        event.preventDefault();
        window.clearTimeout(unitSearchTimer);
        const input = searchForm.querySelector("[data-unit-search]");
        const url = new URL(searchForm.action, window.location.origin);
        url.searchParams.set("status", searchForm.querySelector('[name="status"]').value);
        if (input.value.trim()) url.searchParams.set("search", input.value.trim()); else url.searchParams.delete("search");
        url.searchParams.delete("page");
        try { await refreshUnitResults(url); } catch (error) { showUnitNotice(error.message, true); }
        return;
    }
    const form = event.target.closest("[data-unit-ajax-form], [data-unit-form]");
    if (!form) return;
    event.preventDefault();
    if (form.dataset.confirm) {
        const accepted = await askUnitConfirmation(form.dataset.confirm, form.action.includes("force-delete") ? "Hapus permanen" : form.action.includes("restore") ? "Pulihkan" : "Ya, lanjutkan", !form.action.includes("restore"));
        if (!accepted) return;
    }
    try { await ajaxUnitForm(form); } catch (error) { showUnitNotice(error.message, true); }
});

window.addEventListener("popstate", async () => {
    try { await refreshUnitResults(window.location.href, { history: false }); } catch (error) { showUnitNotice(error.message, true); }
});

if (unitModal && document.querySelector("[data-unit-validation-error]")) unitModal.showModal();
