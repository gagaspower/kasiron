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


// Category management interactions.
const categoryModal = document.querySelector("[data-category-modal]");
const categoryForm = document.querySelector("[data-category-form]");
const categoryName = document.querySelector("#category-name");
const categoryMethod = document.querySelector("[data-category-method]");
const categoryTitle = document.querySelector("[data-category-modal-title]");
const categoryStoreUrl = categoryForm?.action;

const openCategoryModal = (title, action, method, name = "") => {
    if (!categoryModal || !categoryForm) return;
    categoryTitle.textContent = title;
    categoryForm.action = action;
    categoryMethod.value = method;
    categoryName.value = name;
    categoryModal.showModal();
    categoryName.focus();
};

document.querySelector("[data-category-create]")?.addEventListener("click", () => {
    openCategoryModal("Tambah kategori", categoryStoreUrl, "POST");
});

document.querySelectorAll("[data-category-edit]").forEach((button) => {
    button.addEventListener("click", () => {
        openCategoryModal("Edit kategori", button.dataset.url, "PUT", button.dataset.name);
    });
});

document.querySelectorAll("[data-category-close]").forEach((button) => {
    button.addEventListener("click", () => categoryModal?.close());
});

const selectAll = document.querySelector("[data-select-all]");
const rowCheckboxes = [...document.querySelectorAll("[data-row-checkbox]")];

selectAll?.addEventListener("change", () => {
    rowCheckboxes.forEach((checkbox) => { checkbox.checked = selectAll.checked; });
});

rowCheckboxes.forEach((checkbox) => checkbox.addEventListener("change", () => {
    if (selectAll) {
        selectAll.checked = rowCheckboxes.length > 0 && rowCheckboxes.every((item) => item.checked);
    }
}));

document.querySelectorAll("[data-bulk-submit]").forEach((button) => {
    button.addEventListener("click", (event) => {
        if (!rowCheckboxes.some((checkbox) => checkbox.checked)) {
            event.preventDefault();
            window.alert("Pilih minimal satu kategori terlebih dahulu.");
            return;
        }

        const action = button.dataset.bulkSubmit;
        const message = action === "force_delete"
            ? "Hapus permanen kategori yang dipilih? Tindakan ini tidak dapat dibatalkan."
            : action === "restore" ? "Pulihkan kategori yang dipilih?" : "Pindahkan kategori yang dipilih ke sampah?";

        if (!window.confirm(message)) {
            event.preventDefault();
            return;
        }

        document.querySelector("[data-bulk-action]").value = action;
    });
});

document.querySelectorAll("[data-confirm]").forEach((form) => {
    form.addEventListener("submit", (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

if (categoryModal && document.querySelector("[data-category-validation-error]")) {
    categoryModal.showModal();
}
