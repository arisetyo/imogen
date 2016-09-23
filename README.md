Imogen adalah framework scaffolding menggunakan MySQL sebagai database, RedBean & SlimPHP sebagai REST API, dan Angular & Bootstrap sebagai antarmuka.

Cara penggunaan:

1. Unduh atau pull framework
2. Jalankan `bower install`
3. Atur konfigurasi database di direktori `config`
4. Buka `localhost/imogen/_generator/`

*Log Perubahan*

1.2
- Menghilangkan CSS style custom untuk menu (sb-admin)
- Memindahkan bagian menu ke bagian atas halaman
- Mengubah halaman entry dan edit menjadi dua kolom
- Kolom default "created_ts"
- Konfigurasi timezone di app.yaml

1.2.1
- memindahkan home.html ke direktori template
- memindahkan aplikasi ke direktori _app_ (termasuk direktori `controllers` & `views` scaffold)

1.2.2
- menggunakan Angular Service untuk panggilan-panggilan http
- memindahkan fungsi http dari Controller ke Service
- penyederhanaan penulisan model data di form entry/edit