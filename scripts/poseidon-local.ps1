param(
    [switch]$Start,
    [switch]$Stop,
    [switch]$Restart,
    [switch]$Status,
    [switch]$NoBrowser
)

$ErrorActionPreference = 'Stop'

$Root = Split-Path -Parent $PSScriptRoot
$RuntimeDir = Join-Path $Root '.runtime'
$StatePath = Join-Path $RuntimeDir 'poseidon-local-state.json'
$LauncherLog = Join-Path $RuntimeDir 'poseidon-local-launcher.log'
$ServerPort = 8001
$VitePort = 5173
$Url = "http://127.0.0.1:$ServerPort"
$Title = 'Poseidon Local'

New-Item -ItemType Directory -Force -Path $RuntimeDir | Out-Null

function Write-LauncherLog {
    param([string]$Message)
    $stamp = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
    Add-Content -Path $LauncherLog -Value "$stamp $Message"
}

function Show-Notice {
    param(
        [string]$Message,
        [int]$Seconds = 3,
        [int]$Icon = 64
    )

    try {
        $shell = New-Object -ComObject WScript.Shell
        $null = $shell.Popup($Message, $Seconds, $Title, $Icon)
    } catch {
        Write-Host "${Title}: $Message"
    }
}

function Get-LocalPhp {
    $php = Get-ChildItem -Path $RuntimeDir -Recurse -Filter php.exe -ErrorAction SilentlyContinue |
        Where-Object { $_.FullName -like '*php-*' } |
        Sort-Object FullName -Descending |
        Select-Object -First 1

    if (-not $php) {
        throw "Could not find bundled PHP under $RuntimeDir."
    }

    return $php.FullName
}

function Get-NpmCommand {
    $npm = Get-Command npm.cmd -ErrorAction SilentlyContinue
    if ($npm) {
        return $npm.Source
    }

    $npm = Get-Command npm -ErrorAction SilentlyContinue
    if ($npm) {
        return $npm.Source
    }

    throw 'Could not find npm on PATH.'
}

function Get-ListeningPids {
    param([int]$Port)

    @(Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue |
        Select-Object -ExpandProperty OwningProcess -Unique)
}

function Test-PidAlive {
    param([int]$ProcessId)

    try {
        $null = Get-Process -Id $ProcessId -ErrorAction Stop
        return $true
    } catch {
        return $false
    }
}

function Read-State {
    if (-not (Test-Path $StatePath)) {
        return $null
    }

    try {
        return Get-Content -Raw -Path $StatePath | ConvertFrom-Json
    } catch {
        Write-LauncherLog "Ignoring unreadable state file: $($_.Exception.Message)"
        return $null
    }
}

function Get-TrackedPids {
    $state = Read-State
    if (-not $state -or -not $state.Processes) {
        return @()
    }

    @($state.Processes | ForEach-Object { [int]$_.Id })
}

function Stop-ProcessTree {
    param([int]$ProcessId)

    if ($ProcessId -le 0) {
        return
    }

    $children = @(Get-CimInstance Win32_Process -Filter "ParentProcessId = $ProcessId" -ErrorAction SilentlyContinue)
    foreach ($child in $children) {
        Stop-ProcessTree -ProcessId ([int]$child.ProcessId)
    }

    try {
        Stop-Process -Id $ProcessId -Force -ErrorAction Stop
        Write-LauncherLog "Stopped process $ProcessId"
    } catch {
        Write-LauncherLog "Process $ProcessId was already stopped or could not be stopped"
    }
}

