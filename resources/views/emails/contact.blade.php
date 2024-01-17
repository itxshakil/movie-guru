<x-mail::message>
# New Contact Message

You received a new message from {{ $contact->name }} ({{ $contact->email }}):

{{ $contact->message }}

Thanks,
{{ config('app.name') }}
</x-mail::message>
