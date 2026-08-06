<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientId = trim($_POST['google_client_id'] ?? '');
    $clientSecret = trim($_POST['google_client_secret'] ?? '');

    if (!empty($clientId)) {
        dbExecute("INSERT INTO settings (key_name, value) VALUES ('google_client_id', ?) ON DUPLICATE KEY UPDATE value=?", [$clientId, $clientId]);
        dbExecute("INSERT INTO settings (key_name, value) VALUES ('google_client_secret', ?) ON DUPLICATE KEY UPDATE value=?", [$clientSecret, $clientSecret]);
        $message = 'Google Client ID dan Secret berhasil disimpan!';
    } else {
        $message = 'Client ID tidak boleh kosong.';
    }
}

// Ambil data saat ini
$currentId = dbQueryOne("SELECT value FROM settings WHERE key_name='google_client_id'")['value'] ?? '';
$currentSecret = dbQueryOne("SELECT value FROM settings WHERE key_name='google_client_secret'")['value'] ?? '';

?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set API Google</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{bg:'var(--color-bg)',bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm:'var(--color-bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm)'}}}}</script>
    <style>body{background:var(--color-bg); color: white;} .w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors{background:var(--color-sidebar); border:1px solid var(--color-border); color:var(--color-text); border-radius:10px; padding:0.65rem 1rem; width:100%; outline:none;}</style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-8 rounded-2xl w-full max-w-md" style="background:var(--color-bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm); border:1px solid var(--color-border)">
        <h1 class="text-2xl font-bold mb-2">Input Manual API Google</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Gunakan halaman ini untuk memasukkan Client ID dan Secret tanpa harus login terlebih dahulu.</p>

        <?php if ($message): ?>
            <div class="mb-4 px-4 py-3 rounded-xl text-sm <?= strpos($message, 'berhasil') !== false ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-red-900/30 text-red-400 border border-red-800' ?>">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm text-slate-800 dark:text-white mb-1">Google Client ID</label>
                <input type="text" name="google_client_id" value="<?= e($currentId) ?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors font-mono text-sm" placeholder="xxxx-xxxx.apps.googleusercontent.com" required>
            </div>
            <div>
                <label class="block text-sm text-slate-800 dark:text-white mb-1">Google Client Secret (Opsional)</label>
                <input type="text" name="google_client_secret" value="<?= e($currentSecret) ?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors font-mono text-sm" placeholder="GOCSPX-xxxxxxxxxxxxxxxxxxxxx">
            </div>
            
            <button type="submit" class="w-full mt-4 py-2 rounded-xl font-bold transition hover:opacity-90" style="background:var(--color-avatar-grad); color:#fff">
                Simpan API Google
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="login" class="text-blue-400 hover:text-blue-300 text-sm">← Kembali ke halaman Login</a>
        </div>
    </div>
</body>
</html>
