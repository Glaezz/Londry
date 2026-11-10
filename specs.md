# Spesifikasi Proyek: Sistem Laundry Kiloan / Satuan

Stack: **Laravel + Filament** (admin panel), MySQL/PostgreSQL, `barryvdh/laravel-dompdf` untuk struk.

> Catatan: Filament sudah punya sistem login sendiri, jadi **Laravel Breeze tidak diperlukan**. Login kasir/admin cukup lewat panel Filament (`/admin`).

Dokumen ini dibagi menjadi **fase-fase kecil**. Setiap fase diakhiri dengan satu commit manual dengan pesan yang sudah disarankan. Jangan lanjut ke fase berikutnya sebelum _checklist_ fase saat ini lolos.

---

## Ringkasan Domain

| Tabel              | Fungsi                                       |
| ------------------ | -------------------------------------------- |
| `users`            | Akun kasir/admin (kolom `role`)              |
| `pelanggan`        | Master pelanggan                             |
| `layanan`          | Master jenis layanan (kiloan/satuan + harga) |
| `transaksi`        | Header transaksi                             |
| `detail_transaksi` | Item layanan dalam satu transaksi            |

### Relasi Eloquent

- `Pelanggan` hasMany `Transaksi`
- `Transaksi` belongsTo `Pelanggan`, belongsTo `User`, hasMany `DetailTransaksi`
- `DetailTransaksi` belongsTo `Transaksi`, belongsTo `Layanan`
- `Layanan` hasMany `DetailTransaksi`

### Alur Kerja

1. Kasir login.
2. Kasir mendaftarkan pelanggan baru atau memilih pelanggan lama.
3. Kasir membuat transaksi baru, memilih beberapa layanan (misal 3 kg Cuci Kiloan + 1 Bedcover). Sistem menghitung `subtotal` dan `total_bayar` otomatis.
4. Status laundry diperbarui bertahap sampai selesai.
5. Saat pelanggan mengambil pakaian, status menjadi `diambil` dan pembayaran menjadi `lunas`.

### Keputusan Desain (penyimpangan kecil dari skema awal)

1. **Tambah kolom `harga_satuan` di `detail_transaksi`.** Tujuannya menyimpan harga saat transaksi terjadi. Tanpa ini, jika harga layanan diubah nanti, nilai transaksi lama ikut berubah.
2. **`total_bayar` dan `subtotal` dihitung di server**, bukan dipercaya dari input form.
3. **Nama tabel dalam bahasa Indonesia** (`pelanggan`, `layanan`, ...), jadi tiap model perlu `protected $table = '...'`.
4. **Enum memakai PHP Backed Enum** (bisa dipakai langsung untuk label dan warna badge di Filament).

---

## Fase 0: Verifikasi Setup

**Tujuan:** pastikan fondasi berjalan sebelum menulis fitur.

**Tugas**

- [ ] `.env` terhubung ke database, `php artisan migrate` sukses.
- [ ] Filament terpasang: `composer require filament/filament` lalu `php artisan filament:install --panels` (panel id: `admin`).
- [ ] Buat akun awal: `php artisan make:filament-user`.
- [ ] Login ke `/admin` berhasil.
- [ ] Set timezone di `config/app.php` ke `Asia/Jakarta` dan locale sesuai kebutuhan (`id`).

**Commit:** `chore: setup filament panel and base config`

---

## Fase 1: Migration dan Model

**Tujuan:** struktur database lengkap beserta relasi.

**Tugas**

- [ ] Migration tambahan untuk `users`: kolom `role` enum (`admin`, `kasir`), default `kasir`.
- [ ] Migration `pelanggan`: `id`, `nama`, `no_hp`, `alamat`, timestamps.
- [ ] Migration `layanan`: `id`, `nama_layanan`, `jenis` enum (`kiloan`, `satuan`), `harga_per_unit` (decimal/unsignedInteger), timestamps.
- [ ] Migration `transaksi`: `id`, `kode_transaksi` (unique), `pelanggan_id` (FK), `user_id` (FK), `tanggal_masuk`, `tanggal_selesai` (nullable), `status_laundry` enum (default `baru`), `status_pembayaran` enum (default `belum_bayar`), `total_bayar` (default 0), timestamps.
- [ ] Migration `detail_transaksi`: `id`, `transaksi_id` (FK, `cascadeOnDelete`), `layanan_id` (FK, `restrictOnDelete`), `jumlah_unit` (decimal 8,2, karena kg bisa pecahan), `harga_satuan`, `subtotal`, timestamps.
- [ ] Buat model: `Pelanggan`, `Layanan`, `Transaksi`, `DetailTransaksi` dengan `$table`, `$fillable`, dan relasi.
- [ ] Buat PHP Enum di `app/Enums/`:
    - `Role` (admin, kasir)
    - `JenisLayanan` (kiloan, satuan)
    - `StatusLaundry` (baru, dihitung, diproses, selesai, diambil)
    - `StatusPembayaran` (belum_bayar, lunas)
- [ ] Cast enum di model (`$casts`).
- [ ] Tambahkan `role` ke `$fillable` dan `$casts` model `User`.

