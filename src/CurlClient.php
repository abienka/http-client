<?php declare(strict_types=1);

namespace Abienka\HttpClient;

use Abienka\HttpClient\Exception\ClientException;
use Abienka\HttpClient\Exception\NetworkException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class CurlClient implements ClientInterface
{
    /** @var ResponseFactoryInterface */
    private $responseFactory;
    
    /** @var StreamFactoryInterface */
    private $streamFactory;
    
    /** @var array */
    private $options;
    
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        array $options = []
    ) {
        if (!extension_loaded('curl')) {
            throw new \Exception('The cURL extension is required to use the Abienka\HttpClient\CurlClient.');
        };
        
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
        $curl = curl_init();
        if (false === $curl) {
            throw new ClientException('Unable to initialize a cURL session.');
        }
        
        $this->setCurlOptions($curl, $request);
        
        $curlResponse = curl_exec($curl); 

        if (false === $curlResponse) {
            throw new NetworkException($request, curl_error($curl), curl_errno($curl));
        }
        
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = substr($curlResponse, 0, $headerSize);
        $body = substr($curlResponse, $headerSize);
        
        $response = $this->responseFactory->createResponse(curl_getinfo($curl, CURLINFO_RESPONSE_CODE));
        $response = $this->addHeadersToResponse($response, $headers);
        $response = $response->withBody($this->streamFactory->createStream($body));
        
        curl_close($curl);
        
        return $response;
    }
    
    /**
     * @param resource $curl
     * @param RequestInterface $request
     * @return void
     * @throws ClientException
     */
    protected function setCurlOptions($curl, RequestInterface $request): void
    {
        foreach ($this->getOptions($request) as $option => $value) {
            if (null === $value) {
                continue;
            }
            
            if(!curl_setopt($curl, $option, $value)) {
                throw new ClientException(
                    sprintf('An error occurred while try to set cURL option %s => %s.',
                    $option,
                    $value
                ));
            }
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
     * @param string $headers
     * @return ResponseInterface
     */
    protected function addHeadersToResponse(ResponseInterface $response, string $headers): ResponseInterface
    {
        foreach (explode("\n", $headers) as $header) {
            $colonPosition = strpos($header, ':');
            if (false === $colonPosition || 0 === $colonPosition) {
                continue;
            }
            
            [$name, $value] = explode(':', $header, 2);
            
            $response = $response->withAddedHeader(trim($name), trim($value));
        }
        
        return $response;
    }
}
