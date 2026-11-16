# Dokumentasi API Monitoring Service

Base URL langsung pada LAN: `http://HOST_MONITORING:8107`.

## `GET /health`

Endpoint publik untuk memastikan proses Monitoring Service hidup. Endpoint ini tidak menghubungi layanan lain dan tidak memerlukan token.

Respons `200 OK`:

```json
{
  "success": true,
  "service": "monitoring-service",
  "status": "UP",
  "checked_at": "2026-11-11T08:00:00+00:00"
}
```

Metode selain `GET` menghasilkan `405 Method Not Allowed` dan header `Allow: GET`.

## `GET /api/health/all`

Endpoint terlindungi untuk memeriksa health seluruh layanan yang dikonfigurasi. Kirim token pengguna tanpa perubahan:

```http
Authorization: Bearer <token>
```

Monitoring Service memvalidasi token dengan `GET {AUTH_SERVICE_URL}/api/auth/validate` dan meneruskan header `Authorization` yang sama. Pemeriksaan layanan baru dilakukan setelah respons validasi berstatus 2xx dan memiliki `data.valid: true`.

Respons `200 OK`:

```json
{
  "success": true,
  "checked_at": "2026-11-11T08:00:00+00:00",
  "requested_by": {
    "user_id": "usr-contoh",
    "role": "student"
  },
  "total_services": 5,
  "services_up": 4,
  "services_down": 1,
  "overall_status": "DEGRADED",
  "services": [
    {
      "name": "course",
      "status": "DOWN",
      "health_url": "http://HOST_COURSE:8103/api/health",
      "http_code": 0,
      "duration_ms": 3001,
      "reason": "timeout",
      "message": "Batas waktu permintaan terlampaui."
    }
  ]
}
```

Daftar `services` selalu memuat `auth`, `course`, `assignment`, `submission`, dan `notification`. Status `UP` hanya diberikan untuk respons HTTP 2xx. Timeout, kegagalan koneksi, konfigurasi yang tidak valid, dan respons non-2xx ditandai `DOWN`; sistem tidak membuat hasil sukses palsu.

### Respons kesalahan

| HTTP | Kode | Kondisi |
|---:|---|---|
| 401 | `missing_token` | Header Bearer tidak dikirim atau formatnya salah. |
| 401 | `invalid_token` | Auth Service menolak token. |
| 502 | `invalid_auth_response` | Auth Service memberi 2xx, tetapi payload validasi tidak sah. |
| 503 | `auth_service_not_configured` | `AUTH_SERVICE_URL` tidak valid atau belum diatur. |
| 503 | `auth_service_unavailable` | Auth Service timeout, gagal dihubungi, atau merespons 5xx. |
| 405 | `method_not_allowed` | Metode selain `GET`. |

## Jalur melalui Gateway

Gateway meneruskan `/api/v1/monitoring/...` ke jalur backend `/api/...` sesuai akhiran permintaan. Untuk health publik, pemetaan eksplisitnya adalah `/api/v1/monitoring/health` ke `/health`. Untuk agregat, gunakan:

```http
GET /api/v1/monitoring/all
Authorization: Bearer <token>
```

Gateway harus meneruskan header Bearer ke backend agar validasi dapat dilakukan oleh Monitoring Service.

---

## Pengujian dengan Postman (Drag & Drop)

Di dalam folder ini telah disediakan file **`postman_collection.json`** yang sudah dikhususkan untuk service ini:
1. Buka aplikasi **Postman**.
2. **Drag & drop** file `postman_collection.json` dari folder ini langsung ke jendela Postman (atau klik tombol **Import** lalu pilih file ini).
3. Seluruh endpoint pengujian milik kelompok ini akan otomatis terdaftar dan siap dijalankan tanpa perlu mengetik URL atau body secara manual.
4. Variabel `base_url` sudah otomatis diset ke port kelompok ini. Jika dijalankan antar-laptop di jaringan LAN, cukup sesuaikan nilai `base_url` dengan IP laptop tujuan.
