<?php

namespace App\Console\Commands;

use App\Models\Opponent;
use App\Support\NameNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillOpponentNormalization extends Command
{
    protected $signature = 'opponents:backfill-normalization {--chunk=1000}';
    protected $description = 'Compute normalized_name, tokens, latin_key for all opponents and maintain trigrams if enabled';

    public function handle(NameNormalizer $normalizer): int
    {
        $chunk = (int) $this->option('chunk');

        Opponent::query()->orderBy('id')->chunk($chunk, function ($batch) use ($normalizer) {
            DB::beginTransaction();
            foreach ($batch as $opp) {
                $bestName = $opp->opponent_name_ar ?: $opp->opponent_name_en ?: '';
                $r = $normalizer->normalize($bestName);
                $opp->normalized_name = $r['normalized'];
                $opp->first_token = $r['first_token'];
                $opp->last_token = $r['last_token'];
                $opp->token_count = $r['token_count'];
                $opp->latin_key = $r['latin_key'];
                $opp->saveQuietly();

                if (config('fuzzy.features.trigrams') && $opp->normalized_name) {
                    $tris = $this->trigrams($opp->normalized_name);
                    DB::table('opponent_trigrams')->where('opponent_id', $opp->id)->delete();
                    if ($tris) {
                        $rows = array_map(fn($t) => ['opponent_id' => $opp->id, 'tri' => $t], $tris);
                        DB::table('opponent_trigrams')->insert($rows);
                    }
                }
            }
            DB::commit();
            $this->info('Updated ' . $batch->count() . ' opponents');
        });

        return self::SUCCESS;
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
