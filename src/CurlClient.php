<?php declare(strict_types=1);

namespace Abienka\HttpClient;

use Abienka\HttpClient\Exception\ClientException;
use Abienka\HttpClient\Exception\NetworkException;
use Abienka\HttpClient\Exception\RequestException;
use Abienka\HttpClient\Service\CurlService;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class CurlClient implements ClientInterface
{
    /** @var CurlService */
    private $curlService;
    
    /** @var ResponseFactoryInterface */
    private $responseFactory;
    
    /** @var StreamFactoryInterface */
    private $streamFactory;
    
    /** @var array */
    private $options;
    
    /**
     * @param CurlService $curlService
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface $streamFactory
     * @param array $options
     */
    public function __construct(
        CurlService $curlService,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        array $options = []
    ) {
        $this->curlService = $curlService;
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->options = $options;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ClientException
     * @throws NetworkException
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->curlService->reset();
        
        $this->curlService->setOptions($this->getOptions($request));
        
        $result = $this->curlService->execute();

        if (false === $result) {
            $this->handleCurlError($request);
        }
        
        $response = $this->responseFactory->createResponse($this->curlService->getResponseCode());
        $response = $this->addHeadersToResponse($response, $this->curlService->getResponseHeaders());
        $response = $response->withBody($this->streamFactory->createStream($this->curlService->getResponseBody()));
        
        return $response;
    }

    /**
     * @param RequestInterface $request
     * @return void
     * @throws NetworkException
     * @throws RequestException
     */
    protected function handleCurlError(RequestInterface $request): void
    {
        switch ($this->curlService->getErrno()) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_COULDNT_RESOLVE_PROXY:
            case CURLE_OPERATION_TIMEOUTED:
            case CURLE_SSL_CONNECT_ERROR:
                throw new NetworkException(
                    $request,
                    $this->curlService->getError(),
                    $this->curlService->getErrno()
                );
            default:
                throw new RequestException(
                    $request,
                    $this->curlService->getError(),
                    $this->curlService->getErrno()
                );
        }
    }
    
    /**
     * @param RequestInterface $request
     * @return array
     */
    protected function getOptions(RequestInterface $request): array
    {
        $options = $this->options;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_HEADER] = true;
        $options[CURLOPT_CUSTOMREQUEST] = $request->getMethod();
        $options[CURLOPT_URL] = (string) $request->getUri();
        $options[CURLOPT_POSTFIELDS] = (string) $request->getBody();

        foreach ($request->getHeaders() as $name => $value) {
            $options[CURLOPT_HTTPHEADER][] = $name . ': ' . implode(', ', $value);
        }
        
        return $options;
    }
    
    /**
     * @param ResponseInterface $response
     * @param array $headers
     * @return ResponseInterface
     */
    protected function addHeadersToResponse(ResponseInterface $response, array $headers): ResponseInterface
    {
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        
        return $response;
    }
}
