<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Listener;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $exception = $event->getException();

        $this->logException($exception);

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = Response::$statusTexts[$statusCode] ?? 'Unknown status';
        } else {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = $exception->getMessage();
        }

        $event->setResponse(JsonResponse::create(['message' => $message], $statusCode));
    }

    protected function logException(Exception $exception)
    {
        $pattern = 'Uncaught PHP Exception %s: "%s" at %s line %s';
        $message = sprintf($pattern, get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine());

        if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
            $this->logger->critical($message, ['exception' => $exception]);
        } else {
            $this->logger->error($message, ['exception' => $exception]);
        }
    }
}
