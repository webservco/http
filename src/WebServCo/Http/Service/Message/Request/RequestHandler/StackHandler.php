<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message\Request\RequestHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_shift;

/**
 * Stack handler (queue request handler) (PSR-15).
 *
 * Request handler that also uses middleware.
 */
final class StackHandler implements RequestHandlerInterface
{
    /**
     * Middleware storage.
     *
     * @var array<int,\Psr\Http\Server\MiddlewareInterface> $stack
     */
    private array $stack;

    public function __construct(private RequestHandlerInterface $fallbackHandler)
    {
        $this->stack = [];
    }

    /**
     * Add middleware to the stack
     *
     * Custom method. Should be called right after initialization with each middleware needed for the application.
     */
    public function addMiddleware(MiddlewareInterface $middleware): bool
    {
        $this->stack[] = $middleware;

        return true;
    }

    /**
     * Handle request, return response.
     *
     * PSR-15 interface method.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // If no middleware in stack (not added, or exhausted):
        if ($this->stack === []) {
            // Use the fallback handler (could be a NotFoundHandler, UnderConstructionHandler, etc).
            return $this->fallbackHandler->handle($request);
        }

        /**
         * Extract next middleware from the stack.
         *
         * Returns null if array is empty or not an array, hover that is validated above.
         */
        $nextMiddleware = array_shift($this->stack);

        /**
         * Call middleware to handle the request.
         *
         * Give self as next RequestHandlerInterface,
         * so that if the middlware does not produce a response will call this method again
         * which will call the next middleware and so on,
         * until returning back a ResponseInterface.
         */
        return $nextMiddleware->process($request, $this);
    }
}
