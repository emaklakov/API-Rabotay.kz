<?php
?>
<h3>Информация об ошибке:</h3>
<p><strong>Date:</strong> {{ date('d.m.Y H:i A') }}</p>
<p><strong>IP Address:</strong> {{ $r['ip'] }}</p>
<p><strong>URL:</strong> {{ $r['fullUrl'] }}</p>
<p><strong>Method:</strong> {{ $r['method'] }}</p>
<p><strong>Web Code:</strong> {{ $r['webCode'] }}</p>
<p><strong>Input:</strong> {{ $r['input'] }}</p>
<p><strong>Output:</strong> {{ $r['output'] }}</p>
<p><strong>Message:</strong> {{ $e['messageError'] }}</p>
<p><strong>Code:</strong> {{ $e['codeError'] }}</p>
<p><strong>File:</strong> {{ $e['fileError'] }}</p>
<p><strong>Line:</strong> {{ $e['lineError'] }}</p>
<h3>Stack trace:</h3>
<pre>{{ $e['traceAsStringError'] }}</pre>
