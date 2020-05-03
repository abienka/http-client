<?php declare(strict_types=1);

namespace Abienka\HttpClient\Tests\Unit;

use Abienka\HttpClient\CurlClient;
use Abienka\HttpClient\Service\CurlService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class CurlClientTest extends TestCase
{
    public function testSendRequestReturnsResponseInterface()
    {
        $curlServiceMock = $this->createMock(CurlService::class);
        $curlServiceMock->method('execute')->willReturn(true);
        $curlServiceMock->method('getResponseHeaders')->willReturn([]);
        $curlServiceMock->method('getResponseBody')->willReturn('');
        
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('withBody')->willReturn($responseMock);
        
        $responseFactoryMock = $this->createMock(ResponseFactoryInterface::class);
        $responseFactoryMock->method('createResponse')->willReturn($responseMock);
        
        $client = new CurlClient(
            $curlServiceMock,
            $responseFactoryMock,
            $this->createMock(StreamFactoryInterface::class)
        );
        
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getHeaders')->willReturn([]);
        $requestMock->method('getMethod')->willReturn('GET');
        $requestMock->method('getUri')->willReturn('http://foo.bar');
        
        $result = $client->sendRequest($requestMock);
        
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    
    public function testSendRequestCreateResponseWithStatusCode()
    {
        $curlServiceMock = $this->createMock(CurlService::class);
        $curlServiceMock->method('execute')->willReturn(true);
        $curlServiceMock->method('getResponseHeaders')->willReturn([]);
        $curlServiceMock->method('getResponseBody')->willReturn('');
        $curlServiceMock->method('getResponseCode')->willReturn(200);
        
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('withBody')->willReturn($responseMock);
        
        $responseFactoryMock = $this->createMock(ResponseFactoryInterface::class);
        $responseFactoryMock->expects($this->once())
            ->method('createResponse')
            ->with(200)
            ->willReturn($responseMock);
        
        $client = new CurlClient(
            $curlServiceMock,
            $responseFactoryMock,
            $this->createMock(StreamFactoryInterface::class)
        );
        
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getHeaders')->willReturn([]);
        $requestMock->method('getMethod')->willReturn('GET');
        $requestMock->method('getUri')->willReturn('http://foo.bar');
        
        $client->sendRequest($requestMock);
    }
    
    public function testSendRequestSetsResponseBody()
    {
        $curlServiceMock = $this->createMock(CurlService::class);
        $curlServiceMock->method('execute')->willReturn(true);
        $curlServiceMock->method('getResponseHeaders')->willReturn([]);
        $curlServiceMock->method('getResponseBody')->willReturn('');
        
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('withBody')
            ->with($this->isInstanceOf(StreamInterface::class))
            ->willReturn($responseMock);
        
        $responseFactoryMock = $this->createMock(ResponseFactoryInterface::class);
        $responseFactoryMock->method('createResponse')->willReturn($responseMock);
        
        $client = new CurlClient(
            $curlServiceMock,
            $responseFactoryMock,
            $this->createMock(StreamFactoryInterface::class)
        );
        
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getHeaders')->willReturn([]);
        $requestMock->method('getMethod')->willReturn('GET');
        $requestMock->method('getUri')->willReturn('http://foo.bar');
        
        $client->sendRequest($requestMock);
    }
    
    public function testSendRequestCreatesStreamWithResponseBody()
    {
        $curlServiceMock = $this->createMock(CurlService::class);
        $curlServiceMock->method('execute')->willReturn(true);
        $curlServiceMock->method('getResponseHeaders')->willReturn([]);
        $curlServiceMock->method('getResponseBody')->willReturn('Body');
        
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('withBody')->willReturn($responseMock);
        
        $streamFactoryMock = $this->createMock(StreamFactoryInterface::class);
        $streamFactoryMock->expects($this->once())
            ->method('createStream')
            ->with('Body');
        
        $responseFactoryMock = $this->createMock(ResponseFactoryInterface::class);
        $responseFactoryMock->method('createResponse')->willReturn($responseMock);
        
        $client = new CurlClient(
            $curlServiceMock,
            $responseFactoryMock,
            $streamFactoryMock
        );
        
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getHeaders')->willReturn([]);
        $requestMock->method('getMethod')->willReturn('GET');
        $requestMock->method('getUri')->willReturn('http://foo.bar');
        
        $client->sendRequest($requestMock);
    }
    
    public function testSendRequestSetsResponseHeaders()
    {
        $curlServiceMock = $this->createMock(CurlService::class);
        $curlServiceMock->method('execute')->willReturn(true);
        $curlServiceMock->method('getResponseHeaders')->willReturn([
            'name' => 'value'
        ]);
        $curlServiceMock->method('getResponseBody')->willReturn('');
        
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('withHeader')
            ->with('name', 'value')
            ->willReturn($responseMock);
        $responseMock->method('withBody')->willReturn($responseMock);
        
        $responseFactoryMock = $this->createMock(ResponseFactoryInterface::class);
        $responseFactoryMock->method('createResponse')->willReturn($responseMock);
        
        $client = new CurlClient(
            $curlServiceMock,
            $responseFactoryMock,
            $this->createMock(StreamFactoryInterface::class)
        );
        
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getHeaders')->willReturn([]);
        $requestMock->method('getMethod')->willReturn('GET');
        $requestMock->method('getUri')->willReturn('http://foo.bar');
        
        $result = $client->sendRequest($requestMock);
        
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    
    public function testSendRequestThrowsNetworkException()
    {
        $curlServiceMock = $this->createMock(CurlService::class);
        $curlServiceMock->method('execute')->willReturn(false);
        $curlServiceMock->method('getErrno')->willReturn(CURLE_COULDNT_CONNECT);
        $curlServiceMock->method('getError')->willReturn('Could not connect');
        
        $responseFactoryMock = $this->createMock(ResponseFactoryInterface::class);
        
        $client = new CurlClient(
            $curlServiceMock,
            $responseFactoryMock,
            $this->createMock(StreamFactoryInterface::class)
        );
        
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getHeaders')->willReturn([]);
        $requestMock->method('getMethod')->willReturn('GET');
        $requestMock->method('getUri')->willReturn('http://foo.bar');
        
        $this->expectException(NetworkExceptionInterface::class);
        $this->expectExceptionMessage('Could not connect');
        $this->expectExceptionCode(CURLE_COULDNT_CONNECT);
        
        $client->sendRequest($requestMock);
    }
    
    public function testSendRequestThrowsRequestException()
    {
        $curlServiceMock = $this->createMock(CurlService::class);
        $curlServiceMock->method('execute')->willReturn(false);
        $curlServiceMock->method('getErrno')->willReturn(99);
        $curlServiceMock->method('getError')->willReturn('Error message');
        
        $responseFactoryMock = $this->createMock(ResponseFactoryInterface::class);
        
        $client = new CurlClient(
            $curlServiceMock,
            $responseFactoryMock,
            $this->createMock(StreamFactoryInterface::class)
        );
        
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getHeaders')->willReturn([]);
        $requestMock->method('getMethod')->willReturn('GET');
        $requestMock->method('getUri')->willReturn('http://foo.bar');
        
        $this->expectException(RequestExceptionInterface::class);
        $this->expectExceptionMessage('Error message');
        $this->expectExceptionCode(99);
        
        $client->sendRequest($requestMock);
    }
}
