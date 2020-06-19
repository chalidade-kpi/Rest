## REST API NPK - NPKS BILLING DOCUMENTATION
Dalam dokumen ini akan dijelaskan secara lengkap terkait dokumentasi dari REST-API NPK dan NPKS Billing, mulai dari struktur table hingga detail perfuntion untuk memudahkan proses developmennt, sekaligus apabila Rest-Api global ini digunakan untuk keperluan lainya.

## 1. BASIC INSTALLATION
### 1.1. Install Composer
Langakah awal yang wajib dilakukan sebelum menggunakan API ini adalah pastikan composer sudah terinstall di komputer atau server. Download composer melalui link berikut [composer](https://getcomposer.org/download/). Apabila masih bingung cara installasinya, bisa membuka tutorial berikut : [Very Simple, How To Install Composer](https://medium.com/@chalidade).

### 1.2. Download API
Setelah menginstall composer di komputer, download atau clone Rest di halaman ini. Anda bisa mendownload zip dengan klik [link](https://codeload.github.com/chalidade/Rest/zip/dev) berikut. Kemudian letakkan di file htdocs jika Anda menggunakan Xampp atau /var/ww/html/ jika Anda menggunakan Linux.

### 1.3. Setup Composer
Jika sudah, buka terminal / CMD masuk ke direktory project dan ketikkan composer install.
> composer install

Fungsi ini akan otomatis menginstall semua package dan vendor untuk menjalankan API. Jika Anda tidak melakukan langkah ini, maka akan mendapat error message seperti dibawah :
```
Warning: require_once(D:\xampp\htdocs\lupi\bootstrap/../vendor/autoload.php): failed to open stream: No such file or directory in ...
Fatal error: require_once(): Failed opening required ‘D:\xampp\htdocs\lupi\bootstrap/../vendor/autoload’ (include_path=’D:\xampp\php\PEAR’) in ...
```

### 1.4. Setting Connection
Selanjutnya buka folder config/database.php untuk setting koneksi database dan lakukan konfigurasi databasenya.
#### 1.4.1. Contoh Config MySQL :
```
 'exampleMysql'  => [
    'driver'    => 'mysql',
    'host'      => env('DB_HOST', 'localhost'),
    'port'      => env('DB_PORT', 3306),
    'database'  => env('DB_DATABASE', 'your_database'),
    'username'  => env('DB_USERNAME', 'your_username'),
    'password'  => env('DB_PASSWORD', 'your_pass'),
    'charset'   => env('DB_CHARSET', 'utf8'),
    'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
    'prefix'    => env('DB_PREFIX', ''),
    'timezone'  => env('DB_TIMEZONE', '+00:00'),
    'strict'    => env('DB_STRICT_MODE', false),
],
```

#### 1.4.2. Contoh Config ORACLE :
```
 'exampleOracle'  => [
    'driver'        => 'oracle',
    'tns'           => env('DB_TNS', ''),
    'host'          => env('DB_HOST', 'ipserver'),
    'port'          => env('DB_PORT', 1521),
    'database'      => env('DB_DATABASE', 'your_database'),
    'username'      => env('DB_USERNAME', 'your_username'),
    'password'      => env('DB_PASSWORD', 'your_pass'),
    'charset'       => env('DB_CHARSET', 'AL32UTF8'),
    'prefix'        => env('DB_PREFIX', ''),
    'prefix_schema' => env('DB_SCHEMA_PREFIX', ''),
    'edition'       => env('DB_EDITION', 'ora$base'),
],
```

Pastikan nama database, host, username, dan password benar. Anda dapat mengubah nama konfigurasi sesuai keinginan anda. Nama konfigurasi ini lah yang digunakan untuk mempermudah penggunaan nanti.


## 2. STRUKTUR DAN FUNGSI DIRECTORY
Beberapa directory penting dalam proses development API ini kurang lebih hanya berkutat di folder - folder berikut :
| No | Directory                | Fungsi                                                                                                                                                                                         | Keterangan                                              |
|----|--------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------|
|  1 | app/config/              | Digunakan untuk meletakkan file configuration seperti konfigurasi endpoint dan database                                                                                                        | Boleh ditambahkan tapi tidak diubah                     |
|  2 | app/Helper/Globalconfig/ | Pada folder ini disimpan file-file helper untuk semua fungsi global API seperti index, join, store, update, autocomplete, dan fungsi - fungsi yang bisa di re-use lainya                       | Harap tidak diubah - ubah kecuali jika memang ada error |
|  3 | app/Helper/Npk/          | Folder ini menyimpan konfigurasi helper api khusus untuk NPK seperti API untuk Connect External Apps, realisasi npk billing, api untuk send dan approve, request TCA, hingga konfigurasi Uper  | Boleh dimodifikasi                                      |
|  4 | app/Helper/Npks/         | Folder ini menyimpan konfigurasi helper api khusus untuk NPKS seperti api Connect External Apps, Cancel Request, konfigurasi container, E-invoice, Send Tos, Generate Tarif, dll               | Boleh dimodifikasi                                      |
|  5 | app/Http/Controller/     | Digunakan sebagai controller atau file pertama yang dipanggil dari router yang kemudian setiap fungsinya akan dilempar ke helper sesuai dengan fungsi masing - masing                          | Boleh dimodifikasi                                      |
| 6  | resources/views/print/   | Digunakan untuk menyimpan file konfigurasi print dan export untuk NPK dan NPKS seperti print invoice, proforma, uper, bprp, hingga export debitur, traffik produksi dan lain - lain            | Boleh dimodifikasi                                      |
| 7  | public/                  | Digunakan untuk menyimpan semua file yang di upload dari UI                                                                                                                                    | Boleh dimodifikasi                                      |

Note : Apabila ada service baru, ada baiknya dibuatkan sebuah helper beru (folder baru di dalam Helper) agar tidak menginteferensi API existing.

## 3. FUNGSI FILE DI DALAM SEBUAH DIRECTORY
Berikut adalah penjelasan tiap - tiap file dalam sebuah directory pada REST-API NPK NPKS Billing :
| No | Nama File                     | Lokasi Folder                                     | Fungsi                                                                                                                                                                                                                                  |
|----|-------------------------------|---------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 1  | composer.json                 | \                                                 | Dimodifikasi apabila ada penambahan package atau helper baru di dalam API                                                                                                                                                               |
| 2  | .env                          | \                                                 | Apabila setelah installasi dilakukan dan coba diakses gagal periksa apakah sudah ada file .env -nya. pastikan mengisi APP_KEY juga.                                                                                                     |
| 3  | database.php                  | \app\config\database.php                          | File untuk menyimpan konfigurasi database Aplikasi                                                                                                                                                                                      |
| 4  | enpoint.php                   | \app\config\endpoint.php                          | File untuk menyimpan konfigurasi endpoint untuk connnectexternalapp (aplikasi via esb)                                                                                                                                                  |
| 5  | BillingEngine.php             | \app\Helper\Globalconfig\BillingEngine.php        | File konfigurasi global untuk perhitungan tarif, simulasi tarif, dan semua hal terkait pentarifan                                                                                                                                       |
| 6  | FileUpload.php                | \app\Helper\Globalconfig\FileUpload.php           | File konfigurasi untuk meng-convert file menjadi base64 kemudian disimpan di folder public pada proses upload di NPK - NPKS                                                                                                             |
| 7  | GlobalHelper.php              | \app\Helper\Globalconfig\GlobalHelper.php         | Menyimpan konfigurasi helper untuk api global seperti index, list, update, delete, store, saveheaderdetail, dll                                                                                                                         |
| 8  | ListIndexExt.php              | \app\Helper\Globalconfig\ListIndexExt.php         | Menyimpan konfigurasi fungsi global dari extention index seperti clear session, clear login, clear token, dll                                                                                                                           |
| 9  | PrintAndExport.php            | \app\Helper\Globalconfig\PrintAndExport.php       | Menyimpan semua konfigurasi api untuk print dan export                                                                                                                                                                                  |
| 10 | UserAndRoleManagemnt.php      | \app\Helper\Globalconfig\UserAndRoleManagemnt.php | Menyimpan semua konfigurasi api untuk User dan Role Management                                                                                                                                                                          |
| 11 | ViewExt.php                   | \app\Helper\Globalconfig\ViewExt.php              | Menyimpan konfigurasi API khusus untuk kebutuhan view, seperti getDebitur, getRekonsilasi, viewCancelCargo, dll                                                                                                                         |
| 12 | ConnectedExternalAppsNPK.php  | \app\Helper\Npk\ConnectedExternalAppsNPK.php      | Menyimpan konfigurasi API khusus untuk koneksi dengan aplikasi luar (aplikasi via esb) dimana endpoint bisa di cek dibagian config.php                                                                                                  |
| 13 | RealisasiHelper.php           | \app\Helper\Npk\RealisasiHelper.php               | Menyimpan konfigurasi API khusus untuk realisasi di NPK                                                                                                                                                                                 |
| 14 | RequestBookingNPK.php         | \app\Helper\Npk\RequestBookingNPK.php             | File konfigurasi API untuk kebutuhan send, approve, reject request                                                                                                                                                                      |
| 15 | RequestTCA.php                | \app\Helper\Npk\RequestTCA.php                    | File konfigurasi untuk kebutuhan request TCA                                                                                                                                                                                            |
| 16 | UperRequest.php               | \app\Helper\Npk\UperRequest.php                   | File konfigurasi untuk kebutuhan request uper di NPK Billing seperti storepayment, confirmpaymentuper, updatestatusnota, dll                                                                                                            |
| 17 | CanclHelper.php               | \app\Helper\Npks\CanclHelper.php                  | Menyimpan konfigurasi untuk berbagai kebutuhan cancel request di NPKS                                                                                                                                                                   |
| 18 | ConnectedExternalAppsNPKS.php | \app\Helper\Npks\ConnectedExternalAppsNPKS.php    | Menyimpan konfigurasi API khusus untuk koneksi dengan aplikasi luar (aplikasi via esb) di NPKS dimana endpoint bisa di cek dibagian config.php                                                                                          |
| 19 | ContHist.php                  | \app\Helper\Npks\ContHist.php                     | Menyimpan konfigurasi untuk kebutuhan container seperti menyimpan ke history container NPKS                                                                                                                                             |
| 20 | EInvo.php                     | \app\Helper\Npks\EInvo.php                        | Menyimpan semua konfigurasi terkait E-invoice seperti getinvoiceAr, sendReceipt,sendInvoiceApply, dll                                                                                                                                   |
| 21 | FunctTOS.php                  | \app\Helper\Npks\FunctTOS.php                     | File yang menyimpan semua konfigurasi untuk keperluan dari dan ke Repo NPKS seperti get realisasi dan send data ke Repo / TOS                                                                                                           |
| 22 | GenerateTariff.php            | \app\Helper\Npks\GenerateTariff.php               | File yang digunakan untuk menyimpan konfigurasi dan mengatur inputan sebelum dilempar dan dihitung oleh prosedur database                                                                                                               |
| 23 | RequestBookingNPKS.php        | \app\Helper\Npks\RequestBookingNPKS.php           | File konfigurasi API untuk kebutuhan send, approve, reject request di NPKS                                                                                                                                                              |
| 24 | AuthController.php            | \app\Http\Controllers\AuthController.php          | File konfigurasi untuk mengatur terkait authentikasi di API                                                                                                                                                                             |
| 25 | routes.php                    | \app\Http\routes.php                              | File untuk mengarahkan dari endpoint menuju controller mana yang dieksekusi                                                                                                                                                             |
| 26 | IndexController.php           | \app\Http\Controllers\IndexController.php         | File pertama yang dieksekusi oleh lumen melalui route ketika memangil endpoint "/index" di browser, di dalam IndexController ini juga menyimpan semua fungsi sebelum di lempar pada tiap - tiap helper sesuai kebutuhan masing - masing |
| 27 | StoreController.php           | \app\Http\Controllers\StoreController.php         | File pertama yang dieksekusi oleh lumen melalui ketika memanggil endpoint "/store"                                                                                                                                                      |
| 28 | ViewController.php            | \app\Http\Controllers\ViewController.php          | File pertama yang dieksekusi oleh lumen melalui ketika memanggil endpoint "/view"                                                                                                                                                       |

## 4. PENJELASAN PER-FUNCTION TIAP FILE
Berikut adalah penjabaran tiap - tiap function pada file helper API.
### A.  /Helper/Globalconfig/
#### A.1. BillingEngine.php
| No | Nama Function                     | Fungsi                                                                                                                                                                                                     |
|:--:|-----------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
|  1 | storeProfileTariff                |  Menyimpan header profile tarif data dan input pentarifan, berhubungan dengan insert data ke TxProfileTariffHdr dan TsTarif juga berhubugan dengan create iso.                                             |
|  2 | storeProfileTariffDetil           | Menyimpan detail dari profil tarif data, sistem save detail terpisah dari header untuk menanggulangi case ketika tarif di inputkan dan ada error ditengah - tengah, agar tidak mengulang input dari awal.  |
|  3 | deleteProfileTariffDetil          | Menghapus detail profile tarif berdasarkan tariff_id yang diinputkan.                                                                                                                                      |
|  4 | storeCustomerProfileTariffAndUper | Menyimpan data customer profile tarif dan uper dengan meng-insert ke tabel TS_CUSTOMER_PROFILE dan TS_UPER DB engine.                                                                                      |
|  5 | listProfileTariffDetil            | Menampilkan list profile tarif detail serta berhubungan dengan ISO_CODE                                                                                                                                    |
|  6 | viewProfileTariff                 | Menampilkan list profile tarif header dan detail serta berhubungan dengan ISO_CODE                                                                                                                         |
|  7 | viewCustomerProfileTariff         | Menampilkan list customer profile dan tarif berdasarkan inputan cust_profile_id nya.                                                                                                                       |
|  8 | calculateTariff                   | Membatik berdasarkan data inputan untuk perhitungan tarif via prosedur database                                                                                                                            |
|  9 | calculateTariffExcute             | Menjalankan prosedure untuk perhitungan tarif dari hasil batikan fungsi calculateTariff                                                                                                                    |
| 10 | getSimulasiTarif                  | Membatik dan menjalankan prosedur untuk simulasi tariff                                                                                                                                                    |

#### A.2. FileUpload.php
| No | Nama Function | Fungsi                                                                                                                               |
|:--:|---------------|--------------------------------------------------------------------------------------------------------------------------------------|
|  1 | upload_file   | Meng-create sebuah folder di dalam folder public, kemudian men-decode file berformat base64 dan menyimpannya kedalam folder tersebut |

#### A.3. GlobalHelper.php
| No | Nama Function      | Fungsi                                                                                                                      |
|:--:|--------------------|-----------------------------------------------------------------------------------------------------------------------------|
|  1 | viewHeaderDetail   | Menampilkan data dalam format header detail, biasanya digunakan ketika view detail di UI                                    |
|  2 | index              | Menampilkan data dalam list sesuai dengan kondisi / parameter yang ditentukan                                               |
|  3 | filter             | Memfilter data sesuai dengan input dan parameter yang dideklarasikan                                                        |
|  4 | filterByGrid       | Memfilter data dalam grid table, biasanya digunakan di UI Ketika ada table dan ingin mencari data melalui klik header table |
|  5 | autoComplete       | Mencari data berdasarkan inputan dalam format autocomplete dibuat sesuai kebutuhan UI dengan format JSON sesuai extJs       |
|  6 | join               | Menampilkan data dengan fungsi join pada beberapa table                                                                     |
|  7 | whereQuery         | Memiliki fungsi mirip dengan autocomplete namun dengan tambahan where dan filter                                            |
|  8 | tanggalMasukKeluar | Mendapatkan tanggal masuk dan keluar untuk kebutuhan proforma NPK Banten                                                    |
|  9 | saveheaderdetail   | Menyimpan / Mengupdate data dalam format header detail                                                                      |
| 10 | update             | Mengupdate data untuk kebutuhan lebih spesifik                                                                              |
| 11 | delHeaderDetail    | Menghapus data dalam format header detail                                                                                   |
| 12 | getUper            | Mendapatkan nilai uper untuk kebutuhan print proforma dan invoice                                                           |
| 13 | totalPenumpukan    | Mendapatkan nilai total penumpukan untuk kebutuhan print proforma dan invoice                                               |



## Develop By Lumen
Documentation for the framework can be found on the [Lumen website](http://lumen.laravel.com/docs).
