# NovaMira Web Framework installer (Windows / PowerShell)
# Copies agents/ and skills/ into the user's ~/.claude so Claude Code can load them.
$ErrorActionPreference = 'Stop'
$src = $PSScriptRoot
$dest = Join-Path $HOME '.claude'

New-Item -ItemType Directory -Force -Path (Join-Path $dest 'agents') | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $dest 'skills') | Out-Null

Copy-Item -Path (Join-Path $src 'agents\*')  -Destination (Join-Path $dest 'agents')  -Recurse -Force
Copy-Item -Path (Join-Path $src 'skills\*')  -Destination (Join-Path $dest 'skills')  -Recurse -Force

Write-Host "NovaMira Web Framework installed into $dest" -ForegroundColor Green
Write-Host "Agent: novamira-web-orchestrator   Skills: project-context, ux-design-system, elementor-core, divi-core, woocommerce, wordpress-performance, wordpress-seo, qa-review"
