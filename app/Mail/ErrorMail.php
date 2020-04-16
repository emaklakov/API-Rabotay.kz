<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ErrorMail extends Mailable implements ShouldQueue
{
	use Queueable, SerializesModels;

	public $tries = 3;

	protected $request, $exception;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct($request, $exception, $web_code)
	{
		$this->exception = !empty($exception) ? [
			'messageError' => $exception->getMessage(),
			'codeError' => $exception->getCode(),
			'fileError' => $exception->getFile(),
			'lineError' => $exception->getLine(),
			'traceAsStringError' => $exception->getTraceAsString(),
		] : [];

		$this->request = !empty($request) ? [
			'ip' => $request->ip(),
			'fullUrl' => $request->fullUrl(),
			'method' => $request->method(),
			'input' => json_encode($request->all()),
			'output' => $request->getContent(),
			'webCode' => $web_code,
		] : [];
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->from('support@rabotay.kz', 'Rabotay.kz - Support')
			->subject('Error API Rabotay.kz!')
			->view('emails.exception')
			->with(['e' => $this->exception, 'r' => $this->request]);
	}
}
