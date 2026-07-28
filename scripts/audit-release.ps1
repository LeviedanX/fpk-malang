[CmdletBinding()]
param(
    [string] $OutputDirectory = ''
)

$ErrorActionPreference = 'Stop'

function Assert-Condition {
    param(
        [bool] $Condition,
        [string] $Message
    )

    if (-not $Condition) {
        throw $Message
    }
}

function Get-StreamSha256 {
    param(
        [System.IO.Stream] $Stream
    )

    $algorithm = [System.Security.Cryptography.SHA256]::Create()
    try {
        return ([System.BitConverter]::ToString($algorithm.ComputeHash($Stream))).Replace('-', '').ToLowerInvariant()
    } finally {
        $algorithm.Dispose()
        $Stream.Dispose()
    }
}

$projectRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
if ([string]::IsNullOrWhiteSpace($OutputDirectory)) {
    $OutputDirectory = Join-Path $projectRoot 'DEPLOY'
}

$outputRoot = [System.IO.Path]::GetFullPath($OutputDirectory)
$productionZip = Join-Path $outputRoot 'fpk-malang-production.zip'
$uploadsZip = Join-Path $outputRoot 'fpk-malang-uploads.zip'
$publicDataSql = Join-Path $outputRoot 'fpk-malang-public-data.sql'
$environmentTemplate = Join-Path $outputRoot '.env.production.example'
$checksumsFile = Join-Path $outputRoot 'CHECKSUMS.sha256'

$requiredArtifacts = @(
    $productionZip,
    $uploadsZip,
    $publicDataSql,
    $environmentTemplate,
    (Join-Path $outputRoot 'README.md'),
    (Join-Path $outputRoot 'audit-release.ps1'),
    (Join-Path $outputRoot 'BUILD-INFO.txt'),
    $checksumsFile
)

foreach ($artifact in $requiredArtifacts) {
    Assert-Condition (Test-Path -LiteralPath $artifact -PathType Leaf) "Artefak tidak ditemukan: $artifact"
}

$checksumEntries = @{}
foreach ($line in Get-Content -LiteralPath $checksumsFile) {
    if ([string]::IsNullOrWhiteSpace($line)) {
        continue
    }

    Assert-Condition ($line -match '^([a-fA-F0-9]{64})\s{2}(.+)$') "Format checksum tidak valid: $line"
    $checksumEntries[$Matches[2]] = $Matches[1].ToLowerInvariant()
}

foreach ($artifact in $requiredArtifacts | Where-Object { $_ -ne $checksumsFile }) {
    $fileName = [System.IO.Path]::GetFileName($artifact)
    Assert-Condition ($checksumEntries.ContainsKey($fileName)) "Checksum tidak mencakup $fileName."
    $actualHash = (Get-FileHash -Algorithm SHA256 -LiteralPath $artifact).Hash.ToLowerInvariant()
    Assert-Condition ($actualHash -eq $checksumEntries[$fileName]) "Checksum tidak cocok: $fileName"
}

Add-Type -AssemblyName System.IO.Compression.FileSystem

