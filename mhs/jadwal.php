<?php
$pageTitle='Jadwal & Kalender Akademik';
require_once 'header.php';

// Get the student's prodi_id
$prodiId = $mhs['prodi_id'] ?? null;

// Query the jadwal table - always show all-prodi events, plus events for this specific prodi
if ($prodiId) {
    $events  = dbQuery("SELECT j.*, p.nama as pnama FROM jadwal j LEFT JOIN prodi p ON p.id=j.prodi_id WHERE j.prodi_id IS NULL OR j.prodi_id = ? ORDER BY j.tanggal_mulai ASC", [$prodiId]);
    $agenda  = dbQuery("SELECT j.*, p.nama as pnama FROM jadwal j LEFT JOIN prodi p ON p.id=j.prodi_id WHERE (j.prodi_id IS NULL OR j.prodi_id = ?) AND j.tanggal_mulai >= CURDATE() ORDER BY j.tanggal_mulai ASC LIMIT 15", [$prodiId]);
} else {
    $events  = dbQuery("SELECT j.*, p.nama as pnama FROM jadwal j LEFT JOIN prodi p ON p.id=j.prodi_id WHERE j.prodi_id IS NULL ORDER BY j.tanggal_mulai ASC");
    $agenda  = dbQuery("SELECT j.*, p.nama as pnama FROM jadwal j LEFT JOIN prodi p ON p.id=j.prodi_id WHERE j.prodi_id IS NULL AND j.tanggal_mulai >= CURDATE() ORDER BY j.tanggal_mulai ASC LIMIT 15");
}

$totalEvents = count($events ?? []);
$upcoming    = count($agenda  ?? []);

// Count events by type
$jenisCount = [];
foreach (($events??[]) as $e) {
    $j = $e['jenis_event'] ?? 'Lainnya';
    $jenisCount[$j] = ($jenisCount[$j] ?? 0) + 1;
}
arsort($jenisCount);

// Get the latest uploaded PDF calendar
$pdfFiles = glob(__DIR__ . '/../assets/docs/Kalender_Akademik*.pdf');
$latestPdf = 'Kalender_Akademik_2025_2026.pdf';
if ($pdfFiles) {
    usort($pdfFiles, fn($a, $b) => filemtime($b) - filemtime($a));
    $latestPdf = basename($pdfFiles[0]);
}

$academicYearStr = '2025/2026'; // fallback
if (preg_match('/_(\d{4})_(\d{4})\.pdf$/i', $latestPdf, $m)) {
    $academicYearStr = $m[1] . '/' . $m[2];
}

$fcEvents = array_map(function($e) {
    // Strip time from MySQL datetime — FullCalendar all-day events only need YYYY-MM-DD
    $start = $e['tanggal_mulai'] ? substr($e['tanggal_mulai'], 0, 10) : null;
    // FullCalendar exclusive-end: add 1 day so multi-day events cover the end date visually
    $end = null;
    if (!empty($e['tanggal_selesai'])) {
        $endDate = substr($e['tanggal_selesai'], 0, 10);
        $end = date('Y-m-d', strtotime($endDate . ' +1 day'));
    }
    return [
        'id'    => $e['id'],
        'title' => $e['judul'],
        'start' => $start,
        'end'   => $end,
        'allDay' => true,
        'color' => $e['warna'] ?? '#8c0c4c',
        'extendedProps' => [
            'jenis' => $e['jenis_event'],
            'prodi' => $e['pnama'] ?? 'Umum',
            'desc'  => $e['deskripsi'] ?? '',
            'id'    => $e['id'],
        ]
    ];
}, ($events ?? []));

// Helper: Indonesian month abbr
if (!function_exists('idMonth')) {
    function idMonth($ts) {
        $m = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
        return $m[(int)date('n', $ts) - 1];
    }
}
?>

<style>
/* ────────── Premium Jadwal Styles ────────── */
.jadwal-hero-bg {
    background: linear-gradient(135deg, #8c0c4c 0%, #6b1040 40%, #1e1b4b 100%);
}
.jadwal-card {
    background: white;
    border-radius: 1.5rem;
    border: 1px solid rgba(0,0,0,0.06);
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
    transition: box-shadow 0.25s ease;
}
.dark .jadwal-card {
    background: #1e293b;
    border-color: rgba(255,255,255,0.07);
}
.jadwal-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.1); }

.stat-card {
    position: relative;
    overflow: hidden;
}
.stat-card::after {
    content: '';
    position: absolute;
    top: -20px; right: -20px;
    width: 80px; height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
}

/* Agenda items */
.agenda-item {
    position: relative;
    padding: 0.875rem 1rem 0.875rem 1.25rem;
    border-radius: 1rem;
    transition: background 0.18s;
}
.agenda-item:hover { background: rgba(0,0,0,0.03); }
.dark .agenda-item:hover { background: rgba(255,255,255,0.04); }
.agenda-item .bar {
    position: absolute;
    left: 0; top: 8px; bottom: 8px;
    width: 4px; border-radius: 4px;
}

