## REST API NPK - NPKS BILLING DOCUMENTATION
Dalam dokumen ini akan dijelaskan secara lengkap terkait dokumentasi dari REST-API NPK dan NPKS Billing, mulai dari struktur table hingga detail perfuntion untuk memudahkan proses developmennt, sekaligus apabila Rest-Api global ini digunakan untuk keperluan lainya.

## BASIC INSTALLATION
### Install Composer
Lumen need [composer](https://getcomposer.org/download/) to manage its dependencies. So before using REST API, make sure you installed on your machine. If you don't know how to install composer just visit my medium post about [Very Simple, How To Install Composer](https://medium.com/@chalidade).

### Download API
After install composer in your machine, download or clone Rest in this page. You can Download Zip by clicking this [link](https://codeload.github.com/chalidade/Rest/zip/dev). Then put your file into htdoc if you're using Xampp or var/www/html/ if you're using linux server. then extract.

### Setup Composer
Next, open terminal then go to your REST API Directory. Type and enter
> composer install

This function will automatically install All package or vendor to run REST API. You will get error like this, if you don't do this step.
```
Warning: require_once(D:\xampp\htdocs\lupi\bootstrap/../vendor/autoload.php): failed to open stream: No such file or directory in ...
Fatal error: require_once(): Failed opening required ‘D:\xampp\htdocs\lupi\bootstrap/../vendor/autoload’ (include_path=’D:\xampp\php\PEAR’) in ...
```

### Setting Connection
Open folder config/database.php to setting your connection between REST API and your database. You can copy exampleMysql and change value as your configuration.
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

Make sure your database name, username, and password is right. You can change name of your configuration with rename exampleMysql as you want. Keep it mind, that name will use in parameter REST API. So make easier.


## STRUKTUR DAN FUNGSI DIRECTORY
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

## FUNGSI FILE DI DALAM SEBUAH DIRECTORY
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



## Develop By Lumen
Documentation for the framework can be found on the [Lumen website](http://lumen.laravel.com/docs).
