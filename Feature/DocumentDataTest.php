<?php

use PHPUnit\Framework\TestCase;
use App\Services\DocumentData;
use App\Models\PuntoEmision;
use App\Models\User;
use Mockery;

class DocumentDataTest extends TestCase
{
    protected $documentData;

    protected function setUp(): void
    {
        $this->documentData = new DocumentData();
    }

    protected function tearDown(): void
    {
        Mockery::close(); // Cierra todos los mocks al final de cada test.
    }

    public function testPrepareDataSuccess()
    {
        // Mock PuntoEmision con Mockery
        $puntoEmisionMock = Mockery::mock('overload:' . PuntoEmision::class);
        $puntoEmisionMock->shouldReceive('where')->andReturnSelf();
        $puntoEmisionMock->shouldReceive('whereHas')->andReturnSelf();
        $puntoEmisionMock->shouldReceive('lockForUpdate')->andReturnSelf();
        $puntoEmisionMock->shouldReceive('firstOrFail')->andReturn($puntoEmisionMock);
        $puntoEmisionMock->ultimoSecuencial = 123;
        $puntoEmisionMock->establecimiento = (object) ['numero' => '001', 'direccion' => 'Calle Falsa 123'];
        $puntoEmisionMock->numero = '002';
        $puntoEmisionMock->shouldReceive('save');

        // Mock User con Mockery
        $userMock = Mockery::mock('overload:' . User::class);
        $userMock->shouldReceive('findOrFail')->andReturn($userMock);
        $userMock->ambiente = '1';
        $userMock->ruc = '1234567890001';
        $userMock->razonSocial = 'Mi Empresa';
        $userMock->nombreComercial = 'Mi Comercio';
        $userMock->dirMatriz = 'Matriz 123';
        $userMock->contribuyenteEspecial = '1234';
        $userMock->obligadoContabilidad = true;

        // Datos validados
        $validatedData = ['claveAcceso' => '1234567890'];

        $result = $this->documentData->prepareData(1, 1, $validatedData);

        $this->assertArrayHasKey('secuencial', $result);
        $this->assertEquals('000000124', $result['secuencial']);
        $this->assertEquals('001', $result['estab']);
        $this->assertEquals('002', $result['ptoEmi']);
        $this->assertEquals('Calle Falsa 123', $result['dirEstablecimiento']);
    }
}
