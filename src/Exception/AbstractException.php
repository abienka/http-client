<?php declare(strict_types=1);

namespace Abienka\HttpClient\Exception;

use Psr\Http\Message\RequestInterface;

abstract class AbstractException extends \Exception
{
    /** @var RequestInterface */
    protected $request;
    
    public function __construct(
        RequestInterface $request,
        string $message = '',
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->request = $request;
        
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
