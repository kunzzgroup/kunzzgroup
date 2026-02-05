param (
    [string]$SourceFile,
    [int]$StartLine,
    [int]$EndLine,
    [string]$TargetFile
)

if (-not (Test-Path $SourceFile)) {
    Write-Error "Source file not found: $SourceFile"
    exit 1
}

$content = Get-Content $SourceFile
# PowerShell array slicing is inclusive.
# We want lines from StartLine to EndLine (1-based).
# Index = Line - 1.
$startIndex = $StartLine - 1
$endIndex = $EndLine - 1

if ($startIndex -lt 0 -or $endIndex -ge $content.Count) {
    Write-Error "Line numbers out of range. File has $($content.Count) lines."
    exit 1
}

$extracted = $content[$startIndex..$endIndex]

$dir = Split-Path $TargetFile -Parent
if (-not (Test-Path $dir)) {
    New-Item -ItemType Directory -Force -Path $dir | Out-Null
}

$extracted | Set-Content $TargetFile -Encoding UTF8
Write-Host "Extracted lines $StartLine to $EndLine to $TargetFile"
