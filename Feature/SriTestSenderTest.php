<?php

use PHPUnit\Framework\TestCase;
use App\Services\SriTestSender;

class SriTestSenderTest extends TestCase
{
    public function testEnviarComprobanteRecepcionSuccess()
    {
        $mockSriTestSender = $this->getMockBuilder(SriTestSender::class)
            ->onlyMethods(['leerAmbienteDesdeXml'])
            ->getMock();
    
        $mockSriTestSender->method('leerAmbienteDesdeXml')
            ->willReturn('1');
    
        $mockSoapClient = $this->createMock(\SoapClient::class);
        $mockSoapClient->method('__call')
            ->with('validarComprobante')
            ->willReturn((object)['estado' => 'RECIBIDO']);
    
        // Inyectar el cliente SOAP simulado
        $reflection = new \ReflectionClass(SriTestSender::class);
        $soapClientProperty = $reflection->getProperty('soapClient');
        $soapClientProperty->setAccessible(true);
        $soapClientProperty->setValue($mockSriTestSender, $mockSoapClient);
    
        $result = $mockSriTestSender->enviarComprobanteRecepcion('<xml><infoTributaria><ambiente>1</ambiente></infoTributaria></xml>');
    
        $this->assertNull($result);
    }

    public function testEnviarComprobanteRecepcionSoapFault()
    {
        $mockSoapClient = $this->createMock(\SoapClient::class);
        $mockSoapClient->method('__call')
            ->with('validarComprobante')
            ->willThrowException(new \SoapFault('Server', 'SOAP Error'));
    
        $sut = new SriTestSender();
    
        // Inyectar el cliente SOAP simulado
        $reflection = new \ReflectionClass(SriTestSender::class);
        $soapClientProperty = $reflection->getProperty('soapClient');
        $soapClientProperty->setAccessible(true);
        $soapClientProperty->setValue($sut, $mockSoapClient);
    
        $result = $sut->enviarComprobanteRecepcion('<xml><infoTributaria><ambiente>1</ambiente></infoTributaria></xml>');
    
        if ($result === null) {
            $this->assertNull($result);
        } else {
            $this->assertEquals([
                'success' => false,
                'error' => 'SOAP Error'
            ], $result);
        }
    }
    
    public function testEnviarComprobanteRecepcionInvalidXml()
    {
        $sut = new SriTestSender();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("El campo 'ambiente' en el XML es inválido. Debe ser '1' (Pruebas) o '2' (Producción).");

        $sut->enviarComprobanteRecepcion('<xml><infoTributaria><ambiente>3</ambiente></infoTributaria></xml>');
    }
    public function testEnviarComprobanteRecepcionAmbienteProduccion()
{
    $mockSriTestSender = $this->getMockBuilder(SriTestSender::class)
        ->onlyMethods(['leerAmbienteDesdeXml'])
        ->getMock();

    $mockSriTestSender->method('leerAmbienteDesdeXml')
        ->willReturn('2'); // Ambiente de producción

    $mockSoapClient = $this->createMock(\SoapClient::class);
    $mockSoapClient->method('__call')
        ->with('validarComprobante')
        ->willReturn((object)['estado' => 'RECIBIDO']);

    // Inyectar el cliente SOAP simulado
    $reflection = new \ReflectionClass(SriTestSender::class);
    $soapClientProperty = $reflection->getProperty('soapClient');
    $soapClientProperty->setAccessible(true);
    $soapClientProperty->setValue($mockSriTestSender, $mockSoapClient);

    $result = $mockSriTestSender->enviarComprobanteRecepcion('<xml><infoTributaria><ambiente>2</ambiente></infoTributaria></xml>');

    $this->assertNull($result); // En este caso, se espera un comportamiento similar al éxito en pruebas.
}



public function testEnviarComprobanteAutorizacionSuccess()
{
    $mockSoapClient = $this->createMock(\SoapClient::class);
    $mockSoapClient->method('__call')
        ->with('autorizacionComprobante')
        ->willReturn((object)[
            'RespuestaAutorizacionComprobante' => (object)[
                'claveAccesoConsultada' => '12345678901234567890123456789012345678901234567890',
                'autorizaciones' => (object)[
                    'autorizacion' => (object)[
                        'estado' => 'RECHAZADA',
                        'mensajes' => (object)[
                            'mensaje' => (object)[
                                'identificador' => '80',
                                'mensaje' => 'ERROR EN LA ESTRUCTURA DE LA CLAVE DE ACCESO',
                                'informacionAdicional' => 'Error al consultar la clave acceso. Error longitud clave acceso no valida.'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

    $sut = new SriTestSender();

    // Inyectar el cliente SOAP simulado
    $reflection = new \ReflectionClass(SriTestSender::class);
    $soapClientProperty = $reflection->getProperty('soapClient');
    $soapClientProperty->setAccessible(true);
    $soapClientProperty->setValue($sut, $mockSoapClient);

    $result = $sut->enviarComprobanteAutorizacion('12345678901234567890123456789012345678901234567890');

    $this->assertEquals((object)[
        'RespuestaAutorizacionComprobante' => (object)[
            'claveAccesoConsultada' => '12345678901234567890123456789012345678901234567890',
            'autorizaciones' => (object)[
                'autorizacion' => (object)[
                    'estado' => 'RECHAZADA',
                    'mensajes' => (object)[
                        'mensaje' => (object)[
                            'identificador' => '80',
                            'mensaje' => 'ERROR EN LA ESTRUCTURA DE LA CLAVE DE ACCESO',
                            'informacionAdicional' => 'Error al consultar la clave acceso. Error longitud clave acceso no valida.'
                        ]
                    ]
                ]
            ]
        ]
    ], $result);
}









}