/* Legend pills */
.legend-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; border-radius: 999px;
    font-size: 0.7rem; font-weight: 600;
    background: rgba(0,0,0,0.05);
    transition: transform 0.15s;
    cursor: default;
}
.dark .legend-pill { background: rgba(255,255,255,0.06); }
.legend-pill:hover { transform: translateY(-1px); }

/* FullCalendar premium overrides */
.fc .fc-toolbar-title { font-family: 'Poppins',sans-serif; font-weight: 700; font-size: 1.05rem; }
.fc .fc-button {
    background: #f8fafc; border: 1px solid #e2e8f0 !important;
    color: #475569; font-weight: 600; border-radius: 0.6rem !important;
    padding: 0.35rem 0.75rem; font-size: 0.72rem; box-shadow: none !important;
}
.fc .fc-button:hover, .fc .fc-button-active { background: #8c0c4c !important; border-color: #8c0c4c !important; color: white !important; }
.dark .fc .fc-button { background: #334155; border-color: #475569 !important; color: #cbd5e1; }
.dark .fc .fc-button:hover, .dark .fc .fc-button-active { background: #8c0c4c !important; color: white !important; }
.fc .fc-event { border-radius: 5px !important; font-size: 0.68rem; font-weight: 600; border: none !important; padding: 2px 6px; }
.fc .fc-day-today { background: rgba(140,12,76,0.06) !important; }
.fc .fc-col-header-cell-cushion { font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
.dark .fc .fc-col-header-cell-cushion { color: #94a3b8; }
.dark .fc .fc-daygrid-day-number { color: #94a3b8; }
.dark .fc .fc-day-today { background: rgba(140,12,76,0.12) !important; }
.dark .fc table, .dark .fc th, .dark .fc td { border-color: rgba(255,255,255,0.07) !important; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }

@keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}
.anim-up { animation: fadeSlideUp 0.4s ease both; }
</style>

<!-- ══════════════════════════════════════
     PAGE HEADER — Hero Banner
════════════════════════════════════════ -->
<div class="jadwal-hero-bg rounded-3xl p-7 mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-5 anim-up">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-2xl bg-white/15 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <span class="text-white/70 text-sm font-medium tracking-wide uppercase">Pascasarjana Universitas Nusa Putra</span>
        </div>
        <h1 class="font-display font-bold text-2xl md:text-3xl text-white mb-1">Kalender Akademik <?=$academicYearStr?></h1>
        <p class="text-white/60 text-sm">Jadwal resmi kegiatan akademik dan agenda program studi</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-3 self-start sm:self-auto w-full sm:w-auto">
        <a href="../assets/docs/<?=$latestPdf?>" target="_blank" download="<?=$latestPdf?>"
            class="inline-flex justify-center items-center gap-2 px-5 py-2.5 bg-white/10 text-white hover:bg-white/20 rounded-xl font-bold transition-all backdrop-blur-sm border border-white/20 shadow-lg text-sm whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download Kalender PDF
        </a>
    </div>
</div>

<!-- ══════════════════════════════════════
     QUICK STATS
════════════════════════════════════════ -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
    <!-- Total Events -->
    <div class="jadwal-card stat-card p-5 flex items-center gap-4 anim-up" style="animation-delay:0.05s">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#8c0c4c] to-[#a3155b] flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-800 dark:text-white"><?=$totalEvents?></div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Event</div>
        </div>
    </div>
    <!-- Upcoming -->
    <div class="jadwal-card stat-card p-5 flex items-center gap-4 anim-up" style="animation-delay:0.1s">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-800 dark:text-white"><?=$upcoming?></div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Akan Datang</div>
        </div>
    </div>
    <!-- Wisuda -->
    <?php $wisudaCount = $jenisCount['Wisuda'] ?? 0; ?>
    <div class="jadwal-card stat-card p-5 flex items-center gap-4 anim-up" style="animation-delay:0.15s">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-800 dark:text-white"><?=$wisudaCount?></div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Wisuda</div>
        </div>
    </div>
    <!-- Tahun -->
    <div class="jadwal-card stat-card p-5 flex items-center gap-4 anim-up" style="animation-delay:0.2s">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-xl font-bold text-slate-800 dark:text-white">2025/26</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Tahun Aktif</div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════
     LEGEND PILLS
════════════════════════════════════════ -->
<?php if (!empty($jenisCount)): ?>
<div class="jadwal-card p-4 mb-6 anim-up" style="animation-delay:0.22s">
    <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mr-1">Kategori:</span>
        <?php
        $typeColors = [
            'Lainnya'            => '#64748b',
            'Wisuda'             => '#9333ea',
            'Seminar Proposal'   => '#0284c7',
            'Sidang Tesis'       => '#8c0c4c',
            'Ujian Komprehensif' => '#dc2626',
            'Kolokium'           => '#16a34a',
            'IAMP'               => '#d97706',
            'Capstone Project'   => '#0891b2',
            'Rapat Prodi'        => '#475569',
            'Deadline'           => '#ef4444',
        ];
        foreach ($jenisCount as $jenis => $cnt):
            $c = $typeColors[$jenis] ?? '#8c0c4c';
        ?>
        <span class="legend-pill" style="color:<?=$c?>">
            <span style="width:8px;height:8px;border-radius:50%;background:<?=$c?>;display:inline-block;"></span>
            <?=htmlspecialchars($jenis)?> <span class="opacity-60">(<?=$cnt?>)</span>
        </span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════
     MAIN GRID: Calendar + Agenda
════════════════════════════════════════ -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <!-- ── Calendar ── -->
    <div class="xl:col-span-2 jadwal-card p-6 anim-up" style="animation-delay:0.25s">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="font-display font-bold text-lg text-slate-800 dark:text-white">Kalender Kegiatan</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Jadwal penting program studi Anda</p>
            </div>
            <div class="w-9 h-9 rounded-xl bg-[#8c0c4c]/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-[#8c0c4c] dark:text-[#f06ea4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div id="fullcalendar"></div>
    </div>

    <!-- ── Agenda Sidebar ── -->
    <div class="jadwal-card p-6 flex flex-col anim-up" style="animation-delay:0.3s">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-display font-bold text-lg text-slate-800 dark:text-white">Agenda Mendatang</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kegiatan yang akan datang</p>
            </div>
            <span class="px-2.5 py-1 bg-[#8c0c4c]/10 text-[#8c0c4c] dark:text-[#f06ea4] text-xs font-bold rounded-xl"><?=$upcoming?></span>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar space-y-1 -mx-1 px-1">
            <?php if (empty($agenda)): ?>
            <div class="flex flex-col items-center justify-center text-slate-400 py-10">
                <div class="w-16 h-16 bg-slate-50 dark:bg-slate-700/30 rounded-full flex items-center justify-center mb-3">
                    <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-sm font-medium">Tidak ada agenda mendatang</p>
            </div>
            <?php else: foreach ($agenda as $ag):
                $warna = htmlspecialchars($ag['warna'] ?? '#8c0c4c');
                $ts    = strtotime($ag['tanggal_mulai']);
                $dayNum = date('d', $ts);
                $month  = idMonth($ts);
                $jenis  = $ag['jenis_event'] ?? 'Lainnya';
            ?>
            <div class="agenda-item group" onclick="void(0)">
                <div class="bar" style="background:<?=$warna?>"></div>
                <div class="flex items-start gap-3">
                    <!-- Date badge -->
                    <div class="shrink-0 w-11 text-center rounded-xl py-1.5 border" style="border-color:<?=$warna?>22; background:<?=$warna?>10">
                        <div class="text-base font-black leading-none" style="color:<?=$warna?>"><?=$dayNum?></div>
                        <div class="text-[9px] font-bold uppercase tracking-wide text-slate-400 mt-0.5"><?=$month?></div>
                    </div>
                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 dark:text-white truncate leading-snug"><?=htmlspecialchars($ag['judul'])?></p>
                        <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold" style="background:<?=$warna?>18; color:<?=$warna?>"><?=htmlspecialchars($jenis)?></span>
                            <?php if ($ag['pnama']): ?><span class="text-[10px] text-slate-400 dark:text-slate-500"><?=htmlspecialchars($ag['pnama'])?></span><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<?php
$ej = json_encode($fcEvents, JSON_UNESCAPED_UNICODE);
$pageScript = "
const cal = new FullCalendar.Calendar(document.getElementById('fullcalendar'), {
    initialView: 'dayGridMonth',
    locale: 'id',
    events: $ej,
    displayEventTime: false,
    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek' },
    height: 'auto',
    dayMaxEvents: 3,
    eventClick: info => {
        const p = info.event.extendedProps;
        const color = info.event.backgroundColor;
        Swal.fire({
            title: info.event.title,
            html: `
                <div style='text-align:left;padding:4px 0'>
                    <span style='display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:999px;background:\${color}18;color:\${color};font-size:12px;font-weight:700;margin-bottom:10px'>\${p.jenis}</span>
                    <p style='font-size:13px;color:#64748b;margin:6px 0'><strong>Program Studi:</strong> \${p.prodi}</p>
                    \${p.desc ? '<p style=\"font-size:13px;color:#64748b;margin:6px 0\">'+p.desc+'</p>' : ''}
                </div>`,
            confirmButtonColor: '#8c0c4c',
            confirmButtonText: 'Tutup'
        });
    }
});
cal.render();
";
require_once 'footer.php';
?>
