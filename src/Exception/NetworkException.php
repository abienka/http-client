<?php declare(strict_types=1);

namespace Abienka\HttpClient\Exception;

use Psr\Http\Client\NetworkExceptionInterface;

class NetworkException extends AbstractException implements NetworkExceptionInterface
{
}
