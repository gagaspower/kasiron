import { registerSW } from "virtual:pwa-register";

registerSW({ immediate: true });

const searchInput = document.querySelector("[data-transaction-search]");
const transactionRows = [
    ...document.querySelectorAll("[data-transaction-row]"),
];
const emptyState = document.querySelector("[data-transaction-empty]");

if (searchInput && transactionRows.length > 0 && emptyState) {
    searchInput.addEventListener("input", () => {
        const query = searchInput.value.trim().toLocaleLowerCase();
        let visibleRows = 0;

        transactionRows.forEach((row) => {
            const matches = row.dataset.search.includes(query);
            row.hidden = !matches;
            visibleRows += Number(matches);
        });

        emptyState.hidden = visibleRows > 0;
    });
}

const dropdowns = [...document.querySelectorAll("[data-dropdown]")];
const accountNotice = document.querySelector("[data-account-notice]");
const accountNoticeMessage = document.querySelector(
    "[data-account-notice-message]",
);
let accountNoticeTimeout;

document.addEventListener("click", (event) => {
    dropdowns.forEach((dropdown) => {
        if (!dropdown.contains(event.target)) {
            dropdown.open = false;
        }
    });
});

document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        dropdowns.forEach((dropdown) => {
            dropdown.open = false;
        });
    }
});

document
    .querySelectorAll('nav[aria-label="Navigasi mobile"] a')
    .forEach((link) => {
        link.addEventListener("click", () => {
            dropdowns.forEach((dropdown) => {
                dropdown.open = false;
            });
        });
    });

document.querySelectorAll("[data-account-action]").forEach((action) => {
    action.addEventListener("click", () => {
        if (!accountNotice || !accountNoticeMessage) {
            return;
        }

        accountNoticeMessage.textContent =
            "Ganti password akan tersedia setelah autentikasi akun diaktifkan.";
        accountNotice.hidden = false;
        accountNotice.classList.remove("hidden");
        accountNotice.classList.add("flex");

        dropdowns.forEach((dropdown) => {
            dropdown.open = false;
        });

        window.clearTimeout(accountNoticeTimeout);
        accountNoticeTimeout = window.setTimeout(() => {
            accountNotice.hidden = true;
            accountNotice.classList.add("hidden");
            accountNotice.classList.remove("flex");
        }, 4000);
    });
});

document
    .querySelector("[data-account-notice-dismiss]")
    ?.addEventListener("click", () => {
        if (accountNotice) {
            accountNotice.hidden = true;
            accountNotice.classList.add("hidden");
            accountNotice.classList.remove("flex");
        }

        window.clearTimeout(accountNoticeTimeout);
    });

const installButtons = [...document.querySelectorAll("[data-pwa-install]")];
const installHelpDialog = document.querySelector("[data-pwa-install-help]");
let deferredInstallPrompt;
const hideInstallButtons = () => {
    installButtons.forEach((button) => {
        button.hidden = true;
    });
};

const isInstalled =
    window.matchMedia("(display-mode: standalone)").matches ||
    navigator.standalone === true;

if (isInstalled) {
    hideInstallButtons();
}

window.addEventListener("beforeinstallprompt", (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
});

window.addEventListener("appinstalled", () => {
    deferredInstallPrompt = undefined;
    hideInstallButtons();
});

installButtons.forEach((button) => {
    button.addEventListener("click", async () => {
        if (!deferredInstallPrompt) {
            installHelpDialog?.showModal();

            return;
        }

        const installPrompt = deferredInstallPrompt;

        deferredInstallPrompt = undefined;
        const choice = await installPrompt.prompt();

        if (choice?.outcome === "accepted") {
            hideInstallButtons();

            return;
        }

        installHelpDialog?.showModal();
    });
});

const passwordToggle = document.querySelector("[data-password-toggle]");

