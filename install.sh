#!/usr/bin/env bash
# NovaMira Web Framework installer (macOS / Linux)
# Copies agents/ and skills/ into ~/.claude so Claude Code can load them.
set -euo pipefail
SRC="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEST="$HOME/.claude"

mkdir -p "$DEST/agents" "$DEST/skills"
cp -R "$SRC/agents/." "$DEST/agents/"
cp -R "$SRC/skills/." "$DEST/skills/"

echo "NovaMira Web Framework installed into $DEST"
echo "Agent: novamira-web-orchestrator"
echo "Skills: project-context, ux-design-system, elementor-core, divi-core, woocommerce, wordpress-performance, wordpress-seo, qa-review"