function Stop-AppPortListeners {
    foreach ($port in @($ServerPort, $VitePort)) {
        foreach ($listenerPid in Get-ListeningPids -Port $port) {
            try {
                $process = Get-CimInstance Win32_Process -Filter "ProcessId = $listenerPid" -ErrorAction Stop
                $line = ($process.CommandLine + ' ' + $process.ExecutablePath)
                $looksLikeLocalDev = $line -match 'artisan|vite|node|php|npm|powershell|cmd'
                if ($looksLikeLocalDev) {
                    Write-LauncherLog "Stopping listener on port $port pid $listenerPid"
                    Stop-ProcessTree -ProcessId ([int]$listenerPid)
                } else {
                    Write-LauncherLog "Leaving non-dev listener on port $port pid $listenerPid"
                }
            } catch {
                Write-LauncherLog "Could not inspect listener on port $port pid $listenerPid"
            }
        }
    }
}

function Test-HttpOk {
    param([string]$TargetUrl)

    try {
        $response = Invoke-WebRequest -Uri $TargetUrl -UseBasicParsing -TimeoutSec 2 -ErrorAction Stop
        return ($response.StatusCode -lt 500)
    } catch {
        $response = $_.Exception.Response
        if ($response -and [int]$response.StatusCode -lt 500) {
            return $true
        }
        return $false
    }
}

function Get-EnvironmentStatus {
    $trackedAlive = @(Get-TrackedPids | Where-Object { Test-PidAlive -ProcessId $_ })
    $serverPids = @(Get-ListeningPids -Port $ServerPort)
    $vitePids = @(Get-ListeningPids -Port $VitePort)

    [pscustomobject]@{
        TrackedAlive = $trackedAlive
        ServerPids = $serverPids
        VitePids = $vitePids
        ServerHealthy = Test-HttpOk -TargetUrl $Url
        ViteListening = ($vitePids.Count -gt 0)
    }
}

function Start-LoggedProcess {
    param(
        [string]$Name,
        [string]$FilePath,
        [string[]]$ArgumentList
    )

    $outPath = Join-Path $RuntimeDir "$Name-local.out.log"
    $errPath = Join-Path $RuntimeDir "$Name-local.err.log"
    Set-Content -Path $outPath -Value ''
    Set-Content -Path $errPath -Value ''

    $process = Start-Process `
        -FilePath $FilePath `
        -ArgumentList $ArgumentList `
        -WorkingDirectory $Root `
        -WindowStyle Hidden `
        -RedirectStandardOutput $outPath `
        -RedirectStandardError $errPath `
        -PassThru

    Write-LauncherLog "Started $Name pid $($process.Id): $FilePath $($ArgumentList -join ' ')"
    return $process
}

function Start-ViteProcess {
    $npm = Get-NpmCommand
    $outPath = Join-Path $RuntimeDir 'vite-local.out.log'
    $errPath = Join-Path $RuntimeDir 'vite-local.err.log'
    Set-Content -Path $outPath -Value ''
    Set-Content -Path $errPath -Value ''

    $command = "& '$npm' run dev -- --host 127.0.0.1 --port $VitePort --strictPort"
    $process = Start-Process `
        -FilePath 'powershell.exe' `
        -ArgumentList @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-Command', $command) `
        -WorkingDirectory $Root `
        -WindowStyle Hidden `
        -RedirectStandardOutput $outPath `
        -RedirectStandardError $errPath `
        -PassThru

    Write-LauncherLog "Started vite pid $($process.Id)"
    return $process
}

function Wait-ForPort {
    param(
        [int]$Port,
        [int]$Seconds
    )

    $deadline = (Get-Date).AddSeconds($Seconds)
    while ((Get-Date) -lt $deadline) {
        if (@(Get-ListeningPids -Port $Port).Count -gt 0) {
            return $true
        }
        Start-Sleep -Milliseconds 500
    }

    return $false
}

function Save-State {
    param([object[]]$Processes)

    $state = [pscustomobject]@{
        StartedAt = (Get-Date).ToString('o')
        Root = $Root
        ServerPort = $ServerPort
        VitePort = $VitePort
        Processes = @($Processes | ForEach-Object {
            [pscustomobject]@{
                Name = $_.Name
                Id = $_.Process.Id
            }
        })
    }

    $state | ConvertTo-Json -Depth 4 | Set-Content -Path $StatePath
}

