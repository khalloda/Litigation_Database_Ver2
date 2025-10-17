<?php

namespace App\Services;

use App\Models\Opponent;
use App\Support\NameNormalizer;
use App\Support\FuzzyMatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OpponentSuggestionService
{
    public function __construct(
        protected NameNormalizer $normalizer,
        protected FuzzyMatcher $matcher,
    ) {}

    public function suggest(string $incoming): array
    {
        $norm = $this->normalizer->normalize($incoming);
        $n = $norm['normalized'];
        $first = $norm['first_token'];
        $last = $norm['last_token'];
        $latinKey = $norm['latin_key'];

        $capPrefix = (int) config('fuzzy.limits.prefix_like_cap', 200);
        $capMid    = (int) config('fuzzy.limits.mid_like_cap', 200);
        $capTri    = (int) config('fuzzy.limits.trigram_cap', 300);

        $candidates = collect();

        $startTs = microtime(true);

        // 1) normalized_name prefix and mid
        if ($n !== '') {
            $midStart = max(0, (int) floor(mb_strlen($n, 'UTF-8') / 3));
            $mid = mb_substr($n, $midStart, 3, 'UTF-8');

            $q1 = Opponent::query()
                ->select('id', 'opponent_name_ar', 'opponent_name_en', 'normalized_name', 'first_token', 'last_token', 'latin_key')
                ->where('normalized_name', 'like', $n . '%')
                ->limit($capPrefix)
                ->get();
            $candidates = $candidates->merge($q1);

            if ($mid !== '') {
                $q2 = Opponent::query()
                    ->select('id', 'opponent_name_ar', 'opponent_name_en', 'normalized_name', 'first_token', 'last_token', 'latin_key')
                    ->where('normalized_name', 'like', '%' . $mid . '%')
                    ->limit($capMid)
                    ->get();
                $candidates = $candidates->merge($q2);
            }
        }

        // 2) tokens
        if ($first) {
            $candidates = $candidates->merge(
                Opponent::select('id', 'opponent_name_ar', 'opponent_name_en', 'normalized_name', 'first_token', 'last_token', 'latin_key')
                    ->where('first_token', $first)->limit(100)->get()
            );
        }
        if ($last) {
            $candidates = $candidates->merge(
                Opponent::select('id', 'opponent_name_ar', 'opponent_name_en', 'normalized_name', 'first_token', 'last_token', 'latin_key')
                    ->where('last_token', $last)->limit(100)->get()
            );
        }

        // 3) latin-only phonetic
        if ($latinKey) {
            $candidates = $candidates->merge(
                Opponent::select('id', 'opponent_name_ar', 'opponent_name_en', 'normalized_name', 'first_token', 'last_token', 'latin_key')
                    ->where('latin_key', $latinKey)->limit(100)->get()
            );
        }

        // 4) optional trigrams shortlist
        if (config('fuzzy.features.trigrams') && $n !== '') {
            $tris = $this->trigrams($n);
            if ($tris) {
                $ids = DB::table('opponent_trigrams')
                    ->select('opponent_id', DB::raw('COUNT(*) as c'))
                    ->whereIn('tri', $tris)
                    ->groupBy('opponent_id')
                    ->orderByDesc('c')
                    ->limit($capTri)
                    ->pluck('opponent_id')
                    ->all();
                if ($ids) {
                    $candidates = $candidates->merge(
                        Opponent::select('id', 'opponent_name_ar', 'opponent_name_en', 'normalized_name', 'first_token', 'last_token', 'latin_key')
                            ->whereIn('id', $ids)->get()
                    );
                }
            }
        }

        // Dedup
        $candidates = $candidates->unique('id')->values();

        // Score
        $scored = $candidates->map(function ($c) use ($norm) {
            $score = $this->matcher->score(
                $norm['normalized'],
                (string) $c->normalized_name,
                $norm['script'],
                $norm['latin_key'],
                $c->latin_key
            );
            return [
                'id' => $c->id,
                'label' => $c->opponent_name_ar ?: $c->opponent_name_en,
                'score' => $score['score'],
                'band' => $score['band'],
                'breakdown' => $score,
                'why' => trim(($norm['first_token'] ? 'first: ' . $norm['first_token'] : '') . ' ' . ($norm['last_token'] ? 'last: ' . $norm['last_token'] : '')),
            ];
        })->sortByDesc('score')->values()->all();

        if (config('fuzzy.debug.timing_logs')) {
            Log::info('Opponent suggestions timing', [
                'ms' => (int) ((microtime(true) - $startTs) * 1000),
                'incoming' => $incoming,
                'candidates' => count($candidates),
            ]);
        }

        return [
            'incoming' => $incoming,
            'normalized' => $n,
            'script' => $norm['script'],
            'top' => array_slice($scored, 0, 10),
        ];
    }

    private function trigrams(string $s): array
    {
        $s = preg_replace('/\s+/u', ' ', $s);
        $s = str_replace(' ', '_', $s);
        $res = [];
        $len = mb_strlen($s, 'UTF-8');
        for ($i = 0; $i < $len - 2; $i++) {
            $res[] = mb_substr($s, $i, 3, 'UTF-8');
        }
        return array_values(array_unique($res));
    }
}
