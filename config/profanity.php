<?php

/*
|--------------------------------------------------------------------------
| Penyaring kata terlarang untuk chat tamu
|--------------------------------------------------------------------------
|
| Daftar ini dipakai App\Support\ProfanityFilter untuk menolak pesan tamu yang
| berisi umpatan, hinaan SARA, ancaman kekerasan, atau konten seksual. Hanya
| pesan tamu yang disaring; balasan admin tidak pernah diperiksa.
|
| CARA PENCOCOKAN (rinciannya di ProfanityFilter)
|
|  1. Teks dinormalisasi: huruf besar/kecil, aksen, homoglif Kiril/Yunani,
|     karakter lebar penuh, gaya "alay" (4→a, 3→e, 0→o, $→s), dan huruf
|     berulang ("anjiiing" → "anjing") semuanya diseragamkan.
|  2. Pencocokan selalu KATA UTUH, tidak pernah potongan kata. Ini disengaja:
|     tanpa batas kata, "asu" ikut menandai "rasul", "asuransi", dan "masuk",
|     sedangkan "sange" ikut menandai "pisang enak".
|  3. Teks juga diperiksa dalam bentuk "disambung", tempat rentetan penggalan
|     pendek digabung, sehingga "a n j i n g" dan "b.a.n.g.s.a.t" tetap
|     tertangkap sebagai satu kata.
|  4. Bila pesan menunjukkan tanda penyamaran yang jelas (banyak huruf tunggal
|     berpencar atau titik di tengah kata), penyaring menyalakan pemeriksaan
|     potongan kata pada teks yang seluruh spasinya dibuang. Tahap ini sengaja
|     dikunci di balik tanda penyamaran supaya kalimat normal tidak pernah
|     dinilai dengan cara itu.
|
| MENAMBAH ISTILAH
|
|  - `terms` ditulis huruf kecil. Aksen, angka, dan tanda baca tidak perlu —
|    normalisasi sudah menanganinya.
|  - `patterns` adalah regex yang diuji pada teks HASIL normalisasi. Artinya:
|    tanpa tanda baca, antarkata hanya satu spasi, dan HURUF GANDA SUDAH
|    DIRINGKAS. Tulis "ledakan", bukan "ledakkan".
|
*/

