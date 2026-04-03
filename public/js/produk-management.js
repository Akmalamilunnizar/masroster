// Tunggu sampai semua HTML selesai di-load oleh browser
document.addEventListener("DOMContentLoaded", function () {
    // ==========================================
    // 1. Inisialisasi URL dari Jembatan Laravel
    // ==========================================
    const GET_TIPE_URL = window.AppConfig.routes.getTipe;
    const GET_MOTIF_URL = window.AppConfig.routes.getMotif;
    const TEST_AJAX_URL = window.AppConfig.routes.testAjax;
    // (Gunakan window.AppConfig.csrfToken jika butuh token, atau ambil dari meta tag)
    const CSRF_TOKEN = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    // ==========================================
    // 2. Elemen DOM Utama
    // ==========================================
    const jenisSelect = document.getElementById("IdJenisBarang");
    const tipeSelect = document.getElementById("id_tipe");
    const motifSelect = document.getElementById("id_motif");

    // [EXPERT TRICK] Simpan ingatan bawaan Laravel sebelum AJAX menghancurkannya
    if (tipeSelect.value) tipeSelect.dataset.preselected = tipeSelect.value;
    if (motifSelect.value) motifSelect.dataset.preselected = motifSelect.value;

    // ==========================================
    // 3. Fitur Dynamic Add/Remove Ukuran Harga
    // ==========================================
    const addUkuranHargaBtn = document.getElementById("add-ukuran-harga");
    if (addUkuranHargaBtn) {
        addUkuranHargaBtn.onclick = function () {
            let list = document.getElementById("ukuran-harga-list");
            let item = list.querySelector(".ukuran-harga-item").cloneNode(true);
            item.querySelector("select").value = "";
            item.querySelector("input").value = "";
            list.appendChild(item);
        };
    }

    const ukuranHargaList = document.getElementById("ukuran-harga-list");
    if (ukuranHargaList) {
        ukuranHargaList.onclick = function (e) {
            if (e.target.classList.contains("remove-ukuran-harga")) {
                let items = document.querySelectorAll(".ukuran-harga-item");
                if (items.length > 1)
                    e.target.closest(".ukuran-harga-item").remove();
            }
        };
    }

    // ==========================================
    // 4. Fungsi Bantuan (Helper)
    // ==========================================
    function setOptions(selectEl, items, valueKey, labelKey, emptyLabel) {
        if (!selectEl) return;
        selectEl.innerHTML = "";
        const placeholder = document.createElement("option");
        placeholder.value = "";
        placeholder.textContent = emptyLabel;
        selectEl.appendChild(placeholder);

        if (Array.isArray(items) && items.length > 0) {
            items.forEach(function (item) {
                const opt = document.createElement("option");
                opt.value = item[valueKey];
                opt.textContent = item[labelKey];
                selectEl.appendChild(opt);
            });
        }
    }

    function syncNewOptionToSelects(selectorIds, id, name) {
        selectorIds.forEach(function (selId) {
            const sel = document.getElementById(selId);
            if (sel && !sel.querySelector('option[value="' + id + '"]')) {
                const opt = document.createElement("option");
                opt.value = id;
                opt.textContent = name;
                sel.appendChild(opt);
            }
        });
    }

    // ==========================================
    // 5. Logika Cascading Dropdown (Jenis -> Tipe -> Motif)
    // ==========================================
    // ==========================================
    // 5. Logika Cascading Dropdown (Jenis -> Tipe -> Motif)
    // ==========================================
    if (jenisSelect) {
        jenisSelect.addEventListener("change", function () {
            const jenisId = this.value;
            setOptions(tipeSelect, [], "IdTipe", "namaTipe", "Pilih Tipe");

            if (!jenisId) return;

            const loading = document.createElement("option");
            loading.value = "";
            loading.textContent = "Memuat tipe...";
            tipeSelect.innerHTML = "";
            tipeSelect.appendChild(loading);

            fetch(`${GET_TIPE_URL}?jenis_id=${encodeURIComponent(jenisId)}`, {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": CSRF_TOKEN,
                },
                credentials: "same-origin",
            })
                .then((r) => (r.ok ? r.json() : Promise.reject(r)))
                .then((data) => {
                    setOptions(
                        tipeSelect,
                        data,
                        "IdTipe",
                        "namaTipe",
                        data.length ? "Pilih Tipe" : "Tidak ada tipe",
                    );

                    // [EXPERT TRICK] 1. Pulihkan ingatan Tipe setelah AJAX selesai
                    if (tipeSelect.dataset.preselected) {
                        tipeSelect.value = tipeSelect.dataset.preselected;
                        tipeSelect.dataset.preselected = ""; // Hapus ingatannya

                        // Lanjutkan trigger untuk me-load Motif!
                        tipeSelect.dispatchEvent(new Event("change"));
                    }
                })
                .catch((error) => {
                    let errorMessage = "Gagal memuat tipe";
                    if (error.status === 403)
                        errorMessage = "Akses ditolak (403)";
                    else if (error.status === 404)
                        errorMessage = "Endpoint tidak ditemukan (404)";
                    setOptions(
                        tipeSelect,
                        [],
                        "IdTipe",
                        "namaTipe",
                        errorMessage,
                    );
                });
        });

        // Trigger otomatis saat load
        if (jenisSelect.value) {
            // [EXPERT TRICK] 0. Simpan ingatan Tipe & Motif bawaan Laravel SEBELUM dihancurkan oleh AJAX
            if (tipeSelect.value)
                tipeSelect.dataset.preselected = tipeSelect.value;
            if (motifSelect.value)
                motifSelect.dataset.preselected = motifSelect.value;

            jenisSelect.dispatchEvent(new Event("change"));
        }
    }

    if (tipeSelect) {
        tipeSelect.addEventListener("change", function () {
            const tipeId = this.value;
            if (!tipeId) return;

            const loading = document.createElement("option");
            loading.value = "";
            loading.textContent = "Memuat motif...";
            motifSelect.innerHTML = "";
            motifSelect.appendChild(loading);

            fetch(`${GET_MOTIF_URL}?tipe_id=${encodeURIComponent(tipeId)}`, {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": CSRF_TOKEN,
                },
                credentials: "same-origin",
            })
                .then((r) => (r.ok ? r.json() : Promise.reject(r)))
                .then((data) => {
                    setOptions(
                        motifSelect,
                        data,
                        "IdMotif",
                        "nama_motif",
                        data.length ? "Pilih Motif" : "Tidak ada motif",
                    );

                    // [EXPERT TRICK] 2. Pulihkan ingatan Motif setelah AJAX selesai
                    if (motifSelect.dataset.preselected) {
                        motifSelect.value = motifSelect.dataset.preselected;
                        motifSelect.dataset.preselected = ""; // Hapus ingatannya
                    }
                })
                .catch((error) => {
                    setOptions(
                        motifSelect,
                        [],
                        "IdMotif",
                        "nama_motif",
                        "Gagal memuat motif",
                    );
                });
        });
    }

    // Modal Motif Cascade (Dibiarkan sama persis seperti milik Anda)
    const newJenisForMotif = document.getElementById("newJenisForMotif");
    if (newJenisForMotif) {
        // ... (Kode bagian ini milik Anda dibiarkan utuh ke bawah) ...
        newJenisForMotif.addEventListener("change", function () {
            const jenisId = this.value;
            const tipeModalSelect = document.getElementById("newTipeForMotif");

            if (!jenisId) {
                if (tipeModalSelect) {
                    tipeModalSelect.innerHTML =
                        '<option value="">Pilih Tipe</option>';
                }
                return;
            }

            fetch(`${GET_TIPE_URL}?jenis_id=${encodeURIComponent(jenisId)}`, {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": CSRF_TOKEN,
                },
            })
                .then((r) => (r.ok ? r.json() : Promise.reject(r)))
                .then((data) => {
                    setOptions(
                        tipeModalSelect,
                        data,
                        "IdTipe",
                        "namaTipe",
                        data.length ? "Pilih Tipe" : "Tidak ada tipe",
                    );

                    if (tipeModalSelect.dataset.preselected) {
                        tipeModalSelect.value =
                            tipeModalSelect.dataset.preselected; // Set nilainya!
                        tipeModalSelect.dataset.preselected = ""; // Hapus ingatannya agar bersih untuk klik berikutnya
                    }
                })
                .catch(() => {
                    tipeModalSelect.innerHTML =
                        '<option value="">Error loading tipe</option>';
                });
        });
    }

    // ==========================================
    // 6. Preload Data ke Modal (Auto-Select)
    // ==========================================
    const addTipeModal = document.getElementById("addTipeModal");
    if (addTipeModal && jenisSelect) {
        addTipeModal.addEventListener("show.bs.modal", function () {
            const currentJenis = jenisSelect.value;
            const newJenisForTipe = document.getElementById("newJenisForTipe");
            if (currentJenis && newJenisForTipe) {
                newJenisForTipe.value = currentJenis;
            }
        });
    }

    const addMotifModal = document.getElementById("addMotifModal");
    if (addMotifModal && jenisSelect && tipeSelect) {
        addMotifModal.addEventListener("show.bs.modal", function () {
            const currentJenis = jenisSelect.value;
            const currentTipe = tipeSelect.value;

            if (currentJenis && newJenisForMotif) {
                newJenisForMotif.value = currentJenis;

                // Simpan ID tipe yang ingin di-preselect ke dalam data attribute
                const tipeModalSelect =
                    document.getElementById("newTipeForMotif");
                if (tipeModalSelect && currentTipe) {
                    tipeModalSelect.dataset.preselected = currentTipe;
                }

                // Trigger change API untuk me-load Tipe yang sesuai dengan Jenis ini ke dalam modal
                newJenisForMotif.dispatchEvent(new Event("change"));
            }
        });
    }

    // ==========================================
    // 6. Logika AJAX Tambah Data (Tanpa Refresh)
    // ==========================================

    // Perhatikan: window.AppConfig digunakan untuk URL
    window.addJenis = function () {
        const formData = new FormData(document.getElementById("addJenisForm"));
        fetch(window.AppConfig.routes.addJenis, {
            method: "POST",
            body: formData,
            headers: { "X-CSRF-TOKEN": CSRF_TOKEN },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    syncNewOptionToSelects(
                        [
                            "IdJenisBarang",
                            "newJenisForTipe",
                            "newJenisForMotif",
                        ],
                        data.id,
                        data.name,
                    );
                    document.getElementById("IdJenisBarang").value = data.id;
                    jenisSelect.dispatchEvent(new Event("change"));

                    if (typeof window.forceClearAllModals === "function") {
                        window.forceClearAllModals();
                    } else {
                        bootstrap.Modal.getInstance(
                            document.getElementById("addJenisModal"),
                        )?.hide();
                    }
                    document.getElementById("addJenisForm").reset();
                    if (typeof CustomModal !== "undefined")
                        CustomModal.success("Jenis berhasil ditambahkan!");
                } else {
                    if (typeof CustomModal !== "undefined") {
                        CustomModal.error(
                            data.message || "Gagal menambahkan Jenis.",
                        );
                    } else {
                        alert(data.message || "Gagal menambahkan Jenis.");
                    }
                }
            })
            .catch((error) => {
                if (typeof CustomModal !== "undefined") {
                    CustomModal.error(
                        "Terjadi kesalahan jaringan atau server.",
                    );
                } else {
                    alert("Terjadi kesalahan jaringan atau server.");
                }
            });
    };

    window.addTipe = function () {
        const formData = new FormData(document.getElementById("addTipeForm"));
        fetch(window.AppConfig.routes.addTipe, {
            method: "POST",
            body: formData,
            headers: { "X-CSRF-TOKEN": CSRF_TOKEN },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const currentJenis = jenisSelect.value;
                    if (currentJenis) {
                        fetch(
                            `${GET_TIPE_URL}?jenis_id=${encodeURIComponent(currentJenis)}`,
                            {
                                method: "GET",
                                headers: {
                                    "X-Requested-With": "XMLHttpRequest",
                                    Accept: "application/json",
                                    "X-CSRF-TOKEN": CSRF_TOKEN,
                                },
                                credentials: "same-origin",
                            },
                        )
                            .then((r) => (r.ok ? r.json() : Promise.reject(r)))
                            .then((responseData) => {
                                setOptions(
                                    tipeSelect,
                                    responseData,
                                    "IdTipe",
                                    "namaTipe",
                                    responseData.length
                                        ? "Pilih Tipe"
                                        : "Tidak ada tipe",
                                );
                                tipeSelect.value = data.id;
                                tipeSelect.dispatchEvent(new Event("change"));
                            })
                            .catch(() => {
                                jenisSelect.dispatchEvent(new Event("change"));
                            });
                    } else {
                        const newOption = document.createElement("option");
                        newOption.value = data.id;
                        newOption.textContent = data.name;
                        tipeSelect.appendChild(newOption);
                        newOption.selected = true;
                    }

                    if (typeof window.forceClearAllModals === "function") {
                        window.forceClearAllModals();
                    } else {
                        bootstrap.Modal.getInstance(
                            document.getElementById("addTipeModal"),
                        )?.hide();
                    }
                    document.getElementById("addTipeForm").reset();
                } else {
                    if (typeof CustomModal !== "undefined") {
                        CustomModal.error(
                            data.message || "Gagal menambahkan Tipe.",
                        );
                    } else {
                        alert(data.message || "Gagal menambahkan Tipe.");
                    }
                }
            })
            .catch((error) => {
                if (typeof CustomModal !== "undefined") {
                    CustomModal.error(
                        "Terjadi kesalahan jaringan atau server.",
                    );
                } else {
                    alert("Terjadi kesalahan jaringan atau server.");
                }
            });
    };

    window.addMotif = function () {
        const formData = new FormData(document.getElementById("addMotifForm"));
        fetch(window.AppConfig.routes.addMotif, {
            method: "POST",
            body: formData,
            headers: { "X-CSRF-TOKEN": CSRF_TOKEN },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const currentTipe = tipeSelect.value;
                    if (currentTipe) {
                        fetch(
                            `${GET_MOTIF_URL}?tipe_id=${encodeURIComponent(currentTipe)}`,
                            {
                                method: "GET",
                                headers: {
                                    "X-Requested-With": "XMLHttpRequest",
                                    Accept: "application/json",
                                    "X-CSRF-TOKEN": CSRF_TOKEN,
                                },
                                credentials: "same-origin",
                            },
                        )
                            .then((r) => (r.ok ? r.json() : Promise.reject(r)))
                            .then((responseData) => {
                                setOptions(
                                    motifSelect,
                                    responseData,
                                    "IdMotif",
                                    "nama_motif",
                                    responseData.length
                                        ? "Pilih Motif"
                                        : "Tidak ada motif",
                                );
                                motifSelect.value = data.id;
                            })
                            .catch(() => {
                                tipeSelect.dispatchEvent(new Event("change"));
                            });
                    }

                    if (typeof window.forceClearAllModals === "function") {
                        window.forceClearAllModals();
                    } else {
                        bootstrap.Modal.getInstance(
                            document.getElementById("addMotifModal"),
                        )?.hide();
                    }
                    document.getElementById("addMotifForm").reset();
                } else {
                    if (typeof CustomModal !== "undefined") {
                        CustomModal.error(
                            data.message || "Gagal menambahkan Motif.",
                        );
                    } else {
                        alert(data.message || "Gagal menambahkan Motif.");
                    }
                }
            })
            .catch((error) => {
                if (typeof CustomModal !== "undefined") {
                    CustomModal.error(
                        "Terjadi kesalahan jaringan atau server.",
                    );
                } else {
                    alert("Terjadi kesalahan jaringan atau server.");
                }
            });
    };

    window.addSize = function () {
        const formData = new FormData(document.getElementById("addSizeForm"));
        fetch(window.AppConfig.routes.addSize, {
            method: "POST",
            body: formData,
            headers: { "X-CSRF-TOKEN": CSRF_TOKEN },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const sizeSelects = document.querySelectorAll(
                        'select[name="sizes[]"]',
                    );
                    sizeSelects.forEach((select) => {
                        const newOption = document.createElement("option");
                        newOption.value = data.id;
                        newOption.textContent = `${data.name} (${data.panjang} x ${data.lebar} Cm)`;
                        select.appendChild(newOption);
                    });

                    if (typeof window.forceClearAllModals === "function") {
                        window.forceClearAllModals();
                    } else {
                        bootstrap.Modal.getInstance(
                            document.getElementById("addSizeModal"),
                        )?.hide();
                    }
                    document.getElementById("addSizeForm").reset();
                } else {
                    if (typeof CustomModal !== "undefined") {
                        CustomModal.error(
                            data.message || "Gagal menambahkan Ukuran.",
                        );
                    } else {
                        alert(data.message || "Gagal menambahkan Ukuran.");
                    }
                }
            })
            .catch((error) => {
                if (typeof CustomModal !== "undefined") {
                    CustomModal.error(
                        "Terjadi kesalahan jaringan atau server.",
                    );
                } else {
                    alert("Terjadi kesalahan jaringan atau server.");
                }
            });
    };

    // ==========================================
    // 7. Pembersih Modal (Garbage Collector)
    // ==========================================
    window.forceClearAllModals = function () {
        document.querySelectorAll(".modal-backdrop").forEach((b) => b.remove());
        document.querySelectorAll(".modal").forEach((m) => {
            m.classList.remove("show");
            m.style.display = "none";
        });
        document.body.classList.remove("modal-open");
        document.body.style.overflow = "";
        document.body.style.paddingRight = "";
    };

    // Keyboard shortcut (Ctrl+Shift+M) untuk clear modal error
    document.addEventListener("keydown", function (e) {
        if (e.ctrlKey && e.shiftKey && e.key === "M") {
            window.forceClearAllModals();
        }
    });
});
