$ProgressPreference = 'SilentlyContinue'

function Invoke-Endpoint {
    param(
        [string]$Url,
        [int]$Expected
    )

    $sw = [System.Diagnostics.Stopwatch]::StartNew()
    try {
        $resp = Invoke-WebRequest -Uri $Url -Method GET -MaximumRedirection 0 -TimeoutSec 20 -UseBasicParsing
        $status = [int]$resp.StatusCode
        $body = [string]$resp.Content
    } catch {
        if ($_.Exception.Response) {
            $status = [int]$_.Exception.Response.StatusCode
            $reader = New-Object IO.StreamReader($_.Exception.Response.GetResponseStream())
            $body = $reader.ReadToEnd()
            $reader.Close()
        } else {
            $status = -1
            $body = $_.Exception.Message
        }
    }
    $sw.Stop()

    $fatal = $body -match 'Fatal error|Parse error|Uncaught Exception|SQLSTATE'
    $isJson = $false
    try {
        $null = $body | ConvertFrom-Json
        $isJson = $true
    } catch {}

    [PSCustomObject]@{
        status = $status
        ms = $sw.ElapsedMilliseconds
        fatal = $fatal
        json = $isJson
        ok = ($status -eq $Expected -and -not $fatal)
    }
}

# Page scale tests based on previously discovered baseline status.
$pages = Import-Csv admin_page_smoke.csv
$pageIterations = 5
$pageAll = @()
foreach ($row in $pages) {
    $expected = [int]$row.status
    for ($i = 1; $i -le $pageIterations; $i++) {
        $url = "http://localhost/wiet_lib/admin/$($row.page)?ajax=1"
        $r = Invoke-Endpoint -Url $url -Expected $expected
        $pageAll += [PSCustomObject]@{
            component = $row.page
            expected = $expected
            status = $r.status
            ms = $r.ms
            fatal = $r.fatal
            ok = $r.ok
        }
    }
}
$pageAll | Export-Csv -Path admin_scale_pages.csv -NoTypeInformation

# API scale tests based on previously discovered baseline status.
$apis = Import-Csv admin_api_smoke.csv
$apiIterations = 10
$apiAll = @()
foreach ($row in $apis) {
    $expected = [int]$row.status
    for ($i = 1; $i -le $apiIterations; $i++) {
        $url = "http://localhost/wiet_lib/admin/api/$($row.api)"
        $r = Invoke-Endpoint -Url $url -Expected $expected
        $apiAll += [PSCustomObject]@{
            component = $row.api
            expected = $expected
            status = $r.status
            ms = $r.ms
            fatal = $r.fatal
            json = $r.json
            ok = $r.ok
        }
    }
}
$apiAll | Export-Csv -Path admin_scale_apis.csv -NoTypeInformation

# Summaries
$pageSummary = [PSCustomObject]@{
    pages_requests = $pageAll.Count
    pages_ok = ($pageAll | Where-Object { $_.ok }).Count
    pages_fatal = ($pageAll | Where-Object { $_.fatal }).Count
    pages_status_drift = ($pageAll | Where-Object { $_.status -ne $_.expected }).Count
    pages_avg_ms = [Math]::Round((($pageAll | Measure-Object ms -Average).Average), 2)
}

$apiSummary = [PSCustomObject]@{
    api_requests = $apiAll.Count
    api_ok = ($apiAll | Where-Object { $_.ok }).Count
    api_fatal = ($apiAll | Where-Object { $_.fatal }).Count
    api_status_drift = ($apiAll | Where-Object { $_.status -ne $_.expected }).Count
    api_json = ($apiAll | Where-Object { $_.json }).Count
    api_avg_ms = [Math]::Round((($apiAll | Measure-Object ms -Average).Average), 2)
}

"SCALE_PAGE_SUMMARY"
$pageSummary | Format-List | Out-String
"SCALE_API_SUMMARY"
$apiSummary | Format-List | Out-String

"SCALE_PAGE_COMPONENTS_LOWEST_OK"
$pageAll |
    Group-Object component |
    ForEach-Object {
        $g = $_.Group
        [PSCustomObject]@{
            component = $_.Name
            req = $g.Count
            ok = ($g | Where-Object { $_.ok }).Count
            avg_ms = [Math]::Round((($g | Measure-Object ms -Average).Average), 2)
        }
    } |
    Sort-Object ok, component |
    Select-Object -First 20 |
    Format-Table -AutoSize | Out-String

"SCALE_API_COMPONENTS_LOWEST_OK"
$apiAll |
    Group-Object component |
    ForEach-Object {
        $g = $_.Group
        [PSCustomObject]@{
            component = $_.Name
            req = $g.Count
            ok = ($g | Where-Object { $_.ok }).Count
            avg_ms = [Math]::Round((($g | Measure-Object ms -Average).Average), 2)
            json = ($g | Where-Object { $_.json }).Count
        }
    } |
    Sort-Object ok, component |
    Select-Object -First 20 |
    Format-Table -AutoSize | Out-String