**Verifikasi:** `php artisan migrate:fresh` sukses, lalu cek relasi lewat `php artisan tinker`.

**Commit:** `feat: add migrations, models, relations, and enums`

---

## Fase 2: Seeder dan Hak Akses Dasar

**Tujuan:** data awal dan pembatasan akses panel.

**Tugas**

- [ ] `UserSeeder`: 1 admin dan 1 kasir (password di-hash).
- [ ] `LayananSeeder`: contoh data
    - Cuci Komplit Kiloan (kiloan)
    - Cuci Bedcover (satuan)
    - Setrika Saja (kiloan)
- [ ] `PelangganSeeder`: 3 s.d. 5 data contoh.
- [ ] Daftarkan semua seeder di `DatabaseSeeder`.
- [ ] Implementasi `FilamentUser` di model `User` dengan method `canAccessPanel()` (hanya role `admin` dan `kasir` yang boleh masuk).

**Commit:** `feat: add seeders and panel access control`

---

## Fase 3: Resource Master Data (Pelanggan dan Layanan)

**Tujuan:** CRUD master berjalan.

**Tugas**

- [ ] `php artisan make:filament-resource Pelanggan --generate`
- [ ] `php artisan make:filament-resource Layanan --generate`
- [ ] Pelanggan:
    - Form: `nama` (required), `no_hp` (required, `tel`), `alamat` (textarea).
    - Table: kolom nama, no_hp, dengan `searchable()`.
- [ ] Layanan:
    - Form: `nama_layanan`, `jenis` (Select dari enum), `harga_per_unit` (numeric, prefix `Rp`, min 0).
    - Table: badge untuk `jenis`, harga diformat `money('IDR')`.
- [ ] Atur label navigasi, ikon, dan urutan menu.
- [ ] Label form/tabel dalam bahasa Indonesia.

**Aturan bisnis**

- Layanan atau pelanggan yang sudah dipakai di transaksi **tidak boleh dihapus** (FK `restrict`). Tampilkan pesan yang jelas atau sembunyikan tombol hapus.

**Commit:** `feat: add pelanggan and layanan filament resources`

---

## Fase 4: Resource Transaksi (Form Multi-Item)

**Tujuan:** inti aplikasi. Header dan detail tersimpan sekaligus.

**Tugas**

- [ ] `php artisan make:filament-resource Transaksi`
- [ ] Form bagian header:
    - `kode_transaksi`: otomatis, `disabled` + `dehydrated`, format `TRX-YYYYMMDD-NNN` (nomor urut reset per hari).
    - `pelanggan_id`: Select dengan `searchable()` dan `createOptionForm()` (daftar pelanggan baru langsung dari form transaksi).
    - `user_id`: otomatis `auth()->id()` (tidak ditampilkan ke kasir).
    - `tanggal_masuk`: default hari ini; `tanggal_selesai`: opsional.
- [ ] Form bagian detail: **Repeater** relasi `detail`:
    - `layanan_id` (Select, `live()`), saat dipilih, isi otomatis `harga_satuan` dari `layanan.harga_per_unit`.
    - `jumlah_unit` (numeric, min 0.1 atau 1 untuk satuan, `live()`).
    - `harga_satuan` (read-only).
    - `subtotal` = `jumlah_unit × harga_satuan` (read-only, dihitung realtime).
- [ ] Tampilkan total keseluruhan di form (hitung dari seluruh item repeater).
- [ ] **Hitung ulang di server** pada saat simpan (jangan percaya nilai dari browser):
    - Ambil harga dari tabel `layanan`, hitung `subtotal`, jumlahkan menjadi `total_bayar`.
- [ ] Penyimpanan dibungkus **`DB::transaction()`** (override `handleRecordCreation` / `handleRecordUpdate`).
- [ ] Validasi: minimal 1 item di repeater.

**Aturan bisnis**

- Kode transaksi harus unik. Pembuatan kode sebaiknya di dalam DB transaction agar nomor urut tidak bentrok.
- Transaksi dengan status `diambil` tidak boleh diedit item-nya.

**Commit:** `feat: add transaksi resource with multi-item repeater and auto totals`

---

## Fase 5: Status Laundry dan Pembayaran

**Tujuan:** kasir bisa menggerakkan status dengan cepat.

**Tugas**

- [ ] Kolom tabel Transaksi:
    - `kode_transaksi`, `pelanggan.nama`, `tanggal_masuk`, `total_bayar` (money IDR).
    - `status_laundry` sebagai `TextColumn::badge()` (warna per status, gunakan `HasColor`/`HasLabel` di enum).
    - `status_pembayaran` sebagai badge.
- [ ] Row action **"Lanjutkan Status"**: pindah satu langkah maju sesuai urutan `baru → dihitung → diproses → selesai → diambil`. Sembunyikan jika sudah `diambil`.
- [ ] Row action **"Tandai Lunas"** (hanya tampil jika `belum_bayar`), dengan konfirmasi.
- [ ] Saat status menjadi `selesai`, isi `tanggal_selesai` otomatis bila masih kosong.
- [ ] Filter: status laundry, status pembayaran, rentang `tanggal_masuk`.
- [ ] Pencarian: kode transaksi dan nama pelanggan.

