New contact form submission

Name: {{ $payload['name'] ?? '' }}
Email: {{ $payload['email'] ?? '' }}
Phone: {{ $payload['phone'] ?? 'N/A' }}
Language: {{ $payload['locale'] ?? 'en' }}
Page: {{ $payload['page_url'] ?? '' }}

Message:
{{ $payload['message'] ?? '' }}