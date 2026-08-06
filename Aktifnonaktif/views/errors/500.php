<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>500 - Server Error | Universitas Nusa Putra</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>*{font-family:'Poppins',sans-serif}</style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-[#0a0a1a] to-slate-900 min-h-screen flex items-center justify-center p-4">
<div class="text-center max-w-md">
  <div class="text-[120px] font-black text-transparent bg-clip-text bg-gradient-to-b from-orange-400 to-orange-800 leading-none mb-4">500</div>
  <h1 class="text-white font-bold text-2xl mb-2">Terjadi Kesalahan Server</h1>
  <p class="text-slate-400 text-sm mb-8">Mohon maaf, terjadi kesalahan pada sistem. Tim kami akan segera memperbaikinya.</p>
  <div class="flex justify-center gap-3">
    <a href="javascript:location.reload()" class="px-5 py-2.5 border border-slate-600 text-slate-300 rounded-xl hover:bg-slate-700 transition text-sm">↺ Coba Lagi</a>
    <a href="<?php echo defined('APP_URL') ? APP_URL : '/'; ?>/" class="px-5 py-2.5 bg-orange-700 text-white rounded-xl hover:bg-orange-800 transition text-sm font-semibold">🏠 Beranda</a>
  </div>
</div>
</body>
</html>
