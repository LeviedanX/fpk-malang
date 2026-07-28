<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\AdminActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class LogAdminActivity
{
    public function __construct(private readonly AdminActivityLogger $logger) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $statusCode = match (true) {
                $exception instanceof ValidationException => 422,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };
            $this->write($request, $statusCode);

            throw $exception;
        }

        $this->write($request, $response->getStatusCode());

        return $response;
    }

    private function write(Request $request, int $statusCode): void
    {
        if ($request->isMethodSafe()
            && $statusCode < 400
            && ! $request->routeIs(
                'admin.dashboard',
                'admin.account.edit',
                'admin.account.logs.export',
            )) {
            return;
        }

        $user = $request->user();
        $routeName = (string) $request->route()?->getName();

        $this->logger->log(
            $request,
            $routeName !== '' ? $routeName : 'admin.request',
            $this->logger->descriptionFor($request),
            $user instanceof User ? $user : null,
            min(max($statusCode, 100), 599),
        );
    }
}
