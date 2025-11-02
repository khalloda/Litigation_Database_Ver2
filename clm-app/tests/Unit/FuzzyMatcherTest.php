<?php

use App\Support\FuzzyMatcher;
use App\Support\NameNormalizer;

test('AR likely/strong: سامي أحمد أحمد بسيوني vs سامي أحمد أحمد', function () {
    $n = new NameNormalizer();
    $m = new FuzzyMatcher();
    $a = $n->normalize('سامي أحمد أحمد بسيوني');
    $b = $n->normalize('سامي أحمد أحمد');
    $s = $m->score($a['normalized'], $b['normalized'], $a['script'], $a['latin_key'], $b['latin_key']);
    expect(in_array($s['band'], ['likely', 'strong']))->toBeTrue();
});

test('AR likely: سبيد ميديكال vs سبيد ميديكا', function () {
    $n = new NameNormalizer();
    $m = new FuzzyMatcher();
    $a = $n->normalize('سبيد ميديكال');
    $b = $n->normalize('سبيد ميديكا');
    $s = $m->score($a['normalized'], $b['normalized'], $a['script'], $a['latin_key'], $b['latin_key']);
    expect(in_array($s['band'], ['likely', 'strong']))->toBeTrue();
});

test('EN likely/strong: Mohamed A. Ali vs Mohammad Ali', function () {
    $n = new NameNormalizer();
    $m = new FuzzyMatcher();
    $a = $n->normalize('Mohamed A. Ali');
    $b = $n->normalize('Mohammad Ali');
    $s = $m->score($a['normalized'], $b['normalized'], 'latin', $a['latin_key'], $b['latin_key']);
    expect(in_array($s['band'], ['likely', 'strong']))->toBeTrue();
});

test('EN likely: Speed Medical vs Speed Med.', function () {
    $n = new NameNormalizer();
    $m = new FuzzyMatcher();
    $a = $n->normalize('Speed Medical');
    $b = $n->normalize('Speed Med.');
    $s = $m->score($a['normalized'], $b['normalized'], 'latin', $a['latin_key'], $b['latin_key']);
    expect(in_array($s['band'], ['likely', 'strong']))->toBeTrue();
});
