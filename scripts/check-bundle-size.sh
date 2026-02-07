#!/usr/bin/env bash
set -euo pipefail

BUILD_DIR="public/build/assets"
BASELINE_FILE=".bundle-baseline.json"
THRESHOLD=10 # percent

if [ ! -d "$BUILD_DIR" ]; then
  echo "ERROR: Build directory not found at $BUILD_DIR"
  echo "Run 'npm run build' first."
  exit 1
fi

# Calculate total JS bundle size in bytes
current_size=0
while IFS= read -r file; do
  size=$(wc -c < "$file")
  current_size=$((current_size + size))
done < <(find "$BUILD_DIR" -name '*.js' -type f)

current_kb=$(( (current_size + 512) / 1024 ))

echo "Current JS bundle size: ${current_kb}KB (${current_size} bytes)"

if [ ! -f "$BASELINE_FILE" ]; then
  echo "{\"js_bytes\": ${current_size}}" > "$BASELINE_FILE"
  echo "Baseline created at $BASELINE_FILE"
  exit 0
fi

baseline_size=$(grep -o '"js_bytes": *[0-9]*' "$BASELINE_FILE" | grep -o '[0-9]*$')
baseline_kb=$(( (baseline_size + 512) / 1024 ))

if [ "$baseline_size" -eq 0 ]; then
  echo "WARNING: Baseline is 0 bytes, resetting."
  echo "{\"js_bytes\": ${current_size}}" > "$BASELINE_FILE"
  exit 0
fi

diff=$((current_size - baseline_size))
pct=$(( (diff * 100) / baseline_size ))
abs_pct=${pct#-}

echo "Baseline JS bundle size: ${baseline_kb}KB (${baseline_size} bytes)"
echo "Difference: ${pct}%"

if [ "$pct" -gt "$THRESHOLD" ]; then
  echo "WARNING: Bundle size increased by ${pct}% (threshold: ${THRESHOLD}%)"
  echo "Consider running 'npm run analyze' to investigate."
  exit 1
elif [ "$pct" -lt "-$THRESHOLD" ]; then
  echo "Bundle size decreased by ${abs_pct}%. Updating baseline."
  echo "{\"js_bytes\": ${current_size}}" > "$BASELINE_FILE"
fi

echo "Bundle size check passed."
