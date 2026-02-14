$ErrorActionPreference = "Stop"
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$binDir = "$PSScriptRoot\bin"
$phpDir = "$binDir\php"
$phpZip = "$binDir\php.zip"
$composerPhar = "$binDir\composer.phar"

# PHP 8.3.0 NTS x64 - Archive link is safer
$phpUrl = "https://windows.php.net/downloads/releases/archives/php-8.3.0-nts-Win32-vs16-x64.zip"

if (-not (Test-Path $binDir)) { New-Item -ItemType Directory -Path $binDir | Out-Null }

if (-not (Test-Path $phpDir)) {
    Write-Host "Downloading PHP from $phpUrl ..."
    try {
        Invoke-WebRequest -Uri $phpUrl -OutFile $phpZip
    } catch {
        Write-Error "Failed to download PHP. Please check internet connection or URL."
        exit 1
    }

    Write-Host "Extracting PHP..."
    Expand-Archive -Path $phpZip -DestinationPath $phpDir -Force
    Remove-Item $phpZip
} else {
    Write-Host "PHP already exists in $phpDir"
}

if (-not (Test-Path $composerPhar)) {
    Write-Host "Downloading Composer..."
    Invoke-WebRequest -Uri "https://getcomposer.org/composer.phar" -OutFile $composerPhar
}

Write-Host "Configuring PHP..."
$phpIni = "$phpDir\php.ini"
if (-not (Test-Path $phpIni)) {
    Copy-Item "$phpDir\php.ini-production" $phpIni
}

$content = Get-Content $phpIni
# Enable extensions
$content = $content -replace ';extension_dir = "ext"', 'extension_dir = "ext"'
$content = $content -replace ';extension=curl', 'extension=curl'
$content = $content -replace ';extension=fileinfo', 'extension=fileinfo'
$content = $content -replace ';extension=mbstring', 'extension=mbstring'
$content = $content -replace ';extension=openssl', 'extension=openssl'
$content = $content -replace ';extension=pdo_sqlite', 'extension=pdo_sqlite'
$content = $content -replace ';extension=sqlite3', 'extension=sqlite3'
$content = $content -replace ';extension=zip', 'extension=zip'
$content = $content -replace ';extension=intl', 'extension=intl'
$content = $content -replace ';extension=gd', 'extension=gd'

Set-Content $phpIni $content

Write-Host "Setup Complete."
& "$phpDir\php.exe" -v
