<?php

use PHPUnit\Framework\TestCase;
use App\Services\AccessKeyGenerator; // Importa la clase con el namespace.

class AccessKeyGeneratorTest extends TestCase
{
    public function testGenerateReturnsValidAccessKey()
    {
        $data = [
            'fechaEmision' => '2024-12-03',
            'ruc' => '1234567890001',
            'codDoc' => '01',
            'ambiente' => '2',
            'estab' => '001',
            'ptoEmi' => '002',
            'secuencial' => '000000123',
        ];

        $accessKey = AccessKeyGenerator::generate($data);

        $this->assertNotEmpty($accessKey, "La clave de acceso no debe estar vacía.");
        $this->assertEquals(49, strlen($accessKey), "La clave de acceso debe tener 49 caracteres.");
        $this->assertMatchesRegularExpression('/^\d{49}$/', $accessKey, "La clave de acceso debe contener solo números.");
    }

    public function testGenerateHandlesMissingOptionalData()
    {
        $data = [
            'fechaEmision' => '2024-12-03',
            'ruc' => '1234567890001',
            'codDoc' => '01',
            'ambiente' => '2',
            'secuencial' => '000000123',
        ];

        $accessKey = AccessKeyGenerator::generate($data);

        $this->assertNotEmpty($accessKey);
        $this->assertEquals('123456', substr($accessKey, 10, 6), "Debe usar los valores predeterminados para `estab` y `ptoEmi`.");
    }

    /*public function testGenerateThrowsExceptionOnInvalidDate()
    {
        $this->expectException(\Exception::class);


    
        $data = [
            'fechaEmision' => 'invalid-date',
            'ruc' => '1234567890001',
            'codDoc' => '01',
            'ambiente' => '2',
            'secuencial' => '000000123',
        ];
    
        AccessKeyGenerator::generate($data);
    }
    */
}
