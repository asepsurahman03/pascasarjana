<?php
/**
 * Admin Controller
 */
class AdminController
{
    public function dashboard(): void
    {
        requireAdminOrKaprodi();

        $user = currentUser();
        $prodiFilter = isKaprodi() ? $user['program_studi'] : null;

        $stats       = PengunduranDiri::statistics($prodiFilter);
        $mahasiswaCount = Mahasiswa::count(isKaprodi() ? ['program_studi' => $prodiFilter] : []);
        $monthlyData = PengunduranDiri::monthlyChart($prodiFilter);
        $prodiData   = PengunduranDiri::prodiChart($prodiFilter);
        $recentLogs  = ActivityLog::recent(10);
        $recentPd    = PengunduranDiri::all(isKaprodi() ? ['program_studi' => $prodiFilter] : [], 5, 0);

        $data = [
            'title'          => 'Dashboard Admin - ' . APP_NAME,
            'user'           => currentUser(),
            'stats'          => $stats,
            'mahasiswaCount' => $mahasiswaCount,
            'monthlyData'    => $monthlyData,
            'prodiData'      => $prodiData,
            'recentLogs'     => $recentLogs,
            'recentPd'       => $recentPd,
        ];

        require BASE_PATH . '/views/admin/dashboard.php';
    }

    public function pengajuan(): void
    {
        requireAdminOrKaprodi();

        $filters = [
            'search'        => sanitize($_GET['search'] ?? ''),
            'status'        => sanitize($_GET['status'] ?? ''),
            'program_studi' => sanitize($_GET['program_studi'] ?? ''),
            'angkatan'      => sanitize($_GET['angkatan'] ?? ''),
            'tanggal_dari'  => sanitize($_GET['tanggal_dari'] ?? ''),
            'tanggal_sampai'=> sanitize($_GET['tanggal_sampai'] ?? ''),
        ];

        if (isKaprodi()) {
            $filters['program_studi'] = currentUser()['program_studi'];
        }

        $currentPage = max(1, (int)($_GET['page_num'] ?? 1));
        $perPage     = isset($_GET['print_all']) ? 10000 : 10;
        $total       = PengunduranDiri::count($filters);
        $pagination  = paginate($total, $perPage, $currentPage);
        $pengajuan   = PengunduranDiri::all($filters, $perPage, $pagination['offset']);

        $data = [
            'title'      => 'Data Pengajuan - ' . APP_NAME,
            'user'       => currentUser(),
            'pengajuan'  => $pengajuan,
            'filters'    => $filters,
            'pagination' => $pagination,
        ];

        require BASE_PATH . '/views/admin/pengajuan.php';
    }

    public function detail(): void
    {
        requireAdminOrKaprodi();
        $id        = (int)($_GET['id'] ?? 0);
        $pengajuan = PengunduranDiri::findById($id);

        if (!$pengajuan) {
            redirect('?page=admin/pengajuan');
        }

        if (isKaprodi()) {
            $userProdi = currentUser()['program_studi'] ?? '';
            $pengajuanProdi = $pengajuan['program_studi'] ?? '';
            $looseUserProdi = trim(preg_replace('/^([A-Z0-9]+\s*-\s*)/', '', $userProdi));
            $loosePengajuanProdi = trim(preg_replace('/^([A-Z0-9]+\s*-\s*)/', '', $pengajuanProdi));
            if (strcasecmp($looseUserProdi, $loosePengajuanProdi) !== 0) {
                flash('error', 'Anda tidak memiliki akses ke pengajuan ini.');
                redirect('?page=admin/pengajuan');
            }
        }

        $signature = DigitalSignature::findByPengunduranId($id);

        $data = [
            'title'     => 'Detail Pengajuan - ' . APP_NAME,
            'user'      => currentUser(),
            'pengajuan' => $pengajuan,
            'signature' => $signature,
        ];

        require BASE_PATH . '/views/admin/detail.php';
    }

    public function approve(): void
    {
        requireAdminOrKaprodi();

        if (!verifyCsrf()) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }

        $id      = (int)($_POST['id'] ?? 0);
        $catatan = sanitize($_POST['catatan'] ?? '');

