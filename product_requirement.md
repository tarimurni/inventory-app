# Product Requirement Document (PRD)
## Inventory Management System

---

## 1. Ringkasan Produk (Overview)
Sistem Manajemen Inventaris (*Inventory Management System*) adalah aplikasi berbasis web yang dirancang untuk membantu pengelolaan stok barang, data produk, kategori, supplier, serta mencatat transaksi mutasi barang (masuk/keluar) secara *real-time* dan akurat.

---

## 2. Tujuan Aplikasi (Objectives)
- **Efisiensi Pengelolaan Data**: Mengotomatisasi pencatatan stok barang untuk meminimalkan *human error*.
- **Transparansi Stok**: Menyediakan informasi stok rendah secara cepat melalui fitur *Low Stock Alert*.
- **Pencatatan Mutasi**: Mencatat setiap histori barang masuk (IN) dan barang keluar (OUT).
- **Pelaporan Komprehensif**: Memudahkan pembuatan laporan yang siap dicetak dalam format PDF atau di-export ke Microsoft Excel.

---

## 3. Hak Akses Pengguna (User Roles)
- **Admin**: Akses penuh ke seluruh modul aplikasi (Dashboard, Kategori, Produk, Supplier, Transaksi Stok, Laporan).
- **Staff / User**: Mengelola transaksi stok barang dan melihat laporan stok.

---

## 4. Persyaratan Fungsional (Functional Requirements)

### 4.1 Autentikasi & Keamanan
- User wajib login terlebih dahulu sebelum mengakses sistem.
- Proteksi halaman menggunakan penanganan session PHP.

### 4.2 Dashboard
- Menampilkan widget ringkasan: Total Produk, Nilai Total Inventaris, Stok Rendah, Total Barang Masuk, dan Total Barang Keluar.
- Menampilkan tabel peringatan produk yang stoknya mencapai atau di bawah *minimum stock*.

### 4.3 Manajemen Data Master (Kategori, Produk, Supplier)
- Fitur CRUD lengkap (Create, Read, Update, Delete) untuk Kategori, Produk, dan Supplier.
- Produk terhubung secara unik melalui kolom SKU.

### 4.4 Manajemen Mutasi Stok (IN / OUT)
- Pencatatan transaksi stok masuk (IN) dan keluar (OUT).
- Penggunaan *Database Transaction* untuk menjaga integritas perubahan kuantitas stok produk.

### 4.5 Laporan & Export Data
- Filter transaksi berdasarkan rentang tanggal (*start date* s/d *end date*).
- Fitur **Export Excel** (`.xls`).
- Fitur **Cetak / Simpan PDF** melalui halaman *print friendly*.

---

## 5. Persyaratan Non-Fungsional (Non-Functional Requirements)
- **User Interface**: Tampilan antarmuka *responsive* dan modern menggunakan **Bootstrap 5**.
- **Kinerja**: Query database cepat menggunakan **PHP Data Objects (PDO)**.
- **Keamanan**: Implementasi pembersihan input (*escaping*) untuk mencegah *XSS (Cross-Site Scripting)*.