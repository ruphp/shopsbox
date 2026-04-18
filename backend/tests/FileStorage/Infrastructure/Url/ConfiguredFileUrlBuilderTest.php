<?php

declare(strict_types=1);

namespace App\Tests\FileStorage\Infrastructure\Url;

use App\FileStorage\Infrastructure\Url\ConfiguredFileUrlBuilder;
use PHPUnit\Framework\TestCase;

final class ConfiguredFileUrlBuilderTest extends TestCase
{
    public function testItBuildsPublicUrlWithoutDuplicateSlashes(): void
    {
        $builder = new ConfiguredFileUrlBuilder('http://localhost:8080/files/');

        self::assertSame(
            'http://localhost:8080/files/tenant/demo.txt',
            $builder->publicUrl('/tenant/demo.txt'),
        );
    }
}