        $old = PengunduranDiri::findById($id);
        if (!$old) {
            jsonResponse(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        if (isKaprodi()) {
            $userProdi = currentUser()['program_studi'] ?? '';
            $pengajuanProdi = $old['program_studi'] ?? '';
            $looseUserProdi = trim(preg_replace('/^([A-Z0-9]+\s*-\s*)/', '', $userProdi));
            $loosePengajuanProdi = trim(preg_replace('/^([A-Z0-9]+\s*-\s*)/', '', $pengajuanProdi));
            if (strcasecmp($looseUserProdi, $loosePengajuanProdi) !== 0) {
                jsonResponse(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
        }

        PengunduranDiri::update($id, [
            'status'       => 'Approved',
            'catatan_admin' => $catatan,
            'approved_by'  => currentUserId(),
            'approved_at'  => date('Y-m-d H:i:s'),
        ]);

        logActivity('APPROVE', "Pengajuan #{$id} disetujui", 'pengunduran_diri', $id,
            ['status' => $old['status']], ['status' => 'Approved']);

        jsonResponse(['success' => true, 'message' => 'Pengajuan berhasil disetujui.']);
    }

    public function reject(): void
    {
        requireAdminOrKaprodi();

        if (!verifyCsrf()) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }

        $id      = (int)($_POST['id'] ?? 0);
        $catatan = sanitize($_POST['catatan'] ?? '');

        $old = PengunduranDiri::findById($id);
        if (!$old) {
            jsonResponse(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        if (isKaprodi()) {
            $userProdi = currentUser()['program_studi'] ?? '';
            $pengajuanProdi = $old['program_studi'] ?? '';
            $looseUserProdi = trim(preg_replace('/^([A-Z0-9]+\s*-\s*)/', '', $userProdi));
            $loosePengajuanProdi = trim(preg_replace('/^([A-Z0-9]+\s*-\s*)/', '', $pengajuanProdi));
            if (strcasecmp($looseUserProdi, $loosePengajuanProdi) !== 0) {
                jsonResponse(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
        }

        PengunduranDiri::update($id, [
            'status'        => 'Rejected',
            'catatan_admin' => $catatan,
            'approved_by'   => currentUserId(),
            'approved_at'   => date('Y-m-d H:i:s'),
        ]);

        logActivity('REJECT', "Pengajuan #{$id} ditolak", 'pengunduran_diri', $id,
            ['status' => $old['status']], ['status' => 'Rejected']);

        jsonResponse(['success' => true, 'message' => 'Pengajuan berhasil ditolak.']);
    }

    public function updateNomor(): void
    {
        requireAdmin();

        if (!verifyCsrf()) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }

        $id    = (int)($_POST['id'] ?? 0);
        $nomor = sanitize($_POST['nomor_surat'] ?? '');

        if (empty($nomor)) {
            jsonResponse(['success' => false, 'message' => 'Nomor surat wajib diisi.'], 422);
        }

        PengunduranDiri::updateNomor($id, $nomor);
        logActivity('UPDATE_NOMOR', "Nomor surat #{$id} diupdate: $nomor", 'pengunduran_diri', $id);

        jsonResponse(['success' => true, 'message' => 'Nomor surat berhasil diperbarui.']);
    }

    public function deletePengajuan(): void
    {
        requireAdminOrKaprodi();

        if (!verifyCsrf()) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }

        $id = (int)($_POST['id'] ?? 0);
        $pengajuan = PengunduranDiri::findById($id);

        if (!$pengajuan) {
            jsonResponse(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        // Kaprodi hanya boleh hapus data dari prodinya sendiri
        if (isKaprodi()) {
            $userProdi = currentUser()['program_studi'] ?? '';
            $pengajuanProdi = $pengajuan['program_studi'] ?? '';
            // Lakukan pengecekan loose (karena di database kadang ada 'Magister Informatika' vs 'S2 - Magister Informatika')
            $looseUserProdi = trim(preg_replace('/^([A-Z0-9]+\s*-\s*)/', '', $userProdi));
            $loosePengajuanProdi = trim(preg_replace('/^([A-Z0-9]+\s*-\s*)/', '', $pengajuanProdi));
            if (strcasecmp($looseUserProdi, $loosePengajuanProdi) !== 0) {
                jsonResponse(['success' => false, 'message' => 'Anda tidak memiliki akses untuk menghapus data ini.'], 403);
            }
        }

        PengunduranDiri::delete($id);
        logActivity('DELETE', "Pengajuan #{$id} dihapus", 'pengunduran_diri', $id);

        jsonResponse(['success' => true, 'message' => 'Data berhasil dihapus.']);
    }

    public function dataMahasiswa(): void
    {
        requireAdminOrKaprodi();

        $filters = [
            'search'        => sanitize($_GET['search'] ?? ''),
            'program_studi' => sanitize($_GET['program_studi'] ?? ''),
            'angkatan'      => sanitize($_GET['angkatan'] ?? ''),
        ];

        if (isKaprodi()) {
            $filters['program_studi'] = currentUser()['program_studi'];
        }

        $currentPage = max(1, (int)($_GET['page_num'] ?? 1));
        $perPage     = 10;
        $total       = Mahasiswa::count($filters);
        $pagination  = paginate($total, $perPage, $currentPage);
        $list        = Mahasiswa::all($filters, $perPage, $pagination['offset']);

        $data = [
            'title'      => 'Data Mahasiswa - ' . APP_NAME,
            'user'       => currentUser(),
            'list'       => $list,
            'filters'    => $filters,
            'pagination' => $pagination,
        ];

        require BASE_PATH . '/views/admin/mahasiswa.php';
    }

    public function saveMahasiswa(): void
    {
        requireAdminOrKaprodi();

        if (!verifyCsrf()) {
            flash('error', 'Token tidak valid.');
            redirect('?page=admin/mahasiswa');
        }

        $id = (int)($_POST['id'] ?? 0);

        $inputData = [
            'nim'             => sanitize($_POST['nim'] ?? ''),
            'nama'            => sanitize($_POST['nama'] ?? ''),
            'email'           => sanitize($_POST['email'] ?? ''),
            'tanggal_lahir'   => sanitize($_POST['tanggal_lahir'] ?? ''),
            'angkatan'        => (int)($_POST['angkatan'] ?? date('Y')),
            'program_studi'   => sanitize($_POST['program_studi'] ?? ''),
            'status_beasiswa' => sanitize($_POST['status_beasiswa'] ?? 'Non Beasiswa'),
            'no_hp'           => sanitize($_POST['no_hp'] ?? ''),
            'alamat'          => sanitize($_POST['alamat'] ?? ''),
        ];

        if (isKaprodi()) {
            $inputData['program_studi'] = currentUser()['program_studi'];
        }

        if ($id > 0) {
            $oldMhs = Mahasiswa::findById($id);
            if (!$oldMhs) {
                flash('error', 'Data tidak ditemukan.');
                redirect('?page=admin/mahasiswa');
            }
            if (isKaprodi()) {
                $userProdi = currentUser()['program_studi'] ?? '';
                $mhsProdi = $oldMhs['program_studi'] ?? '';
                $looseUserProdi = trim(preg_replace('/^([A-Z0-9]+\s*-\s*)/', '', $userProdi));
                $looseMhsProdi = trim(preg_replace('/^([A-Z0-9]+\s*-\s*)/', '', $mhsProdi));
                if (strcasecmp($looseUserProdi, $looseMhsProdi) !== 0) {
                    flash('error', 'Anda tidak memiliki akses untuk mengedit mahasiswa ini.');
                    redirect('?page=admin/mahasiswa');
                }
            }
            Mahasiswa::update($id, $inputData);
            flash('success', 'Data mahasiswa berhasil diperbarui.');
            logActivity('UPDATE_MAHASISWA', "Data mahasiswa ID #{$id} diupdate", 'mahasiswa', $id);
        } else {
            // Check NIM uniqueness
            if (Mahasiswa::findByNim($inputData['nim'])) {
                flash('error', 'NIM sudah terdaftar.');
                redirect('?page=admin/mahasiswa');
            }

            // Create user account first
            $userId = User::create([
                'nama'     => $inputData['nama'],
                'email'    => $inputData['email'] ?: $inputData['nim'] . '@student.nusaputra.ac.id',
                'password' => $inputData['nim'],
                'role'     => 'mahasiswa',
            ]);

            $inputData['user_id'] = $userId;
            $newId = Mahasiswa::create($inputData);
            flash('success', 'Data mahasiswa berhasil ditambahkan.');
            logActivity('CREATE_MAHASISWA', "Mahasiswa baru: {$inputData['nim']}", 'mahasiswa', $newId);
        }

        redirect('?page=admin/mahasiswa');
    }

    public function deleteMahasiswa(): void
    {
        requireAdminOrKaprodi();

        if (!verifyCsrf()) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }

        $id = (int)($_POST['id'] ?? 0);
        $mahasiswa = Mahasiswa::findById($id);

        if (!$mahasiswa) {
            jsonResponse(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        if (isKaprodi()) {
            $userProdi = currentUser()['program_studi'] ?? '';
            $mhsProdi = $mahasiswa['program_studi'] ?? '';
            $looseUserProdi = trim(preg_replace('/^([A-Z0-9]+\s*-\s*)/', '', $userProdi));
            $looseMhsProdi = trim(preg_replace('/^([A-Z0-9]+\s*-\s*)/', '', $mhsProdi));
            if (strcasecmp($looseUserProdi, $looseMhsProdi) !== 0) {
                jsonResponse(['success' => false, 'message' => 'Anda tidak memiliki akses untuk menghapus mahasiswa ini.'], 403);
            }
        }

        Mahasiswa::delete($id);
        logActivity('DELETE_MAHASISWA', "Mahasiswa ID #{$id} dihapus", 'mahasiswa', $id);

        jsonResponse(['success' => true, 'message' => 'Data mahasiswa berhasil dihapus.']);
    }

    public function users(): void
    {
        requireAdmin();
        $users = User::all();
        $data  = [
            'title' => 'Manajemen Users - ' . APP_NAME,
            'user'  => currentUser(),
            'users' => $users,
        ];
        require BASE_PATH . '/views/admin/users.php';
    }

    public function saveUser(): void
    {
        requireAdmin();

        if (!verifyCsrf()) {
            flash('error', 'Token tidak valid.');
            redirect('?page=admin/users');
        }

        $id = (int)($_POST['id'] ?? 0);

        $inputData = [
            'nama'          => sanitize($_POST['nama'] ?? ''),
            'email'         => sanitize($_POST['email'] ?? ''),
            'role'          => sanitize($_POST['role'] ?? 'mahasiswa'),
            'is_active'     => isset($_POST['is_active']) ? 1 : 0,
        ];

        if (isset($_POST['program_studi'])) {
            $val = sanitize($_POST['program_studi']);
            $inputData['program_studi'] = $val !== '' ? $val : null;
        }
        if (!empty($_POST['password'])) {
            $inputData['password'] = $_POST['password'];
        }

        // ── Handle Upload TTD Kaprodi ──────────────────────────────────────
        if (!empty($_FILES['ttd_file']['name']) && $_FILES['ttd_file']['error'] === UPLOAD_ERR_OK) {
            $file     = $_FILES['ttd_file'];
            $allowExt = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowExt)) {
                flash('error', 'Format file TTD tidak didukung. Gunakan PNG, JPG, atau GIF.');
                redirect('?page=admin/users');
            }

            if ($file['size'] > 2 * 1024 * 1024) { // maks 2MB
                flash('error', 'Ukuran file TTD maksimal 2MB.');
                redirect('?page=admin/users');
            }

            $ttdDir = BASE_PATH . '/uploads/ttd_kaprodi/';
            if (!is_dir($ttdDir)) @mkdir($ttdDir, 0755, true);

            // Hapus file TTD lama jika ada
            if ($id > 0) {
                $oldUser = User::findById($id);
                if ($oldUser && !empty($oldUser['ttd_path'])) {
                    $oldFile = BASE_PATH . '/' . ltrim($oldUser['ttd_path'], '/');
                    if (file_exists($oldFile)) @unlink($oldFile);
                }
            }

            // Buat nama file unik berdasarkan nama user/prodi
            $safeProdi = preg_replace('/[^a-z0-9]+/', '_', strtolower($inputData['program_studi'] ?? 'user'));
            $filename  = 'ttd_kaprodi_' . $safeProdi . '_' . time() . '.' . $ext;
            $destPath  = $ttdDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                flash('error', 'Gagal menyimpan file TTD. Periksa permission folder uploads/ttd_kaprodi/.');
                redirect('?page=admin/users');
            }

            $inputData['ttd_path'] = 'uploads/ttd_kaprodi/' . $filename;
        }

        // Hapus TTD jika diminta
        if (isset($_POST['hapus_ttd']) && $_POST['hapus_ttd'] === '1' && $id > 0) {
            $oldUser = User::findById($id);
            if ($oldUser && !empty($oldUser['ttd_path'])) {
                $oldFile = BASE_PATH . '/' . ltrim($oldUser['ttd_path'], '/');
                if (file_exists($oldFile)) @unlink($oldFile);
            }
            $inputData['ttd_path'] = null;
        }
        // ───────────────────────────────────────────────────────────────────

        if ($id > 0) {
            User::update($id, $inputData);
            flash('success', 'Data user berhasil diperbarui.');
            logActivity('UPDATE_USER', "Data user ID #{$id} diupdate", 'users', $id);
        } else {
            if (empty($_POST['password'])) {
                flash('error', 'Password wajib diisi untuk user baru.');
                redirect('?page=admin/users');
            }
            
            // Check Email uniqueness
            if (User::findByEmail($inputData['email'])) {
                flash('error', 'Email sudah terdaftar.');
                redirect('?page=admin/users');
            }

            $newId = User::create($inputData);
            // set is_active
            if (isset($inputData['is_active'])) {
                User::update($newId, ['is_active' => $inputData['is_active']]);
            }

            // Simpan TTD jika ada (untuk user baru)
            if (!empty($inputData['ttd_path'])) {
                User::update($newId, ['ttd_path' => $inputData['ttd_path']]);
            }
            
            flash('success', 'Data user berhasil ditambahkan.');
            logActivity('CREATE_USER', "User baru: {$inputData['email']}", 'users', $newId);
        }

        redirect('?page=admin/users');
    }

    public function deleteUser(): void
    {
        requireAdmin();

        if (!verifyCsrf()) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }

        $id = (int)($_POST['id'] ?? 0);
        
        $user = User::findById($id);
        if ($user && $user['role'] === 'admin') {
            jsonResponse(['success' => false, 'message' => 'Tidak dapat menghapus admin.'], 403);
        }

        User::delete($id);
        logActivity('DELETE_USER', "User ID #{$id} dihapus", 'users', $id);

        jsonResponse(['success' => true, 'message' => 'Data user berhasil dihapus.']);
    }

    public function settings(): void
    {
        requireAdmin();
        $settings = Settings::all();
        $data = [
            'title'    => 'Pengaturan Sistem - ' . APP_NAME,
            'user'     => currentUser(),
            'settings' => $settings,
        ];
        require BASE_PATH . '/views/admin/settings.php';
    }

    public function saveSettings(): void
    {
        requireAdmin();

        if (!verifyCsrf()) {
            flash('error', 'Token tidak valid.');
            redirect('?page=admin/settings');
        }

        $allowed = [
            'app_name', 'university_name', 'university_address', 'university_phone',
            'university_email', 'university_website', 'nomor_surat_prefix',
            'ketua_prodi_s1_teknik_informatika', 'ketua_prodi_s1_manajemen', 'ketua_prodi_s1_akuntansi', 'ketua_prodi_s1_teknik_sipil', 'ketua_prodi_s1_sistem_informasi', 'ketua_prodi_s1_hukum', 'ketua_prodi_s1_pendidikan_guru_sekolah_dasar', 'ketua_prodi_s1_teknik_mesin', 'ketua_prodi_s1_teknik_elektro', 'ketua_prodi_s1_desain_komunikasi_visual', 'ketua_prodi_s1_gizi', 'ketua_prodi_s1_bioteknologi', 'ketua_prodi_s1_teknologi_pangan', 'ketua_prodi_s1_administrasi_kesehatan', 'ketua_prodi_d3_keperawatan', 'ketua_prodi_s2_magister_informatika', 'ketua_prodi_s2_magister_hukum', 'ketua_prodi_s2_magister_pedagogi', 'ketua_prodi_s2_magister_manajemen', 'ketua_prodi_s3_doktor_ilmu_komputer',
            'session_timeout', 'max_login_attempts',
        ];

        foreach ($allowed as $key) {
            if (isset($_POST[$key])) {
                Settings::set($key, sanitize($_POST[$key]));
            }
        }

        logActivity('UPDATE_SETTINGS', 'Pengaturan sistem diperbarui');
        flash('success', 'Pengaturan berhasil disimpan.');
        redirect('?page=admin/settings');
    }

    public function printPdf(): void
    {
        require BASE_PATH . '/controllers/PDFController.php';
        $pdf = new PDFController();
        $pdf->generate();
    }

    public function exportExcel(): void
    {
        requireAdminOrKaprodi();

        $filters = [
            'status'        => sanitize($_GET['status'] ?? ''),
            'program_studi' => sanitize($_GET['program_studi'] ?? ''),
            'angkatan'      => sanitize($_GET['angkatan'] ?? ''),
            'tanggal_dari'  => sanitize($_GET['tanggal_dari'] ?? ''),
            'tanggal_sampai'=> sanitize($_GET['tanggal_sampai'] ?? ''),
        ];

        if (isKaprodi()) {
            $filters['program_studi'] = currentUser()['program_studi'];
        }

        $pengajuan = PengunduranDiri::all($filters, 10000, 0);

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="Data_Pengunduran_Diri_' . date('Ymd') . '.xls"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        echo '<html><head><meta charset="UTF-8"></head><body>';
        
        echo '<table border="1" cellpadding="5" cellspacing="0" style="font-family: sans-serif; width: 100%; border-collapse: collapse;">';
        echo '<thead>';
        echo '<tr>';
        echo '<th colspan="10" style="text-align: center; font-size: 18pt; font-weight: bold; border: none; padding-bottom: 5px;">LAPORAN DATA PENGAJUAN PENGUNDURAN DIRI</th>';
        echo '</tr>';
        echo '<tr>';
        echo '<th colspan="10" style="text-align: center; font-size: 16pt; font-weight: bold; border: none; padding-bottom: 20px;">Nusa Putra University</th>';
        echo '</tr>';
        echo '<tr>';
        
        $headers = ['No', 'Tanggal', 'Nama', 'NIM', 'Program Studi', 'Angkatan', 'Status Mahasiswa', 'Bersedia Mundur', 'Status', 'Tanggal Submit'];
        foreach ($headers as $h) {
            echo '<th style="background-color: #961d5a; color: #ffffff; font-weight: bold; padding: 10px; text-align: center;">' . $h . '</th>';
        }
        
        echo '</tr></thead><tbody>';

        foreach ($pengajuan as $i => $row) {
            echo '<tr>';
            echo '<td style="text-align: center;">' . ($i + 1) . '</td>';
            echo '<td style="text-align: center;">' . e($row['tanggal_surat']) . '</td>';
            echo '<td>' . e($row['nama_pemohon']) . '</td>';
            echo '<td style="text-align: center;">' . e($row['nim']) . '</td>';
            echo '<td>' . e($row['program_studi']) . '</td>';
            echo '<td style="text-align: center;">' . e($row['angkatan']) . '</td>';
            echo '<td style="text-align: center;">' . e($row['status_mahasiswa']) . '</td>';
            echo '<td style="text-align: center;">' . ($row['bersedia_mundur'] === 'YES' ? 'Ya' : 'Tidak') . '</td>';
            echo '<td style="text-align: center;">' . e($row['status']) . '</td>';
            echo '<td style="text-align: center;">' . e($row['created_at']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</body></html>';
        exit;
    }
}
