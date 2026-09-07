<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $data->type === 'weekly' ? 'Weekly' : 'Monthly' }} Admin Digest</title>
</head>
<body
    style="margin:0;padding:0;background:#f5f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f7;padding:40px 16px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">

                <!-- Logo -->
                <tr>
                    <td align="center" style="padding-bottom:32px;">
                        <img src="{{ config('app.url') }}/icons/ios/64.png" alt="Movie Guru" width="48" height="48"
                             style="border-radius:12px;display:block;margin:0 auto 12px;"/>
                        <span
                            style="font-size:17px;font-weight:700;color:#1d1d1f;letter-spacing:-0.3px;">Movie Guru</span>
                    </td>
                </tr>

                <!-- Hero card -->
                <tr>
                    <td style="background:#ffffff;border-radius:20px 20px 0 0;padding:40px 40px 32px;box-shadow:0 2px 20px rgba(0,0,0,0.06);">
                        <p style="margin:0 0 8px;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">
                            {{ $data->type === 'weekly' ? 'Weekly Digest' : 'Monthly Digest' }}</p>
                        <h1 style="margin:0 0 12px;font-size:26px;font-weight:700;color:#1d1d1f;letter-spacing:-0.5px;line-height:1.2;">
                            Admin Activity Summary
                        </h1>
                        <p style="margin:0;font-size:15px;color:#515154;line-height:1.6;">
                            {{ $data->periodStart->toFormattedDateString() }} - {{ $data->periodEnd->toFormattedDateString() }}
                        </p>
                    </td>
                </tr>

                <!-- Counts -->
                <tr>
                    <td style="background:#ffffff;padding:0 40px 8px;">
                        <p style="margin:0 0 16px;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">
                            Activity</p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#ffffff;padding:0 40px 24px;">
                        <table width="100%" cellpadding="0" cellspacing="0"
                               style="border:1px solid #e8e8ed;border-radius:16px;overflow:hidden;">
                            @php
                                $rows = [
                                    'New Signups' => $data->newSignupsCount,
                                    'Newsletter Subscribes' => $data->newsletterSubscribesCount,
                                    'Newsletter Unsubscribes' => $data->newsletterUnsubscribesCount,
                                    'Contact Submissions' => $data->contactSubmissionsCount,
                                    'Watchlist Adds' => $data->watchlistAddsCount,
                                    'New Movies Added' => $data->newMoviesCount,
                                    'Error Log Lines' => $data->errorLogCount,
                                ];
                            @endphp
                            @foreach($rows as $label => $value)
                                <tr>
                                    <td style="padding:14px 20px;border-bottom:1px solid #e8e8ed;font-size:14px;color:#515154;">{{ $label }}</td>
                                    <td align="right" style="padding:14px 20px;border-bottom:1px solid #e8e8ed;font-size:14px;font-weight:700;color:#1d1d1f;">{{ $value }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>

                @if(count($data->newSignups) > 0)
                    <!-- New signups list -->
                    <tr>
                        <td style="background:#ffffff;padding:0 40px 8px;">
                            <p style="margin:0 0 16px;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">
                                New Signups</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#ffffff;padding:0 40px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="border:1px solid #e8e8ed;border-radius:16px;overflow:hidden;">
                                @foreach($data->newSignups as $signup)
                                    <tr>
                                        <td style="padding:14px 20px;border-bottom:1px solid #e8e8ed;font-size:14px;color:#1d1d1f;">{{ $signup['name'] }}</td>
                                        <td align="right" style="padding:14px 20px;border-bottom:1px solid #e8e8ed;font-size:14px;color:#86868b;">{{ $signup['email'] }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                @endif

                @if($data->type === 'monthly' && count($data->topSearches) > 0)
                    <!-- Top searches -->
                    <tr>
                        <td style="background:#ffffff;padding:0 40px 8px;">
                            <p style="margin:0 0 16px;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">
                                Top Searches</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#ffffff;border-radius:0 0 20px 20px;padding:0 40px 40px;box-shadow:0 2px 20px rgba(0,0,0,0.06);">
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="border:1px solid #e8e8ed;border-radius:16px;overflow:hidden;">
                                @foreach($data->topSearches as $search)
                                    <tr>
                                        <td style="padding:14px 20px;border-bottom:1px solid #e8e8ed;font-size:14px;color:#1d1d1f;">{{ $search['query'] }}</td>
                                        <td align="right" style="padding:14px 20px;border-bottom:1px solid #e8e8ed;font-size:14px;color:#86868b;">{{ $search['count'] }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                @else
                    <tr>
                        <td style="background:#ffffff;border-radius:0 0 20px 20px;padding:8px 40px 40px;box-shadow:0 2px 20px rgba(0,0,0,0.06);"></td>
                    </tr>
                @endif

                <!-- Footer -->
                <tr>
                    <td align="center" style="padding:28px 0 0;">
                        <p style="margin:0;font-size:12px;color:#86868b;line-height:1.6;">
                            Automated admin digest from Movie Guru.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
