<!DOCTYPE html>
<html lang="fr" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>{{ $title ?? 'DiscovTrip' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#F2F2F2;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;">

@if(isset($preheader))
<div style="display:none;font-size:1px;color:#F2F2F2;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">{{ $preheader }}</div>
@endif

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F2F2F2;padding:40px 0;">
<tr><td align="center" style="padding:0 16px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;">

  {{-- HEADER --}}
  <tr><td style="background:#ffffff;border-radius:8px 8px 0 0;padding:28px 40px;text-align:center;border-bottom:1px solid #E8E8E8;">
    <a href="{{ config('app.url') }}" style="text-decoration:none;">
      <img src="{{ config('app.url') }}/images/logo.png" alt="DiscovTrip" style="height:40px;width:auto;display:block;margin:0 auto;">
    </a>
    <p style="margin:8px 0 0;font-size:10px;color:#BBBBBB;letter-spacing:0.12em;text-transform:uppercase;">Explorer l'Afrique autrement</p>
  </td></tr>

  {{-- CONTENU --}}
  {{ $slot }}

  {{-- FOOTER --}}
  <tr><td style="background:#1A1A1A;border-radius:0 0 8px 8px;padding:32px 40px;text-align:center;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
      <tr><td align="center" style="padding-bottom:16px;">
        <a href="{{ route('home') }}" style="color:rgba(255,255,255,0.4);text-decoration:none;font-size:12px;margin:0 10px;">Accueil</a>
        <a href="{{ route('destinations') }}" style="color:rgba(255,255,255,0.4);text-decoration:none;font-size:12px;margin:0 10px;">Destinations</a>
        <a href="{{ route('offers.index') }}" style="color:rgba(255,255,255,0.4);text-decoration:none;font-size:12px;margin:0 10px;">Expériences</a>
        <a href="{{ route('contact') }}" style="color:rgba(255,255,255,0.4);text-decoration:none;font-size:12px;margin:0 10px;">Contact</a>
      </td></tr>
      <tr><td align="center">
        <p style="margin:0;font-size:11px;color:rgba(255,255,255,0.2);line-height:1.8;">
          © {{ date('Y') }} DiscovTrip · Cotonou, Bénin<br>
          Vous recevez cet email car vous avez un compte sur DiscovTrip.<br>
          <a href="{{ route('account.profile') }}" style="color:rgba(255,255,255,0.3);text-decoration:none;">Gérer mes préférences</a>
        </p>
      </td></tr>
    </table>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>