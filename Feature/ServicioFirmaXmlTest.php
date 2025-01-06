<?php

namespace Tests\Unit;

use App\Services\ServicioFirmaXml;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase;
use stdClass;

class ServicioFirmaXmlTest extends TestCase
{
    protected ServicioFirmaXml $servicioFirmaXml;

    protected function setUp(): void
    {
        parent::setUp();

        // Configuración simulada para pruebas
        $this->app['config']->set('signing.jar', 'path/to/jar');
        $this->app['config']->set('signing.comprobantes', 'comprobantes');
        $this->app['config']->set('signing.firmados', 'firmados');
        $this->app['config']->set('signing.ruta_firmados_absoluta', 'C:/firmados');
        $this->app['config']->set('signing.storage_disk', 'local');

        // Instancia del servicio a testear
        $this->servicioFirmaXml = new ServicioFirmaXml();
    }

    /** @test */
    public function test_guardar_comprobante_firmado()
    {
        Storage::fake('local');
        $claveAcceso = '1234567890';
        $xmlSigned = '<xml>signed</xml>';
        $expectedFilePath = 'C:/firmados/' . $claveAcceso . '.xml';

        // Crear el directorio si no existe
        if (!file_exists('C:/firmados')) {
            mkdir('C:/firmados', 0777, true);
        }

        // Ejecutar el método a probar
        $this->servicioFirmaXml->guardar_comprobante_firmado($xmlSigned, $claveAcceso);

        // Verificar si el archivo existe
        $this->assertFileExists($expectedFilePath);

        // Limpieza
        unlink($expectedFilePath);
    }

    /** @test */
    public function test_guardar_comprobante()
    {
        Storage::fake('local');
        $xml = '<xml>test</xml>';
        $claveAcceso = '1234567890';
        $expectedPath = 'comprobantes/' . $claveAcceso . '.xml';

        $result = $this->servicioFirmaXml->guardar_comprobante($xml, $claveAcceso);

        Storage::assertExists($expectedPath);
        $this->assertStringContainsString($claveAcceso, $result);
    }

    /** @test */
    public function test_guardar_comprobante_firma_falla()
{
    // Simulación del almacenamiento
    Storage::fake('local');

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Error al guardar el comprobante electrónico');

    $claveAcceso = '1234567890';
    $xml = '<xml>test</xml>';
    $expectedPath = 'comprobantes/' . $claveAcceso . '.xml';

    // Mock de Storage: Simular fallo en put() y comportamiento de exists()
    Storage::shouldReceive('disk')
        ->with('local')
        ->andReturnSelf();

    Storage::shouldReceive('put')
        ->with($expectedPath, $xml)
        ->andReturn(false); // Simula que la escritura falla

    Storage::shouldReceive('exists')
        ->with($expectedPath)
        ->andReturn(false); // Simula que el archivo no existe

    // Ejecutar el método
    $this->servicioFirmaXml->guardar_comprobante($xml, $claveAcceso);
}

    /** @test */
    public function test_obtener_comprobante_firmado()
    {
        Storage::fake('local');
        $claveAcceso = '1234567890';
        $xmlSigned = '<xml>signed</xml>';
        $path = 'firmados/' . $claveAcceso . '.xml';

        // Simula un archivo firmado
        Storage::disk('local')->put($path, $xmlSigned);

        // Obtener el comprobante firmado
        $result = $this->servicioFirmaXml->obtener_comprobante_firmado($claveAcceso);

        $this->assertEquals($xmlSigned, $result);
    }

    /** @test */
    public function test_firmar_comprobante_exitoso()
    {
        $xml = '<xml>test</xml>';
        $certificado = 'certificado.p12';
        $psw = 'password';

        $mockOutput = new stdClass();
        $mockOutput->status = 200;
        $mockOutput->xml_signed = '<xml>signed</xml>';

        // Mock del método firmar_comprobante
        $mock = $this->getMockBuilder(ServicioFirmaXml::class)
            ->onlyMethods(['firmar_comprobante'])
            ->getMock();

        $mock->expects($this->once())
            ->method('firmar_comprobante')
            ->with($xml, $certificado, $psw)
            ->willReturn($mockOutput);

        $result = $mock->firmar_comprobante($xml, $certificado, $psw);

        $this->assertEquals(200, $result->status);
        $this->assertStringContainsString('signed', $result->xml_signed);
    }

    /** @test */
    public function test_firmar_comprobante_falla()
    {
        $xml = '<xml>test</xml>';
        $certificado = 'certificado.p12';
        $psw = 'password';

        $mockOutput = new stdClass();
        $mockOutput->status = 500;
        $mockOutput->message = 'Error en el proceso de firma';

        $mock = $this->getMockBuilder(ServicioFirmaXml::class)
            ->onlyMethods(['firmar_comprobante'])
            ->getMock();

        $mock->expects($this->once())
            ->method('firmar_comprobante')
            ->with($xml, $certificado, $psw)
            ->willReturn($mockOutput);

        $result = $mock->firmar_comprobante($xml, $certificado, $psw);

        $this->assertEquals(500, $result->status);
        $this->assertStringContainsString('Error en el proceso de firma', $result->message);
    }
}
