<?php

use Mockery;
use PHPUnit\Framework\TestCase;
use App\Services\DocumentGenerator;
use App\Services\XmlBlockGenerator; // Asegúrate de que esta línea esté presente
use App\Services\AccessKeyGenerator;
use Saloon\XmlWrangler\XmlWriter;
use Saloon\XmlWrangler\Data\Element;

class DocumentGeneratorTest extends TestCase
{
    protected $documentGenerator;
    protected $xmlBlockGeneratorMock;
    protected function setUp(): void
    {
        // Crear un mock parcial de XmlBlockGenerator
        $this->xmlBlockGeneratorMock = Mockery::mock(XmlBlockGenerator::class)->makePartial();
    
        // Crear la instancia de DocumentGenerator con el mock
        $this->documentGenerator = new DocumentGenerator($this->xmlBlockGeneratorMock);
    }
    protected function tearDown(): void
    {
        // Cerramos Mockery para limpiar los mocks después de cada prueba
        Mockery::close();
    }

    public function testNewFacturaGeneratesCorrectXmlAndAccessKey()
    {
        $preparedData = [
            'claveAcceso' => '12345678901234567890123456789012345678901234567',
            'detalles' => [['item' => 'Producto 1']],
            'otrosRubrosTerceros' => [['rubro' => 'Otro']],
            'infoAdicional' => [['campoAdicional' => 'Dato adicional']],
            'codDoc' => '01', // 'codDoc' ya está presente
        ];

        $expectedAccessKey = 'expectedAccessKey';
        $expectedXml = '<factura id="comprobante" version="2.1.0"><xml>test</xml></factura>';

        // Mock estático de AccessKeyGenerator::generate usando Mockery
        Mockery::mock('alias:' . AccessKeyGenerator::class)
            ->shouldReceive('generate')
            ->with(Mockery::on(function ($arg) {
                return isset($arg['codDoc']) && $arg['codDoc'] === '01'; // Verifica que codDoc esté presente y correcto
            }))
            ->andReturn($expectedAccessKey);

        // Crear objetos mock del tipo Element
        $mockElement = $this->createMock(Element::class);

        // Configurar mocks de XmlBlockGenerator para devolver elementos válidos
        $this->xmlBlockGeneratorMock
            ->shouldReceive('addInfoTributaria')
            ->andReturn($mockElement);

        $this->xmlBlockGeneratorMock
            ->shouldReceive('addInfoFactura')
            ->andReturn($mockElement);

        $this->xmlBlockGeneratorMock
            ->shouldReceive('addDetalles')
            ->andReturn($mockElement);

        $this->xmlBlockGeneratorMock
            ->shouldReceive('addRubrosTerceros')
            ->andReturn($mockElement);

        $this->xmlBlockGeneratorMock
            ->shouldReceive('addInfoAdicional')
            ->andReturn($mockElement);

        // Simulamos el método estático filterNullValues de XmlBlockGenerator
        $this->xmlBlockGeneratorMock
            ->shouldReceive('filterNullValues')
            ->andReturn([
                "infoTributaria" => $mockElement,
                "infoFactura" => $mockElement,
                "detalles" => $mockElement,
                "otrosRubrosTerceros" => $mockElement,
                "infoAdicional" => $mockElement,
            ]);

        // Mock estático de XmlWriter::make usando Mockery
        $xmlWriterMock = Mockery::mock('alias:' . XmlWriter::class);
        $xmlWriterMock->shouldReceive('make')->andReturnSelf();
        $xmlWriterMock->shouldReceive('write')->andReturn($expectedXml);

        // Ejecutar la prueba
        $result = $this->documentGenerator->newFactura($preparedData);

        // Asegurarse de que los resultados sean los esperados
        $this->assertEquals($expectedXml, $result['xml']);
        $this->assertEquals($expectedAccessKey, $result['accessKey']);
    }
}
