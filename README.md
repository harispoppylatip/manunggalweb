# Dashboard IoT Manunggal

Proyek ini menampilkan data sensor dan kontrol pompa melalui web. Aplikasi berjalan di atas PHP sederhana dengan Chart.js untuk menampilkan grafik.

## Menjalankan Aplikasi

1. Pastikan **Docker** dan **Docker Compose** terpasang.
2. Jalankan layanan web dan basis data:
   ```bash
   docker-compose up -d
   ```
   Perintah di atas akan memulai kontainer web serta kontainer MySQL yang otomatis terisi dengan skema dan data contoh dari folder `database/`.
3. Buka `http://localhost:5500` di peramban.

## Konfigurasi Basis Data

Secara bawaan, `docker-compose` akan menjalankan MySQL dengan kredensial berikut:

- host: `db`
- database: `manunggaljaya`
- user: `manunggal`
- password: `jaya333`

Anda dapat mengubah kredensial tersebut melalui variabel lingkungan `DB_HOST`, `DB_USER`, `DB_PASS`, dan `DB_NAME`.

### Struktur Tabel
Jika menjalankan basis data secara manual, buat tabel berikut:

```sql
CREATE TABLE sensor_realtime (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  suhu DOUBLE,
  kelembapan_tanah INT,
  ph DOUBLE,
  relay TINYINT
);

CREATE TABLE sensor_hourly (
  hour_start DATETIME NOT NULL,
  suhu_avg DOUBLE,
  kelembapan_avg DOUBLE,
  ph_avg DOUBLE,
  PRIMARY KEY (hour_start)
);

CREATE TABLE pump_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  duration_sec INT,
  reason VARCHAR(20),
  action VARCHAR(10),
  note VARCHAR(255)
);
```

## Alur Data

- Endpoint `senddata.php` menerima data sensor dalam format JSON dan menyimpannya ke tabel `sensor_realtime`.
- `get_realtime.php?hours=6` mengambil data beberapa jam terakhir dari `sensor_realtime`.
- `get_hourly.php?days=7` mengambil data agregat per jam dari `sensor_hourly`.
- Halaman `index.html` menampilkan grafik dan akan memuat data 1H, 6H, 24H atau 7D dari basis data sesuai pilihan pengguna.

### Contoh Basis Data untuk Grafik

Folder `database/` menyediakan skrip SQL untuk mengisi basis data dengan data dummy:

- `schema.sql` — membuat tabel yang diperlukan.
- `seed_6h.sql` — contoh data 6 jam terakhir.
- `seed_24h.sql` — contoh data 24 jam (juga memenuhi grafik 6 jam).
- `seed_7d.sql` — contoh data agregat 7 hari.

Pada penggunaan `docker-compose`, berkas-berkas SQL di folder `database/` akan dijalankan secara otomatis saat kontainer MySQL pertama kali dibuat, sehingga contoh data untuk grafik telah tersedia.

## API Kunci

Untuk mengirim data ke `senddata.php`, sertakan header `X-API-KEY: GROWY_SECRET_123` dan JSON:

```json
{
  "suhu": 25.4,
  "kelembapan_tanah": 60,
  "ph": 6.5,
  "relay": 1
}
```
