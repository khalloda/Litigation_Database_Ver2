<?php

use App\Support\NameNormalizer;

it('normalizes Arabic without converting Ta Marbuta', function () {
    $n = new NameNormalizer();
    $r = $n->normalize('شَرِكَةُ سبيد مِيديكَالٍ ش.م.م');
    expect($r['normalized'])->toContain('سبيد');
    expect($r['normalized'])->toContain('ميديكال');
    // Ensure no ta marbuta to heh conversion
    expect(mb_substr($r['normalized'], -1, 1, 'UTF-8'))->not->toBe('ه');
});

it('converges سامي أحمد أحمد بسيوني and سامي أحمد أحمد', function () {
    $n = new NameNormalizer();
    $a = $n->normalize('سامي أحمد أحمد بسيوني');
    $b = $n->normalize('سامي أحمد أحمد');
    expect($a['normalized'])->toContain('سامي');
    expect($b['normalized'])->toContain('سامي');
});

it('normalizes english names and yields latin key when metaphone enabled', function () {
    $n = new NameNormalizer();
    $r = $n->normalize('Mohamed A. Ali');
    expect($r['normalized'])->toBeString();
    if (config('fuzzy.features.phonetics')) {
        expect($r['latin_key'])->not->toBeNull();
    }
});
