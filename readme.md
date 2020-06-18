## REST API NPK - NPKS BILLING DOCUMENTATION
Dalam dokumen ini akan dijelaskan secara lengkap terkait dokumentasi dari REST-API NPK dan NPKS Billing, mulai dari struktur table hingga detail perfuntion untuk memudahkan proses developmennt, sekaligus apabila Rest-Api global ini digunakan untuk keperluan lainya.

### STRUKTUR DIRECTORY
Beberapa directory penting dalam proses development API ini kurang lebih hanya berkutat di folder - folder berikut :
| No | Directory                | Fungsi                                                                                                                                                                                         | Keterangan |
|----|--------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------|
|  1 | app/config/              | Digunakan untuk meletakkan file configuration seperti konfigurasi endpoint dan database                                                                                                        |            |
|  2 | app/Helper/Globalconfig/ | Pada folder ini disimpan file-file helper untuk semua fungsi global API seperti index, join, store, update, autocomplete, dan fungsi - fungsi yang bisa di re-use lainya                       |            |
|  3 | app/Helper/Npk/          | Folder ini menyimpan konfigurasi helper api khusus untuk NPK seperti API untuk Connect External Apps, realisasi npk billing, api untuk send dan approve, request TCA, hingga konfigurasi Uper  |            |
|  4 | app/Helper/Npks/         | Folder ini menyimpan konfigurasi helper api khusus untuk NPKS seperti api Connect External Apps, Cancel Request, konfigurasi container, E-invoice, Send Tos, Generate Tarif, dll               |            |
|  5 | app/Http/Controller/     | Digunakan sebagai controller atau file pertama yang dipanggil dari router yang kemudian setiap fungsinya akan dilempar ke helper sesuai dengan fungsi masing - masing                          |            |
| 6  | resources/views/print/   | Digunakan untuk menyimpan file konfigurasi print dan export untuk NPK dan NPKS seperti print invoice, proforma, uper, bprp, hingga export debitur, traffik produksi dan lain - lain            |            |
| 7  | public/                  | Digunakan untuk menyimpan semua file yang di upload dari UI                                                                                                                                    |            |

###



## Develop By Lumen
Documentation for the framework can be found on the [Lumen website](http://lumen.laravel.com/docs).
