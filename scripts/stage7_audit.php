<?php
/**
 * JasaWarga Stage 7 readiness preflight. No Laravel bootstrap / DB connection required.
 * Usage: php scripts/stage7_audit.php [--mode=baseline|release] [--json] [--root=/path]
 * Exit 0: no FAIL; exit 1: check(s) failed; exit 2: invalid arguments.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$mode = 'baseline';
$json = false;
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--json') {
        $json = true;
    } elseif (str_starts_with($arg, '--mode=')) {
        $mode = substr($arg, strlen('--mode='));
    } elseif (str_starts_with($arg, '--root=')) {
        $root = substr($arg, strlen('--root='));
    } elseif ($arg === '--help' || $arg === '-h') {
        echo "php scripts/stage7_audit.php [--mode=baseline|release] [--json] [--root=/path]\n";
        exit(0);
    } else {
        fwrite(STDERR, "Argumen tidak dikenal: {$arg}\n");
        exit(2);
    }
}
if (!in_array($mode, ['baseline', 'release'], true) || !is_dir($root)) {
    fwrite(STDERR, "Mode harus baseline/release dan direktori root harus ada.\n");
    exit(2);
}
$root = realpath($root);
$checks = [];
$check = static function (string $id, bool $condition, string $pass, string $failure, string $severity = 'FAIL') use (&$checks): void {
    $checks[] = [
        'id' => $id,
        'status' => $condition ? 'PASS' : $severity,
        'message' => $condition ? $pass : $failure,
    ];
};
$exists = static fn (string $path): bool => is_file($root.'/'.$path);
$contents = static function (string $path) use ($root): string {
    $data = @file_get_contents($root.'/'.$path);
    return $data === false ? '' : $data;
};

foreach ([
    'composer.json', 'package.json', 'bootstrap/app.php', 'routes/web.php',
    'app/Models/User.php', 'app/Models/PartnerProfile.php',
    'routes/stage3_catalog.php', 'routes/stage5_feedback.php',
    'app/Models/ServiceCategory.php', 'app/Models/PartnerService.php',
    'app/Models/ServiceReview.php', 'app/Models/ServiceComplaint.php',
    'app/Services/Feedback/ReviewBookingVerifier.php',
] as $required) {
    $check('file:'.$required, $exists($required), 'Ada: '.$required, 'File sumber diperlukan hilang: '.$required);
}
$routes = $contents('routes/web.php');
$check('routes-stage3', str_contains($routes, "require __DIR__.'/stage3_catalog.php'"),
    'Route Tahap 3 terdaftar.', 'Route Tahap 3 tidak dimuat melalui routes/web.php.');
$check('routes-stage5', str_contains($routes, "require __DIR__.'/stage5_feedback.php'"),
    'Route Tahap 5 terdaftar.', 'Route Tahap 5 tidak dimuat melalui routes/web.php.');
$check('auth-role-middleware', str_contains($contents('bootstrap/app.php'), "'role' => EnsureRole::class"),
    'Middleware role terdaftar.', 'Alias middleware role tidak ditemukan.');
$check('login-session', str_contains($contents('app/Http/Controllers/AuthController.php'), 'session()->regenerate()')
    && str_contains($contents('app/Http/Controllers/AuthController.php'), 'session()->invalidate()'),
    'Regenerasi dan invalidasi sesi ada.', 'Kontrol sesi login/logout perlu diperiksa.');
$check('no-default-seed-password', !preg_match('/^\s*JASAWARGA_DEMO_PASSWORD=\S+/m', $contents('.env.example')),
    'Tidak ada password demo dalam .env.example.', 'Password demo tertanam di .env.example.');
$reviewVerifier = $contents('app/Services/Feedback/ReviewBookingVerifier.php');
$failClosed = preg_match('/function\s+isEligible\s*\([^)]*\)\s*:\s*bool\s*\{\s*return\s+false\s*;\s*\}/s', $reviewVerifier) === 1;
$check('review-verification-adapter', !$failClosed,
    'Adapter verifikasi pesanan telah diubah dari default fail-closed (tetap wajib diuji terhadap pesanan asli).',
    'ReviewBookingVerifier masih return false; aman sebagai default, tetapi belum terintegrasi dengan bukti pesanan Tahap 4.',
    $mode === 'release' ? 'FAIL' : 'WARN');
$check('postgres-extension', extension_loaded('pdo_pgsql'),
    'Ekstensi PDO PostgreSQL tersedia pada PHP CLI.',
    'Ekstensi pdo_pgsql belum tersedia pada PHP CLI; uji DB PostgreSQL perlu lingkungan yang sesuai.',
    $mode === 'release' ? 'FAIL' : 'WARN');
$check('sqlite-test-extension', extension_loaded('pdo_sqlite'),
    'Ekstensi SQLite untuk PHPUnit memory tersedia.',
    'pdo_sqlite tidak tersedia; jalankan PHPUnit di PHP yang mempunyai ekstensi SQLite.',
    $mode === 'release' ? 'FAIL' : 'WARN');
$check('composer-dependencies', $exists('vendor/autoload.php'),
    'Dependensi Composer telah terpasang.',
    'vendor/autoload.php tidak ada; jalankan composer install di lingkungan pengujian.',
    $mode === 'release' ? 'FAIL' : 'WARN');
$check('composer-lock', $exists('composer.lock'),
    'composer.lock tersedia untuk build yang dapat direproduksi.',
    'composer.lock belum dibuat; jalankan composer update terkontrol dan commit lockfile setelah evaluasi dependensi.',
    $mode === 'release' ? 'FAIL' : 'WARN');

$handoffPath = $root.'/docs/STAGE7_TEAM_HANDOFF.json';
$handoff = $exists('docs/STAGE7_TEAM_HANDOFF.json') ? json_decode((string) file_get_contents($handoffPath), true) : null;
$check('handoff-format', is_array($handoff) && isset($handoff['phases']) && is_array($handoff['phases']),
    'Manifest handoff tersedia.', 'Manifest handoff hilang atau JSON tidak valid.');
foreach (['2', '4', '6'] as $phase) {
    $entry = is_array($handoff) ? ($handoff['phases'][$phase] ?? null) : null;
    $integrated = is_array($entry) && ($entry['status'] ?? '') === 'integrated'
        && !empty($entry['merge_commit']) && is_string($entry['merge_commit'])
        && !empty($entry['test_evidence']) && is_string($entry['test_evidence']);
    $check('phase-'.$phase.'-handoff', $integrated,
        'Tahap '.$phase.' tercatat terintegrasi dengan bukti merge dan uji (perlu pemeriksaan manual).',
        'Tahap '.$phase.' belum terkonfirmasi merge + bukti pengujian. Jangan anggap sudah digabung.',
        $mode === 'release' ? 'FAIL' : 'WARN');
}
foreach (['tests/Feature/Stage7RouteAccessTest.php',
    'tests/Feature/Stage7DataBoundaryTest.php',
    'tests/Feature/Stage7MergeContractTest.php',
    'tests/Feature/Stage7BookingVerifierTest.php',
    '.github/workflows/stage7-ci.yml',
] as $artifact) {
    $check('qa:'.$artifact, $exists($artifact), 'Ada: '.$artifact, 'Artefak QA Tahap 7 hilang: '.$artifact);
}
$phpFiles = [];
foreach (['app', 'bootstrap', 'config', 'database', 'routes', 'tests'] as $directory) {
    $dir = $root.'/'.$directory;
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') $phpFiles[] = $file->getPathname();
    }
}
$failedLint = [];
foreach ($phpFiles as $path) {
    $command = escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1';
    exec($command, $out, $exitCode);
    if ($exitCode !== 0) $failedLint[] = str_replace($root.'/', '', $path);
    $out = [];
}
$check('php-lint', count($failedLint) === 0,
    count($phpFiles).' file PHP lolos php -l.',
    'PHP syntax error: '.implode(', ', array_slice($failedLint, 0, 8)));
$names = [];
foreach (glob($root.'/database/migrations/*.php') ?: [] as $migration) {
    $prefix = substr(basename($migration), 0, 17);
    $names[$prefix] = ($names[$prefix] ?? 0) + 1;
}
$duplicates = array_keys(array_filter($names, static fn (int $count) => $count > 1));
$check('migration-timestamps', count($duplicates) === 0,
    'Tidak ditemukan timestamp migration kembar.',
    'Timestamp migration kembar: '.implode(', ', $duplicates));
$summary = ['PASS'=>0,'WARN'=>0,'FAIL'=>0];
foreach ($checks as $row) $summary[$row['status']]++;
$output = ['tool'=>'JasaWarga Stage7 Preflight', 'mode'=>$mode,
    'status'=>$summary['FAIL'] === 0 ? 'PASS_WITH_WARNINGS_OR_PASS' : 'FAIL',
    'summary'=>$summary, 'checks'=>$checks];
if ($json) {
    echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL;
} else {
    echo "JasaWarga Stage 7 — mode {$mode}\n";
    foreach ($checks as $row) echo sprintf("[%-4s] %s\n", $row['status'], $row['message']);
    echo "Hasil: {$summary['PASS']} PASS, {$summary['WARN']} WARN, {$summary['FAIL']} FAIL\n";
    echo $mode === 'release' ? "Mode release: semua syarat harus PASS sebelum deploy.\n" : "Mode baseline: WARN berarti fitur tim/bukti produksi belum tersedia.\n";
}
exit($summary['FAIL'] === 0 ? 0 : 1);
