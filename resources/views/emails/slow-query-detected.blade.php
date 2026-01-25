# A Slow Query Occurred

A Slow Query Occurred on **{{ $connectionName }}** that took **{{ number_format($time, 2) }} seconds** to execute.

## Query
```sql
    {!! $query !!}
```

Thanks,<br>
{{ config('app.name') }}
