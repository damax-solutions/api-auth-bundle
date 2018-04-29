<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Listener;

use Damax\Bundle\ApiAuthBundle\Listener\ExceptionListener;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListenerTest extends TestCase
{
    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var RequestMatcherInterface|MockObject
     */
    private $requestMatcher;

    /**
     * @var ExceptionListener
     */
    private $listener;

    protected function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->requestMatcher = $this->createMock(RequestMatcherInterface::class);
        $this->listener = new ExceptionListener($this->logger, $this->requestMatcher);
    }

    /**
     * @test
     */
    public function it_skips_non_master_request()
    {
        $event = $this->createEvent(HttpKernelInterface::SUB_REQUEST);

        $this->listener->onKernelException($event);

        $this->assertNull($event->getResponse());
        $this->logger
            ->expects($this->never())
            ->method('critical')
        ;
        $this->logger
            ->expects($this->never())
            ->method('error')
        ;
    }

    /**
     * @test
     */
    public function it_skips_non_matched_request()
    {
        $event = $this->createEvent();

        $this->requestMatcher
            ->expects($this->once())
            ->method('matches')
            ->with($this->identicalTo($event->getRequest()))
            ->willReturn(false)
        ;

        $this->listener->onKernelException($event);

        $this->assertNull($event->getResponse());
        $this->logger
            ->expects($this->never())
            ->method('critical')
        ;
        $this->logger
            ->expects($this->never())
            ->method('error')
        ;
    }

    /**
     * @test
     *
     * @dataProvider provideExceptionData
     */
    public function it_converts_exception_to_response(Exception $exception, string $message, int $statusCode, string $logger, int $line)
    {
        $event = $this->createEvent();
        $event->setException($exception);

        $this->requestMatcher
            ->expects($this->once())
            ->method('matches')
            ->with($this->identicalTo($event->getRequest()))
            ->willReturn(true)
        ;

        $error = sprintf('Uncaught PHP Exception %s: "%s" at %s line %s', get_class($exception), $exception->getMessage(), __FILE__, $line);

        $this->logger
            ->expects($this->once())
            ->method($logger)
            ->with($error, $this->identicalTo(['exception' => $exception]))
        ;

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(JsonResponse::class, $event->getResponse());
        $this->assertEquals('{"message":"' . $message . '"}', $event->getResponse()->getContent());
        $this->assertSame($statusCode, $event->getResponse()->getStatusCode());
    }

    private function createEvent(int $requestType = HttpKernelInterface::MASTER_REQUEST): GetResponseForExceptionEvent
    {
        /** @var HttpKernelInterface $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new GetResponseForExceptionEvent($kernel, Request::create(''), $requestType, new Exception());
    }

    public function provideExceptionData(): array
    {
        return [
            [new BadRequestHttpException('Invalid request'), 'Invalid request', Response::HTTP_BAD_REQUEST, 'error', __LINE__],
            [new BadRequestHttpException(), 'Bad Request', Response::HTTP_BAD_REQUEST, 'error', __LINE__],
            [new MethodNotAllowedHttpException(['GET', 'POST']), 'Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED, 'error', __LINE__],
            [new NotFoundHttpException(), 'Not Found', Response::HTTP_NOT_FOUND, 'error', __LINE__],
            [new ServiceUnavailableHttpException(), 'Service Unavailable', Response::HTTP_SERVICE_UNAVAILABLE, 'critical', __LINE__],
            [new RuntimeException('Application error.'), 'Application error.', Response::HTTP_INTERNAL_SERVER_ERROR, 'critical', __LINE__],
        ];
    }
}
