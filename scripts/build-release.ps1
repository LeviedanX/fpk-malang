[CmdletBinding()]
param(
    [string] $OutputDirectory = ''
)

$ErrorActionPreference = 'Stop'

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

function New-PortableZip {
    param(
        [string] $SourceDirectory,
        [string] $DestinationFile
    )

    $sourceRoot = [System.IO.Path]::GetFullPath($SourceDirectory).TrimEnd('\') + '\'
    $archive = [System.IO.Compression.ZipFile]::Open(
        $DestinationFile,
        [System.IO.Compression.ZipArchiveMode]::Create
    )

    try {
        foreach ($file in Get-ChildItem -LiteralPath $sourceRoot -Recurse -File) {
            $entryName = $file.FullName.Substring($sourceRoot.Length).Replace('\', '/')
            [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
                $archive,
                $file.FullName,
                $entryName,
                [System.IO.Compression.CompressionLevel]::Optimal
            ) | Out-Null
        }
    } finally {
        $archive.Dispose()
    }
}

$projectRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
if ([string]::IsNullOrWhiteSpace($OutputDirectory)) {
    $OutputDirectory = Join-Path $projectRoot 'DEPLOY'
}

$outputRoot = [System.IO.Path]::GetFullPath($OutputDirectory)
$projectPrefix = $projectRoot.TrimEnd('\') + '\'

if (-not $outputRoot.StartsWith($projectPrefix, [System.StringComparison]::OrdinalIgnoreCase)) {
    throw 'OutputDirectory harus berada di dalam project.'
}

$staging = Join-Path $outputRoot '.staging-fpk-malang'
$sourceZip = Join-Path $outputRoot 'fpk-malang-production.zip'
$uploadsZip = Join-Path $outputRoot 'fpk-malang-uploads.zip'
$publicDataSql = Join-Path $outputRoot 'fpk-malang-public-data.sql'
$checksums = Join-Path $outputRoot 'CHECKSUMS.sha256'
$buildInfo = Join-Path $outputRoot 'BUILD-INFO.txt'
$auditScript = Join-Path $outputRoot 'audit-release.ps1'

foreach ($target in @($staging, $sourceZip, $uploadsZip, $checksums, $buildInfo, $auditScript)) {
    $resolved = [System.IO.Path]::GetFullPath($target)
    if (-not $resolved.StartsWith($outputRoot.TrimEnd('\') + '\', [System.StringComparison]::OrdinalIgnoreCase)) {
        throw "Target build keluar dari DEPLOY: $resolved"
    }

    if (Test-Path -LiteralPath $resolved) {
        Remove-Item -LiteralPath $resolved -Recurse -Force
    }
}

New-Item -ItemType Directory -Force -Path $outputRoot, $staging | Out-Null

$excludedDirectories = @(
    (Join-Path $projectRoot '.git'),
    (Join-Path $projectRoot '.playwright-cli'),
    (Join-Path $projectRoot 'DEPLOY'),
    (Join-Path $projectRoot 'node_modules'),
    (Join-Path $projectRoot 'output'),
    (Join-Path $projectRoot 'scripts'),
    (Join-Path $projectRoot 'tests'),
    (Join-Path $projectRoot 'vendor'),
    (Join-Path $projectRoot 'public\storage'),
    (Join-Path $projectRoot 'storage\app\public'),
    (Join-Path $projectRoot 'storage\framework\cache\data'),
    (Join-Path $projectRoot 'storage\framework\sessions'),
    (Join-Path $projectRoot 'storage\framework\testing'),
    (Join-Path $projectRoot 'storage\framework\views'),
    (Join-Path $projectRoot 'storage\logs'),
    (Join-Path $projectRoot 'bootstrap\cache')
)

$excludedFiles = @(
    (Join-Path $projectRoot '.env'),
    (Join-Path $projectRoot '.editorconfig'),
    (Join-Path $projectRoot '.gitattributes'),
    (Join-Path $projectRoot '.gitignore'),
    (Join-Path $projectRoot '.npmrc'),
    (Join-Path $projectRoot '.phpunit.result.cache'),
    (Join-Path $projectRoot 'package-lock.json'),
    (Join-Path $projectRoot 'package.json'),
    (Join-Path $projectRoot 'phpunit.xml'),
    (Join-Path $projectRoot 'public\hot'),
    (Join-Path $projectRoot 'public\fonts-manifest.dev.json'),
    (Join-Path $projectRoot 'vite.config.js')
)

$robocopyArgs = @(
    $projectRoot,
    $staging,
    '/E',
    '/NFL',
    '/NDL',
    '/NJH',
    '/NJS',
    '/NP',
    '/R:1',
    '/W:1',
    '/XD'
) + $excludedDirectories + @('/XF') + $excludedFiles

& robocopy @robocopyArgs | Out-Null
if ($LASTEXITCODE -ge 8) {
    throw "Robocopy gagal dengan exit code $LASTEXITCODE."
}

$runtimeDirectories = @(
    'bootstrap\cache',
    'storage\app\public',
    'storage\framework\cache\data',
    'storage\framework\sessions',
    'storage\framework\testing',
    'storage\framework\views',
    'storage\logs'
)

foreach ($relativeDirectory in $runtimeDirectories) {
    New-Item -ItemType Directory -Force -Path (Join-Path $staging $relativeDirectory) | Out-Null
}

$runtimeKeepFiles = @(
    'bootstrap\cache\.gitignore',
    'storage\app\public\.gitignore',
    'storage\app\public\.htaccess',
    'storage\framework\cache\data\.gitignore',
    'storage\framework\sessions\.gitignore',
    'storage\framework\testing\.gitignore',
    'storage\framework\views\.gitignore',
    'storage\logs\.gitignore'
)

foreach ($relativeFile in $runtimeKeepFiles) {
    $source = Join-Path $projectRoot $relativeFile
    if (Test-Path -LiteralPath $source) {
        Copy-Item -LiteralPath $source -Destination (Join-Path $staging $relativeFile) -Force
    }
}

Push-Location $staging
try {
    & composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress
    if ($LASTEXITCODE -ne 0) {
        throw 'Composer production install gagal.'
    }
} finally {
    Pop-Location
}

$forbidden = @(
    '.env',
    '.git',
    'node_modules',
    'tests',
    'public\hot',
    'public\storage',
    'vendor\phpunit'
)

foreach ($relativePath in $forbidden) {
    if (Test-Path -LiteralPath (Join-Path $staging $relativePath)) {
        throw "Artefak terlarang masuk staging: $relativePath"
    }
}

if (-not (Test-Path -LiteralPath (Join-Path $staging 'public\build\manifest.json'))) {
    throw 'Manifest Vite tidak ditemukan di staging.'
}

if (-not (Test-Path -LiteralPath (Join-Path $staging 'vendor\autoload.php'))) {
    throw 'Dependency Composer production tidak ditemukan di staging.'
}

# Templat .env production yang aman-secret, diturunkan otomatis dari .env kerja
# saat ini. Proyek ini hanya menyimpan SATU .env (root, tidak pernah di-commit);
# tidak ada lagi .env.production.example yang dijaga manual dan bisa tertinggal
# tidak sinkron. Nilai rahasia dikosongkan dan pengaturan keamanan dipaksa ke
# posisi production apa pun isi .env sumbernya, sehingga templat yang dihasilkan
# selalu aman dibagikan meski .env sumbernya sedang berisi nilai pengembangan.
$envSourcePath = Join-Path $projectRoot '.env'
if (-not (Test-Path -LiteralPath $envSourcePath)) {
    throw '.env tidak ditemukan di root proyek -- tidak bisa membuat templat rilis.'
}

$secretKeys = @(
    'APP_KEY', 'DB_USERNAME', 'DB_PASSWORD',
    'ADMIN_NAME', 'ADMIN_EMAIL', 'ADMIN_PASSWORD',
    'MAIL_USERNAME', 'MAIL_PASSWORD',
    'AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY'
)
$forcedValues = @{
    'APP_ENV'               = 'production'
    'APP_DEBUG'              = 'false'
    'APP_URL'                = 'https://fpk.malangkota.go.id'
    'LOG_STACK'              = 'daily'
    'LOG_LEVEL'              = 'warning'
    'SESSION_ENCRYPT'        = 'true'
    'SESSION_SECURE_COOKIE'  = 'true'
    'QUEUE_CONNECTION'       = 'sync'
}

$templateLines = foreach ($line in Get-Content -LiteralPath $envSourcePath) {
    if ($line -match '^([A-Z][A-Z0-9_]*)=') {
        $key = $Matches[1]
        if ($secretKeys -contains $key) {
            "$key="
        } elseif ($forcedValues.ContainsKey($key)) {
            "$key=$($forcedValues[$key])"
        } else {
            $line
        }
    } else {
        $line
    }
}

$environmentTemplatePath = Join-Path $staging '.env.production.example'
# LF murni, tanpa BOM: file .env sumbernya sudah begitu, dan audit-release.ps1
# mencocokkan nilai lewat regex `(?m)^KEY=value$` yang mengasumsikan tidak ada
# `\r` tersisa sebelum setiap baris baru.
$environmentContent = ($templateLines -join "`n") + "`n"
[System.IO.File]::WriteAllText($environmentTemplatePath, $environmentContent, (New-Object System.Text.UTF8Encoding($false)))

New-PortableZip -SourceDirectory $staging -DestinationFile $sourceZip
New-PortableZip -SourceDirectory (Join-Path $projectRoot 'storage\app\public') -DestinationFile $uploadsZip

Copy-Item -LiteralPath (Join-Path $projectRoot 'README.md') -Destination $outputRoot -Force
Copy-Item -LiteralPath $environmentTemplatePath -Destination $outputRoot -Force
Copy-Item -LiteralPath (Join-Path $projectRoot 'scripts\audit-release.ps1') -Destination $auditScript -Force

$commit = (& git -C $projectRoot rev-parse HEAD).Trim()
$workingTreeChanges = @(& git -C $projectRoot status --porcelain --untracked-files=normal)
$workingTreeState = if ($workingTreeChanges.Count -eq 0) {
    'clean'
} else {
    'modified; ZIP dibangun dari working tree saat ini, termasuk perubahan yang belum di-commit'
}
$buildLines = @(
    'FPK Malang production handoff',
    "Built at: $([DateTimeOffset]::Now.ToString('o'))",
    "Base Git commit: $commit",
    "Working tree: $workingTreeState",
    "PHP: $(php -r 'echo PHP_VERSION;')",
    'Source ZIP excludes secrets, development dependencies, tests, public/hot, and runtime uploads.',
    'Uploads ZIP must be extracted to storage/app/public before php artisan storage:link.'
)
$buildLines | Set-Content -LiteralPath $buildInfo -Encoding UTF8

$artifactPaths = @(
    $sourceZip,
    $uploadsZip,
    (Join-Path $outputRoot '.env.production.example'),
    (Join-Path $outputRoot 'README.md'),
    $auditScript,
    $buildInfo
)

if (Test-Path -LiteralPath $publicDataSql) {
    $artifactPaths += $publicDataSql
}

$checksumLines = foreach ($artifact in $artifactPaths) {
    $hash = Get-FileHash -Algorithm SHA256 -LiteralPath $artifact
    "$($hash.Hash.ToLowerInvariant())  $([System.IO.Path]::GetFileName($artifact))"
}
$checksumLines | Set-Content -LiteralPath $checksums -Encoding ASCII

Remove-Item -LiteralPath $staging -Recurse -Force

Write-Output "Release source: $sourceZip"
Write-Output "Runtime uploads: $uploadsZip"
Write-Output "Checksums: $checksums"
