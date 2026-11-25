// Category management: AJAX search, pagination, actions and modal.
const categoryModal = document.querySelector("[data-category-modal]");
const categoryForm = document.querySelector("[data-category-form]");
const categoryName = document.querySelector("#category-name");
const categoryMethod = document.querySelector("[data-category-method]");
const categoryTitle = document.querySelector("[data-category-modal-title]");
const categoryResults = document.querySelector("[data-category-results]");
const categoryStoreUrl = categoryForm?.action;
let categorySearchTimer;
let categoryLoadingOverlay = null;
let resolveCategoryConfirmation = null;
const confirmationModal = document.querySelector('[data-category-confirm-modal]');
const confirmationMessage = document.querySelector('[data-category-confirm-message]');

const categoryContext = () => {
    const params = new URLSearchParams(window.location.search);
    return {
        status: params.get("status") || "active",
        search: params.get("search") || "",
        page: params.get("page") || "1",
    };
};

const setCategoryLoading = (loading) => {
    if (!categoryResults) return;

    if (loading) {
        if (categoryLoadingOverlay) return;
        categoryResults.classList.add("relative", "min-h-[160px]");
        categoryResults.setAttribute("aria-busy", "true");

        categoryLoadingOverlay = document.createElement("div");
        categoryLoadingOverlay.className = "absolute inset-0 z-20 flex items-center justify-center rounded-2xl bg-white/75 backdrop-blur-[1px]";
        categoryLoadingOverlay.innerHTML = `
            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-600 shadow-sm">
                <span class="size-5 animate-spin rounded-full border-2 border-blue-100 border-t-blue-600" aria-hidden="true"></span>
                <span>Memuat data...</span>
            </div>`;
        categoryResults.append(categoryLoadingOverlay);
        return;
    }

    categoryLoadingOverlay?.remove();
    categoryLoadingOverlay = null;
    categoryResults.removeAttribute("aria-busy");
};

const showCategoryNotice = (message, isError = false) => {
    let notice = document.querySelector("[data-category-notice]");
    if (!notice) {
        notice = document.createElement("div");
        notice.dataset.categoryNotice = "";
        notice.className = "mb-5 rounded-xl border px-4 py-3 text-sm";
        categoryResults?.before(notice);
    }
    notice.className = isError
        ? "mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
        : "mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800";
    notice.textContent = message;
};

const refreshCategoryResults = async (url = window.location.href, options = {}) => {
    if (!categoryResults) return;
    const target = new URL(url, window.location.origin);
    const focusedSearch = document.activeElement?.matches("[data-category-search]") === true;
    const searchSelection = focusedSearch
        ? [document.activeElement.selectionStart, document.activeElement.selectionEnd]
        : null;

    setCategoryLoading(true);
    let payload;
    try {
        const response = await fetch(target, {
            headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
            ...options,
        });
        payload = await response.json();
        if (!response.ok) throw new Error(payload.message || "Tidak dapat memuat data kategori.");
        categoryResults.innerHTML = payload.html;
    } finally {
        setCategoryLoading(false);
    }

    // Re-focus the newly rendered search field so typing can continue uninterrupted.
    if (focusedSearch) {
        const nextSearch = categoryResults.querySelector("[data-category-search]");
        if (nextSearch) {
            nextSearch.focus({ preventScroll: true });
            if (searchSelection) nextSearch.setSelectionRange(...searchSelection);
        }
    }

    if (options.history !== false) window.history.pushState({}, "", payload.url || target);
    return payload;
};

