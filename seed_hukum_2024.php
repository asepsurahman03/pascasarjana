<?php
$host = 'localhost'; $db = 'pascasarjana_unp'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (Exception $e) { die("DB Error: " . $e->getMessage() . "\n"); }

$defaultPassword = password_hash('nusaputraku', PASSWORD_DEFAULT);

$mahasiswaData = [
    ['20240160001','DEVI ERVIANA MUHARAM','3202210207930005','L','NANI SUMARNI','Islam',null,'SUKABUMI','1993-07-02','Kp. Cisande Rt/RW 005/002 Desa Cijalingan Kecamatan Cicantayan Kabupaten Sukabumi 43155','085779364131',null,'paksikujang@gmail.com',3,2024,'Aktif'],
    ['20240160002','BUDI ARDIANSYAH','3202171810830003','L','ROIHAH','Islam',null,'SUKABUMI','1983-10-18','Kp. Cikreo Rt/Rw 02/05 Desa Cidahu Kec. Cidahu Kab. Sukabumi','085759054579',null,'budiard99@gmail.co',3,2024,'Aktif'],
    ['20240160003','ANDRI PRANATA TARIGAN','1206022707980002','L','NURSANI BR GINTING','Islam',null,'DOLAT RAYAT','1998-07-27',null,'082216694420','andri.pranata@nusaputra.ac.id','andri.pranata@nusaputra.ac.id',3,2024,'Aktif'],
    ['20240160004','SUNANDAR','3202091406880002','L','-','Islam',null,'SUKABUMI','1988-04-16',null,'6285894861891',null,'sunandar@nusaputra.ac.id',3,2024,'Aktif'],
    ['20240160005','SAMSUL HIDAYAT','3202221811840001','L','HJ. JUARIYAH','Islam',null,'SUKABUMI','1984-11-18','Jl. Bangun nusa raya no 68','085723912868','samsul.hidayat_mh24@nusaputra.ac.id','samsulhidayatcevi84@gmail.com',3,2024,'Aktif'],
    ['20240160006','MOCH. CAESAR MAULANA','3202272408930002','L','IBU','Islam',null,'SUKABUMI','1993-08-24',null,'6285720484043','moch.caesar_mh24@nusaputra.ac.id','moch.caesar@nusaputra.ac.id',3,2024,'Aktif'],
    ['20240160007','KOKO MUHAMAD','3202102810820004','L','MAYA','Islam',null,'SUKABUMI','1982-10-28','KP. KEBON JERUK','0816594813',null,'baniadama668@gmail.com',3,2024,'Aktif'],
    ['20240160008','MUHAMMAD AMINUDDIN','3202292602850005','L','DIDAH','Islam',null,'SUKABUMI','1985-02-26','Gg. H. Marzuki II No. 29 B','6285722221116',null,'muhammad.aminuddin@nusaputra.ac.id',3,2024,'Aktif'],
    ['20240160009','SAMINGUN','3202261506850003','L','SARTIAH','Islam',null,'SUKABUMI','1985-06-15','Kp. Karangsari','6285772356057',null,'samingunsam019@gmail.com',3,2024,'Aktif'],
    ['20240160010','HANNA FITRI RAZIAH','3202304901000001','P','IAH FARIAH','Islam',null,'SUKABUMI','2000-01-09','KP. GUNUNG JATI DESA CIKAHURIPAN','085721112074',null,'hannafitri12345@gmail.com',3,2024,'Aktif'],
    ['20240160011','ROLAN BENYAMIN PARDAMEAN HUTABARAT','3216020606770010','L','MAGDALENA SITOMPUL','Kristen',null,'PEMATANG SIANTAR','1977-06-06','PERUM GADING KENCANA BLOK E4 NO. 1','081281173677',null,'rolan.benyamin@gmail.com',3,2024,'Aktif'],
    ['20240160012','ANDRIYANSYAH','3202142512810001','L','-','Islam',null,'SUKABUMI','1981-12-25',null,'08156232832',null,'galtsa2011@gmail.com',3,2024,'Aktif'],
    ['20240160013','ILHAM AZZIKRI TARSIL','3272050809920001','L','DRA. ELIN PAULINA, MM','Islam',null,'SUKABUMI','1992-09-08','Jl. Lingkar selatan no.148, RT/RW 004/002, Kel/desa sudjaya hilir, kecamatan baros','081399937327','ilham.azzikri_mh@nusaputra.ac.id','ilham.azzikri_mh@nusaputra.ac.id',3,2024,'Aktif'],
    ['20240160014','NENG ANGIE RIVERA','3202306406970001','P','IDA HIDAYATI','Islam',null,'SUKABUMI','1997-06-24','jl situgung km 05 cibunar II, no.1','082114277820','neng.angie@nusaputra.ac.id','nengangier@gmail.com',3,2024,'Aktif'],
    ['20240160015','IVAN FAIZAL','3272042903810901','L','WATI SUMARTINI','Islam',null,'SUKABUMI','1981-03-29',null,'08164630399','ivan.faizal_mh24@nusaputra.ac.id','ivan.faizal@gmail.com',3,2024,'Aktif'],
    ['20240160016','DELITA ASTERINA TARIGAN','3272075301960900','P','AI NURYENI','Islam',null,'SUKABUMI','1996-01-13','PERUM SINDANG PALAY BLOK D NO. 4','081294758952','delita.asterina@nusaputra.ac.id','delitaasterinatarigan13@gmail.com',3,2024,'Aktif'],
    ['20240160017','ER ER SRIBAYUNINGSIH','3202316912800001','P','-','Islam',null,'SUKABUMI','1980-12-29',null,'081563336566','er.sribayuningsih@nusaputra.ac.id','er.er.sribayu@gmail.com',3,2024,'Aktif'],
    ['20240160018','HADITYA YUDA NEGARA HERDIANA','3202121107950003','L','IRNI SUGIARTI','Islam',null,'SUKABUMI','1995-07-11','PERUM TAMAN LESTARI BLOK C NO. 6','083818587558','haditya.yuda_mh24@nusaputra.ac.id','haditya.yuda_mh24@nusaputra.ac.id',3,2024,'Aktif'],
    ['20240160019','PAIZAL REZA','3202110104860007','L','IMAS MASMAWATI','Islam',null,'SUKABUMI','1986-04-01','KP. BOJONG SETRA','08122223656','paizal.reza_mh24@nusaputra.ac.id','paizal.reza@gmail.com',3,2024,'Aktif'],
    ['20240160020','FERRY SUPRIYADI','3202280811850005','L','E MARYATI','Islam',null,'SUKABUMI','1985-11-18','Kp. Cikukulu','081210187235','ferry.supriyadi@nusaputra.ac.id','ferysupriyadi08@gmail.com',3,2024,'Aktif'],
    ['20240160021','DEDEN SUHENDAR','3202041308880004','L','OON','Islam',null,'SUKABUMI','1988-08-13','KP. CIREUNDEU','085659435906','deden.suhendar_mh24@nusaputra.ac.id','deden.suhendar_mh24@nusaputra.ac.id',3,2024,'Aktif'],
    ['20240160022','WISELY','3216191510910002','L','-','Islam',null,'TANJUNGBALAI','1991-10-15',null,'081280068066','wisely@nusaputra.ac.id','wiselyxu1991@gmail.com',3,2024,'Aktif'],
];

