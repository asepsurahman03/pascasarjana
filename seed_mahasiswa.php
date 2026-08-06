<?php
/**
 * Seed Mahasiswa dari Data Laporan Daftar Mahasiswa
 * Password default: nusaputraku
 * Login bisa dengan: NIM, email kampus, atau email pribadi
 */
$host = 'localhost'; $db = 'pascasarjana_unp'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (Exception $e) { die("DB Error: " . $e->getMessage() . "\n"); }

$defaultPassword = password_hash('nusaputraku', PASSWORD_DEFAULT);

// Ambil prodi_id
$prodiMap = [];
$prodiRows = $pdo->query("SELECT id, nama FROM prodi")->fetchAll();
foreach ($prodiRows as $p) $prodiMap[$p['id']] = $p['nama'];
// prodi_id: 1=Informatika, 2=Manajemen, 3=Hukum, 4=Pedagogi, 5=Doktor Ilmu Komputer

echo "=== PRODI MAP ===\n";
foreach ($prodiMap as $id => $n) echo "  $id = $n\n";

// ─── DATA MAHASISWA ─────────────────────────────────────────────────────────
// Format: [nim, nama, nik, jk(L/P), nama_ibu, agama, kelas, tempat_lahir, tgl_lahir, alamat, hp, email_kampus, email_pribadi, prodi_id, angkatan, status]

