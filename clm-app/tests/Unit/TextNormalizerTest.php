<?php

namespace Tests\Unit;

use App\Support\TextNormalizer;
use PHPUnit\Framework\TestCase;

class TextNormalizerTest extends TestCase
{
    private TextNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new TextNormalizer();
    }

    public function test_hamza_variants_unified_to_bare_alif(): void
    {
        $base = 'سلطة اتهام';
        $hamza1 = 'سلطة إتهام';
        $hamza2 = 'سلطة أتهام';
        $hamza3 = 'سلطة آتهام';

        $nBase   = $this->normalizer->normalize($base);
        $nHamza1 = $this->normalizer->normalize($hamza1);
        $nHamza2 = $this->normalizer->normalize($hamza2);
        $nHamza3 = $this->normalizer->normalize($hamza3);

        $this->assertSame($nBase, $nHamza1);
        $this->assertSame($nBase, $nHamza2);
        $this->assertSame($nBase, $nHamza3);
    }

    public function test_zero_width_and_nbsp_removed_and_spaces_collapsed(): void
    {
        $withMarks = "\u{200F}س\u{200D}لطة\u{200C}  \u{00A0}إِتِهام\u{FEFF}";
        $normalized = $this->normalizer->normalize($withMarks);
        $this->assertSame('سلطة اتهام', $normalized);
    }
}

