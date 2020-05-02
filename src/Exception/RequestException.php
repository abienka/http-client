<?php declare(strict_types=1);

namespace Abienka\HttpClient\Exception;

use Psr\Http\Client\RequestExceptionInterface;

class RequestException extends AbstractException implements RequestExceptionInterface
{
}