$mahasiswaData = [

    // ══════════════════════════════════════════════════════════
    // S2 INFORMATIKA (prodi_id=1) - Semester Gasal 2025/2026
    // ══════════════════════════════════════════════════════════
    ['20250130001','ASEP SURAHMAN SULAEMAN','3203180303030004','L','AMAH','Islam','Kelas B','CIANJUR','2003-03-03','Kp Babakan Kondang Desa Hegarmanah Kec Takokak Kab Cianjur','085659836977','asep.surahman@nusaputra.ac.id','asepsurahman03@gmail.com',1,2025,'Aktif'],
    ['20250130002','SURYO PRAYOGO DOETOYO','3578042503940012','L','Partini Nuraeni','Islam','SOCS A','LAHAT','1994-03-25','Komplek TNI AD G 46','082812400209823','suryo.prayogo@nusaputra.ac.id','satriadnata97@gmail.com',1,2025,'Aktif'],
    ['20250130003','RIMACHINA NOVIANA MEGA','3175045110220001','P','Wanny Berbudi','Islam','SOCS A','SERANG','2002-11-15','JL. BUMI PRATAMA VIII BLOK.N NO.1','6282130134215','rimachina.noviana@nusaputra.ac.id','rimadhinanmega15@gmail.com',1,2025,'Aktif'],
    ['20250130004','AUZARINA WUKIRASH','3171506100220001','P','Lowiek Widhi Pangestu','Islam','Kelas B','JAKARTA','2002-10-16','KOPLEK TNI AD G 46','6282128282157','auzarina.wukirash@nusaputra.ac.id','auzarinawukirash16@gmail.com',1,2025,'Aktif'],
    ['20250130005','ABUBAKAR IBRAHIM MUHAMMAD','602343968','L',null,'Islam',null,'NIGERIA','1998-08-04',null,'620908188236','kutfaabubakar58@gmail.com','kutfaabubakar58@gmail.com',1,2025,'Aktif'],
    ['20250130006','ARMAN SARI','3202291102890001','L','YUYU YUHAETI','Islam','Kelas B','SUKABUMI','1989-02-11','jl. padajaran 2 kp rambay tengan ds. sukamanah rt 24 rw 08 kec cisaat kab sukabumi','085724421148','arman.sari_mif25@nusaputra.ac.id','armansari625@gmail.com',1,2025,'Aktif'],
    ['20250130007','MUHAMMAD ABDUL AZIZ','3202332404000002','L','DEDEH SOLIHAH','Islam','Kelas B','SUKABUMI','2000-04-24','Kp. sibayawak rt 01/00 desa langensari kec. sukaraja kab. sukabumi','088809571974','muhammad.abdul_mif25@nusaputra.ac.id','muhammadabdulaziz0424@gmail.com',1,2025,'Aktif'],
    ['20250130008','HILMAN YUNUS','3272055201860021','L','NURYATI','Islam','Kelas B','SUKABUMI','1986-02-01','Jl anubawasasana no 132','087726840673','hilman.yunus_mif25@nusaputra.ac.id','jumphier07@gmail.com',1,2025,'Aktif'],
    ['20250130009','Febryanto Dwi Putra Sulistyo','3674070402840005','L','Sri Pujati','Islam','SOCS A','Jakarta','1984-02-04',null,'081910234444','febryanto.dwiputra@nusaputra.ac.id','febryanto.dwiputra@nusaputra.ac.id',1,2025,'Aktif'],
    ['20250130010','Ardhi Pamungkas','3171070501950004','L','Maryuliah','Islam','SOCS A','Jakarta','1995-01-05','Jl. Sabeni No 6','081285182752','ardhi.pamungkas@nusaputra.ac.id','ardhi.pamungkas@nusaputra.ac.id',1,2025,'Aktif'],
    ['20250130011','Ahmad Hilmy Al-Syafa',null,'L','Ibu',null,'SOCS A','Magelang','1985-04-30',null,null,'ahmad.hilmy@nusaputra.ac.id',null,1,2025,'Aktif'],
    ['20250130012','JULIUS CASPAR BERIYAPRANATA','3578201007980003','L','Theresia','Katolik','SOCS A','SURABAYA','1985-07-10','JAJARTUNGGAL SELATAN K21','081331477808','julius.caspar@nusaputra.ac.id','juliuskaspar@gmail.com',1,2025,'Aktif'],
    ['20250130013','IBNU DAUD','3201111805960001','L','Manik','Islam','SOCS A','BOGOR','1996-11-18','KP PADURENAN','085819118309','ibnu.daud@nusaputra.ac.id','ibnu.daud@nusaputra.ac.id',1,2025,'Aktif'],
    ['20250130014','RIZKY ARYA EKA PUTRA','3171206040870002','L','Ernawati','Islam','SOCS A','SURABAYA','1997-08-04','JL. RAWA BJAKTI NO.5B','088970298846','rizky.arya@nusaputra.ac.id','rizky.arya@nusaputra.ac.id',1,2025,'Aktif'],
    ['20250130015','TASYA YUSTIRA AGUSTIAN','3202296008850008','P','TATI MARYATI','Islam','Kelas B','SUKABUMI','1985-08-28','Kp. Cibolang Gg. Muhajirin',null,'tasya.yustira28@gmail.com','tasya.yustira28@gmail.com',1,2025,'Aktif'],
    ['20250130016','DEAS AGHELLAR','3272024412890021','P','ASTIANI','Islam','Kelas B','BEKASI','1989-12-31','Gg. Kawani 3 no 8 RT 006/08','085874158574','deas.aghellar@nusaputra.ac.id','daghellar@gmail.com',1,2025,'Aktif'],
    ['20250130017','DINDA TASYA MAHARDIKA','3202124304000002','P','IKA RENIKA','Islam','Kelas B','SUKABUMI','2000-03-04','Kp Panuyuran Rt002/00 Desa Cisarua kecamatan Cisarua kab sukabumi','085157410032','dinda.tasya_mif25@nusaputra.ac.id','dinda.tasya_mif25@nusaputra.ac.id',1,2025,'Aktif'],
    ['20250130018','ALFIAN PAMUNGKAS SARAWIGUNA','3202143011950003','L','Imas Masitoh','Islam','SOCS A','SUKABUMI','1995-11-30','TATAR LOKACITRA JL LOKASUNYA NO.10','081382494241','alfian.pamungkas@nusaputra.ac.id','alfian.saka@gmail.com',1,2025,'Aktif'],
    ['20250130019','JAUHARUL ARIFIN','3514123040100003','L','Juliali','Islam','SOCS A','PASURUAN','1990-04-13','APT. BASSURA CITY C-29-CS JL. BASUKI RAHMAT NO.1A','081615669445','jauharul.arifin@nusaputra.ac.id','jauharul.arifin@nusaputra.ac.id',1,2025,'Aktif'],
    ['20250130020','GUGUN NURYAKIN','3202052405880006','L','IBU',null,'Kelas B','SUKABUMI','1988-08-24',null,'082165016716','gugun.nuryakin_ci25@nusaputra.ac.id','gugun.nuryakin_ci25@nusaputra.ac.id',1,2025,'Aktif'],
    ['20250130021','GUGUN NURYAKIN','3202052405880006','L','IBU',null,'Kelas B','SUKABUMI','1988-08-24',null,'082165016716','gugun.nuryakin_cis25@nusaputra.ac.id','gugun.nuryakin_cis25@nusaputra.ac.id',1,2025,'Aktif'],
    ['20250130022','INGDRID ELISABETH PARDEDE','1212020610030002','P','LILIS SIAGIN','Kristen','SOCS A','DURI','2003-10-20','SITANGKOLA','081254098047','ingdrid.elisabeth@nusaputra.ac.id','ingdridpardede72@gmail.com',1,2025,'Aktif'],
    ['20250130023','JOHANNES BASTIAN JASA SIPAYANG','1271021706050002','L','SARI RONITA PURBA','Kristen','SOCS A','MEDAN','2005-02-16','AS RAJA NO. 125 MEDAN','082161919411','johannes.bastian@nusaputra.ac.id','johannessipayang27@gmail.com',1,2025,'Aktif'],
    ['20250130024','SYAHRIAL JEREMIA SINAGA','1212021108040003','L','MOLYSTAR SIMANUNTAK','Kristen','SOCS A','JAKARTA','2004-09-03','SIBARAN NADAMPULUNAMUNGKUP','087731758649','syahrial.jeremia@nusaputra.ac.id','syahrialsinaga@gmail.com',1,2025,'Aktif'],
    ['20250130025','FRANS ELO HANSEN PANJAITAN','1212021210040002','L','MEIDA SIANTURI','Kristen','SOCS A','BALIGE','2004-01-21','HINALANG BAGASAN','081292067576','trans.elo@nusaputra.ac.id','transparijantan21z@gmail.com',1,2025,'Aktif'],
    ['20250130026','VIVALID ADVENTUS SIMANGUNSONG','1202041312040002','L','RINI MARLINA SIHAAN','Kristen','SOCS A','PADANG','2004-12-13','SANGKAE DOLOK-SIANJUR','082277922115','vivalid.adventus@nusaputra.ac.id','vivadvent@gmail.com',1,2025,'Aktif'],
    ['20250130027','BENYAMIN SIBARAN','1212020006040003','L','LASMARINA SINAGA','Kristen','SOCS A','LAGUBOTI','2004-06-20','JL. DIPONEGORO','081360344859','benyamin.sibaran@nusaputra.ac.id','jamine111224@gmail.com',1,2025,'Aktif'],
    ['20250130028','CALVIN JOSÉP SILAEN','1212011806040002','L','SUSY MARLINA PURBA','Kristen','SOCS A','MEDAN','2004-06-18','JL. HUTABOLON','082164541765','calvin.josép@nusaputra.ac.id','calvinjosep123@gmail.com',1,2025,'Aktif'],
    ['20250130029','JOICE SHARON GABRIELLA SINAGA','1500815408040000','P','MUITARA HUTABALIAN','Kristen','SOCS A','JAMBI','2004-08-14','KPR - TIP BLOK H. III NO. 04','085760418785','joice.sharon@nusaputra.ac.id','joicesharon14@gmail.com',1,2025,'Aktif'],
    ['20250130030','BAMBANG WAHYUDI','3174011152640008','L','SITI MADUUNAH','Islam','SOCS A','TEGAL','1964-08-15','TEBET TIMUR DALAM VIII-W NO.23','081320008923','bambang.wahyudi@nusaputra.ac.id','yuthiwakita64@gmail.com',1,2025,'Aktif'],
    ['20250130031','MUZAKKI AFANDI','3514112008000003','L','MUSLIKAN','Islam','SOCS A','PASURUAN','2000-02-20','DUSUN KEDAWUNG','085731730969','muzakki.afandi@nusaputra.ac.id','almuzakker@gmail.com',1,2025,'Aktif'],
    ['20250130032','SRI RAHMAWATI','3202125309010003','P','NANI ROHANI','Islam','SOCS A','SUKABUMI','2001-09-03','KP. PASIR BENTIK','085723651315','sri.rahmawati@nusaputra.ac.id','srirahmawati1961@gmail.com',1,2025,'Aktif'],
    ['20250130033','SEPTI MUHAMMAD AGUSTIRA','3272071608060021','L','CUCUM','Islam','Kelas B','SUKABUMI','2006-08-16','LIMUSNUNGGAL','089541138552','septi.muhammad_mim25@nusaputra.ac.id','septimuhammadagustira21@gmail.com',1,2025,'Aktif'],
    ['20250130034','YESSI ASTRIANA','1100010010010001','P','ASMINA','Islam','SOCS A','PURWOKERTO','1994-05-20','JL. ANGGUR RAYA BLOK C3 NO 7 PERUM SUKATANI PERMAI','081232041190','yessi.astriana@nusaputra.ac.id','yessiyastryana@gmail.com',1,2025,'Aktif'],
    ['20250130035','RISWAN ZAENAL ARIPIN','3202330711950001','L','TETI SUPRIATI','Islam','Kelas B','TANGERANG','1995-11-07','VILLA ADIPRIMA JL. ARJUNA C5 NO. 24','087834671018','riswan.zaenal@nusaputra.ac.id','ari91168240@gmail.com',1,2025,'Aktif'],

    // S2 INFORMATIKA - Semester Gasal 2024/2025 (angkatan 2024)
    ['20240130001','Bekto Suprapto','3174040704540006','L','Sugiran','Kristen','Kelas A','Yogyakarta','1954-04-07',null,'085697087746','bekto.suprapto@nusaputra.ac.id','supraptobekto@gmail.com',1,2024,'Aktif'],
    ['20240130002','I Made Redi Hartana','3275031806850028','L','Lusiyanti','Islam','Kelas A','Intramayu','1985-03-18',null,'081295582006','made.redi@nusaputra.ac.id','devyhasanah94@gmail.com',1,2024,'Aktif'],
    ['20240130003','Antonius Endra Prabow','3374052509750003','L','Kuwati Rahayu','Katolik','Kelas A','Kabupaten Semarang','1975-09-25',null,'085713314242','antonius.endra@nusaputra.ac.id','alfabetaornega.allen@gmail.com',1,2024,'Aktif'],
    ['20240130004','Gun Gun Febrianza','3250011702920003','L','Rumyati','Islam','Kelas A','Bandung','1992-06-24',null,'081313190101','gungun.febrianza@nusaputra.ac.id','gungunfebrianza@gmail.com',1,2024,'Aktif'],
    ['20240130005','Mulyana Yusuf','3200042104900001','L','Titin Prihatin','Islam','Kelas A','Cianjur','1990-04-21',null,'085710867033','mulyana.yusuf@nusaputra.ac.id','mulyanayu50@gmail.com',1,2024,'Aktif'],
    ['20240130006','Roger Wagiu','7171104200060020','L','Evelien Manuelle','Kristen','Kelas A','Manado','2002-06-05',null,'087709100468','roger.wagiu@nusaputra.ac.id','davidrogerwagiu@gmail.com',1,2024,'Aktif'],
    ['20240130007','Delfry Praise Utomo','7102165812020001','P','Dirtah Alfrina Geniung','Kristen','Kelas A','Manado','2002-12-18',null,'085677346062','delfry.praise@nusaputra.ac.id','praiseuto18@gmail.com',1,2024,'Aktif'],
    ['20240130008','Andi Hermawan Abdolah','0177101308750008','L','Masrukha','Islam','Kelas A','Jombang','1975-08-13',null,'081237079213','andi.hermawan@nusaputra.ac.id','saaand97@gmail.com',1,2024,'Aktif'],
    ['20240130009','Samsul Alamm','3202421701850001','L','Nurhayati','Islam',null,'Sukabumi','1985-01-17',null,'085888225395',null,'alam.slekaa@gmail.com',1,2024,'Aktif'],
    ['20240130010','Widya Purnama','3174096058800002','P','Tite Pusita','Islam','Kelas A','Bogor','1986-05-20',null,'081298737392','widya.purnama@nusaputra.ac.id','widya.purnama@nusaputra.ac.id',1,2024,'Aktif'],
    ['20240130011','Muhammad Nurifki Filino','3271024930040001','L','Sri Nurbeddi Maharni','Islam','Kelas A','Jakarta','1993-04-01',null,'082129578398','nurifki.filino@nusaputra.ac.id','nurifki.filino@nusaputra.ac.id',1,2024,'Aktif'],
    ['20240130012','Dhisa Septiano','3174070601990003','L','Tite Pusita','Islam','Kelas A','Jakarta','1993-09-22',null,'081960177710','dhisa.septiano@nusaputra.ac.id','dhisa.septiano@nusaputra.ac.id',1,2024,'Aktif'],
    ['20240130013','RIZA RUMAYANTI DEWI','1272025111020022','P','LIANA SALEHA ASIH','Islam','Kelas I','GARUT','2002-11-11','GG CIPELANG LEUTIK VI NO 181','085502359864',null,'rizarumayanttidewi@gmail.com',1,2024,'Aktif'],
    ['20240130014','RIKA AGISHA SITI NURAZIZAH','3202286109010002','P','ANI NURHAYATI','Islam','Kelas I','SUKABUMI','2001-08-21','Perumahan Griya Lestari Cijangkian Blok A No 7','085891250749','rika.agisha@nusaputra.ac.id','rikaagisha06@gmail.com',1,2024,'Aktif'],
    ['20240130015','SAMSUL ALAM','3202421701950001','L','NURHAYATI','Islam','Kelas B','SUKABUMI','1995-01-17','KP. PASANGRAHAN','085888225395','samsul.alam@nusaputra.ac.id','alamm.slekaa@gmail.com',1,2024,'Aktif'],
    ['20240130016','REHAENI ATIPAH','3213156603990002','P','ANI MARNI','Islam','Kelas B','SUBANG','1999-03-25','Perum baru langkap blok A 151','085759133462','rehaeni.atipah@nusaputra.ac.id','atipahrehaeni@gmail.com',1,2024,'Aktif'],
    ['20240130017','FERRY FEBIANSAH','3272011202870042','L','SUMARNI','Islam','Kelas B','SUKABUMI','1987-02-12','KP. TALUN LT.004/005 DESA GUNUNG BENTANG KABUPATEN SUKABUMI','085872129448','ferry.febiansah_ci24@nusaputra.ac.id','ferry.febiansah_ci24@nusaputra.ac.id',1,2024,'Aktif'],
    ['20240130018','SITI NADIYA FAUZANI','3202117005050007','P','TITIN SUTINAH','Islam','Kelas B','KABUPATEN SUKABUMI','2000-03-30','J. Karangtengan No. 891','085721571590','siti.nadiya@nusaputra.ac.id','siti.nadiya@nusaputra.ac.id',1,2024,'Aktif'],
    ['20240130019','NUR ELAH','3202287019500004','P','IMAS','Islam','Kelas B','SUKABUMI','1995-01-27','KP KAUM KALER','085759842831','nur.elah@nusaputra.ac.id','nur.elah@nusaputra.ac.id',1,2024,'Aktif'],
    ['20240130020','AHDI NAUFAL HAMDI','3202201311930007','L','ANIS HILMIYAH','Islam','Kelas B','SUKABUMI','1993-11-23','Kp. Tipar RT/RW 047/010 Desa Cibolang Kaler Kec. Cisaat Kab. Sukabumi','085624044620','ahdi.naufal_mif24@nusaputra.ac.id','ahdinaufalhamdi@gmail.com',1,2024,'Aktif'],
    ['20240130021','RIKI MULYANA','3272012502910001','P','YUYUN RUSMIANTI','Islam','Kelas I','SUKABUMI','1991-02-24','Kp. Tegalega','085608888824','riki.mulyana_mif24@nusaputra.ac.id','riki.mulyana_mif24@nusaputra.ac.id',1,2024,'Aktif'],
    ['20240130022','MUHAMAD HAVIDZ ALKAUSAR','3202282910980002','L','IMAS MASITOH','Islam','Kelas B','KABUPATEN SUKABUMI','1998-10-29','Kp.Brijong Tengah','015363965163','muhamad.havidz_mif24@nusaputra.ac.id','muhamad.havidz_mif24@nusaputra.ac.id',1,2024,'Aktif'],
    ['20240130023','TAUFIK ALFIKAR','3202141811890002','L','SITI JUBAEDAH','Islam','Kelas B','SUKABUMI','1999-01-18','SUKABUMI','082720835080','taufie.alfikar_mif24@nusaputra.ac.id','alfikar.taufik@gmail.com',1,2024,'Aktif'],
    ['20240130024','ARIS MAULANA','3273022006050043','L','IIS MELHANDAYANI','Islam','Kelas I','SUKABUMI','2000-05-20','JL. RA KOSASIH GG ADDOA','089652913849',null,'maulianaaris851@gmail.com',1,2024,'Aktif'],
    ['20240130025','YUSUF SUPRIYANTO','3202071601880003','L','Ibu','Islam','Kelas I','SUKABUMI','1985-01-15','KP CIGANGGENG RAYA','085567890135','yusufupriyanto@nusaputra.ac.id','yusufsupriyanto@sukabumikab.go.id',1,2024,'Aktif'],
    ['20240130026','DZAKY BNU RUSYO','3203251208930003','L','ENTIN KARTINI','Islam','Kelas B','YOGYAKARTA','1993-09-12','Perenu Villa Adiprima Blok F3 No 1 Jl.Kirena','085777511702','dzaky.rusyo22@gmail.com','dzaky.rusyo22@gmail.com',1,2024,'Aktif'],
    ['20240130027','SUNOKO','3203293708090008','L','SUNIPAH','Islam','Kelas B','BLORA','1990-08-07','KP. TALUN PEUNTAS','081911876296','sunoko.11131267@gmail.com','sunoko.11131267@gmail.com',1,2024,'Aktif'],
    ['20240130028','ERIP SURATNO','3202060508850002','L','JUANSH','Islam','Kelas B','SUKABUMI','1985-08-05','KP. SIDOMUKTI','081282984433','erip.suratno@gmail.com','erip.suratno@gmail.com',1,2024,'Aktif'],
    ['20240130029','FAHMY RODIBILLAH','3202231078910002','L','ELIS MARDIYAH','Islam','Kelas B','SUKABUMI','1991-08-28','KP BAKANAJATI','085562141792','flercb@gmail.com','flercb@gmail.com',1,2024,'Aktif'],
    ['20240130030','ARGI GINANJAR','3203282604900003','L','IDA SUHAETI','Islam','Kelas B','SUKABUMI','1990-04-28','KP. JATI','6285665128883','argi.ginanjar@gmail.com','argi.ginanjar@gmail.com',1,2024,'Aktif'],
    ['20240130031','CEPI SURYADI MARWAN','3272085710700001','L','BADRIAH','Islam','Kelas B','SUKABUMI','1970-10-17','JERUK NYELAP','081504655069','csmlike@gmail.com','csmlike@gmail.com',1,2024,'Aktif'],
    ['20240130032','PREETAM KUMAR',null,'L','LACHHMAN','Hindu','Kelas I','THARPARKAR','1996-02-03',null,'923472078690','preetam.kthv4@gmail.com','preetam.kthv4@gmail.com',1,2024,'Aktif'],
    ['20240130033','DULE ABERA',null,'L','ADANECH REFERA','Kristen','Kelas I','WOLISO','1999-02-22',null,'251023568160','duleabera06@gmail.com','duleabera06@gmail.com',1,2024,'Aktif'],
    ['20240130034','SISSOKO MAKAN',null,'L','Asalatu Trace','Kristen','Kelas I','BAMAKO','1999-07-04',null,'22392714168','makansissoko158@gmail.com','makansissoko158@gmail.com',1,2024,'Aktif'],
    ['20240130035','FIRMAN ARIFMAN','3674042504750003','L','Ibu','Islam','Kelas A','BANDUNG','1975-04-25',null,'08170000900','firman.arifman@nusaputra.ac.id','fa.arifman1@gmail.com',1,2024,'Aktif'],
    ['20240130036','OPIE SOPPYAN','3204240608930001','L','IBU','Islam',null,'SUKABUMI','1983-08-06','Kp. Munjur',null,'opierydes2@gmail.com','opierydes2@gmail.com',1,2024,'Aktif'],
    ['20240130037','RUDI IRAWAN','3203321808800001','L','Ibu','Islam',null,'SUKABUMI','1980-08-25','JJ.Panunggsah Gg Karya Bakti 2','085715788607','itawawi.r01@gmail.com','itawawi.r01@gmail.com',1,2024,'Aktif'],

    // ══════════════════════════════════════════════════════════
    // S2 MANAJEMEN (prodi_id=2) - Semester Gasal 2025/2026
    // ══════════════════════════════════════════════════════════
    ['20250150001','AGRI HAMDANI APRIANA','3272041604900022','L','ISOH HARYATI','Islam','Kelas B','SUKABUMI','1990-04-16','HAREMPOY JL. RA. KOSASIH','081282016824','agri@nusaputra.ac.id','hamdaniarg@yahoo.co.id',2,2025,'Aktif'],
    ['20250150002','MEILINA SUKMARINI','3272074105690002','P','HJ. CUCU SAMSIAH','Islam','Kelas B','KOTA SUKABUMI','1969-05-01','Jl. Malabar No. 8 Puri Cibeureum Permai','085862689767','meilina.sukmarini@nusaputra.ac.id','meilina_5@yahoo.com',2,2025,'Aktif'],
    ['20250150003','NENDA SUHANDA','3272040412890900','L','CICIH SUKAESIH','Islam','Kelas B','SUKABUMI','1989-12-04','Perum Panggon Mas Blok 32B','085797518000','nenda.suhanda@nusaputra.ac.id','anendia.piaggio@gmail.co',2,2025,'Aktif'],
    ['20250150004','N. FITRIYAH NUR WAHIDAH','3203035804950001','P','SADIYAH','Islam','Kelas B','CIANJUR','1995-04-18','KP. BABAKAN GELAR','087725874518','n.fitriyah_mm25@nusaputra.ac.id','fitriyahnurwahidah334@gmail.com',2,2025,'Aktif'],
    ['20250150005','SELVIYANI','3175016009900001','P','AZUARNI','Islam','Kelas B','JAKARTA','1990-09-20','Jl. Siliwangi No. 61','085863020944','selviyani@nusaputra.ac.id','selviyanigunawan@gmail.com',2,2025,'Aktif'],
    ['20250150006','YANA PRIYANA','3272024610970002','P','LILIS LISNIAWATI','Islam','Kelas B','SUKABUMI','1997-10-10',null,'085321514410','yana.priyana_mm25@nusaputra.ac.id','mrpyana@gmail.com',2,2025,'Aktif'],
    ['20250150007','JATNIKA EKA PATRA','3202392202890001','L','HAYUN','Islam','Kelas B','SUKABUMI','1989-02-22','KP SETIA BAKTI RT 02/01 DESA KOMPA','085794728473','jatnika.eka@nusaputra.ac.id','jatnika.eka@nusaputra.ac.id',2,2025,'Aktif'],
    ['20250150008','INTAN NURHAYATI','3211204205780006','P','Uka Rumyati','Islam','Kelas B','SUMEDANG','1978-05-02','PERUMAHAN MUTIARA BUMI METRO','081546573793','intan.nurhayati_mm25@nusaputra.ac.id','intan4me@gmail.com',2,2025,'Aktif'],
    ['20250150009','SITI BADRIYAH','3272066908850001','P','YAYAN NURYANAN','Islam','Kelas B','SUKABUMI','1985-09-29','Jl. Proklamasi Kp. Cicadas Hilir RT 004 RW 009 Kel. Cikundul Kec. Lembursitu Kota Sukabumi','083890384239','siti.badriyah@nusaputra.ac.id','badriyah23@gmail.com',2,2025,'Aktif'],
    ['20250150010','MUHAMMAD ASAADULHAQ ASH SHIDIQ','3674041907990002','L','ZETTI MARNI','Islam','Kelas B','JAKARTA','1999-07-19','Vita Gunung Lestari blok F1 no.2','081807558950','muhammad.asaadulhaq@nusaputra.ac.id','asaadul.haq@gmail.com',2,2025,'Aktif'],
    ['20250150011','FITRI AFRINA','3276056904880005','P','TRI SARTIKA','Islam','Kelas B','JAKARTA','1988-04-29','Jln No 101 Serua Indah Cipulat','081210191601','fitri.afrina@nusaputra.ac.id','fitri.afrina@ymail.com',2,2025,'Aktif'],
    ['20250150012','ALYA SYIFA FADILLA','3272025002000901','P','YULIANTI','Islam','Kelas B','KOTA SUKABUMI','2000-02-10','Jalan Clalul Kaler','081311564678','alya.syifa_mm25@nusaputra.ac.id','alyafadilla348@gmail.com',2,2025,'Aktif'],
    ['20250150013','NENG MEDINA','3205014912890002','P','SITI APOH DZALFAH','Islam','Kelas B','GARUT','1989-12-09','Jalan Selabintana KM. 3','081563240240','neng.medina_mm25@nusaputra.ac.id','medinanimar8@gmail.com',2,2025,'Aktif'],
    ['20250150014','CITRA DWIYANTI RIDWAN','3202125601900004','P','YATI NURLAELA SARI','Islam','Kelas B','SUKABUMI','1990-01-16','Kp cisadata rt 05/02 ds.Cisarua kec nagrak kab sukabumi','085603400813','citra.dwiyanti_mm25@nusaputra.ac.id','citradwindwan@gmail.com',2,2025,'Aktif'],
    ['20250150015','IRVAN AGUNG APRIANSYAH','3202411804970003','L','HJ IIS SUMYATI','Islam','Kelas B','SUKABUMI','1997-04-18','KP.BOJONGHERANG','085556566562','irvan.agung_mm25@nusaputra.ac.id','irvan.agung_mm25@nusaputra.ac.id',2,2025,'Aktif'],
    ['20250150016','MUHAMMAD RIHAN RISKI FAHLEVI','3202280410920003','L','YUYU YUHANAH','Islam','Kelas B','SUKABUMI','1992-10-04','Jalan cisande RT 06 RW 02 desa Cijangkian kec. Cicantayan Kab Sukabumi 43155','0816338854','muhammad.rihan_mm25@nusaputra.ac.id','muhammad.rihan_mm25@nusaputra.ac.id',2,2025,'Aktif'],
    ['20250150017','SOLEH GUNAWAN','3272041807880001','L','CICAH','Islam','Kelas B','SUKABUMI','1988-07-18','Perumahan bumi marhama blok p no.7','08781900214','soleh.gunawan_mm25@nusaputra.ac.id','blenkagunawan4@gmail.com',2,2025,'Aktif'],
    ['20250150018','MUHAMMAD HAMDI','3202331409930004','L','TITIN FATIMAH','Islam','Kelas B','JAKARTA','1993-09-14','PERUM CIKEMBANG PERMAI','085892082629','muhammad.hamdi@nusaputra.ac.id','muhammadhamdi93@gmail.com',2,2025,'Aktif'],
    ['20250150019','FERRY PRIANTONO','3202101105840005','L','Uus Yusyawali','Islam','Kelas B','SUKABUMI','1984-05-11',null,'085720897200','ferry.priantono_mm25@nusaputra.ac.id','ferry.priantono.smi@gmail.com',2,2025,'Aktif'],
    ['20250150020','GINA SAHADATUN NISA','3202294712010007','P','NENENG EKASARI','Islam','Kelas B','SUKABUMI','2001-12-07','KP. CISAAT','085871555317','gina.sahadatun_mm25@nusaputra.ac.id','gina.sahadatun_mm25@nusaputra.ac.id',2,2025,'Aktif'],
    ['20250150021','NANI NAFISAH','3202277110830001','P','LILIS SUMIATI','Islam','Kelas B','SUKABUMI','1983-10-31','PERUM MANGKALAYA RW 02 desa','6287876183630','nani.nafisah_mm25@nusaputra.ac.id','nani.perpusdasml@gmail.com',2,2025,'Aktif'],

    // ══════════════════════════════════════════════════════════
    // S2 HUKUM (prodi_id=3) - Semester Gasal 2025/2026
    // ══════════════════════════════════════════════════════════
    ['20250160001','ALFIAN NUR UBAY','3201021603900007','L','CAHYANINGSIH','Islam',null,'JAKARTA','1990-03-16','Jl. Siliwangi no 61','085777894540','alfian.nur@nusaputra.ac.id','alfiannurubay@ymail.com',3,2025,'Aktif'],
    ['20250160002','ASEP ARIPIN','3202421012910003','L','H.SAROYAH','Islam',null,'SUKABUMI','1991-12-10','Kp.kaum','085795059000','asep.aripin_mh25@nusaputra.ac.id','asep.aripin_mh25@nusaputra.ac.id',3,2025,'Aktif'],
    ['20250160003','ENENG RAIMA KORDELINAS','3202024606900011','P','IJOH','Islam',null,'SUKABUMI','1990-06-10','Perum tanjung sari rt 004 /014','085798737909','eneng.raima_mh25@nusaputra.ac.id','raimaneng0606@gmail.com',3,2025,'Aktif'],
    ['20250160004','DINDIN','3202060507770003','L','ROHANAH','Islam',null,'BANDUNG','1977-07-05','Kp. Tipar RT 05/04 Desa Cikiray Kec. Cikidang Kab. Sukabumi','085871527325','dindin@nusaputra.ac.id','dinmdn5@gmail.com',3,2025,'Aktif'],
    ['20250160005','YUDI NURUL ANWAR','3272042010970021','L','NURAENI','Islam',null,'SUKABUMI','1997-10-20','Jl.sukakarya babakan','085720654497','yudi.nurul_mh25@nusaputra.ac.id','nurulyudi32@gmail.com',3,2025,'Aktif'],

    // ══════════════════════════════════════════════════════════
    // S2 PEDAGOGI (prodi_id=4) - Semester Gasal 2025/2026
    // ══════════════════════════════════════════════════════════
    ['20250140001','RIKA RAHAYU','3272026105000001','P','LINA','Islam','Kelas B','SUKABUMI','2000-05-21','Cijangkar wetan RT 004 RW 006 kelurahan Cisarua kecamatan Cikole kota Sukabumi 43115','085861528003',null,'rika.rahayu_mpd25@nusaputra.ac.id',4,2025,'Aktif'],
    ['20250140002','LUSIANA ALAWIYAH','3202245507850008','P','EUIS HERAWATI','Islam','Kelas B','SUKABUMI','1985-07-15','Kp. Cikakak RT 001/ RW 009, Kelurahan Cisanua Surade, Kecamatan Surade, Kabupaten Sukabumi','081563900085','lusiana.alawiyah@nusaputra.ac.id','uchie.alwy15@gmail.com',4,2025,'Aktif'],
    ['20250140003','DEDE KURNIA SAFARI','3202241110860007','L','HALIMAH','Islam','Kelas B','SUKABUMI','1986-10-11','Kp. Cikakak','0816335497','dede.kurnia@nusaputra.ac.id','dhe.eboeds@gmail.com',4,2025,'Aktif'],
    ['20250140004','ESIH SUKAESIH','3601135102900001','P','SAPIAH','Islam','Kelas B','PANDEGLANG','1992-02-11','PESANTREN AL-MA TUO JL.KADUDAMPIT KM.3 KP. CIKAROYA','082321733008','esih.sukaesih@nusaputra.ac.id','esih.sukaesih@nusaputra.ac.id',4,2025,'Aktif'],
    ['20250140005','SAEPULANA','3202370605790002','L','KARTIWI','Islam','Kelas B','SUKABUMI','1979-06-05','KP. CIBIRU','085864557969','saepulana@nusaputra.ac.id','anasaepulana@gmail.com',4,2025,'Aktif'],
    ['20250140006','DENY SETIAWAN','3602201102800005','L','JUMASIH','Islam','Kelas B','LEBAK','1980-02-11','Kp.Cikamunding RT.2 RW.1 Cikamunding','081563672677','deny.setiawan_mpd25@nusaputra.ac.id','paden1180@gmail.com',4,2025,'Aktif'],
    ['20250140007','ASEP RUSWANDI','3202331004850001','L','UMAMAH','Islam','Kelas B','SUKABUMI','1985-04-10','Kp. Cirumput','085603925600','asep.ruswandi_mpd25@nusaputra.ac.id','asepr0889@gmail.com',4,2025,'Aktif'],
    ['20250140008','ISMATULLAH','3272042505900902','L','AJAN','Islam','Kelas B','SUKABUMI','1990-05-25','kp nagrak tengah rt 15 rw 02 desa nagrak kecamatan cisaat kab.sukabumi','085720756290',null,'ismtatullah5451@gmail.com',4,2025,'Aktif'],
    ['20250140009','SATRIA NURRAJAB TANOEJIWA','3202112611950001','L','LIA YULIANTI','Islam','Kelas B','SUKABUMI','1995-11-26','Jl.Primer No.327','085659445944','satria.nurrajab@nusaputra.ac.id','srnbx.one@gmail.com',4,2025,'Aktif'],
    ['20250140010','HERU HERMAWAN','3202122004880003','L','E. KARTINAH','Islam','Kelas B','SUKABUMI','1988-04-20','Kp. Bobojong','085722573025','heru.hermawan@nusaputra.ac.id','hheru0538@gmail.com',4,2025,'Aktif'],
    ['20250140011','ASEP PURNAWAN','3202380212950003','L','YUYUN','Islam','Kelas B','KABUPATEN SUKABUMI','1995-12-02','KP.CIKARAE RT.01/07 DESA CIMERANG KEC.PURABAYA','085759629433','asep.purnawan@nusaputra.ac.id','asep.purnawan@nusaputra.ac.id',4,2025,'Aktif'],

    // ══════════════════════════════════════════════════════════
    // S3 DOKTOR ILMU KOMPUTER (prodi_id=5) - Gasal 2025/2026
    // ══════════════════════════════════════════════════════════
    ['20250170001','SYAIFULLAH DJAFAR','7271031507620000','L','Hapsah Dali','Islam','SOCS A','MAKASSAR','1962-07-15','BTN TAWANJUKA MAS BLOK B NO.8','0811456242','syaiful.djafar@nusaputra.ac.id','syaiful.djafar@nusaputra.ac.id',5,2025,'Aktif'],
    ['20250170002','NANAN SOEKARNA','3175093007550007','L','Hartati','Islam','SOCS A','PURWAKARTA','1955-07-30','JL. RAYA MALAKA NO.9 A','0817177878','nanan.soekarna@nusaputra.ac.id','nanan.soekarna@nusaputra.ac.id',5,2025,'Aktif'],
    ['20250170003','MUHAMMAD HANAFI','3273212807740003','L','Zainab Harun','Islam','SOCS A','MEDAN','1974-07-28',null,'081188888757','muhammad.hanafi@nusaputra.ac.id','muhammad.hanafi@nusaputra.ac.id',5,2025,'Aktif'],
    ['20250170004','PRATAMA DAHLIAN PERSADHA','3174041410770002','L','Sri Kamini','Islam','SOCS A','BLORA','1977-10-14','JL. PALAKALI NO.45 A','082388887000','pratama@nusaputra.ac.id','pratama@nusaputra.ac.id',5,2025,'Aktif'],
    ['20250170005','ARYO DE WIBOWO MUHAMMAD SIDIK','3273240212880002','L','Helmiza','Islam','SOCS A','SUKABUMI','1988-12-02','KP PANYINDANGAN','081320796151','aryo.dewibowo@nusaputra.ac.id','aryo.de.wibowo.ms@gmail.com',5,2025,'Aktif'],
    ['20250170006','ITO SUMARDI','3174091708530023','L','Siti Zaetun Kamarukmi','Islam','SOCS A','Bogor','1953-06-17','TANJUNG MASRAYA E-1/20','08127571977','itosoemardi@nusaputra.ac.id','itosoemardi@nusaputra.ac.id',5,2025,'Aktif'],
    ['20250170007','NORMAN ZAINAL','3175023005620003','L','Nurcaya Syam','Islam','SOCS A','SUNUR','1962-05-03','KOMPLEK RS PERSAHABATAN NO.7','08121032232','norman.zainal@nusaputra.ac.id','normanzainal@nusaputra.ac.id',5,2025,'Aktif'],
    ['20250170008','SIHABUDIN','3202281608850002','L','Entim','Islam',null,'SUKABUMI','1985-09-16','JL. KH Darnan Huri KM 1 Karadenan RT 13/04 Desa Cimahi Kecamatan Cicantayan Kabupaten Sukabumi','08111609520',null,'ihab.khoirun@gmail.com',5,2025,'Aktif'],
    ['20250170009','MARINA ARTIYASA','3271044312730008','P','Efty Kusmiayati','Islam','SOCS A','BANDUNG','1973-12-03','GG. MENTENG, NO.107','089516823634','marina@nusaputra.ac.id','marin.ai@nusaputra.ac.id',5,2025,'Aktif'],
    ['20250170010','FIRMAN ARIFMAN','3674042504750003','L','AA NUGRAHA','Islam','SOCS A','BANDUNG','1975-04-25','RUKO EMERALD BOULEVARD BLOK AA 1 NO.2','08170000900','firmanarifman@nusaputra.ac.id','firmanarifman@nusaputra.ac.ID',5,2025,'Aktif'],
    ['20250170011','ERICK DAZKI','3674030205900012','L','Yuzidareni','Islam','SOCS A','JAKARTA','1990-05-02','JL KALIBARU BARAT','082299010304','erick.dazki@nusaputra.ac.id','ericklazki@gmail.com',5,2025,'Aktif'],
    ['20250170012','WAHYU WIBOWO','3273301507840001','L','-','Islam','SOCS A','PURWOREJO','1984-07-15',null,'087826888743','aripurno.wahyu@nusaputra.ac.id','aripurno.wahyu@nusaputra.ac.id',5,2025,'Aktif'],
    ['20250170013','IMRON COTAN','3174042112540004','L','IBU','Islam','SOCS A','P. SIANTAR','1954-12-21',null,'000000000000','imron.cotan@nusaputra.ac.id','imron.cotan@nusaputra.ac.id',5,2025,'Aktif'],
    ['20250170014','FALENTINO SEMBIRING','3273010802910002','L','MASTIANNA SIMATUPANG','Kristen','SOCS A','SIBUHUAN','1991-02-08',null,'082116760065',null,'falentinosembiring@nusaputra.ac.id',5,2025,'Aktif'],
    ['20250170015','SUDIN SAEPUDIN','3272071408860021','L','CUCUM','Islam','SOCS A','SUKABUMI','1986-08-14',null,'083147982451','sudin.saepudin_s3@nusaputra.ac.id','sudin.saepudin_s3@nusaputra.ac.id',5,2025,'Aktif'],
    ['20250170016','MUSTAR AMAN','3601382108800028','L','NURHAYATI','Islam','SOCS A','REMBAN','1980-08-21','Kp.Kadu RT.002/001 Bunder, Cikupa-Tangerang','081382982664',null,'amanmustar@gmail.com',5,2025,'Aktif'],
    ['20250170017','ANDANG NUGROHO','3174032403670001','L','NANIK','Katolik','SOCS A','JAKARTA','1967-03-24','JL. PONDOK KARYA H-31','0811800958','andang.nugroho@nusaputra.ac.id','bidwisely@yahoo.com',5,2025,'Aktif'],
    ['20250170018','ADITYA','3275032009760034','L','IRMAILIS','Islam','SOCS A','JAKARTA','1976-09-20','JL. AGATHIS I BLOK C NO 109','085952873707','aditya_s3@nusaputra.ac.id','adityautama0976@gmail.com',5,2025,'Aktif'],
    ['20250170019','ALUN SUJJADA','3573051810800003','L','SUMINI','Islam','SOCS A','MALANG','1980-10-18','JL. BENDUNGAN SUTAMI II/387','081805040468','alun.sujjada_s3@nusaputra.ac.id','alun.sujjada_s3@nusaputra.ac.id',5,2025,'Aktif'],
    ['20250170020','AMANDA','3275056605710003','P','R DARWIN ASINARA','Islam','SOCS A','JAKARTA','1971-05-26','JL. PANGANDARAN A NO 76','0895335775666','amanda_s3@nusaputra.ac.id','amanda.saad1123@gmail.com',5,2025,'Aktif'],
];

