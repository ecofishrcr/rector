<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\SetTypeToCastRector;

use Rector\CodingStyle\Rector\FuncCall\SetTypeToCastRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SetTypeToCastRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/assign.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return SetTypeToCastRector::class;
    }
}
