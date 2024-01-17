<x-mail::message>
    # Database Approaching Maximum Connection

    The Database Connection **{{$connectionName}}** has **{{ $connections }}** connections.

    Please make sure that the database connection is not reaching its maximum connection limit.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