// ─── CEK STRUKTUR TABEL ──────────────────────────────────────────────────────
$cols = $pdo->query("DESCRIBE mahasiswa")->fetchAll();
$colNames = array_column($cols, 'Field');
echo "\n=== KOLOM TABEL MAHASISWA ===\n";
echo implode(', ', $colNames) . "\n";

// ─── INSERT / UPDATE ─────────────────────────────────────────────────────────
$inserted = 0; $updated = 0; $skipped = 0;
$usersInserted = 0; $usersUpdated = 0;

$hasNIK      = in_array('nik', $colNames);
$hasNamaIbu  = in_array('nama_ibu', $colNames);
$hasAgama    = in_array('agama', $colNames);
$hasKelas    = in_array('kelas', $colNames);
$hasTmptLhr  = in_array('tempat_lahir', $colNames);
$hasTglLhr   = in_array('tanggal_lahir', $colNames);
$hasAlamat   = in_array('alamat', $colNames);
$hasHP       = in_array('no_hp', $colNames) ? 'no_hp' : (in_array('hp', $colNames) ? 'hp' : null);
$hasEmailPrb = in_array('email_pribadi', $colNames);

echo "\nMulai proses insert/update...\n";

foreach ($mahasiswaData as $m) {
    [$nim,$nama,$nik,$jk,$namaIbu,$agama,$kelas,$tmptLhr,$tglLhr,$alamat,$hp,$emailKmp,$emailPrb,$prodiId,$angkatan,$status] = $m;

    $jkVal = ($jk === 'L') ? 'Laki-laki' : (($jk === 'P') ? 'Perempuan' : $jk);

    // ── 1. INSERT/UPDATE tabel mahasiswa ──
    $existMhs = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ?");
    $existMhs->execute([$nim]);
    $mhsRow = $existMhs->fetch();

    if ($mhsRow) {
        // Update data mahasiswa
        $upd = "UPDATE mahasiswa SET nama=?, jenis_kelamin=?, prodi_id=?, angkatan=?, status=?, email=?";
        $updVals = [$nama, $jkVal, $prodiId, $angkatan, $status, $emailKmp];
        if ($hasHP && $hp)       { $upd .= ", $hasHP=?"; $updVals[] = $hp; }
        if ($hasEmailPrb && $emailPrb) { $upd .= ", email_pribadi=?"; $updVals[] = $emailPrb; }
        if ($hasTglLhr && $tglLhr) { $upd .= ", tanggal_lahir=?"; $updVals[] = $tglLhr; }
        if ($hasTmptLhr && $tmptLhr) { $upd .= ", tempat_lahir=?"; $updVals[] = $tmptLhr; }
        if ($hasAlamat && $alamat) { $upd .= ", alamat=?"; $updVals[] = $alamat; }
        if ($hasNIK && $nik)     { $upd .= ", nik=?"; $updVals[] = $nik; }
        if ($hasNamaIbu && $namaIbu) { $upd .= ", nama_ibu=?"; $updVals[] = $namaIbu; }
        if ($hasAgama && $agama) { $upd .= ", agama=?"; $updVals[] = $agama; }
        if ($hasKelas && $kelas) { $upd .= ", kelas=?"; $updVals[] = $kelas; }
        $upd .= " WHERE nim=?";
        $updVals[] = $nim;
        $pdo->prepare($upd)->execute($updVals);
        $updated++;
    } else {
        // Insert mahasiswa
        $fields = ['nim','nama','jenis_kelamin','prodi_id','angkatan','status','email'];
        $values = [$nim,$nama,$jkVal,$prodiId,$angkatan,$status,$emailKmp];
        if ($hasHP && $hp)       { $fields[] = $hasHP; $values[] = $hp; }
        if ($hasEmailPrb && $emailPrb) { $fields[] = 'email_pribadi'; $values[] = $emailPrb; }
        if ($hasTglLhr && $tglLhr) { $fields[] = 'tanggal_lahir'; $values[] = $tglLhr; }
        if ($hasTmptLhr && $tmptLhr) { $fields[] = 'tempat_lahir'; $values[] = $tmptLhr; }
        if ($hasAlamat && $alamat) { $fields[] = 'alamat'; $values[] = $alamat; }
        if ($hasNIK && $nik)     { $fields[] = 'nik'; $values[] = $nik; }
        if ($hasNamaIbu && $namaIbu) { $fields[] = 'nama_ibu'; $values[] = $namaIbu; }
        if ($hasAgama && $agama) { $fields[] = 'agama'; $values[] = $agama; }
        if ($hasKelas && $kelas) { $fields[] = 'kelas'; $values[] = $kelas; }
        $ph = implode(',', array_fill(0, count($fields), '?'));
        $fl = implode(',', $fields);
        try {
            $pdo->prepare("INSERT INTO mahasiswa($fl) VALUES($ph)")->execute($values);
            $inserted++;
        } catch (Exception $e) {
            echo "  SKIP mhs {$nim}: " . $e->getMessage() . "\n";
            $skipped++;
            continue; // skip users juga
        }
    }

    // ── 2. INSERT/UPDATE tabel users (untuk login) ──
    // Login bisa pakai: NIM, email kampus, atau email pribadi
    // username di users = NIM (primary identifier)
    $existUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $existUser->execute([$nim]);
    if ($existUser->fetch()) {
        // Update password & info
        $pdo->prepare("UPDATE users SET password_hash=?, nama=?, email=?, prodi_id=?, role='mahasiswa' WHERE username=?")
            ->execute([$defaultPassword, $nama, $emailKmp, $prodiId, $nim]);
        $usersUpdated++;
    } else {
        try {
            $pdo->prepare("INSERT INTO users(username, password_hash, role, prodi_id, nama, email) VALUES(?,?,?,?,?,?)")
                ->execute([$nim, $defaultPassword, 'mahasiswa', $prodiId, $nama, $emailKmp]);
            $usersInserted++;
        } catch (Exception $e) {
            echo "  SKIP user {$nim}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== HASIL ===\n";
echo "Inserted : $inserted\n";
echo "Updated  : $updated\n";
echo "Skipped  : $skipped\n";

$total = $pdo->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();
echo "Total mahasiswa di DB: $total\n";

// Ringkasan per prodi
echo "\nPer Prodi:\n";
$perProdi = $pdo->query("SELECT prodi_id, COUNT(*) as cnt FROM mahasiswa GROUP BY prodi_id ORDER BY prodi_id")->fetchAll();
foreach ($perProdi as $p) {
    $name = $prodiMap[$p['prodi_id']] ?? 'Unknown';
    echo "  prodi_id={$p['prodi_id']} ({$name}): {$p['cnt']} mahasiswa\n";
}

echo "\nSelesai!\n";
