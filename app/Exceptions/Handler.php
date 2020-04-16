<?php

namespace App\Exceptions;

use App\Jobs\SendError;
use App\Mail\ErrorMail;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Swift_TransportException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class Handler extends ExceptionHandler
{
	/**
	 * A list of the exception types that are not reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		//
	];

	/**
	 * A list of the inputs that are never flashed for validation exceptions.
	 *
	 * @var array
	 */
	protected $dontFlash = [
		'firebase_user_uid',
	];

	/**
	 * Report or log an exception.
	 *
	 * @param \Exception $exception
	 *
	 * @return void
	 */
	public function report(Exception $exception)
	{
		parent::report($exception);

	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Exception               $exception
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $exception)
	{
		if ($exception instanceof AuthenticationException) {
			return response()->json([
										'message' => 'Requires authentication' // При пустом токине
									], 401);
		}

		if ($exception instanceof AccessDeniedHttpException) {
			return response()->json([
										'message' => $exception->getMessage(),
									], 401);
		}

		if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
			return response()->json([
										'message' => 'Bad credentials' // При неправильном токине
									], 401);
		}

		if ($exception instanceof MethodNotAllowedHttpException) {
			$this->sendLogException($request, $exception, 400);

			return response()->json([
										'message' => $exception->getMessage(),
									], 400);
		}

		if ($exception instanceof ModelNotFoundException) {
			$this->sendLogException($request, $exception, 404);

			return response()->json([
										'message' => 'Not Found',
									], 404);
		}

		if ($exception instanceof NotFoundHttpException) {
			$this->sendLogException($request, $exception, 404);

			return response()->json([
										'message' => 'Not Found',
									], 404);
		}

		if ($exception instanceof RouteNotFoundException) {
			$this->sendLogException($request, $exception, 404);

			return response()->json([
										'message' => 'Not Found',
									], 404);
		}

		if ($exception instanceof ValidationException) {
			return response()->json([
										'message' => $exception->getMessage(),
										'errors' => $exception->errors(),
									], 400);
		}

		if ($exception instanceof Exception) {
			$this->sendLogException($request, $exception, 400);

			return response()->json([
										'message' => $exception->getMessage(),
									], 400);
		}

		return parent::render($request, $exception);
	}

	private function sendLogException($request, $exception, $web_code) {
		$message = (new ErrorMail($request, $exception, $web_code))
			->onQueue('errors')
			->delay(now()->addMinutes(1));

		Mail::to('support@rabotay.kz', 'Rabotay.kz - Support')->queue($message);
	}
}
