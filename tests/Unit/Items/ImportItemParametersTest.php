<?php

namespace Tests\Unit\Items;

use PHPUnit\Framework\TestCase;
use App\Console\Commands\Tenant\ImportItemParametersCommand;
use ReflectionMethod;

class ImportItemParametersTest extends TestCase
{
    private ImportItemParametersCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new ImportItemParametersCommand();
    }

    private function invokeParseNumber($val): ?float
    {
        $method = new ReflectionMethod(ImportItemParametersCommand::class, 'parseNumber');
        $method->setAccessible(true);
        return $method->invoke($this->command, $val);
    }

    /**
     * Prueba que los números con sufijos de texto (ej. "15 CV", "1.44W") se extraigan limpiamente
     */
    public function test_parse_number_with_text_suffix(): void
    {
        $this->assertEquals(15.0, $this->invokeParseNumber('15 CV'));
        $this->assertEquals(25.0, $this->invokeParseNumber('25 CV'));
        $this->assertEquals(1.44, $this->invokeParseNumber('1.44W'));
        $this->assertEquals(0.72, $this->invokeParseNumber('0.72'));
    }

    /**
     * Prueba que las comas decimales se conviertan correctamente a puntos
     */
    public function test_parse_number_with_comma_decimal(): void
    {
        $this->assertEquals(1.44, $this->invokeParseNumber('1,44'));
        $this->assertEquals(5.5, $this->invokeParseNumber('5,5'));
    }

    /**
     * Prueba que valores nulos, vacíos o la cadena 'NULL' devuelvan null
     */
    public function test_parse_number_with_null_and_empty_values(): void
    {
        $this->assertNull($this->invokeParseNumber(null));
        $this->assertNull($this->invokeParseNumber(''));
        $this->assertNull($this->invokeParseNumber('NULL'));
        $this->assertNull($this->invokeParseNumber('   null   '));
    }
}