$archive = [System.IO.Compression.ZipFile]::OpenRead($productionZip)
try {
    $nonPortableEntry = $archive.Entries | Where-Object { $_.FullName.Contains('\') } | Select-Object -First 1
    Assert-Condition ($null -eq $nonPortableEntry) "Separator path ZIP produksi tidak portabel: $($nonPortableEntry.FullName)"
    $entryNames = @($archive.Entries | ForEach-Object { $_.FullName.Replace('\', '/') })

    $requiredEntries = @(
        'artisan',
        'composer.json',
        'composer.lock',
        'vendor/autoload.php',
        'public/index.php',
        'public/build/manifest.json',
        '.env.production.example',
        'README.md'
    )

    foreach ($requiredEntry in $requiredEntries) {
        Assert-Condition ($entryNames -contains $requiredEntry) "ZIP produksi tidak memiliki $requiredEntry."
    }

    # Bandingkan tiap entry kritis di dalam ZIP dengan sumber acuannya. Untuk
    # file yang benar-benar berasal dari repo (README.md, kode aplikasi), acuan
    # itu adalah file proyek yang sama. .env.production.example TIDAK dijaga
    # manual sebagai file proyek -- ia diturunkan otomatis dari .env oleh
    # build-release.ps1 setiap kali build dijalankan -- sehingga acuannya adalah
    # salinan hasil generate di $outputRoot, bukan file di root proyek.
    $criticalEntries = @(
        @{ Zip = 'app/Console/Commands/DeployCheck.php'; Source = (Join-Path $projectRoot 'app\Console\Commands\DeployCheck.php') },
        @{ Zip = 'public/build/manifest.json'; Source = (Join-Path $projectRoot 'public\build\manifest.json') },
        @{ Zip = 'README.md'; Source = (Join-Path $projectRoot 'README.md') },
        @{ Zip = '.env.production.example'; Source = $environmentTemplate }
    )

    foreach ($criticalEntry in $criticalEntries) {
        $zipEntry = $archive.GetEntry($criticalEntry.Zip)
        Assert-Condition ($null -ne $zipEntry) "File kritis tidak ditemukan di ZIP: $($criticalEntry.Zip)"
        $zipHash = Get-StreamSha256 -Stream $zipEntry.Open()
        $sourceHash = (Get-FileHash -Algorithm SHA256 -LiteralPath $criticalEntry.Source).Hash.ToLowerInvariant()
        Assert-Condition ($zipHash -eq $sourceHash) "File kritis dalam ZIP berbeda dari source: $($criticalEntry.Zip)"
    }

    $forbiddenPatterns = @(
        '(^|/)\.env$',
        '(^|/)\.git/',
        '^\.npmrc$',
        '(^|/)node_modules/',
        '^package(-lock)?\.json$',
        '^scripts/',
        '(^|/)tests/',
        '^vite\.config\.js$',
        '^public/hot$',
        '^public/storage/',
        '^vendor/phpunit/',
        '^vendor/laravel/pint/',
        '^vendor/fakerphp/',
        '^vendor/mockery/',
        '^vendor/nunomaduro/collision/',
        '(^|/)(id_rsa|auth\.json)$',
        '\.(pem|p12|pfx)$'
    )

    foreach ($entryName in $entryNames) {
        foreach ($pattern in $forbiddenPatterns) {
            Assert-Condition (-not ($entryName -match $pattern)) "Path terlarang di ZIP produksi: $entryName"
        }

        if ($entryName -like 'storage/app/public/*') {
            Assert-Condition (
                $entryName -in @(
                    'storage/app/public/.gitignore',
                    'storage/app/public/.htaccess'
                )
            ) "Upload runtime masuk ZIP produksi: $entryName"
        }
    }

    $secretPatterns = @(
        '-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----',
        '(?m)^APP_KEY[ \t]*=[ \t]*base64:[A-Za-z0-9+/=]{20,}[ \t]*$',
        '(?m)^DB_PASSWORD[ \t]*=[ \t]*\S+[ \t]*$',
        '(?m)^ADMIN_PASSWORD[ \t]*=[ \t]*\S+[ \t]*$'
    )

    $textExtensions = @(
        '.css', '.env', '.example', '.html', '.js', '.json', '.md', '.php',
        '.ps1', '.sql', '.svg', '.txt', '.xml', '.yaml', '.yml'
    )

    foreach ($entry in $archive.Entries) {
        if ($entry.Length -eq 0 -or $entry.Length -gt 2MB) {
            continue
        }

        $extension = [System.IO.Path]::GetExtension($entry.FullName).ToLowerInvariant()
        if ($extension -notin $textExtensions) {
            continue
        }

        $reader = New-Object System.IO.StreamReader($entry.Open())
        try {
            $content = $reader.ReadToEnd()
        } finally {
            $reader.Dispose()
        }

        foreach ($pattern in $secretPatterns) {
            Assert-Condition (-not ($content -match $pattern)) "Pola secret ditemukan di $($entry.FullName)."
        }
    }

    $productionEntryCount = $archive.Entries.Count
    $productionUncompressedBytes = ($archive.Entries | Measure-Object -Property Length -Sum).Sum
} finally {
    $archive.Dispose()
}

$uploadsArchive = [System.IO.Compression.ZipFile]::OpenRead($uploadsZip)
try {
    $nonPortableUploadEntry = $uploadsArchive.Entries | Where-Object { $_.FullName.Contains('\') } | Select-Object -First 1
    Assert-Condition ($null -eq $nonPortableUploadEntry) "Separator path ZIP upload tidak portabel: $($nonPortableUploadEntry.FullName)"
    $uploadEntryNames = @($uploadsArchive.Entries | ForEach-Object { $_.FullName.Replace('\', '/') })
    foreach ($entryName in $uploadEntryNames) {
        Assert-Condition (-not ($entryName -match '(^|/)\.env$')) "File .env ditemukan dalam ZIP upload."
        Assert-Condition (-not ($entryName -match '(^|/)(id_rsa|auth\.json)$')) "Credential ditemukan dalam ZIP upload."
        Assert-Condition (-not ($entryName -match '\.(pem|p12|pfx)$')) "File kunci ditemukan dalam ZIP upload."
    }

    $uploadEntryCount = $uploadsArchive.Entries.Count
    $uploadUncompressedBytes = ($uploadsArchive.Entries | Measure-Object -Property Length -Sum).Sum
} finally {
    $uploadsArchive.Dispose()
}

$sql = Get-Content -Raw -LiteralPath $publicDataSql
$allowedTables = @(
    'site_settings',
    'fpk_profiles',
    'contact_settings',
    'management_periods',
    'management_members',
    'articles',
    'agendas',
    'gallery_images'
)
$insertedTables = @(
    [regex]::Matches($sql, 'INSERT INTO `([^`]+)`') |
        ForEach-Object { $_.Groups[1].Value } |
        Sort-Object -Unique
)

Assert-Condition ($insertedTables.Count -gt 0) 'Dump SQL tidak memiliki data.'
foreach ($table in $insertedTables) {
    Assert-Condition ($table -in $allowedTables) "Tabel tidak diizinkan dalam dump publik: $table"
}

$forbiddenSqlPatterns = @(
    '(?im)^\s*CREATE\s+DATABASE\b',
    '(?im)^\s*USE\s+`?',
    '(?im)^\s*(GRANT|REVOKE)\b',
    '(?im)^\s*(CREATE|ALTER|DROP)\s+USER\b',
    '(?i)\bDEFINER\s*=',
    '(?i)INSERT INTO `(users|sessions|password_reset_tokens|cache|cache_locks|jobs|job_batches|failed_jobs|admin_activity_logs|agenda_logs)`'
)

foreach ($pattern in $forbiddenSqlPatterns) {
    Assert-Condition (-not ($sql -match $pattern)) 'Dump SQL mengandung statement atau tabel privat.'
}

$environmentLines = Get-Content -LiteralPath $environmentTemplate
$environmentKeys = @()
foreach ($line in $environmentLines) {
    if ($line -match '^\s*([A-Z][A-Z0-9_]*)=') {
        $environmentKeys += $Matches[1]
    }
}

$duplicateKeys = @($environmentKeys | Group-Object | Where-Object { $_.Count -gt 1 })
Assert-Condition ($duplicateKeys.Count -eq 0) 'Template environment memiliki key duplikat.'

$environment = Get-Content -Raw -LiteralPath $environmentTemplate
Assert-Condition ($environment -match '(?m)^APP_ENV=production$') 'APP_ENV production belum benar.'
Assert-Condition ($environment -match '(?m)^APP_DEBUG=false$') 'APP_DEBUG belum false.'
Assert-Condition ($environment -match '(?m)^APP_URL=https://') 'APP_URL belum HTTPS.'
Assert-Condition ($environment -match '(?m)^SESSION_SECURE_COOKIE=true$') 'Cookie secure belum aktif.'
Assert-Condition ($environment -match '(?m)^SESSION_ENCRYPT=true$') 'Enkripsi session belum aktif.'
Assert-Condition ($environment -match '(?m)^APP_KEY=$') 'APP_KEY template harus kosong.'
Assert-Condition ($environment -match '(?m)^DB_USERNAME=$') 'DB_USERNAME template harus kosong.'
Assert-Condition ($environment -match '(?m)^DB_PASSWORD=$') 'DB_PASSWORD template harus kosong.'
Assert-Condition ($environment -match '(?m)^ADMIN_PASSWORD=$') 'ADMIN_PASSWORD template harus kosong.'

Write-Output 'Audit release: LULUS'
Write-Output "ZIP produksi: $productionEntryCount entri, $productionUncompressedBytes byte tanpa kompresi"
Write-Output "ZIP upload: $uploadEntryCount entri, $uploadUncompressedBytes byte tanpa kompresi"
Write-Output "Tabel SQL publik: $($insertedTables -join ', ')"
Write-Output "Checksum terverifikasi: $($requiredArtifacts.Count - 1) artefak"