return [

    'enabled' => (bool) env('CHAT_PROFANITY_FILTER', true),

    // Panjang minimum istilah agar ikut diperiksa pada tahap 4 (teks rapat).
    // Di bawah ini risiko salah tangkap terlalu besar walau sudah ada tanda
    // penyamaran: "tai" akan mengenai "pantai", "anal" mengenai "analisis".
    'compact_min_length' => 5,

    /*
    |--------------------------------------------------------------------------
    | Kata yang JUSTRU harus lolos
    |--------------------------------------------------------------------------
    |
    | Kata sah yang memuat istilah terlarang di dalamnya. Disembunyikan sebelum
    | tahap 4 dijalankan. Penting untuk forum lintas agama dan suku: "Pantekosta"
    | memuat "pantek", "Silitonga" memuat "silit", "Rasulullah" memuat "asu".
    |
    */
    'exceptions' => [
        'pantekosta', 'pentakosta', 'rasul', 'rasulullah', 'silitonga',
        'memekik', 'memekakkan', 'celengan', 'asuransi', 'asuhan', 'asupan',
        'asusila', 'basuh', 'kasur', 'masuk', 'masukan', 'pasukan', 'pantai',
        'santai', 'petai', 'analisis', 'analisa', 'analog', 'kanal', 'brokoli',
        'pukis', 'babinsa', 'pisang', 'sarapan', 'bangkit', 'kentang',
    ],

    /*
    |--------------------------------------------------------------------------
    | Kategori
    |--------------------------------------------------------------------------
    |
    | Tiap kategori punya pesan penolakan sendiri supaya tamu tahu bagian mana
    | yang bermasalah tanpa kita mengulang kata kasarnya.
    |
    */
    'categories' => [

        /*
        | Konten seksual eksplisit dan alat kelamin.
        |
        | Istilah klinis dan netral sengaja TIDAK dimasukkan: "waria",
        | "pornografi", "pelecehan seksual", "masturbasi", dan "perawan" wajar
        | muncul pada pertanyaan serius soal perlindungan perempuan dan anak.
        */
        'seksual' => [
            'message' => 'Pesan mengandung kata bermuatan seksual. Silakan tulis ulang dengan bahasa yang sopan.',
            'terms' => [
                'kontol', 'konthol', 'kontl', 'kntl', 'memek', 'pepek', 'meki',
                'itil', 'jembut', 'peler', 'pelir', 'titit', 'toket', 'tetek',
                'nenen', 'tempik', 'turuk',
                'ngentot', 'ngentod', 'entot', 'entod', 'ngntd', 'ewe', 'ngewe',
                'ewean', 'coli', 'colmek', 'ngocok', 'onani', 'sange',
                'ngaceng', 'bokep', 'bugil', 'bispak', 'sepong', 'jilmek',
                'kimcil',
                // Pekerja seks sebagai hinaan
                'lonte', 'pelacur', 'sundal', 'sundala', 'jablay', 'perek',
                'jalang', 'gigolo',
                // Hinaan orientasi/identitas. Istilah hormat "waria" tidak termasuk.
                'bencong', 'banci', 'maho',
                // Daerah
                'silit', 'pantek', 'kanciang', 'lasso', 'kote', 'puki',
                'pukimak', 'pukimai', 'kimak', 'cipap', 'lancau', 'cibai',
                'pundek',
                // Asing
                'pussy', 'dick', 'blowjob', 'hentai', 'porn', 'horny',
                'bitch', 'whore', 'slut',
            ],
        ],

        /*
        | Hinaan SARA — suku, agama, ras, antargolongan.
        |
        | Kategori paling penting untuk FPK. Isinya hanya sebutan yang memang
        | merendahkan. Nama kelompok yang netral ("Tionghoa", "Yahudi",
        | "Nasrani", "Dayak") TIDAK boleh masuk daftar: menyebut nama sebuah
        | kelompok bukan penghinaan, dan memblokirnya justru membuat pertanyaan
        | tentang kerukunan tidak bisa dikirim.
        */
        'sara' => [
            'message' => 'Pesan mengandung ujaran yang merendahkan suku, agama, ras, atau golongan. FPK Kota Malang tidak menerima pesan semacam ini.',
            'terms' => [
                'cokin', 'singkek', 'singkeh', 'aseng', 'inlander',
                'negro', 'nigger', 'chink',
                // Slur politik yang lazim dipakai memecah belah
                'cebong', 'kecebong', 'kadrun', 'kampret',
                // Perendahan asal daerah
                'ndeso', 'udik', 'kampungan',
            ],
            'patterns' => [
                // "dasar cina", "namanya juga jawa" — nada merendahkan yang
                // menyasar nama suku/agama. Kata "khas" sengaja tidak dipakai:
                // "makanan khas Jawa" kalimat yang sepenuhnya wajar.
                '/\b(?:dasar|namanya\s+juga)\s+(?:orang\s+)?(?:cina|jawa|batak|madura|arab|papua|ambon|bugis|sunda|dayak|tionghoa|pribumi|islam|kristen|katolik|hindu|budha|konghucu)\b/u',
                // Menyandingkan nama kelompok dengan binatang atau umpatan.
                '/\b(?:cina|jawa|batak|madura|arab|papua|ambon|bugis|sunda|dayak|tionghoa|islam|kristen|katolik|hindu|budha|konghucu)\s+(?:babi|anjing|asu|monyet|bangsat|laknat|haram|sialan|kotor|najis)\b/u',
                '/\b(?:babi|anjing|asu|monyet|bangsat|laknat|najis)\s+(?:cina|jawa|batak|madura|arab|papua|ambon|bugis|sunda|dayak|tionghoa|islam|kristen|katolik|hindu|budha|konghucu)\b/u',
                // Ajakan mengusir kelompok atau merusak rumah ibadah.
                '/\b(?:usir|bubarkan|basmi|musnahkan|hancurkan|bakar)\s+(?:semua\s+|semua\s+orang\s+|orang\s+|rumah\s+|tempat\s+)?(?:cina|jawa|batak|madura|arab|papua|ambon|bugis|sunda|dayak|tionghoa|islam|kristen|katolik|hindu|budha|konghucu|masjid|gereja|pura|vihara|klenteng|kelenteng)\b/u',
            ],
        ],

        /*
        | Ancaman kekerasan.
        |
        | Hampir seluruhnya POLA, bukan kata tunggal. Alasannya praktis: "bakar"
        | ada pada "ayam bakar", "bom" pada "bom waktu", "bunuh" pada "membunuh
        | waktu". Yang menjadikannya ancaman adalah kehadiran pelaku atau
        | sasaran di sekitarnya.
        */
        'ancaman' => [
            'message' => 'Pesan terbaca sebagai ancaman kekerasan sehingga tidak dapat dikirim. Bila ini keadaan darurat, hubungi 110.',
            'terms' => [
                // Kata yang praktis selalu bermuatan kekerasan. Bentuk berimbuhan
                // seperti "penganiayaan" dan "pemerkosaan" tidak ikut tertandai
                // karena pencocokan memakai batas kata.
                'bacok', 'membacok', 'dibacok', 'gorok', 'digorok', 'penggal',
                'memenggal', 'cincang', 'mutilasi', 'culik', 'menculik',
                'perkosa', 'memperkosa', 'aniaya', 'keroyok', 'mengeroyok',
                'santet', 'teluh',
            ],
            'patterns' => [
                // Pelaku menyatakan niat: "aku bunuh", "gue akan bacok".
                // "bakar" dan "hancurkan" sengaja TIDAK di sini — "saya bakar
                // ayam" kalimat wajar. Perusakan ditangani pola sasaran di bawah.
                '/\b(?:aku|saya|gue|gua|gw|ane|tak|ta)\s+(?:akan\s+|bakal\s+|mau\s+|pengen\s+|siap\s+)?(?:bunuh|bacok|tembak|tusuk|gorok|penggal|habisi|hajar|keroyok|culik|perkosa|ledakan|cincang|pateni|mateni|gebuk|pentung|basmi|musnahkan)\b/u',
                // Bentuk terikat: "kubunuh", "kubacok".
                '/\bku(?:bunuh|bacok|tembak|tusuk|gorok|penggal|habisi|hajar|culik|bakar|cincang)\b/u',
                // Sasaran disebut setelah kata kerja: "bunuh kau", "gebuk lu".
                '/\b(?:bunuh|bacok|tembak|tusuk|gorok|penggal|habisi|hajar|keroyok|culik|perkosa|gebuk|cincang|pentung)\s+(?:kau|kamu|km|lu|lo|elu|elo|anda|kalian|dia|mereka|situ|ente|awakmu|koen|sampeyan|nyawamu|keluargamu)\b/u',
                // Harapan kematian yang ditujukan ke orang.
                '/\b(?:mati|mampus|modar|matek)\s+(?:kau|kamu|km|lu|lo|elu|elo|aja|saja|wae|sana)\b/u',
                // Pembakaran atau peledakan sasaran nyata.
                '/\b(?:bakar|ledakan|meledakan|bom|ngebom|hancurkan|rusak)\s+(?:rumah|kantor|sekolah|masjid|gereja|pura|vihara|klenteng|kelenteng|tempat|gedung|mobil|kantormu|rumahmu)\b/u',
                '/\b(?:pasang|taruh|kirim|ada)\s+bom\b/u',
                // Ancaman terbuka. "tunggu" tidak dipakai: "saya tunggu kamu di
                // kantor" justru kalimat ramah.
                '/\bawas\s+(?:kau|kamu|km|lu|lo|elu|elo)\b/u',
                '/\b(?:kutunggu|kucari|kudatangi|kusamperin|kuhabisi)\b/u',
            ],
        ],

        /*
        | Umpatan dan hinaan umum, termasuk bahasa daerah.
        */
        'umpatan' => [
            'message' => 'Pesan mengandung kata kasar. Silakan tulis ulang dengan bahasa yang sopan agar bisa kami bantu.',
            'terms' => [
                // Indonesia baku dan slang
                'anjing', 'anjeng', 'anjg', 'anjng', 'ajg', 'anjir', 'anjrit',
                'anjay', 'bangsat', 'bgst', 'bngst', 'bajingan', 'bjgn',
                'bangke', 'keparat', 'brengsek', 'berengsek', 'sialan',
                'laknat', 'jahanam', 'biadab', 'bejat', 'kunyuk', 'bangsul',
                'goblok', 'goblog', 'gblk', 'tolol', 'tlol', 'bego', 'begok',
                'dungu', 'sinting', 'gendeng', 'sarap', 'gembel', 'tai',
                'taik', 'tahi', 'taek', 'telek', 'bacot', 'bacod', 'sompret',
                'sontoloyo',
                // Jawa
                'jancok', 'jancuk', 'jancuq', 'jncok', 'jncuk', 'dancok',
                'diancok', 'diancuk', 'cok', 'cuk', 'asu', 'kirik', 'celeng',
                'bedes', 'wedus', 'matamu', 'ndasmu', 'raimu', 'mbokne',
                'pekok', 'gemblung', 'koplok', 'gathel', 'bajindul', 'kampang',
                // Sunda
                'belegug', 'kehed', 'bagong', 'borokokok', 'jurig',
                // Minang, Melayu, Batak
                'cilako', 'kanina', 'haramjadah',
                // Bugis, Makassar, Bali
                'tolo', 'cicing',
                // Asing
                'fuck', 'fucking', 'fck', 'shit', 'bastard', 'asshole',
                'motherfucker', 'damn', 'stupid', 'idiot', 'moron',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Kata yang bergantung konteks
    |--------------------------------------------------------------------------
    |
    | Kata di bawah ini SAH pada percakapan biasa dan baru dihitung sebagai
    | hinaan bila BERSEBELAHAN dengan penanda sasaran. "Babi" wajar pada
    | pertanyaan makanan halal, "kafir" dan "murtad" wajar pada diskusi
    | keagamaan, "monyet" wajar pada pertanyaan kebun binatang — tetapi "dasar
    | babi", "kamu kafir", dan "monyet lu" jelas serangan.
    |
    | `distance` sengaja 1 (harus bersebelahan). Dengan 2, kalimat wajar seperti
    | "apakah kamu makan babi" ikut tertolak, dan itu justru mematikan fungsi
    | forum ini sebagai tempat bertanya soal kerukunan.
    |
    */
    'contextual' => [
        'distance' => 1,
        'message' => 'Pesan terbaca sebagai hinaan kepada orang atau kelompok lain. Silakan sampaikan maksud Anda tanpa merendahkan pihak mana pun.',
        'markers' => [
            'kau', 'kamu', 'km', 'lu', 'lo', 'elu', 'elo', 'anda', 'kalian',
            'situ', 'ente', 'awakmu', 'koen', 'sampeyan', 'dasar', 'si',
        ],
        'terms' => [
            // Binatang yang lazim dipakai merendahkan orang
            'babi', 'monyet', 'kera', 'kadal', 'ular', 'tikus', 'kecoa',
            'cacing', 'kambing', 'keledai', 'buaya', 'bangkai',
            // Sebutan keagamaan — netral pada diskusi, menyerang bila ditujukan
            'kafir', 'murtad', 'musyrik', 'sesat', 'najis', 'haram', 'zionis',
            'teroris', 'radikal', 'fanatik', 'munafik',
            // Umum
            'setan', 'iblis', 'gila', 'sampah', 'busuk', 'bodoh', 'miskin',
            'kotor', 'jelek', 'homo',
        ],
    ],

];
