# WordPress Orchestrator framework installer (Windows / PowerShell)
# Copies agents/ and skills/ into the user's ~/.claude so Claude Code can load them.
$ErrorActionPreference = 'Stop'
$src = $PSScriptRoot
$dest = Join-Path $HOME '.claude'

New-Item -ItemType Directory -Force -Path (Join-Path $dest 'agents') | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $dest 'skills') | Out-Null

Copy-Item -Path (Join-Path $src 'agents\*')  -Destination (Join-Path $dest 'agents')  -Recurse -Force
Copy-Item -Path (Join-Path $src 'skills\*')  -Destination (Join-Path $dest 'skills')  -Recurse -Force

# Report exactly what is on disk, so this message can't drift from the repo.
$agents = Get-ChildItem -Path (Join-Path $src 'agents') -Filter '*.md' -File |
          Sort-Object Name | ForEach-Object { $_.BaseName }
$skills = Get-ChildItem -Path (Join-Path $src 'skills') -Directory |
          Sort-Object Name | ForEach-Object { $_.Name }

Write-Host "WordPress Orchestrator framework installed into $dest" -ForegroundColor Green
Write-Host ("Agents ({0}): {1}" -f $agents.Count, ($agents -join ', '))
Write-Host ("Skills ({0}): {1}" -f $skills.Count, ($skills -join ', '))
Write-Host ""
Write-Host "NOTE: this is an overwrite-in-place install. Files with the same name are replaced," -ForegroundColor Yellow
# ASCII only, deliberately. Windows PowerShell 5.1 reads a BOM-less .ps1 as the system ANSI
# codepage, so a UTF-8 em-dash arrives as three bytes whose last one PowerShell treats as a closing
# quote -- the string terminates early and the whole script fails to parse. Measured: this file
# refused to run under 5.1 while working under 7. Keep every character in this file ASCII.
Write-Host "but files deleted upstream are NOT removed from $dest -- a skill or reference dropped" -ForegroundColor Yellow
Write-Host "from the repo keeps living there until you delete it by hand." -ForegroundColor Yellow
