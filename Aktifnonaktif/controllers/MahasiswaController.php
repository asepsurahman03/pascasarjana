<?php
/**
 * Mahasiswa Controller
 */
class MahasiswaController
{
    public function dashboard(): void
    {
        requireMahasiswa();
        $user      = currentUser();
        $mahasiswa = $user['mahasiswa'];
        $mahasiswaId = $mahasiswa['id'] ?? 0;

        $pengajuan = PengunduranDiri::findByMahasiswaId($mahasiswaId);
        $lastPengajuan = $pengajuan[0] ?? null;

        // Progress calculation
        $progress = 0;
        if ($lastPengajuan) {
            $progress = match ($lastPengajuan['status']) {
                'Draft'    => 25,
                'Pending'  => 50,
                'Approved' => 100,
                'Rejected' => 75,
                default    => 0,
            };
        }

        $data = [
            'title'         => 'Dashboard Mahasiswa - ' . APP_NAME,
            'user'          => $user,
            'mahasiswa'     => $mahasiswa,
            'pengajuan'     => $pengajuan,
            'lastPengajuan' => $lastPengajuan,
            'progress'      => $progress,
            'totalPengajuan' => count($pengajuan),
        ];

        require BASE_PATH . '/views/mahasiswa/dashboard.php';
    }

    public function form(): void
    {
        $data = [
            'title'     => 'Formulir Pengunduran Diri - ' . APP_NAME,
        ];

        require BASE_PATH . '/views/mahasiswa/form.php';
    }

    public function csrfToken(): void
    {
        // Return current token so it stays valid
        jsonResponse(['token' => csrfToken()]);
    }