$cols = $pdo->query("DESCRIBE mahasiswa")->fetchAll();
$colNames = array_column($cols, 'Field');
$hasNIK      = in_array('nik', $colNames);
$hasNamaIbu  = in_array('nama_ibu', $colNames);
$hasAgama    = in_array('agama', $colNames);
$hasKelas    = in_array('kelas', $colNames);
$hasTmptLhr  = in_array('tempat_lahir', $colNames);
$hasTglLhr   = in_array('tanggal_lahir', $colNames);
$hasAlamat   = in_array('alamat', $colNames);
$hasHP       = in_array('no_hp', $colNames) ? 'no_hp' : (in_array('hp', $colNames) ? 'hp' : null);
$hasEmailPrb = in_array('email_pribadi', $colNames);

$inserted = 0; $updated = 0;
$usersInserted = 0; $usersUpdated = 0;

foreach ($mahasiswaData as $m) {
    [$nim,$nama,$nik,$jk,$namaIbu,$agama,$kelas,$tmptLhr,$tglLhr,$alamat,$hp,$emailKmp,$emailPrb,$prodiId,$angkatan,$status] = $m;
    $jkVal = ($jk === 'L') ? 'Laki-laki' : (($jk === 'P') ? 'Perempuan' : $jk);

    // 1. MAHASISWA
    $existMhs = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ?");
    $existMhs->execute([$nim]);
    if ($existMhs->fetch()) {
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
        $pdo->prepare("INSERT INTO mahasiswa($fl) VALUES($ph)")->execute($values);
        $inserted++;
    }

    // 2. USERS
    $existUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $existUser->execute([$nim]);
    if ($existUser->fetch()) {
        $pdo->prepare("UPDATE users SET password_hash=?, nama=?, email=?, prodi_id=?, role='mahasiswa' WHERE username=?")
            ->execute([$defaultPassword, $nama, $emailKmp, $prodiId, $nim]);
        $usersUpdated++;
    } else {
        $pdo->prepare("INSERT INTO users(username, password_hash, role, prodi_id, nama, email) VALUES(?,?,?,?,?,?)")
            ->execute([$nim, $defaultPassword, 'mahasiswa', $prodiId, $nama, $emailKmp]);
        $usersInserted++;
    }
}

echo "MHS: Inserted=$inserted, Updated=$updated\n";
echo "USR: Inserted=$usersInserted, Updated=$usersUpdated\n";
