<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\ColorNormalizer;
use PHPUnit\Framework\TestCase;

class NormalizacaoCorTest extends TestCase
{
    public function test_rgba_string_is_converted_to_hex(): void
    {
        $this->assertSame('#ff00ed', ColorNormalizer::hexFromAny('rgba(255, 0, 237, 1)'));
    }

    public function test_rgb_string_is_converted_to_hex(): void
    {
        $this->assertSame('#10b981', ColorNormalizer::hexFromAny('rgb(16, 185, 129)'));
    }

    public function test_valid_hex_is_returned_unchanged(): void
    {
        $this->assertSame('#4F46E5', ColorNormalizer::hexFromAny('#4F46E5'));
    }

    public function test_lowercase_hex_is_returned_unchanged(): void
    {
        $this->assertSame('#ffffff', ColorNormalizer::hexFromAny('#ffffff'));
    }

    public function test_rgba_with_spaces_is_converted(): void
    {
        $this->assertSame('#0a0a0a', ColorNormalizer::hexFromAny('rgba( 10, 10, 10, 0.5 )'));
    }

    public function test_unrecognized_value_is_returned_unchanged(): void
    {
        $this->assertSame('hsl(200, 50%, 50%)', ColorNormalizer::hexFromAny('hsl(200, 50%, 50%)'));
    }

    public function test_value_is_trimmed(): void
    {
        $this->assertSame('#FF0000', ColorNormalizer::hexFromAny('  #FF0000  '));
    }
}
