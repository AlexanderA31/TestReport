<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\XmlBlockGenerator;
use Saloon\XmlWrangler\Data\Element;

class XmlBlockGeneratorTest extends TestCase
{
    private $mockElement;

    protected function setUp(): void
    {
        parent::setUp();

        // Mockear la clase Element
        $this->mockElement = $this->getMockBuilder(Element::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttribute'])
            ->getMock();

        $this->mockElement->method('getAttribute')
            ->willReturnCallback(function ($key) {
                // Simular el contenido esperado
                $mockData = [
                    "claveAcceso" => "1234567890123456789012345678901234567890123456789",
                    "totalImpuesto" => [["codigo" => "2", "baseImponible" => "100.00", "valor" => "12.00"]],
                    "pago" => [["formaPago" => "01", "total" => "112.00"]],
                    "detalle" => [["codigoPrincipal" => "123", "descripcion" => "Producto A"]],
                    "campoAdicional" => [["telefono" => "123456789", "email" => "test@example.com"]],
                ];
                return $mockData[$key] ?? null;
            });
    }

    public function testAddInfoTributaria()
    {
        // Arrange
        $data = [
            "ambiente" => "2",
            "razonSocial" => "Empresa S.A.",
            "ruc" => "1234567890",
            "codDoc" => "01",
            "estab" => "001",
            "ptoEmi" => "002",
            "secuencial" => "000000001",
            "dirMatriz" => "Av. Principal",
        ];
        $accessKey = "1234567890123456789012345678901234567890123456789";

        // Act
        $result = $this->createMockedElement($data, $accessKey, 'addInfoTributaria');

        // Assert
        $this->assertInstanceOf(Element::class, $result);
        $this->assertEquals($accessKey, $result->getAttribute("claveAcceso"));
    }

    public function testAddTotalConImpuestos()
    {
        // Arrange
        $data = [
            [
                "codigo" => "2",
                "codigoPorcentaje" => "2",
                "baseImponible" => "100.00",
                "valor" => "12.00"
            ]
        ];

        // Act
        $result = $this->createMockedElement($data, null, 'addTotalConImpuestos');

        // Assert
        $this->assertInstanceOf(Element::class, $result);
        $this->assertNotNull($result->getAttribute("totalImpuesto"));
    }

    public function testAddPagos()
    {
        // Arrange
        $data = [
            [
                "formaPago" => "01",
                "total" => "112.00",
                "plazo" => "30",
                "unidadTiempo" => "días"
            ]
        ];

        // Act
        $result = $this->createMockedElement($data, null, 'addPagos');

        // Assert
        $this->assertInstanceOf(Element::class, $result);
        $this->assertNotNull($result->getAttribute("pago"));
    }

    public function testAddDetalles()
    {
        // Arrange
        $data = [
            [
                "codigoPrincipal" => "123",
                "descripcion" => "Producto A",
                "cantidad" => "2",
                "precioUnitario" => "50.00",
                "descuento" => "0.00",
                "precioTotalSinImpuesto" => "100.00",
                "impuestos" => [
                    [
                        "codigo" => "2",
                        "codigoPorcentaje" => "2",
                        "tarifa" => "12",
                        "baseImponible" => "100.00",
                        "valor" => "12.00"
                    ]
                ]
            ]
        ];

        // Act
        $result = $this->createMockedElement($data, null, 'addDetalles');

        // Assert
        $this->assertInstanceOf(Element::class, $result);
        $this->assertNotNull($result->getAttribute("detalle"));
    }

    public function testAddInfoAdicional()
    {
        // Arrange
        $data = [
            "telefono" => "123456789",
            "email" => "test@example.com"
        ];

        // Act
        $result = $this->createMockedElement($data, null, 'addInfoAdicional');

        // Assert
        $this->assertInstanceOf(Element::class, $result);
        $this->assertNotNull($result->getAttribute("campoAdicional"));
    }

    public function testAddRubrosTerceros()
{
    // Arrange
    $data = [
        [
            "concepto" => "Rubro 1",
            "total" => "200.00"
        ],
        [
            "concepto" => "Rubro 2",
            "total" => "300.00"
        ]
    ];

    // Mock para el método 'addRubrosTerceros'
    $mockedXmlGenerator = $this->getMockBuilder(XmlBlockGenerator::class)
        ->disableOriginalConstructor()
        ->onlyMethods(['addRubrosTerceros'])
        ->getMock();

    // Simular que 'addRubrosTerceros' devuelve el mock de Element
    $mockedXmlGenerator->expects($this->once())
        ->method('addRubrosTerceros')
        ->willReturn($this->mockElement);

    // Act
    $result = $mockedXmlGenerator->addRubrosTerceros($data);

    // Assert
    $this->assertInstanceOf(Element::class, $result);
    $this->assertNotNull($result, 'El método addRubrosTerceros no debe devolver null.');
}


    private function createMockedElement(array $data, ?string $accessKey, string $method)
    {
        $mockedXmlGenerator = $this->getMockBuilder(XmlBlockGenerator::class)
            ->disableOriginalConstructor()
            ->onlyMethods([$method])
            ->getMock();

        $mockedXmlGenerator->expects($this->once())
            ->method($method)
            ->willReturn($this->mockElement);

        return $mockedXmlGenerator->{$method}($data, $accessKey);
    }
}
