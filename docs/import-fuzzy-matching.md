# Import Fuzzy Matching (AR/EN)

Branch: `feat/import-fuzzy-matching-bilingual`

## Overview
Adds bilingual (Arabic/English) fuzzy matching to Import Preflight for opponents. Provides Unicode‑safe normalization and scoring, cPanel‑safe candidate pruning, explainable UI, alias/merge workflow, reconciliation CSV, and backfill.

## Environment Readiness
- PHP extensions:
  - intl (Normalizer): NFC normalization. If missing, skip with warning.
  - iconv: ASCII folding for Latin. If missing, skip with warning.
  - metaphone: phonetic scoring for Latin. If unavailable, phonetics disabled and weights auto‑rebalance.
- MySQL: InnoDB, utf8mb4, `utf8mb4_unicode_ci`.

## Schema
- opponents: `normalized_name(512)`, `first_token(64)`, `last_token(64)`, `token_count TINYINT`, `latin_key(64)`
  - Indexes: `normalized_name(191)`, `first_token`, `last_token`, `token_count`, `latin_key`.
- opponent_aliases: `(opponent_id, alias_normalized(512))` unique per opponent, index `(alias_normalized(191))`.
- opponent_trigrams (optional): `(opponent_id, tri CHAR(3))`, PK(opponent_id, tri), INDEX(tri).

## Normalization (NameNormalizer)
- NFC (intl), standardize digits, strip punctuation, collapse whitespace.
- Arabic:
  - Remove diacritics U+064B–U+0652, remove tatweel U+0640
  - Normalize Alif/Hamza: أ/إ/آ/ٱ → ا; Yeh/Maqsura: ى → ي
  - Lock: keep Ta Marbuta ة as‑is (no ة↔ه)
- English:
  - lowercase, collapse initials (A. → A), ASCII fold via iconv
- Remove stopwords (AR/EN), tokenize, drop len≤1, compute first/last/token_count; latin_key via metaphone (feature‑flag)

## Similarity (FuzzyMatcher)
- UTF‑8 codepoint Levenshtein
- Token Dice (set overlap)
- Jaro–Winkler
- Phonetic (Latin only) via metaphone when enabled
- Weights (configurable):
  - Arabic/Mixed: 0.55·lev + 0.35·dice + 0.10·jaro
  - Latin: 0.40·lev + 0.30·dice + 0.20·jaro + 0.10·phonetic
- Bands: strong ≥ 0.92; likely 0.85–<0.92
- If phonetics disabled, weights auto‑rebalance

## Candidate Retrieval (OpponentSuggestionService)
- Normalize incoming string
- Prune by:
  - `normalized_name LIKE :prefix%` (cap 200)
  - `normalized_name LIKE %:mid%` (cap 200)
  - `first_token` OR `last_token`
  - `latin_key =` for Latin
  - Optional trigrams shortlist (cap 300)
- Score in PHP, return top 10 with breakdown and token "why"
- Timing logs (debug flag)

## Preflight UX
- Columns: Incoming | Script | Normalized | Top Suggestion (score) | Decision
- dir="auto" on names; RTL‑ready
- Actions: Use suggestion / Keep as new / More… (Top 10 modal)
- Tooltip: lev/dice/jaro[/phon] + “why” (token matches)
- Bulk: Accept all Strong, Reject all Low
- Decisions posted with the import form; no DB writes in preflight

## Commit Phase
- Apply decisions:
  - match → set `opponent_id`; optional alias insert (guard same alias across opponents)
  - new → create opponent (+normalized fields); optional alias
- Reconciliation CSV: `incoming_name, normalized_in, matched_to_id, matched_name, score, decision, import_batch_id, source_file, row_no`
- DB backup runs before commit (existing pipeline)

## Backfill
- `php artisan opponents:backfill-normalization` to compute normalized fields and maintain trigrams

## Tests
- NameNormalizer: Arabic rules (no ة↔ه), tokenization, stopwords, Latin key
- FuzzyMatcher pairs:
  - سامي أحمد أحمد بسيوني ↔ سامي أحمد أحمد → Likely/Strong
  - سبيد ميديكال ↔ سبيد ميديكا → Likely
  - Mohamed A. Ali ↔ Mohammad Ali → Likely/Strong
  - Speed Medical ↔ Speed Med. → Likely
- Performance stub recommendation: service‑level cap enforcement with timing logs

## Configuration
- `config/fuzzy.php`:
  - thresholds, weights, stopwords, feature toggles (phonetics/trigrams), limits, debug
  - env fallbacks logging

## Troubleshooting
- Low scores: inspect tooltip breakdown and tokens; tweak thresholds/weights in config
- Arabic drift: verify diacritics removal and alif/yeh normalization; ensure ة lock not violated
- Slow preflight: enable trigrams, lower caps, enable timing logs; ensure indexes present

## Rollout
1. Migrate
2. Backfill normalization
3. Test preflight on a small file
4. Review reconciliation CSV
5. Enable trigrams if dataset is large