function Stop-Environment {
    Write-LauncherLog 'Stopping environment'

    foreach ($trackedPid in Get-TrackedPids) {
        Stop-ProcessTree -ProcessId $trackedPid
    }

    Stop-AppPortListeners

    if (Test-Path $StatePath) {
        Remove-Item -Path $StatePath -Force
    }

    $hotPath = Join-Path $Root 'public\hot'
    if (Test-Path $hotPath) {
        Remove-Item -Path $hotPath -Force
        Write-LauncherLog 'Removed stale public\hot file'
    }
}

function Start-Environment {
    Write-LauncherLog 'Starting environment'
    Stop-AppPortListeners

    $php = Get-LocalPhp
    $processes = @()

    $processes += [pscustomobject]@{
        Name = 'server'
        Process = Start-LoggedProcess -Name 'server' -FilePath $php -ArgumentList @('artisan', 'serve', '--host=127.0.0.1', "--port=$ServerPort")
    }
    $processes += [pscustomobject]@{
        Name = 'queue'
        Process = Start-LoggedProcess -Name 'queue' -FilePath $php -ArgumentList @('artisan', 'queue:listen', '--tries=1', '--timeout=0')
    }
    $processes += [pscustomobject]@{
        Name = 'pail'
        Process = Start-LoggedProcess -Name 'pail' -FilePath $php -ArgumentList @('artisan', 'pail', '--timeout=0')
    }
    $processes += [pscustomobject]@{
        Name = 'vite'
        Process = Start-ViteProcess
    }

    Save-State -Processes $processes
    Show-Notice -Message 'Starting Poseidon. The browser will open when it is ready.'

    $serverReady = Wait-ForPort -Port $ServerPort -Seconds 45
    $viteReady = Wait-ForPort -Port $VitePort -Seconds 45

    if ($serverReady -and $viteReady) {
        Write-LauncherLog 'Environment ready'
        if (-not $NoBrowser) {
            Start-Process $Url
        }
        return
    }

    Write-LauncherLog "Start timed out. serverReady=$serverReady viteReady=$viteReady"
    Show-Notice -Message "Poseidon did not fully start. Check the logs in $RuntimeDir." -Seconds 6 -Icon 48
    Start-Process $RuntimeDir
}

function Show-Status {
    $current = Get-EnvironmentStatus
    $tracked = if ($current.TrackedAlive.Count) { $current.TrackedAlive -join ', ' } else { 'none' }
    $server = if ($current.ServerPids.Count) { $current.ServerPids -join ', ' } else { 'none' }
    $vite = if ($current.VitePids.Count) { $current.VitePids -join ', ' } else { 'none' }

    Write-Host "Tracked processes: $tracked"
    Write-Host "Server port ${ServerPort}: $server"
    Write-Host "Vite port ${VitePort}: $vite"
    Write-Host "Server healthy: $($current.ServerHealthy)"
}

try {
    if ($Status) {
        Show-Status
        exit 0
    }

    if ($Restart) {
        Stop-Environment
        Start-Environment
        exit 0
    }

    if ($Stop) {
        Stop-Environment
        Show-Notice -Message 'Poseidon stopped.'
        exit 0
    }

    if ($Start) {
        Start-Environment
        exit 0
    }

    $current = Get-EnvironmentStatus
    $fullyRunning = (
        $current.TrackedAlive.Count -gt 0 -and
        $current.ServerHealthy -and
        $current.ViteListening
    )

    if ($fullyRunning) {
        Stop-Environment
        Show-Notice -Message 'Poseidon stopped.'
    } else {
        if ($current.TrackedAlive.Count -gt 0) {
            Stop-Environment
            Show-Notice -Message 'Cleaned up the partial Poseidon environment. Click again to start it.'
        } else {
            Start-Environment
        }
    }
} catch {
    Write-LauncherLog "ERROR: $($_.Exception.Message)"
    Show-Notice -Message "Poseidon launcher error: $($_.Exception.Message)" -Seconds 8 -Icon 16
    throw
}
