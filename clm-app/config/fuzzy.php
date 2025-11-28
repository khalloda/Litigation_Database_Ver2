<?php

return [
    'thresholds' => [
        'strong' => 0.92,
        'likely_min' => 0.85,
    ],

    'weights' => [
        'ar_mixed' => ['lev' => 0.55, 'dice' => 0.35, 'jaro' => 0.10, 'phonetic' => 0.0],
        'latin'    => ['lev' => 0.40, 'dice' => 0.30, 'jaro' => 0.20, 'phonetic' => 0.10],
    ],

    'stopwords' => [
        'en' => ['co', 'co.', 'company', 'inc', 'inc.', 'corp', 'ltd', 'llc', 'plc', 'sae', 's.a.e.', 'group', 'holdings', '&', 'and'],
        'ar' => ['شركة', 'شركه', 'ش.م.م', 'ش م م', 'ش.م.ع', 'مجموعة', 'القابضة', 'ومشاركه', 'وشركاه'],
    ],

    'features' => [
        'phonetics' => function_exists('metaphone'),
        'trigrams'  => true,
    ],

    'limits' => [
        'prefix_like_cap' => 200,
        'mid_like_cap'    => 200,
        'trigram_cap'     => 300,
    ],

    'debug' => [
        'timing_logs' => false,
        'log_env_fallbacks' => true,
    ],
];