**Aturan bisnis (sesuai alur kerja)**

- Status `diambil` **hanya boleh** jika `status_pembayaran = lunas`. Jika belum lunas, tawarkan opsi "Lunasi dan Tandai Diambil" dalam satu aksi.

**Commit:** `feat: add status workflow actions, badges, and filters`

---

## Fase 6: Cetak Struk PDF

**Tujuan:** nota transaksi dapat dicetak.

**Tugas**

- [ ] `composer require barryvdh/laravel-dompdf`
- [ ] Buat view Blade `resources/views/pdf/struk.blade.php`, isi:
    - Nama usaha, kode transaksi, tanggal masuk, nama kasir.
    - Data pelanggan (nama, no HP).
    - Tabel item: layanan, jumlah (kg/pcs), harga satuan, subtotal.
    - Total bayar, status pembayaran.
- [ ] Route `GET /transaksi/{transaksi}/struk` (dilindungi middleware `auth`) yang mengembalikan PDF (stream/download).
- [ ] Action **"Cetak Struk"** di tabel dan halaman view/edit transaksi (buka di tab baru).
- [ ] Ukuran kertas: A5 atau ukuran struk kecil (misal custom paper 80 mm).

**Commit:** `feat: add transaction receipt pdf`

---

## Fase 7: Dashboard dan Pembatasan Role

**Tujuan:** ringkasan operasional dan perbedaan hak admin vs kasir.

**Tugas**

- [ ] Widget statistik (`StatsOverviewWidget`):
    - Transaksi hari ini.
    - Pendapatan hari ini (hanya transaksi lunas).
    - Jumlah laundry yang sedang diproses.
    - Jumlah belum bayar.
- [ ] (Opsional) Widget tabel "Laundry siap diambil" (`status = selesai`).
- [ ] Pembatasan role:
    - **Admin:** akses penuh, termasuk Resource User dan hapus data.
    - **Kasir:** kelola pelanggan, buat transaksi, ubah status; **tidak** bisa menghapus layanan/transaksi dan tidak melihat menu User.
- [ ] Buat Resource `User` (khusus admin) dengan field `role`.
- [ ] Terapkan lewat Policy (`php artisan make:policy`) atau `canViewAny()` / `canDelete()` di Resource.

**Commit:** `feat: add dashboard widgets and role-based access`

---

## Fase 8: Pengujian dan Finalisasi

**Tujuan:** siap dikumpulkan.

**Tugas**

- [ ] Skenario uji manual end-to-end (lihat daftar di bawah).
- [ ] (Opsional) Feature test untuk: perhitungan total, pembuatan kode transaksi, aturan `diambil` hanya jika lunas.
- [ ] Rapikan label, urutan menu, dan pesan validasi.
- [ ] `README.md`: cara instalasi (`composer install`, `.env`, `migrate --seed`), akun demo, daftar fitur.
- [ ] Ambil screenshot untuk laporan/portofolio.

**Commit:** `docs: add readme and final polish`

### Skenario Uji Manual

1. Login sebagai kasir, pelanggan baru dibuat langsung dari form transaksi.
2. Transaksi: 3 kg Cuci Komplit Kiloan + 1 Cuci Bedcover, total sesuai hitungan manual.
3. Ubah harga layanan, transaksi lama tetap memakai harga lama.
4. Jalankan status `baru → ... → selesai`, `tanggal_selesai` terisi.
5. Coba tandai `diambil` saat belum lunas, harus ditolak atau ditawari pelunasan.
6. Cetak struk, isi sesuai.
7. Login sebagai kasir, tidak ada menu User dan tidak bisa hapus layanan.
8. Hapus layanan yang sudah dipakai, ditolak dengan pesan jelas.

---

## Ringkasan Fase dan Commit

| Fase | Fokus                      | Pesan commit                                                            |
| ---- | -------------------------- | ----------------------------------------------------------------------- |
| 0    | Setup Filament             | `chore: setup filament panel and base config`                           |
| 1    | Migration, model, enum     | `feat: add migrations, models, relations, and enums`                    |
| 2    | Seeder, akses panel        | `feat: add seeders and panel access control`                            |
| 3    | CRUD Pelanggan dan Layanan | `feat: add pelanggan and layanan filament resources`                    |
| 4    | Transaksi multi-item       | `feat: add transaksi resource with multi-item repeater and auto totals` |
| 5    | Status dan pembayaran      | `feat: add status workflow actions, badges, and filters`                |
| 6    | Struk PDF                  | `feat: add transaction receipt pdf`                                     |
| 7    | Dashboard dan role         | `feat: add dashboard widgets and role-based access`                     |
| 8    | Test dan README            | `docs: add readme and final polish`                                     |

> Jika waktu terbatas, **Fase 7 (dashboard)** dan sebagian **Fase 8 (test otomatis)** bisa dipersingkat. Fase 0 sampai 6 sudah mencakup seluruh alur kerja inti.
