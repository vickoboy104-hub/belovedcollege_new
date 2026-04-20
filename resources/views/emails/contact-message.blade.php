<h2>New Website Contact Message</h2>

<p><strong>Name:</strong> {{ $payload['name'] }}</p>
<p><strong>Email:</strong> {{ $payload['email'] }}</p>
<p><strong>Phone:</strong> {{ $payload['phone'] ?: 'Not provided' }}</p>
<p><strong>Subject:</strong> {{ $payload['subject'] }}</p>

<hr>

<p>{!! nl2br(e($payload['message'])) !!}</p>
