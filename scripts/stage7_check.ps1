<# Windows PowerShell / PowerShell 7. Tanpa ekstensi DB/vendor, hasilnya SKIP, bukan PASSED. #>
param(
    [ValidateSet('baseline', 'release')][string]$Mode = 'baseline'
)
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Push-Location $root
try {
    & php scripts/stage7_audit.php "--mode=$Mode"
    if ($LASTEXITCODE -ne 0) { throw "Stage7 audit gagal (exit $LASTEXITCODE)." }
    if (Get-Command node -ErrorAction SilentlyContinue) {
        $tests = Get-ChildItem 'tests/js/*.test.mjs' | ForEach-Object { $_.FullName }
        & node --test @tests
        if ($LASTEXITCODE -ne 0) { throw 'Pengujian JavaScript gagal.' }
    } else {
        Write-Warning 'Node tidak ditemukan. Uji JavaScript dilewati.'
        if ($Mode -eq 'release') { throw 'Release memerlukan pengujian JavaScript.' }
    }
    if (Test-Path 'vendor/autoload.php') {
        & php artisan test
        if ($LASTEXITCODE -ne 0) { throw 'Pengujian Laravel/PHPUnit gagal.' }
    } else {
        Write-Warning 'vendor/autoload.php belum ada; pengujian Laravel BELUM dijalankan. Jalankan composer install.'
        if ($Mode -eq 'release') { throw 'Release memerlukan pengujian Laravel.' }
    }
    Write-Host 'Tahap baseline selesai. Lanjutkan smoke HTTP dan E2E manual.'
} finally {
    Pop-Location
}
