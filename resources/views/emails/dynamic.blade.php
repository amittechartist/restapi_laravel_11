@component('mail::message')
# {{ $emailData['subject'] }}

{{ $emailData['body'] }}

Thanks,<br>
{{ config('mail.from.name') }}
@endcomponent