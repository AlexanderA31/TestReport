<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\XmlValidator;
use Exception;

class XmlValidatorTest extends TestCase
{
    public function testValidateFacturaSuccess()
{
    $xml = <<<XML
    <factura>
        <infoTributaria>
            <ambiente>1</ambiente>
            <tipoEmision>1</tipoEmision>
        </infoTributaria>
    </factura>
    XML;

    $xsdPath = base_path('app/Services/xsd/factura_V2.1.0.xsd');

    // Crear un esquema XSD temporal
    if (!file_exists(dirname($xsdPath))) {
        mkdir(dirname($xsdPath), 0777, true);
    }

    file_put_contents($xsdPath, <<<XSD
    <xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
        <xs:element name="factura">
            <xs:complexType>
                <xs:sequence>
                    <xs:element name="infoTributaria">
                        <xs:complexType>
                            <xs:sequence>
                                <xs:element name="ambiente" type="xs:string"/>
                                <xs:element name="tipoEmision" type="xs:string"/>
                            </xs:sequence>
                        </xs:complexType>
                    </xs:element>
                </xs:sequence>
            </xs:complexType>
        </xs:element>
    </xs:schema>
    XSD);

    // Ejecutar la validación
    $this->assertTrue(XmlValidator::validateFactura($xml));

    // Limpiar después del test
    unlink($xsdPath);
}

    

    public function testValidateFacturaMissingXsd()
    {
        $xml = <<<XML
        <factura>
            <infoTributaria>
                <ambiente>1</ambiente>
                <tipoEmision>1</tipoEmision>
            </infoTributaria>
        </factura>
        XML;

        $xsdPath = base_path('app/Services/xsd/factura_V2.1.0.xsd');

        if (file_exists($xsdPath)) {
            rename($xsdPath, $xsdPath . '.bak'); // Renombra temporalmente
        }

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("No se encontró el esquema XSD para el tipo 'factura' y versión '2.1.0'.");

        try {
            XmlValidator::validateFactura($xml);
        } finally {
            if (file_exists($xsdPath . '.bak')) {
                rename($xsdPath . '.bak', $xsdPath);
            }
        }
    }

    public function testValidateFacturaInvalidXml()
{
    $xml = <<<XML
    <factura>
        <infoTributaria>
            <ambiente>1</ambiente>
            <!-- Falta cierre de etiqueta tipoEmision -->
    </factura>
    XML;

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Error al validar el XML: DOMDocument::loadXML(): Opening and ending tag mismatch");

    XmlValidator::validateFactura($xml);
}

}