    public function submitForm(): void
    {
        if (!verifyCsrf()) {
            jsonResponse(['success' => false, 'message' => 'Token keamanan tidak valid.'], 403);
        }

        // Validate input
        $errors = $this->validateForm($_POST);
        if (!empty($errors)) {
            jsonResponse(['success' => false, 'errors' => $errors], 422);
        }

        try {
            Database::beginTransaction();

            $nim = sanitize($_POST['nim']);
            $nama = sanitize($_POST['nama_pemohon']);
            $angkatan = (int)$_POST['angkatan'];

            // Find or create Mahasiswa by NIM
            $existing = Database::query("SELECT id, user_id FROM mahasiswa WHERE nim = ?", [$nim])->fetch();
            $mhsId = 0;
            if ($existing) {
                $mhsId = $existing['id'];
                Database::query("UPDATE mahasiswa SET nama = ?, angkatan = ? WHERE id = ?", [$nama, $angkatan, $mhsId]);
            } else {
                Database::query(
                    "INSERT INTO mahasiswa (nim, nama, angkatan, program_studi, status_beasiswa, tanggal_lahir) VALUES (?, ?, ?, ?, ?, '2000-01-01')",
                    [$nim, $nama, $angkatan, sanitize($_POST['program_studi']), sanitize($_POST['status_mahasiswa'])]
                );
                $mhsId = (int)Database::lastInsertId();
            }

            // If user is logged in but mahasiswa has no user_id, link them!
            if (isLoggedIn()) {
                $currentUser = currentUser();
                if ($currentUser['role'] === 'mahasiswa' && empty($existing['user_id'])) {
                    Database::query("UPDATE mahasiswa SET user_id = ? WHERE id = ?", [$currentUser['id'], $mhsId]);
                    // Update session
                    $mahasiswaData = Mahasiswa::findById($mhsId);
                    setUserSession($currentUser, $mahasiswaData);
                }
            }

            // Generate nomor surat
            $nomorSurat = generateNomorSurat();

            // Save pengunduran diri
            $pdId = PengunduranDiri::create([
                'nomor_surat'      => $nomorSurat,
                'mahasiswa_id'     => $mhsId,
                'tanggal_surat'    => date('Y-m-d'),
                'nama_pemohon'     => $nama,
                'nim'              => $nim,
                'angkatan'         => $angkatan,
                'program_studi'    => sanitize($_POST['program_studi']),
                'status_mahasiswa' => sanitize($_POST['status_mahasiswa']),
                'bersedia_mundur'  => sanitize($_POST['bersedia_mundur'] ?? ''),
                'alasan'           => sanitize($_POST['alasan'] ?? ''),
                'status'           => 'Pending',
            ]);

            // Save digital signature if YES
            if ($_POST['bersedia_mundur'] === 'YES' && !empty($_POST['signature_data'])) {
                $signatureFilename = 'sig_' . $mhsId . '_' . time() . '.png';
                $savedPath = saveSignatureImage($_POST['signature_data'], $signatureFilename);

                DigitalSignature::create([
                    'pengunduran_id' => $pdId,
                    'mahasiswa_id'   => $mhsId,
                    'signature_data' => $_POST['signature_data'],
                    'signature_path' => $savedPath ?: null,
                ]);
            }

            Database::commit();

            logActivity('SUBMIT_FORM', "Mahasiswa {$nim} mengajukan pengunduran diri", 'pengunduran_diri', $pdId);

            jsonResponse([
                'success'     => true,
                'message'     => 'Pengajuan berhasil disimpan!',
                'redirect'    => APP_URL . '/?page=mahasiswa/success&id=' . $pdId,
                'pengajuan_id' => $pdId,
            ]);

        } catch (Exception $e) {
            Database::rollback();
            error_log('[SUBMIT ERROR] ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan. Silakan coba lagi.'], 500);
        }
    }

    private function validateForm(array $post): array
    {
        $errors = [];

        if (empty($post['nama_pemohon'])) $errors['nama_pemohon'] = 'Nama pemohon wajib diisi.';
        if (empty($post['nim']))          $errors['nim']          = 'NIM wajib diisi.';
        if (empty($post['angkatan']))      $errors['angkatan']     = 'Angkatan wajib diisi.';
        if (empty($post['program_studi'])) $errors['program_studi'] = 'Program studi wajib dipilih.';
        if (empty($post['status_mahasiswa'])) $errors['status_mahasiswa'] = 'Status mahasiswa wajib dipilih.';
        if (empty($post['bersedia_mundur'])) $errors['bersedia_mundur'] = 'Pilihan bersedia wajib dipilih.';

        if ($post['bersedia_mundur'] === 'YES' && empty(trim($post['alasan'] ?? ''))) {
            $errors['alasan'] = 'Alasan pengunduran diri wajib diisi.';
        } elseif (!empty($post['alasan']) && strlen($post['alasan']) > 1000) {
            $errors['alasan'] = 'Alasan maksimal 1000 karakter.';
        }

        if (!in_array($post['program_studi'] ?? '', PROGRAM_STUDI)) {
            $errors['program_studi'] = 'Program studi tidak valid.';
        }

        return $errors;
    }

    public function success(): void
    {
        $id        = (int)($_GET['id'] ?? 0);
        $pengajuan = PengunduranDiri::findById($id);

        if (!$pengajuan) {
            redirect('?page=form');
        }

        $data = [
            'title'     => 'Pengajuan Berhasil - ' . APP_NAME,
            'pengajuan' => $pengajuan,
        ];

        require BASE_PATH . '/views/mahasiswa/success.php';
    }

    public function riwayat(): void
    {
        requireMahasiswa();
        $user      = currentUser();
        $mahasiswa = $user['mahasiswa'];

        $pengajuan = PengunduranDiri::findByMahasiswaId($mahasiswa['id'] ?? 0);

        $data = [
            'title'     => 'Riwayat Pengajuan - ' . APP_NAME,
            'user'      => $user,
            'mahasiswa' => $mahasiswa,
            'pengajuan' => $pengajuan,
        ];

        require BASE_PATH . '/views/mahasiswa/riwayat.php';
    }

    public function profile(): void
    {
        requireMahasiswa();
        $user      = currentUser();
        $mahasiswa = $user['mahasiswa'];

        $data = [
            'title'     => 'Profil - ' . APP_NAME,
            'user'      => $user,
            'mahasiswa' => $mahasiswa,
        ];

        require BASE_PATH . '/views/mahasiswa/profile.php';
    }

    public function updateProfile(): void
    {
        requireMahasiswa();

        if (!verifyCsrf()) {
            flash('error', 'Token keamanan tidak valid.');
            redirect('?page=mahasiswa/profile');
        }

        $user      = currentUser();
        $mahasiswa = $user['mahasiswa'];

        Mahasiswa::update($mahasiswa['id'], [
            'no_hp'  => sanitize($_POST['no_hp'] ?? ''),
            'alamat' => sanitize($_POST['alamat'] ?? ''),
        ]);

        flash('success', 'Profil berhasil diperbarui.');
        redirect('?page=mahasiswa/profile');
    }

    public function cetakPdf(): void
    {
        require BASE_PATH . '/controllers/PDFController.php';
        $pdf = new PDFController();
        $pdf->generate();
    }
}