const ajaxCategoryForm = async (form) => {
    const data = new FormData(form);
    const context = categoryContext();
    data.set("status", context.status);
    data.set("search", context.search);
    data.set("page", context.page);
    setCategoryLoading(true);
    let payload;
    let response;
    try {
        response = await fetch(form.getAttribute("action"), {
            method: "POST",
            body: data,
            headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
        });
        payload = await response.json();
        if (!response.ok && response.status !== 422) {
            throw new Error(payload.message || "Aksi kategori gagal.");
        }
    } finally {
        setCategoryLoading(false);
    }
    if (response.status === 422) {
        if (categoryModal?.open) {
            const error = payload.errors?.nama?.[0] || payload.message;
            let errorNode = categoryForm.querySelector("[data-category-field-error]");
            if (!errorNode) {
                errorNode = document.createElement("p");
                errorNode.dataset.categoryFieldError = "";
                errorNode.className = "mt-2 text-xs text-rose-600";
                categoryName.after(errorNode);
            }
            errorNode.textContent = error;
        } else {
            showCategoryNotice(payload.message || "Aksi tidak dapat diproses.", true);
        }
        return;
    }
    if (payload.html) categoryResults.innerHTML = payload.html;
    if (categoryModal?.open) categoryModal.close();
    showCategoryNotice(payload.message || "Data kategori berhasil diperbarui.");
};

const askCategoryConfirmation = (message, confirmLabel = "Ya, lanjutkan", destructive = false) => {
    if (!confirmationModal) return Promise.resolve(window.confirm(message));

    confirmationMessage.textContent = message;
    const confirmButton = confirmationModal.querySelector("[data-category-confirm-accept]");
    confirmButton.textContent = confirmLabel;
    confirmButton.className = destructive
        ? "rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700 focus:outline-none focus:ring-4 focus:ring-rose-500/15"
        : "rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/15";

    confirmationModal.showModal();
    return new Promise((resolve) => { resolveCategoryConfirmation = resolve; });
};

const closeCategoryConfirmation = (accepted) => {
    if (!confirmationModal?.open) return;
    confirmationModal.close();
    const resolve = resolveCategoryConfirmation;
    resolveCategoryConfirmation = null;
    resolve?.(accepted);
};

const openCategoryModal = (title, action, method, name = "") => {
    if (!categoryModal || !categoryForm) return;
    categoryTitle.textContent = title;
    categoryForm.action = action;
    categoryMethod.value = method;
    categoryName.value = name;
    categoryForm.querySelector("[data-category-field-error]")?.remove();
    categoryModal.showModal();
    categoryName.focus();
};

document.addEventListener("click", async (event) => {
    const createButton = event.target.closest("[data-category-create]");
    if (createButton) {
        openCategoryModal("Tambah kategori", categoryStoreUrl, "POST");
        return;
    }

    const editButton = event.target.closest("[data-category-edit]");
    if (editButton) {
        openCategoryModal("Edit kategori", editButton.dataset.url, "PUT", editButton.dataset.name);
        return;
    }

    if (event.target.closest("[data-category-close]")) {
        categoryModal?.close();
        return;
    }

    if (event.target.closest("[data-category-confirm-cancel]")) {
        closeCategoryConfirmation(false);
        return;
    }

    if (event.target.closest("[data-category-confirm-accept]")) {
        closeCategoryConfirmation(true);
        return;
    }

    const clearButton = event.target.closest("[data-category-search-clear]");
    if (clearButton) {
        const input = document.querySelector("[data-category-search]");
        if (input) {
            input.value = "";
            clearButton.classList.add("hidden");
            const form = input.closest("form");
            const url = new URL(form.action, window.location.origin);
            url.searchParams.set("status", categoryContext().status);
            url.searchParams.delete("search");
            try { await refreshCategoryResults(url); }
            catch (error) { showCategoryNotice(error.message, true); }
        }
        return;
    }

    const pageLink = event.target.closest("[data-category-results] nav a, [data-category-results] a[rel='next'], [data-category-results] a[rel='prev']");
    if (pageLink && pageLink.href) {
        event.preventDefault();
        try { await refreshCategoryResults(pageLink.href); }
        catch (error) { showCategoryNotice(error.message, true); }
        return;
    }

    const bulkButton = event.target.closest("[data-bulk-submit]");
    if (bulkButton) {
        const form = bulkButton.closest("form");
        // The row checkboxes live in the table outside the bulk form and
        // are associated through their HTML form="category-bulk-form" attribute.
        const hasSelectedRows = form
            ? Array.from(form.elements).some((element) =>
                element.matches?.("[data-row-checkbox]") && element.checked
            )
            : false;

        if (!hasSelectedRows) {
            event.preventDefault();
            showCategoryNotice("Pilih minimal satu kategori terlebih dahulu.", true);
            return;
        }
        event.preventDefault();
        const action = bulkButton.dataset.bulkSubmit;
        const confirmation = action === "force_delete"
            ? "Hapus permanen kategori yang dipilih? Tindakan ini tidak dapat dibatalkan."
            : action === "restore" ? "Pulihkan kategori yang dipilih?" : "Pindahkan kategori yang dipilih ke sampah?";
        const accepted = await askCategoryConfirmation(
            confirmation,
            action === "force_delete" ? "Hapus permanen" : action === "restore" ? "Pulihkan" : "Pindahkan ke sampah",
            action !== "restore"
        );
        if (!accepted) return;
        form.querySelector("[data-bulk-action]").value = action;
        try { await ajaxCategoryForm(form); }
        catch (error) { showCategoryNotice(error.message, true); }
        return;
    }

    const selectAll = event.target.closest("[data-select-all]");
    if (selectAll) {
        document.querySelectorAll("[data-row-checkbox]").forEach((checkbox) => {
            checkbox.checked = selectAll.checked;
        });
    }
});

