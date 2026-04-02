$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

$repoRoot = Split-Path -Parent $PSScriptRoot
Set-Location $repoRoot

$timestamp = Get-Date -Format 'yyyy-MM-dd_HHmmss'
$outDir = Join-Path $repoRoot 'Automated-Test'
if (!(Test-Path $outDir)) {
    New-Item -ItemType Directory -Path $outDir | Out-Null
}

$phpExe = 'C:\xampp\php\php.exe'
if (!(Test-Path $phpExe)) {
    throw "PHP executable not found at $phpExe"
}

function Get-RelativePath([string]$fullPath) {
    $root = (Resolve-Path $repoRoot).Path
    $normalized = (Resolve-Path $fullPath).Path
    if ($normalized.StartsWith($root, [System.StringComparison]::OrdinalIgnoreCase)) {
        return $normalized.Substring($root.Length).TrimStart([char[]]@('\', '/')) -replace '\\', '/'
    }
    return $fullPath -replace '\\', '/'
}

function Invoke-AuditUrl {
    param(
        [string]$Category,
        [string]$Target,
        [string]$Url
    )

    $sw = [System.Diagnostics.Stopwatch]::StartNew()
    $status = -1
    $body = ''
    $location = ''

    try {
        $resp = Invoke-WebRequest -Uri $Url -Method GET -MaximumRedirection 0 -TimeoutSec 20 -UseBasicParsing -ErrorAction Stop
        $status = [int]$resp.StatusCode
        $body = [string]$resp.Content
        if ($resp.Headers['Location']) {
            $location = [string]$resp.Headers['Location']
        }
    } catch {
        if ($_.Exception.Response) {
            $status = [int]$_.Exception.Response.StatusCode
            $location = [string]$_.Exception.Response.Headers['Location']
            $stream = $_.Exception.Response.GetResponseStream()
            if ($stream) {
                $reader = New-Object System.IO.StreamReader($stream)
                $body = $reader.ReadToEnd()
                $reader.Close()
            }
        } else {
            $body = $_.Exception.Message
        }
    }

    $sw.Stop()

    $fatal = $body -match 'Fatal error|Parse error|Uncaught Exception|Uncaught Error|SQLSTATE\[|Warning:\s'
    $json = $false
    try {
        $null = $body | ConvertFrom-Json
        $json = $true
    } catch {}

    [PSCustomObject]@{
        category = $Category
        target = $Target
        url = $Url
        status = $status
        ms = $sw.ElapsedMilliseconds
        fatal = $fatal
        is_json = $json
        redirect_location = $location
        body_sample = (($body -replace "`r|`n", ' ') -replace '\s+', ' ').Trim().Substring(0, [Math]::Min(180, (($body -replace "`r|`n", ' ') -replace '\s+', ' ').Trim().Length))
    }
}

# 1) PHP lint (functional baseline)
$phpLintRows = @()
$phpFiles = Get-ChildItem -Path $repoRoot -Recurse -File -Filter '*.php' |
    Where-Object { $_.FullName -notmatch '\\node_modules\\|\\storage\\|\\.git\\' }

foreach ($file in $phpFiles) {
    $out = & $phpExe -l $file.FullName 2>&1
    $ok = $LASTEXITCODE -eq 0
    $phpLintRows += [PSCustomObject]@{
        file = Get-RelativePath $file.FullName
        ok = $ok
        output = ($out -join ' ')
    }
}
$phpLintPath = Join-Path $outDir ("full_php_lint_{0}.csv" -f $timestamp)
$phpLintRows | Export-Csv -Path $phpLintPath -NoTypeInformation

# 2) HTTP functional matrix
$httpRows = @()
$base = 'http://localhost/wiet_lib'

$rootTargets = @('index.php', 'opac.php')
foreach ($p in $rootTargets) {
    $httpRows += Invoke-AuditUrl -Category 'root_page' -Target $p -Url "$base/$p"
}

$adminPages = Get-ChildItem -Path (Join-Path $repoRoot 'admin') -File -Filter '*.php' |
    Where-Object { $_.Name -notin @('ajax-handler.php', 'layout.php', 'layout2.php', 'auth_system.php', 'admin_auth_system.php', 'session_check.php') }
foreach ($p in $adminPages) {
    $httpRows += Invoke-AuditUrl -Category 'admin_page' -Target $p.Name -Url "$base/admin/$($p.Name)?ajax=1"
}

$studentPages = Get-ChildItem -Path (Join-Path $repoRoot 'student') -File -Filter '*.php' |
    Where-Object { $_.Name -notin @('layout.php', 'student_session_check.php') }
foreach ($p in $studentPages) {
    $httpRows += Invoke-AuditUrl -Category 'student_page' -Target $p.Name -Url "$base/student/$($p.Name)"
}

$adminApis = Get-ChildItem -Path (Join-Path $repoRoot 'admin/api') -File -Filter '*.php'
foreach ($p in $adminApis) {
    $httpRows += Invoke-AuditUrl -Category 'admin_api' -Target $p.Name -Url "$base/admin/api/$($p.Name)"
}

$mobileIndex = Join-Path $repoRoot 'student/api/mobile/index.php'
if (Test-Path $mobileIndex) {
    $httpRows += Invoke-AuditUrl -Category 'student_mobile_api' -Target 'index.php' -Url "$base/student/api/mobile/index.php"
}

$authApis = Get-ChildItem -Path (Join-Path $repoRoot 'student/api/mobile/auth') -File -Filter '*.php'
foreach ($p in $authApis) {
    $httpRows += Invoke-AuditUrl -Category 'student_mobile_api' -Target "auth/$($p.Name)" -Url "$base/student/api/mobile/auth/$($p.Name)"
}

$resourceApis = Get-ChildItem -Path (Join-Path $repoRoot 'student/api/mobile/resources') -File -Filter '*.php'
foreach ($p in $resourceApis) {
    $httpRows += Invoke-AuditUrl -Category 'student_mobile_api' -Target "resources/$($p.Name)" -Url "$base/student/api/mobile/resources/$($p.Name)"
}

$httpPath = Join-Path $outDir ("full_http_matrix_{0}.csv" -f $timestamp)
$httpRows | Export-Csv -Path $httpPath -NoTypeInformation

# 3) Security static pattern scan
$scanPatterns = @(
    @{ name = 'dangerous_function'; pattern = '(?i)(?<!->)\b(eval|exec|shell_exec|system|passthru|proc_open|popen)\s*\(' },
    @{ name = 'weak_hash'; pattern = '(?i)\b(md5|sha1)\s*\(' },
    @{ name = 'sql_concat_risk'; pattern = '(?i)(->query\s*\(.*(\$_(GET|POST|REQUEST|COOKIE)|\.\s*\$))|(mysqli_query\s*\(.*\.\s*\$)' },
    @{ name = 'debug_leak'; pattern = '(?i)\b(var_dump|print_r)\s*\(|display_errors' },
    @{ name = 'hardcoded_secret_candidate'; pattern = '(?i)(api[_-]?key\s*[:=]\s*\S+)|(secret\s*[:=]\s*\S+)' }
)

$securityRows = @()
$codeFiles = Get-ChildItem -Path $repoRoot -Recurse -File |
    Where-Object {
        $_.Extension -in @('.php', '.js', '.ts', '.tsx', '.json', '.env') -and
        $_.FullName -notmatch '\\node_modules\\|\\storage\\|\\.git\\'
    }

foreach ($rule in $scanPatterns) {
    $matches = Select-String -Path ($codeFiles.FullName) -Pattern $rule.pattern -AllMatches
    foreach ($m in $matches) {
        $securityRows += [PSCustomObject]@{
            rule = $rule.name
            file = Get-RelativePath $m.Path
            line = $m.LineNumber
            snippet = ($m.Line.Trim() -replace '\s+', ' ')
        }
    }
}

$securityPath = Join-Path $outDir ("full_security_patterns_{0}.csv" -f $timestamp)
$securityRows | Export-Csv -Path $securityPath -NoTypeInformation

# 4) CSRF heuristic for forms
$formRows = @()
$formFiles = Get-ChildItem -Path (Join-Path $repoRoot 'admin'), (Join-Path $repoRoot 'student') -Recurse -File -Filter '*.php'

foreach ($f in $formFiles) {
    $content = Get-Content -Path $f.FullName -Raw
    if ($content -match '(?i)<form\b') {
        $hasCsrf = $content -match '(?i)csrf|csrf_token|generateCSRFToken|validateCSRFToken'
        $formRows += [PSCustomObject]@{
            file = Get-RelativePath $f.FullName
            has_form = $true
            has_csrf_marker = $hasCsrf
        }
    }
}
$formPath = Join-Path $outDir ("full_form_csrf_heuristic_{0}.csv" -f $timestamp)
$formRows | Export-Csv -Path $formPath -NoTypeInformation

# 5) Mobile app checks (ts + npm audit)
$mobileDir = Join-Path $repoRoot 'student-mobile-app'
$tsPath = Join-Path $outDir ("full_mobile_tsc_{0}.txt" -f $timestamp)
$auditPath = Join-Path $outDir ("full_mobile_npm_audit_{0}.json" -f $timestamp)

if (Test-Path $mobileDir) {
    Push-Location $mobileDir
    try {
        $tsOut = npx tsc --noEmit 2>&1
        $tsExit = $LASTEXITCODE
        "ExitCode=$tsExit" | Set-Content -Path $tsPath
        ($tsOut -join "`r`n") | Add-Content -Path $tsPath

        try {
            $auditOut = npm audit --json 2>&1
            ($auditOut -join "`r`n") | Set-Content -Path $auditPath
            $auditExit = $LASTEXITCODE
        } catch {
            $auditExit = 1
            $_ | Out-String | Set-Content -Path $auditPath
        }
    } finally {
        Pop-Location
    }
} else {
    "student-mobile-app directory not found." | Set-Content -Path $tsPath
    "{}" | Set-Content -Path $auditPath
    $tsExit = -1
    $auditExit = -1
}

# 6) Emit machine-readable summary for report generation
$summary = [PSCustomObject]@{
    timestamp = $timestamp
    php_total = $phpLintRows.Count
    php_failed = ($phpLintRows | Where-Object { -not $_.ok }).Count
    http_total = $httpRows.Count
    http_fatal = ($httpRows | Where-Object { $_.fatal }).Count
    http_status_2xx = ($httpRows | Where-Object { $_.status -ge 200 -and $_.status -lt 300 }).Count
    http_status_3xx = ($httpRows | Where-Object { $_.status -ge 300 -and $_.status -lt 400 }).Count
    http_status_4xx = ($httpRows | Where-Object { $_.status -ge 400 -and $_.status -lt 500 }).Count
    http_status_5xx = ($httpRows | Where-Object { $_.status -ge 500 -and $_.status -lt 600 }).Count
    security_hits = @($securityRows).Count
    security_dangerous = @($securityRows | Where-Object { $_.rule -eq 'dangerous_function' }).Count
    security_weak_hash = @($securityRows | Where-Object { $_.rule -eq 'weak_hash' }).Count
    security_sql_concat = @($securityRows | Where-Object { $_.rule -eq 'sql_concat_risk' }).Count
    security_debug = @($securityRows | Where-Object { $_.rule -eq 'debug_leak' }).Count
    security_secret_candidates = @($securityRows | Where-Object { $_.rule -eq 'hardcoded_secret_candidate' }).Count
    forms_total = $formRows.Count
    forms_without_csrf_marker = ($formRows | Where-Object { -not $_.has_csrf_marker }).Count
    mobile_tsc_exit = $tsExit
    mobile_npm_audit_exit = $auditExit
    artifacts = @{
        php_lint_csv = (Split-Path -Leaf $phpLintPath)
        http_matrix_csv = (Split-Path -Leaf $httpPath)
        security_csv = (Split-Path -Leaf $securityPath)
        csrf_csv = (Split-Path -Leaf $formPath)
        mobile_tsc_txt = (Split-Path -Leaf $tsPath)
        mobile_npm_audit_json = (Split-Path -Leaf $auditPath)
    }
}

$summaryPath = Join-Path $outDir ("full_audit_summary_{0}.json" -f $timestamp)
$summary | ConvertTo-Json -Depth 6 | Set-Content -Path $summaryPath

Write-Output "FULL_AUDIT_DONE"
Write-Output "TIMESTAMP=$timestamp"
Write-Output "SUMMARY_PATH=$summaryPath"
