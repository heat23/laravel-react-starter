#!/bin/bash
set -e

echo "🧪 Test Quality Audit"
echo "===================="

# 1. Count tests
TOTAL_TESTS=$(php artisan test --list-tests | wc -l)
echo "📊 Total tests: $TOTAL_TESTS"

# 2. Check for tests without assertions
echo ""
echo "⚠️  Tests with potential issues:"
grep -rn "test_\|it(" tests/ --include="*.php" | while read -r line; do
    FILE=$(echo "$line" | cut -d: -f1)
    LINE_NUM=$(echo "$line" | cut -d: -f2)

    # Check if test has any assertion in next 20 lines
    ASSERTIONS=$(sed -n "${LINE_NUM},$((LINE_NUM + 20))p" "$FILE" | grep -c "assert\|expect" || true)

    if [ "$ASSERTIONS" -eq 0 ]; then
        echo "  ❌ $FILE:$LINE_NUM - No assertions found"
    fi
done

# 3. Check for skipped tests
SKIPPED=$(php artisan test --list-tests | grep -c "skipped" || echo "0")
if [ "$SKIPPED" -gt 0 ]; then
    echo ""
    echo "⏭️  Skipped tests: $SKIPPED"
    echo "  Consider re-enabling or documenting why they're skipped"
fi

# 4. Check for tests marked as risky
echo ""
echo "🔍 Running tests to detect risky tests..."
php artisan test --parallel | tee /tmp/test-output.txt
RISKY=$(grep -c "risky" /tmp/test-output.txt || echo "0")
if [ "$RISKY" -gt 0 ]; then
    echo "⚠️  Risky tests found: $RISKY"
    echo "  Risky tests don't have assertions or interact with global state"
fi

# 5. Coverage check
echo ""
echo "📈 Checking test coverage..."
php artisan test --coverage --min=80 2>&1 || {
    echo "❌ Coverage below 80%"
    echo "  Add tests for uncovered code paths"
    exit 1
}

echo ""
echo "Mutation Testing (advisory)"
echo "=============================="
if command -v vendor/bin/infection &> /dev/null; then
    vendor/bin/infection --threads=4 --only-covered --show-mutations 2>&1 | tail -20 || true
    # Note: minMsi floor will be tightened once baseline is established
else
    echo "Infection not available — skipping mutation tests"
fi

echo ""
echo "✅ Test quality check complete!"
