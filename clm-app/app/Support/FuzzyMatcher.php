<?php

namespace App\Support;

class FuzzyMatcher
{
    public function score(string $a, string $b, string $script = 'mixed', ?string $latinKeyA = null, ?string $latinKeyB = null): array
    {
        $lev  = $this->levRatioUtf8($a, $b);
        $dice = $this->tokenDice($a, $b);
        $jaro = $this->jaroWinkler($a, $b);
        $phon = 0.0;

        $w = config('fuzzy.weights.ar_mixed');
        if ($script === 'latin') $w = config('fuzzy.weights.latin');

        $phoneticsOn = config('fuzzy.features.phonetics') && $script === 'latin' && $latinKeyA && $latinKeyB;
        if ($phoneticsOn) {
            $phon = $this->levRatioUtf8($latinKeyA, $latinKeyB);
        } else {
            $sum = ($w['lev'] + $w['dice'] + $w['jaro']);
            $w['lev']  = $w['lev']  / $sum;
            $w['dice'] = $w['dice'] / $sum;
            $w['jaro'] = $w['jaro'] / $sum;
            $w['phonetic'] = 0.0;
        }

        $score = $w['lev'] * $lev + $w['dice'] * $dice + $w['jaro'] * $jaro + ($w['phonetic'] ?? 0.0) * $phon;
        $band = $this->band($score);

        return compact('lev', 'dice', 'jaro', 'phon', 'score', 'band');
    }

    private function band(float $score): string
    {
        $strong = config('fuzzy.thresholds.strong', 0.92);
        $likely = config('fuzzy.thresholds.likely_min', 0.85);
        if ($score >= $strong) return 'strong';
        if ($score >= $likely) return 'likely';
        return 'none';
    }

    private function levRatioUtf8(string $a, string $b): float
    {
        $A = preg_split('//u', $a, -1, PREG_SPLIT_NO_EMPTY);
        $B = preg_split('//u', $b, -1, PREG_SPLIT_NO_EMPTY);
        if ($A === false || $B === false) return 0.0;
        $la = count($A);
        $lb = count($B);
        if ($la === 0 && $lb === 0) return 1.0;

        $dp = array_fill(0, $la + 1, array_fill(0, $lb + 1, 0));
        for ($i = 0; $i <= $la; $i++) $dp[$i][0] = $i;
        for ($j = 0; $j <= $lb; $j++) $dp[0][j] = $j;

        for ($i = 1; $i <= $la; $i++) {
            for ($j = 1; $j <= $lb; $j++) {
                $cost = ($A[$i - 1] === $B[$j - 1]) ? 0 : 1;
                $dp[$i][$j] = min(
                    $dp[$i - 1][$j] + 1,
                    $dp[$i][$j - 1] + 1,
                    $dp[$i - 1][$j - 1] + $cost
                );
            }
        }
        $dist = $dp[$la][$lb];
        $maxLen = max($la, $lb);
        return $maxLen > 0 ? max(0.0, 1.0 - ($dist / $maxLen)) : 1.0;
    }

    private function tokenDice(string $a, string $b): float
    {
        $ta = array_values(array_unique(array_filter(explode(' ', $a))));
        $tb = array_values(array_unique(array_filter(explode(' ', $b))));
        if (!$ta || !$tb) return 0.0;
        $inter = count(array_intersect($ta, $tb));
        return (2 * $inter) / (count($ta) + count($tb));
    }

    private function jaroWinkler(string $s1, string $s2): float
    {
        $l1 = strlen($s1);
        $l2 = strlen($s2);
        if ($l1 === 0 && $l2 === 0) return 1.0;
        $md = (int) floor(max($l1, $l2) / 2) - 1;
        $m1 = array_fill(0, $l1, false);
        $m2 = array_fill(0, $l2, false);
        $m = 0;
        $t = 0;

        for ($i = 0; $i < $l1; $i++) {
            $st = max(0, $i - $md);
            $en = min($i + $md + 1, $l2);
            for ($j = $st; $j < $en; $j++) {
                if ($m2[$j] || $s1[$i] !== $s2[$j]) continue;
                $m1[$i] = true;
                $m2[$j] = true;
                $m++;
                break;
            }
        }
        if ($m === 0) return 0.0;

        $k = 0;
        for ($i = 0; $i < $l1; $i++) {
            if (!$m1[$i]) continue;
            while (!$m2[$k]) $k++;
            if ($s1[$i] !== $s2[$k]) $t++;
            $k++;
        }
        $jaro = (($m / $l1) + ($m / $l2) + (($m - $t / 2) / $m)) / 3.0;

        $p = 0;
        $maxp = 4;
        for ($i = 0; $i < min($maxp, $l1, $l2); $i++) {
            if ($s1[$i] === $s2[$i]) $p++;
            else break;
        }
        return $jaro + 0.1 * $p * (1 - $jaro);
    }
}
