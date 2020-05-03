<?php declare(strict_types=1);

namespace Abienka\HttpClient\Service;

class CurlService
{
    /** @var resource */
    private $curl;
    
    /** @var int|null */
    private $errno;
    
    /** @var string|null */
    private $error;
    
    /** @var string|null */
    private $response;
    
    /** @var array|null */
    private $responseHeaders;
    
    /** @var string|null */
    private $responseBody;
    
    /**
     * @throws ClientException
     */
    public function __construct()
    {
        $this->curl = curl_init();
        
        if (false === $this->curl) {
            throw new ClientException('Unable to initialize a cURL session.');
        }
    }
    
    public function __destruct()
    {
        curl_close($this->curl);
    }
    
    /**
     * @return void
     */
    public function reset(): void
    {
        curl_reset($this->curl);
        $this->response = null;
        $this->responseHeaders = null;
        $this->responseBody = null;
        $this->error = null;
        $this->errno = null;
    }
    
    /**
     * Execute cURL request
     * 
     * @return bool Returns TRUE on success or FALSE on failure
     */
    public function execute(): bool
    {
        $response = curl_exec($this->curl);
        
        if (is_null($response)) {
            return false;
        }
        
        $this->response = $response;
        
        return true;
    }
    
    /**
     * @param array $options
     * @return void
     */
    public function setOptions(array $options): void
    {
        curl_setopt_array($this->curl, $options);
    }
    
    /**
     * @return string
     */
    public function getError(): string
    {
        if (is_null($this->error)) {
            $this->error = curl_error($this->curl);
        }
        
        return $this->error;
    }
    
    /**
     * @return int
     */
    public function getErrno(): int
    {
        if (is_null($this->errno)) {
            $this->errno = curl_errno($this->handle);
        }
        
        return $this->errno;
    }
    
    /**
     * @param int|null $option
     * @return type
     */
    public function getInfo(?int $option)
    {
        if ($option) {
            return curl_getinfo($this->curl, $option);
        }
        
        return curl_getinfo($this->curl);
    }
    
    /**
     * @return int
     */
    public function getResponseHeaderSize(): int
    {
        return $this->getInfo(CURLINFO_HEADER_SIZE);
    }
    
    /**
     * @return int
     */
    public function getResponseCode(): int
    {
        return $this->getInfo(CURLINFO_RESPONSE_CODE);
    }
    
    /**
     * @return array|null
     */
    public function getResponseHeaders(): ?array
    {
        if (is_null($this->response)) {
            return null;
        }
        
        if (!is_array($this->responseHeaders)) {
            $headers = [];
            
            $headersString = substr($this->response, 0, $this->getResponseHeaderSize());
        
            foreach (explode("\n", $headersString) as $header) {
                $colonPosition = strpos($header, ':');
                if (false === $colonPosition || 0 === $colonPosition) {
                    continue;
                }

                [$name, $value] = explode(':', $header, 2);

                $headers[trim($name)] = trim($value);
            }
            
            $this->responseHeaders = $headers;
        }
        
        return $this->responseHeaders;
    }
    
    /**
     * @return string|null
     */
    public function getResponseBody(): ?string
    {
        if (is_null($this->response)) {
            return null;
        }
        
        if (!is_string($this->responseBody)) {
            $this->responseBody = substr($this->response, $this->getResponseHeaderSize());
        }
        
        return $this->responseBody;
    }
}
