# Monitoring Service Mini-LMS

Monitoring Service memeriksa ketersediaan layanan Mini-LMS secara langsung tanpa membaca registry Gateway. Pemisahan ini mencegah ketergantungan melingkar: Gateway boleh meneruskan permintaan monitoring, tetapi Monitoring Service tidak bergantung kembali kepada Gateway untuk mengetahui alamat layanan.

## Menjalankan di LAN

Gunakan PHP 7.4 atau yang lebih baru. Contoh berikut membuka layanan pada seluruh antarmuka jaringan di port `8107`:

```bash
AUTH_SERVICE_URL=http://HOST_AUTH:8102 \
COURSE_SERVICE_URL=http://HOST_COURSE:8103 \
ASSIGNMENT_SERVICE_URL=http://HOST_ASSIGNMENT:8104 \
SUBMISSION_SERVICE_URL=http://HOST_SUBMISSION:8105 \
NOTIFICATION_SERVICE_URL=http://HOST_NOTIFICATION:8106 \
php -S 0.0.0.0:8107 router.php
```

Ganti setiap nama host dengan alamat LAN atau DNS yang sesuai. Kode tidak menyimpan alamat IP tertentu.

### Variabel lingkungan

| Variabel | Wajib | Kegunaan |
|---|---:|---|
| `AUTH_SERVICE_URL` | Ya | URL dasar Auth Service, termasuk untuk validasi token. |
| `COURSE_SERVICE_URL` | Ya | URL dasar Course Service. |
| `ASSIGNMENT_SERVICE_URL` | Ya | URL dasar Assignment Service. |
| `SUBMISSION_SERVICE_URL` | Ya | URL dasar Submission Service. |
| `NOTIFICATION_SERVICE_URL` | Ya | URL dasar Notification Service. |
| `MONITORING_TIMEOUT_SECONDS` | Tidak | Batas waktu tiap permintaan, bawaan 3 detik; rentang 0,1–30 detik. |

Jalur health bawaan berturut-turut adalah `/health`, `/api/health`, `/health`, `/health`, dan `/health`. Bila implementasi layanan berbeda, atur `AUTH_HEALTH_PATH`, `COURSE_HEALTH_PATH`, `ASSIGNMENT_HEALTH_PATH`, `SUBMISSION_HEALTH_PATH`, atau `NOTIFICATION_HEALTH_PATH`.

## Integrasi Gateway

Gateway menerima rute privat `/api/v1/monitoring/...`, menghapus prefiks tersebut, lalu meneruskannya ke Monitoring Service. Contoh pemetaan:

- `GET /api/v1/monitoring/health` → `GET http://HOST_MONITORING:8107/health`
- `GET /api/v1/monitoring/all` → `GET http://HOST_MONITORING:8107/api/health/all`

Header `Authorization` harus dipertahankan saat Gateway meneruskan endpoint terlindungi.

## Pengujian singkat

```bash
curl -i http://HOST_MONITORING:8107/health
curl -i -H 'Authorization: Bearer TOKEN' http://HOST_MONITORING:8107/api/health/all
```

Endpoint agregat tetap merespons `200` setelah autentikasi berhasil meskipun satu layanan `DOWN`; hasil tersebut ditandai dengan `overall_status: DEGRADED`. Kegagalan autentikasi menghasilkan `401`, sedangkan Auth Service yang tidak dapat dihubungi menghasilkan `503`.

---

## Pengujian dengan Postman (Drag & Drop)

Di dalam folder ini telah disediakan file **`postman_collection.json`** yang sudah dikhususkan untuk service ini:
1. Buka aplikasi **Postman**.
2. **Drag & drop** file `postman_collection.json` dari folder ini langsung ke jendela Postman (atau klik tombol **Import** lalu pilih file ini).
3. Seluruh endpoint pengujian milik kelompok ini akan otomatis terdaftar dan siap dijalankan tanpa perlu mengetik URL atau body secara manual.
4. Variabel `base_url` sudah otomatis diset ke port kelompok ini. Jika dijalankan antar-laptop di jaringan LAN, cukup sesuaikan nilai `base_url` dengan IP laptop tujuan.
