@php
    $logoUrl = asset('images/logo/pavicon.png');
    $appName = config('app.name', 'LYNERA');
    $tagline = config('app.tagline', 'Smart Tools for Modern Makeup Artists');
    $badge = $badge ?? null;
    $intro = $intro ?? null;
    $outro = $outro ?? null;
    $details = $details ?? [];
    $preheader = $preheader ?? $heading;
    $actionLabel = $actionLabel ?? null;
    $actionUrl = $actionUrl ?? null;
    $securityNote = $securityNote ?? null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heading }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f2ef; font-family:'Segoe UI', Helvetica, Arial, sans-serif; color:#292524;">
    <span style="display:none!important; visibility:hidden; opacity:0; color:transparent; height:0; width:0; overflow:hidden;">{{ $preheader }}</span>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f2ef; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px; width:100%; background-color:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 8px 30px rgba(190,120,120,0.12);">
                    <!-- Accent bar -->
                    <tr><td style="height:6px; background:linear-gradient(90deg,#e11d48,#f59e0b);"></td></tr>

                    <!-- Header -->
                    <tr>
                        <td style="padding:28px 32px 8px 32px;" align="left">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-right:12px;" valign="middle">
                                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="46" height="46" style="display:block; width:46px; height:46px; object-fit:contain;">
                                    </td>
                                    <td valign="middle">
                                        <div style="font-size:20px; font-weight:700; letter-spacing:2px; color:#be123c;">{{ strtoupper($appName) }}</div>
                                        <div style="font-size:12px; color:#a8a29e;">{{ $tagline }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:16px 32px 8px 32px;" align="left">
                            @if($badge)
                                <span style="display:inline-block; background-color:#ffe4e6; color:#be123c; font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase; padding:5px 12px; border-radius:999px; margin-bottom:12px;">{{ $badge }}</span>
                            @endif
                            <h1 style="margin:8px 0 12px 0; font-size:22px; font-weight:700; color:#1c1917;">{{ $heading }}</h1>
                            @if($intro)
                                <p style="margin:0 0 20px 0; font-size:15px; line-height:1.6; color:#57534e;">{!! nl2br(e($intro)) !!}</p>
                            @endif

                            @if(!empty($details))
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#faf7f5; border:1px solid #f1e7e3; border-radius:14px; padding:6px 4px;">
                                    @foreach($details as $label => $value)
                                        <tr>
                                            <td style="padding:10px 18px; font-size:13px; color:#a8a29e; width:38%;" valign="top">{{ $label }}</td>
                                            <td style="padding:10px 18px; font-size:14px; font-weight:600; color:#292524;" valign="top">{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            @if($outro)
                                <p style="margin:20px 0 0 0; font-size:14px; line-height:1.6; color:#57534e;">{!! nl2br(e($outro)) !!}</p>
                            @endif

                            @if($actionLabel && $actionUrl)
                                <table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:24px;">
                                    <tr>
                                        <td style="border-radius:12px; background-color:#be123c;">
                                            <a href="{{ $actionUrl }}" style="display:inline-block; padding:13px 22px; color:#ffffff; font-size:14px; font-weight:700; text-decoration:none; border-radius:12px;">{{ $actionLabel }}</a>
                                        </td>
                                    </tr>
                                </table>
                                <p style="margin:16px 0 0 0; font-size:11px; line-height:1.5; color:#a8a29e; word-break:break-all;">Jika tombol tidak bekerja, buka tautan berikut:<br><a href="{{ $actionUrl }}" style="color:#be123c;">{{ $actionUrl }}</a></p>
                            @endif

                            @if($securityNote)
                                <p style="margin:20px 0 0 0; border-left:3px solid #f59e0b; background-color:#fffbeb; padding:12px 14px; font-size:12px; line-height:1.6; color:#78716c;">{{ $securityNote }}</p>
                            @endif
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:24px 32px 30px 32px;" align="left">
                            <hr style="border:none; border-top:1px solid #f1e7e3; margin:0 0 16px 0;">
                            <p style="margin:0; font-size:12px; line-height:1.6; color:#a8a29e;">
                                Email ini dikirim otomatis oleh {{ $appName }}. Mohon jangan membalas email ini.<br>
                                &copy; {{ date('Y') }} {{ $appName }} — {{ $tagline }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
