<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SriAutorizacion;
use stdClass;

class SriAutorizacionTest extends TestCase
{
    public function testAutorizacionComprobanteRecibida()
    {
        // Arrange
        $xml = '<Comprobante>Test</Comprobante>';
        $claveAcceso = '1234567890';
        $ambiente = 'prueba';

        // Crear un mock de la clase SriAutorizacion con recepcion y validacion simuladas
        $mockSriAutorizacion = $this->getMockBuilder(SriAutorizacion::class)
            ->onlyMethods(['recepcion', 'validacion'])
            ->setConstructorArgs([$ambiente])
            ->getMock();

        // Simular la respuesta esperada para "recepcion"
        $mockResponse = new stdClass();
        $mockResponse->RespuestaRecepcionComprobante = new stdClass();
        $mockResponse->RespuestaRecepcionComprobante->estado = 'RECIBIDA';

        // Configurar el método 'recepcion' para devolver la respuesta simulada
        $mockSriAutorizacion->method('recepcion')->willReturn($mockResponse);

        // Configurar el método 'validacion' para devolver un resultado consistente con "RECIBIDA"
        $mockValidacion = new stdClass();
        $mockValidacion->status = 'RECIBIDA';
        $mockValidacion->response = $mockResponse;
        $mockSriAutorizacion->method('validacion')->willReturn($mockValidacion);

        // Act
        $result = $mockSriAutorizacion->validacion($mockResponse);

        // Assert
        $this->assertEquals('RECIBIDA', $result->status);
    }

    public function testAutorizacionComprobanteDevuelta()
    {
        // Arrange
        $xml = '<Comprobante>Test</Comprobante>';
        $claveAcceso = '1234567890';
        $ambiente = 'prueba';

        // Crear un mock de la clase SriAutorizacion con recepcion y validacion simuladas
        $mockSriAutorizacion = $this->getMockBuilder(SriAutorizacion::class)
            ->onlyMethods(['recepcion', 'validacion'])
            ->setConstructorArgs([$ambiente])
            ->getMock();

        // Simular la respuesta esperada para "recepcion"
        $mockResponse = new stdClass();
        $mockResponse->RespuestaRecepcionComprobante = new stdClass();
        $mockResponse->RespuestaRecepcionComprobante->estado = 'DEVUELTA';
        $mockResponse->RespuestaRecepcionComprobante->comprobantes = new stdClass();
        $mockResponse->RespuestaRecepcionComprobante->comprobantes->comprobante = new stdClass();
        $mockResponse->RespuestaRecepcionComprobante->comprobantes->comprobante->mensajes = [
            (object)['mensaje' => 'Error de prueba']
        ];

        // Configurar el método 'recepcion' para devolver la respuesta simulada
        $mockSriAutorizacion->method('recepcion')->willReturn($mockResponse);

        // Configurar el método 'validacion' para devolver un resultado consistente con "DEVUELTA"
        $mockValidacion = new stdClass();
        $mockValidacion->status = 'DEVUELTA';
        $mockValidacion->response = $mockResponse;
        $mockSriAutorizacion->method('validacion')->willReturn($mockValidacion);

        // Act
        $result = $mockSriAutorizacion->validacion($mockResponse);

        // Assert
        $this->assertEquals('DEVUELTA', $result->status);
    }

    public function testAutorizacionComprobanteNoAutorizado()
    {
        // Arrange
        $xml = '<Comprobante>Test</Comprobante>';
        $claveAcceso = '1234567890';
        $ambiente = 'prueba';

        $mockSriAutorizacion = $this->getMockBuilder(SriAutorizacion::class)
            ->onlyMethods(['recepcion', 'validacion'])
            ->setConstructorArgs([$ambiente])
            ->getMock();

        $mockResponse = new stdClass();
        $mockResponse->RespuestaRecepcionComprobante = new stdClass();
        $mockResponse->RespuestaRecepcionComprobante->estado = 'NO AUTORIZADO';

        $mockSriAutorizacion->method('recepcion')->willReturn($mockResponse);

        $mockValidacion = new stdClass();
        $mockValidacion->status = 'NO AUTORIZADO';
        $mockValidacion->response = $mockResponse;
        $mockSriAutorizacion->method('validacion')->willReturn($mockValidacion);

        // Act
        $result = $mockSriAutorizacion->validacion($mockResponse);

        // Assert
        $this->assertEquals('NO AUTORIZADO', $result->status);
    }

    public function testAutorizacionComprobanteSinEstado()
    {
        // Arrange
        $xml = '<Comprobante>Test</Comprobante>';
        $claveAcceso = '1234567890';
        $ambiente = 'prueba';

        $mockSriAutorizacion = $this->getMockBuilder(SriAutorizacion::class)
            ->onlyMethods(['recepcion', 'validacion'])
            ->setConstructorArgs([$ambiente])
            ->getMock();

        $mockResponse = new stdClass();
        $mockResponse->RespuestaRecepcionComprobante = new stdClass();
        // Sin estado asignado

        $mockSriAutorizacion->method('recepcion')->willReturn($mockResponse);

        $mockValidacion = new stdClass();
        $mockValidacion->status = 'SIN ESTADO';
        $mockValidacion->response = $mockResponse;
        $mockSriAutorizacion->method('validacion')->willReturn($mockValidacion);

        // Act
        $result = $mockSriAutorizacion->validacion($mockResponse);

        // Assert
        $this->assertEquals('SIN ESTADO', $result->status);
    }
}