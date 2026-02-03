<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <style>
        :root {
            color-scheme: light dark;
            supported-color-schemes: light dark;
        }

        @media (prefers-color-scheme: dark) {
            body, .wrapper, .body {
                background-color: #0f172a !important;
                color: #cbd5e1 !important;
            }

            .inner-body {
                background-color: #1e293b !important;
                border-color: #334155 !important;
            }

            .header a, .footer p, .footer a {
                color: #94a3b8 !important;
            }

            h1, h2, h3, h4 {
                color: #f1f5f9 !important;
            }

            p, td {
                color: #cbd5e1 !important;
            }

            .movie-card {
                background-color: #334155 !important;
                border-color: #475569 !important;
            }

            .movie-title, .movie-title a {
                color: #f8fafc !important;
            }

            .movie-plot {
                color: #cbd5e1 !important;
            }

            .movie-plot-special {
                color: #cbd5e1 !important;
            }

            .movie-meta {
                color: #94a3b8 !important;
            }

            .footer-text {
                color: #f1f5f9 !important;
            }

            .special-selections-container {
                background-color: #1e293b !important;
                border-color: #334155 !important;
            }

            .special-card {
                background-color: #334155 !important;
            }
        }

        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }

            /* Responsive Special Selections for Mobile */
            .special-card td {
                display: block !important;
                width: 100% !important;
                padding-right: 0 !important;
                padding-left: 0 !important;
            }

            .special-card img {
                width: 100% !important;
                height: auto !important;
                border-radius: 8px 8px 0 0 !important;
            }

            .special-card h3, .special-card h4, .special-card .movie-meta, .special-card .movie-plot-special, .special-card table {
                padding-left: 20px !important;
                padding-right: 20px !important;
                box-sizing: border-box !important;
            }

            .special-card table {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .special-card td[width="90"] {
                width: 100% !important;
                padding-right: 0 !important;
                margin-bottom: 15px !important;
            }

            .special-poster-td, .special-details-td {
                display: block !important;
                width: 100% !important;
                padding-right: 0 !important;
            }

            .special-poster-td img {
                width: 100% !important;
                height: auto !important;
                border-radius: 8px !important;
                margin-bottom: 15px !important;
            }

            .special-header {
                text-align: center !important;
            }

            .movie-poster-td, .movie-details-td {
                display: block !important;
                width: 100% !important;
            }

            .movie-poster-td img {
                width: 100% !important;
                height: auto !important;
                max-height: 200px;
                border-radius: 16px 16px 0 0 !important;
            }

            .movie-actions-table td {
                display: block !important;
                width: 100% !important;
                padding-left: 0 !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
    {!! $head ?? '' !!}
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                {!! $header ?? '' !!}

                <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
                               role="presentation">
                            <!-- Body content -->
                            <tr>
                                <td class="content-cell">
                                    {!! $slot !!}

                                    {!! $subcopy ?? '' !!}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {!! $footer ?? '' !!}
            </table>
        </td>
    </tr>
</table>
</body>
</html>
