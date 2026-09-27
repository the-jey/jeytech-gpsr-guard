#!/usr/bin/env bash
# Lance le scénario dans un site Playground neuf, affiche le rapport, échoue si une vérification échoue.
# Usage : bin/run-test.sh hpos|posts
set -uo pipefail
cd "$(dirname "$0")/.."

store="${1:-hpos}"
case "$store" in
	hpos)  blueprint="dev/test-hpos.json" ;;
	posts) blueprint="dev/test-legacy.json" ;;
	*) echo "Usage : $0 hpos|posts" >&2; exit 2 ;;
esac
report="dev/.test-output-${store}.txt"
rm -f dev/.test-output-*.txt

npx wp-playground-cli run-blueprint \
	--blueprint="$blueprint" \
	--mount=.:/wordpress/wp-content/plugins/jeytech-safety-data-by-brand
code=$?

if [[ ! -f "$report" ]]; then
	echo "✗ Aucun rapport « $report » (sortie Playground : $code). Rapports présents :" >&2
	ls dev/.test-output-*.txt 2>/dev/null >&2 || echo "  (aucun)" >&2
	exit 1
fi
cat "$report"
if grep -q "^FAIL" "$report"; then
	exit 1
fi
exit 0
