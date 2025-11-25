<h2>🔥 Production Exception Report</h2>

<p><strong>Message:</strong> {{ $data['message'] }}</p>
<p><strong>File:</strong> {{ $data['file'] }} : {{ $data['line'] }}</p>
<p><strong>Timestamp:</strong> {{ $data['timestamp'] }}</p>

<hr>

<h3>Request</h3>
<p><strong>URL:</strong> {{ $data['url'] }}</p>
<p><strong>Method:</strong> {{ $data['method'] }}</p>

<p><strong>Payload:</strong></p>
<pre>{{ json_encode($data['payload'], JSON_PRETTY_PRINT) }}</pre>

@if($data['user'])
    <p><strong>User ID:</strong> {{ $data['user'] }}</p>
@endif

<hr>

<h3>Stack Trace</h3>
<pre style="white-space: pre-wrap;">{{ $data['trace'] }}</pre>
