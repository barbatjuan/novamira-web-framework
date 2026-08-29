#!/usr/bin/env bash
# WordPress Orchestrator framework installer (macOS / Linux)
# Copies agents/ and skills/ into ~/.claude so Claude Code can load them.
set -euo pipefail
SRC="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEST="$HOME/.claude"

mkdir -p "$DEST/agents" "$DEST/skills"
cp -R "$SRC/agents/." "$DEST/agents/"
cp -R "$SRC/skills/." "$DEST/skills/"

# Report exactly what is on disk, so this message can't drift from the repo.
AGENTS=""; AGENT_COUNT=0
for f in "$SRC"/agents/*.md; do
  [ -f "$f" ] || continue
  name="$(basename "$f" .md)"
  AGENTS="${AGENTS:+$AGENTS, }$name"
  AGENT_COUNT=$((AGENT_COUNT + 1))
done

SKILLS=""; SKILL_COUNT=0
for d in "$SRC"/skills/*/; do
  [ -d "$d" ] || continue
  name="$(basename "$d")"
  SKILLS="${SKILLS:+$SKILLS, }$name"
  SKILL_COUNT=$((SKILL_COUNT + 1))
done

echo "WordPress Orchestrator framework installed into $DEST"
echo "Agents ($AGENT_COUNT): $AGENTS"
echo "Skills ($SKILL_COUNT): $SKILLS"
echo
echo "NOTE: this is an overwrite-in-place install. Files with the same name are replaced,"
echo "but files deleted upstream are NOT removed from $DEST — a skill or reference dropped"
echo "from the repo keeps living there until you delete it by hand."