if (passwordToggle) {
    const passwordInput = document.getElementById(
        passwordToggle.getAttribute("aria-controls"),
    );
    const iconShow = passwordToggle.querySelector("[data-icon-show]");
    const iconHide = passwordToggle.querySelector("[data-icon-hide]");

    passwordToggle.addEventListener("click", () => {
        const isVisible = passwordInput.type === "text";

        passwordInput.type = isVisible ? "password" : "text";
        passwordToggle.setAttribute("aria-pressed", String(!isVisible));
        passwordToggle.setAttribute(
            "aria-label",
            isVisible ? "Tampilkan password" : "Sembunyikan password",
        );
        iconShow.classList.toggle("hidden", !isVisible);
        iconHide.classList.toggle("hidden", isVisible);
    });
}

const loginForm = document.querySelector("[data-login-form]");

if (loginForm) {
    const submitButton = loginForm.querySelector("[data-login-submit]");

    loginForm.addEventListener("submit", () => {
        submitButton.disabled = true;
        submitButton
            .querySelector("[data-login-spinner]")
            .classList.remove("hidden");
        submitButton.querySelector("[data-login-label]").textContent =
            "Memproses...";
    });
}


// Category management: AJAX search, pagination, actions and modal.
const categoryModal = document.querySelector("[data-category-modal]");
const categoryForm = document.querySelector("[data-category-form]");
const categoryName = document.querySelector("#category-name");
const categoryMethod = document.querySelector("[data-category-method]");
const categoryTitle = document.querySelector("[data-category-modal-title]");
const categoryResults = document.querySelector("[data-category-results]");
const categoryStoreUrl = categoryForm?.action;
let categorySearchTimer;

const categoryContext = () => {
    const params = new URLSearchParams(window.location.search);
    return { status: params.get("status") || "active", search: params.get("search") || "" };
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
    const response = await fetch(target, {
        headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
        ...options,
    });
    const payload = await response.json();
    if (!response.ok) throw new Error(payload.message || "Tidak dapat memuat data kategori.");
    categoryResults.innerHTML = payload.html;
    if (options.history !== false) window.history.pushState({}, "", payload.url || target);
    return payload;
};

const ajaxCategoryForm = async (form) => {
    const data = new FormData(form);
    const context = categoryContext();
    data.set("status", context.status);
    data.set("search", context.search);
    const response = await fetch(form.action, {
        method: "POST",
        body: data,
        headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
    });
    const payload = await response.json();
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
    if (!response.ok) throw new Error(payload.message || "Aksi kategori gagal.");
    if (payload.html) categoryResults.innerHTML = payload.html;
    if (categoryModal?.open) categoryModal.close();
    showCategoryNotice(payload.message || "Data kategori berhasil diperbarui.");
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
        if (!form?.querySelector("[data-row-checkbox]:checked")) {
            event.preventDefault();
            showCategoryNotice("Pilih minimal satu kategori terlebih dahulu.", true);
            return;
        }
        const action = bulkButton.dataset.bulkSubmit;
        const confirmation = action === "force_delete"
            ? "Hapus permanen kategori yang dipilih? Tindakan ini tidak dapat dibatalkan."
            : action === "restore" ? "Pulihkan kategori yang dipilih?" : "Pindahkan kategori yang dipilih ke sampah?";
        if (!window.confirm(confirmation)) {
            event.preventDefault();
            return;
        }
        form.querySelector("[data-bulk-action]").value = action;
        return;
    }

    const selectAll = event.target.closest("[data-select-all]");
    if (selectAll) {
        selectAll.closest("form").querySelectorAll("[data-row-checkbox]").forEach((checkbox) => {
            checkbox.checked = selectAll.checked;
        });
    }
});

document.addEventListener("change", (event) => {
    if (event.target.matches("[data-row-checkbox]")) {
        const form = event.target.closest("form");
        const boxes = [...form.querySelectorAll("[data-row-checkbox]")];
        const selectAll = form.querySelector("[data-select-all]");
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
    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) return;
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