document.addEventListener("change", async (event) => {
    if (event.target.matches("[data-category-status-filter]")) {
        const indexUrl = categoryResults.querySelector("[data-category-search-form]")?.action || window.location.pathname;
        const url = new URL(indexUrl, window.location.origin);
        url.searchParams.set("status", event.target.value);
        url.searchParams.delete("search");
        url.searchParams.delete("page");
        try { await refreshCategoryResults(url); }
        catch (error) { showCategoryNotice(error.message, true); }
        return;
    }

    if (event.target.matches("[data-row-checkbox]")) {
        const boxes = [...document.querySelectorAll("[data-row-checkbox]")];
        const selectAll = document.querySelector("[data-select-all]");
        if (selectAll) selectAll.checked = boxes.length > 0 && boxes.every((box) => box.checked);
    }
});

document.addEventListener("input", (event) => {
    if (!event.target.matches("[data-category-search]")) return;
    const input = event.target;
    const clear = input.parentElement.querySelector("[data-category-search-clear]");
    clear?.classList.toggle("hidden", input.value.length === 0);
    window.clearTimeout(categorySearchTimer);
    categorySearchTimer = window.setTimeout(async () => {
        const form = input.closest("form");
        const url = new URL(form.action, window.location.origin);
        url.searchParams.set("status", form.querySelector('[name="status"]').value);
        if (input.value.trim()) url.searchParams.set("search", input.value.trim());
        else url.searchParams.delete("search");
        url.searchParams.delete("page");
        try { await refreshCategoryResults(url); }
        catch (error) { showCategoryNotice(error.message, true); }
    }, 350);
});

document.addEventListener("submit", async (event) => {
    const searchForm = event.target.closest("[data-category-search-form]");
    if (searchForm) {
        event.preventDefault();
        window.clearTimeout(categorySearchTimer);
        const input = searchForm.querySelector("[data-category-search]");
        const url = new URL(searchForm.action, window.location.origin);
        url.searchParams.set("status", searchForm.querySelector('[name="status"]').value);
        if (input.value.trim()) url.searchParams.set("search", input.value.trim());
        else url.searchParams.delete("search");
        url.searchParams.delete("page");
        try { await refreshCategoryResults(url); }
        catch (error) { showCategoryNotice(error.message, true); }
        return;
    }

    const form = event.target.closest("[data-category-ajax-form], [data-category-form]");
    if (!form) return;
    event.preventDefault();
    if (form.dataset.confirm) {
        const accepted = await askCategoryConfirmation(
            form.dataset.confirm,
            form.action.includes("force-delete") ? "Hapus permanen" :
                form.action.includes("restore") ? "Pulihkan" : "Ya, lanjutkan",
            !form.action.includes("restore")
        );
        if (!accepted) return;
    }
    try { await ajaxCategoryForm(form); }
    catch (error) { showCategoryNotice(error.message, true); }
});

window.addEventListener("popstate", async () => {
    try { await refreshCategoryResults(window.location.href, { history: false }); }
    catch (error) { showCategoryNotice(error.message, true); }
});

if (categoryModal && document.querySelector("[data-category-validation-error]")) {
    categoryModal.showModal();
}
