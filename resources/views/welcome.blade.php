<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Rabotay.kz</title>

	<!-- Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">

	<!-- Styles -->
	<style>
		html, body {
			background-color: #10dc60;
			color: #ffffff;
			font-family: 'Roboto', sans-serif;
			font-weight: 400;
			height: 100vh;
			margin: 0;
		}

		.full-height {
			height: 100vh;
		}

		.flex-center {
			align-items: center;
			display: flex;
			justify-content: center;
		}

		.position-ref {
			position: relative;
		}

		.top-right {
			position: absolute;
			right: 10px;
			top: 18px;
		}

		.content {
			text-align: center;
		}

		.title {
			font-size: 84px;
			color: #ffffff;
			font-weight: 700;
			text-shadow: 1px 1px 2px black;
		}

		.title span {
			color: #000000;
		}

		.m-b-md {
			margin-bottom: 30px;
		}

		@media only screen and (max-width: 768px) {
			.title {
				font-size: 65px;
			}
		}
	</style>
</head>
<body>
<div class="flex-center position-ref full-height">
	@if (Route::has('login'))
		<div class="top-right links">
			@auth
				<a href="{{ url('/home') }}">Home</a>
			@else
				<a href="{{ route('login') }}">Login</a>

				@if (Route::has('register'))
					<a href="{{ route('register') }}">Register</a>
				@endif
			@endauth
		</div>
	@endif

	<div class="content">
		<div class="title m-b-md">
			<span>R</span>abotay.kz
		</div>
	</div>
</div>
</body>
</html>
