<?php
/**
 * DiscovTrip — Deploy email templates v2
 * HTML 100% inline — compatible Gmail, Outlook, mobile
 */

$base = '/home/u848872852/domains/discovtrip.com/discovtrip_app/resources/views/emails';

// ─── LAYOUT ───────────────────────────────────────────────────────────────────
file_put_contents("$base/layout.blade.php", <<<'HTML'
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
HTML);
echo "layout.blade.php OK\n";

// ─── WELCOME ──────────────────────────────────────────────────────────────────
file_put_contents("$base/welcome.blade.php", <<<'HTML'
<x-emails.layout
    title="Bienvenue sur DiscovTrip"
    preheader="Bienvenue {{ $user->first_name }} ! Votre compte est prêt. Explorez l'Afrique autrement.">

{{-- HERO --}}
<tr><td style="background:#1A1A1A;padding:48px 40px;text-align:center;">
  <p style="margin:0 0 16px;font-size:40px;line-height:1;">🌍</p>
  <h1 style="margin:0 0 10px;font-size:24px;font-weight:700;color:#FFFFFF;line-height:1.3;letter-spacing:-0.3px;">
    Bienvenue, <span style="color:#D4A20F;">{{ $user->first_name }}</span>
  </h1>
  <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.5);line-height:1.6;">
    Votre compte est activé. L'aventure commence maintenant.
  </p>
</td></tr>

{{-- BODY --}}
<tr><td style="background:#FFFFFF;padding:40px 40px 16px;">
  <p style="margin:0 0 8px;font-size:16px;font-weight:600;color:#1A1A1A;">Bonjour {{ $user->first_name }},</p>
  <p style="margin:0 0 32px;font-size:15px;line-height:1.8;color:#555555;">
    Merci de rejoindre DiscovTrip. Vous pouvez dès maintenant explorer nos destinations
    et réserver vos premières expériences authentiques au Bénin et au Togo.
  </p>

  {{-- CARD --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E8E8E8;border-radius:6px;overflow:hidden;margin-bottom:32px;">
    <tr><td style="background:#F7F7F7;padding:12px 20px;border-bottom:1px solid #E8E8E8;">
      <p style="margin:0;font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:#999999;">Ce qui vous attend</p>
    </td></tr>
    <tr><td style="padding:0;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">🏛️&nbsp; Destinations</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">Cotonou, Ganvié, Ouidah…</td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">🎯&nbsp; Guides certifiés</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">100 % authentiques</td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">🛡️&nbsp; Annulation</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">Gratuite jusqu'à 48h</td>
        </tr>
        <tr>
          <td style="padding:14px 20px;font-size:13px;color:#888888;">💳&nbsp; Paiement</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">CB, Mobile Money</td>
        </tr>
      </table>
    </td></tr>
  </table>

  {{-- CTA --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
    <tr><td align="center">
      <a href="{{ route('destinations') }}"
         style="display:inline-block;padding:15px 44px;background:#1A1A1A;color:#FFFFFF;text-decoration:none;border-radius:5px;font-size:14px;font-weight:600;letter-spacing:0.02em;">
        Explorer les destinations
      </a>
    </td></tr>
  </table>

  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #F0F0F0;margin-bottom:0;">
    <tr><td align="center" style="padding-top:24px;padding-bottom:32px;">
      <p style="margin:0;font-size:12px;color:#AAAAAA;">
        Des questions ?&nbsp;
        <a href="{{ route('contact') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Nous contacter</a>
        &nbsp;·&nbsp;
        <a href="{{ route('faq') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">FAQ</a>
      </p>
    </td></tr>
  </table>
</td></tr>

</x-emails.layout>
HTML);
echo "welcome.blade.php OK\n";

// ─── BOOKING CONFIRMATION (paiement sur place) ────────────────────────────────
file_put_contents("$base/booking-confirmation.blade.php", <<<'HTML'
<x-emails.layout
    title="Demande reçue · #{{ $booking->reference }}"
    preheader="Votre demande {{ $booking->offer->title }} a bien été reçue. Paiement sur place le jour J.">

{{-- HERO --}}
<tr><td style="background:#1A1A1A;padding:48px 40px;text-align:center;">
  <p style="margin:0 0 16px;font-size:40px;line-height:1;">📋</p>
  <h1 style="margin:0 0 10px;font-size:24px;font-weight:700;color:#FFFFFF;line-height:1.3;letter-spacing:-0.3px;">
    Demande <span style="color:#D4A20F;">bien reçue</span>
  </h1>
  <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.5);line-height:1.6;">
    Paiement à régler sur place le jour de votre expérience.
  </p>
</td></tr>

{{-- BODY --}}
<tr><td style="background:#FFFFFF;padding:40px 40px 16px;">
  <p style="margin:0 0 8px;font-size:16px;font-weight:600;color:#1A1A1A;">Bonjour {{ $booking->user->first_name }},</p>
  <p style="margin:0 0 32px;font-size:15px;line-height:1.8;color:#555555;">
    Votre demande de réservation a bien été enregistrée.
    Notre équipe va la confirmer sous 24h et vous recevrez un email de confirmation.
  </p>

  {{-- RECAP CARD --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E8E8E8;border-radius:6px;overflow:hidden;margin-bottom:24px;">
    <tr><td style="background:#F7F7F7;padding:12px 20px;border-bottom:1px solid #E8E8E8;">
      <p style="margin:0;font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:#999999;">Récapitulatif</p>
    </td></tr>
    <tr><td style="padding:0;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Référence</td>
          <td style="padding:14px 20px;font-size:13px;text-align:right;">
            <span style="font-family:'Courier New',monospace;font-weight:700;color:#D4A20F;background:#FFF8E6;padding:3px 8px;border-radius:3px;">{{ $booking->reference }}</span>
          </td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Expérience</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ $booking->offer->title }}</td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Destination</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ $booking->offer->city->name ?? 'Bénin' }}</td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Date</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ \Carbon\Carbon::parse($booking->booking_date)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</td>
        </tr>
        @if($booking->booking_time)
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Heure</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ $booking->booking_time }}</td>
        </tr>
        @endif
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Participants</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ $booking->total_participants ?? $booking->participants }} personne(s)</td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Montant à régler</td>
          <td style="padding:14px 20px;font-size:15px;color:#D4A20F;font-weight:700;text-align:right;">{{ number_format($booking->total_price, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Paiement</td>
          <td style="padding:14px 20px;text-align:right;">
            <span style="background:#FFF3CD;color:#7A5C00;padding:3px 10px;border-radius:3px;font-size:11px;font-weight:600;">Sur place</span>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>

  {{-- ALERTS --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:16px;">
    <tr><td style="background:#FFFBF0;border-left:3px solid #D4A20F;border-radius:0 5px 5px 0;padding:14px 18px;">
      <p style="margin:0;font-size:13px;color:#7A5C00;line-height:1.7;">
        <strong>Comment ça marche ?</strong> Notre équipe confirme votre réservation sous 24h. Vous recevrez un email de confirmation dès validation.
      </p>
    </td></tr>
  </table>
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
    <tr><td style="background:#F0FAF4;border-left:3px solid #34A85A;border-radius:0 5px 5px 0;padding:14px 18px;">
      <p style="margin:0;font-size:13px;color:#1E7A45;line-height:1.7;">
        <strong>Annulation gratuite</strong> jusqu'à 48h avant votre expérience, depuis votre espace client.
      </p>
    </td></tr>
  </table>

  {{-- CTA --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
    <tr><td align="center">
      <a href="{{ route('account.bookings') }}"
         style="display:inline-block;padding:15px 44px;background:#1A1A1A;color:#FFFFFF;text-decoration:none;border-radius:5px;font-size:14px;font-weight:600;">
        Voir ma réservation
      </a>
    </td></tr>
  </table>

  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #F0F0F0;">
    <tr><td align="center" style="padding-top:24px;padding-bottom:32px;">
      <p style="margin:0;font-size:12px;color:#AAAAAA;">
        <a href="{{ route('contact') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Nous contacter</a>
        &nbsp;·&nbsp;
        <a href="{{ route('faq') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">FAQ</a>
      </p>
    </td></tr>
  </table>
</td></tr>

</x-emails.layout>
HTML);
echo "booking-confirmation.blade.php OK\n";

// ─── BOOKING CONFIRMED ────────────────────────────────────────────────────────
file_put_contents("$base/booking-confirmed.blade.php", <<<'HTML'
<x-emails.layout
    title="Réservation confirmée · #{{ $booking->reference }}"
    preheader="Votre réservation {{ $booking->offer->title }} est confirmée ! Rendez-vous le {{ \Carbon\Carbon::parse($booking->booking_date)->locale('fr')->isoFormat('D MMMM YYYY') }}.">

{{-- HERO --}}
<tr><td style="background:#1A1A1A;padding:48px 40px;text-align:center;">
  <p style="margin:0 0 16px;font-size:40px;line-height:1;">✅</p>
  <h1 style="margin:0 0 10px;font-size:24px;font-weight:700;color:#FFFFFF;line-height:1.3;letter-spacing:-0.3px;">
    Réservation <span style="color:#D4A20F;">confirmée</span>
  </h1>
  <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.5);line-height:1.6;">
    Tout est prêt. Il ne vous reste qu'à profiter.
  </p>
</td></tr>

{{-- BODY --}}
<tr><td style="background:#FFFFFF;padding:40px 40px 16px;">
  <p style="margin:0 0 8px;font-size:16px;font-weight:600;color:#1A1A1A;">Bonjour {{ $booking->user->first_name }},</p>
  <p style="margin:0 0 32px;font-size:15px;line-height:1.8;color:#555555;">
    Votre réservation est confirmée. Voici le récapitulatif de votre expérience.
  </p>

  {{-- RECAP CARD --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E8E8E8;border-radius:6px;overflow:hidden;margin-bottom:24px;">
    <tr><td style="background:#F7F7F7;padding:12px 20px;border-bottom:1px solid #E8E8E8;">
      <p style="margin:0;font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:#999999;">Récapitulatif de réservation</p>
    </td></tr>
    <tr><td style="padding:0;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Référence</td>
          <td style="padding:14px 20px;font-size:13px;text-align:right;">
            <span style="font-family:'Courier New',monospace;font-weight:700;color:#D4A20F;background:#FFF8E6;padding:3px 8px;border-radius:3px;">{{ $booking->reference }}</span>
          </td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Expérience</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ $booking->offer->title }}</td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Destination</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ $booking->offer->city->name ?? 'Bénin' }}</td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Date</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ $booking->booking_date->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</td>
        </tr>
        @if($booking->booking_time)
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Heure</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ $booking->booking_time }}</td>
        </tr>
        @endif
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Participants</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ $booking->total_participants ?? $booking->participants }} personne(s)</td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Montant total</td>
          <td style="padding:14px 20px;font-size:15px;color:#D4A20F;font-weight:700;text-align:right;">{{ number_format($booking->total_price, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Statut</td>
          <td style="padding:14px 20px;text-align:right;">
            <span style="background:#E8F5EE;color:#1E7A45;padding:3px 10px;border-radius:3px;font-size:11px;font-weight:600;">✓ Confirmée</span>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>

  {{-- ALERT --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
    <tr><td style="background:#FFFBF0;border-left:3px solid #D4A20F;border-radius:0 5px 5px 0;padding:14px 18px;">
      <p style="margin:0;font-size:13px;color:#7A5C00;line-height:1.7;">
        <strong>Annulation gratuite</strong> jusqu'au {{ $booking->booking_date->subHours(48)->locale('fr')->isoFormat('D MMMM [à] HH[h]mm') }}. Passé ce délai, des frais s'appliquent.
      </p>
    </td></tr>
  </table>

  {{-- CTA --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
    <tr><td align="center">
      <a href="{{ route('account.bookings') }}"
         style="display:inline-block;padding:15px 44px;background:#1A1A1A;color:#FFFFFF;text-decoration:none;border-radius:5px;font-size:14px;font-weight:600;">
        Voir ma réservation
      </a>
    </td></tr>
  </table>

  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #F0F0F0;">
    <tr><td align="center" style="padding-top:24px;padding-bottom:32px;">
      <p style="margin:0;font-size:12px;color:#AAAAAA;">
        Besoin de modifier votre réservation ?&nbsp;
        <a href="{{ route('account.bookings') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Espace client</a>
        &nbsp;·&nbsp;
        <a href="{{ route('contact') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Nous contacter</a>
      </p>
    </td></tr>
  </table>
</td></tr>

</x-emails.layout>
HTML);
echo "booking-confirmed.blade.php OK\n";

// ─── BOOKING CANCELLED ────────────────────────────────────────────────────────
file_put_contents("$base/booking-cancelled.blade.php", <<<'HTML'
<x-emails.layout
    title="Réservation annulée · #{{ $booking->reference }}"
    preheader="Votre réservation {{ $booking->offer->title }} a été annulée.">

{{-- HERO --}}
<tr><td style="background:#1A1A1A;padding:48px 40px;text-align:center;">
  <p style="margin:0 0 16px;font-size:40px;line-height:1;">❌</p>
  <h1 style="margin:0 0 10px;font-size:24px;font-weight:700;color:#FFFFFF;line-height:1.3;letter-spacing:-0.3px;">
    Réservation <span style="color:#E05C5C;">annulée</span>
  </h1>
  <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.5);line-height:1.6;">
    Votre annulation a bien été prise en compte.
  </p>
</td></tr>

{{-- BODY --}}
<tr><td style="background:#FFFFFF;padding:40px 40px 16px;">
  <p style="margin:0 0 8px;font-size:16px;font-weight:600;color:#1A1A1A;">Bonjour {{ $booking->user->first_name }},</p>
  <p style="margin:0 0 32px;font-size:15px;line-height:1.8;color:#555555;">
    Votre réservation a bien été annulée. Nous espérons vous revoir bientôt pour vivre une nouvelle expérience en Afrique de l'Ouest.
  </p>

  {{-- RECAP CARD --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E8E8E8;border-radius:6px;overflow:hidden;margin-bottom:24px;">
    <tr><td style="background:#F7F7F7;padding:12px 20px;border-bottom:1px solid #E8E8E8;">
      <p style="margin:0;font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:#999999;">Réservation annulée</p>
    </td></tr>
    <tr><td style="padding:0;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Référence</td>
          <td style="padding:14px 20px;text-align:right;">
            <span style="font-family:'Courier New',monospace;font-weight:700;color:#D4A20F;background:#FFF8E6;padding:3px 8px;border-radius:3px;">{{ $booking->reference }}</span>
          </td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Expérience</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ $booking->offer->title }}</td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Date prévue</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ \Carbon\Carbon::parse($booking->booking_date)->locale('fr')->isoFormat('D MMMM YYYY') }}</td>
        </tr>
        <tr>
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Statut</td>
          <td style="padding:14px 20px;text-align:right;">
            <span style="background:#FDECEA;color:#B03520;padding:3px 10px;border-radius:3px;font-size:11px;font-weight:600;">Annulée</span>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>

  {{-- CTA --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
    <tr><td align="center">
      <a href="{{ route('offers.index') }}"
         style="display:inline-block;padding:15px 44px;background:#1A1A1A;color:#FFFFFF;text-decoration:none;border-radius:5px;font-size:14px;font-weight:600;">
        Explorer d'autres expériences
      </a>
    </td></tr>
  </table>

  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #F0F0F0;">
    <tr><td align="center" style="padding-top:24px;padding-bottom:32px;">
      <p style="margin:0;font-size:12px;color:#AAAAAA;">
        Des questions sur votre annulation ?&nbsp;
        <a href="{{ route('contact') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Nous contacter</a>
      </p>
    </td></tr>
  </table>
</td></tr>

</x-emails.layout>
HTML);
echo "booking-cancelled.blade.php OK\n";

// ─── BOOKING REMINDER ─────────────────────────────────────────────────────────
file_put_contents("$base/booking-reminder.blade.php", <<<'HTML'
<x-emails.layout
    title="Rappel · Votre expérience demain"
    preheader="Rappel : votre expérience {{ $booking->offer->title }} a lieu demain. Tout est prêt ?">

{{-- HERO --}}
<tr><td style="background:#1A1A1A;padding:48px 40px;text-align:center;">
  <p style="margin:0 0 16px;font-size:40px;line-height:1;">🔔</p>
  <h1 style="margin:0 0 10px;font-size:24px;font-weight:700;color:#FFFFFF;line-height:1.3;letter-spacing:-0.3px;">
    Votre expérience <span style="color:#D4A20F;">demain</span>
  </h1>
  <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.5);line-height:1.6;">
    Tout est prêt ? Voici un rappel de votre réservation.
  </p>
</td></tr>

{{-- BODY --}}
<tr><td style="background:#FFFFFF;padding:40px 40px 16px;">
  <p style="margin:0 0 8px;font-size:16px;font-weight:600;color:#1A1A1A;">Bonjour {{ $booking->user->first_name }},</p>
  <p style="margin:0 0 32px;font-size:15px;line-height:1.8;color:#555555;">
    Votre expérience a lieu <strong style="color:#1A1A1A;">demain</strong>. Voici les informations pour bien préparer votre journée.
  </p>

  {{-- RECAP CARD --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E8E8E8;border-radius:6px;overflow:hidden;margin-bottom:24px;">
    <tr><td style="background:#F7F7F7;padding:12px 20px;border-bottom:1px solid #E8E8E8;">
      <p style="margin:0;font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:#999999;">Votre réservation</p>
    </td></tr>
    <tr><td style="padding:0;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Référence</td>
          <td style="padding:14px 20px;text-align:right;">
            <span style="font-family:'Courier New',monospace;font-weight:700;color:#D4A20F;background:#FFF8E6;padding:3px 8px;border-radius:3px;">{{ $booking->reference }}</span>
          </td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Expérience</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ $booking->offer->title }}</td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Date</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ \Carbon\Carbon::parse($booking->booking_date)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</td>
        </tr>
        @if($booking->booking_time)
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Heure de départ</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ $booking->booking_time }}</td>
        </tr>
        @endif
        <tr>
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Point de rendez-vous</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ $booking->offer->meeting_point ?? 'Communiqué par votre guide' }}</td>
        </tr>
      </table>
    </td></tr>
  </table>

  {{-- ALERT --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
    <tr><td style="background:#F0FAF4;border-left:3px solid #34A85A;border-radius:0 5px 5px 0;padding:14px 18px;">
      <p style="margin:0;font-size:13px;color:#1E7A45;line-height:1.7;">
        <strong>Conseils pratiques :</strong> Prévoyez une tenue légère, de l'eau et un appareil photo. Votre guide sera ponctuel au point de rendez-vous.
      </p>
    </td></tr>
  </table>

  {{-- CTA --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
    <tr><td align="center">
      <a href="{{ route('account.bookings') }}"
         style="display:inline-block;padding:15px 44px;background:#1A1A1A;color:#FFFFFF;text-decoration:none;border-radius:5px;font-size:14px;font-weight:600;">
        Voir ma réservation
      </a>
    </td></tr>
  </table>

  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #F0F0F0;">
    <tr><td align="center" style="padding-top:24px;padding-bottom:32px;">
      <p style="margin:0;font-size:12px;color:#AAAAAA;">
        Un empêchement ?&nbsp;
        <a href="{{ route('account.bookings') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Gérer ma réservation</a>
        &nbsp;·&nbsp;
        <a href="{{ route('contact') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Nous contacter</a>
      </p>
    </td></tr>
  </table>
</td></tr>

</x-emails.layout>
HTML);
echo "booking-reminder.blade.php OK\n";

// ─── BOOKING THANKYOU ─────────────────────────────────────────────────────────
file_put_contents("$base/booking-thankyou.blade.php", <<<'HTML'
<x-emails.layout
    title="Merci pour votre expérience"
    preheader="Nous espérons que {{ $booking->offer->title }} vous a enchanté. Partagez votre avis !">

{{-- HERO --}}
<tr><td style="background:#1A1A1A;padding:48px 40px;text-align:center;">
  <p style="margin:0 0 16px;font-size:40px;line-height:1;">🌟</p>
  <h1 style="margin:0 0 10px;font-size:24px;font-weight:700;color:#FFFFFF;line-height:1.3;letter-spacing:-0.3px;">
    Merci, <span style="color:#D4A20F;">{{ $booking->user->first_name }}</span>
  </h1>
  <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.5);line-height:1.6;">
    Nous espérons que votre expérience vous a enchanté.
  </p>
</td></tr>

{{-- BODY --}}
<tr><td style="background:#FFFFFF;padding:40px 40px 16px;">
  <p style="margin:0 0 8px;font-size:16px;font-weight:600;color:#1A1A1A;">Bonjour {{ $booking->user->first_name }},</p>
  <p style="margin:0 0 32px;font-size:15px;line-height:1.8;color:#555555;">
    Votre expérience <strong style="color:#1A1A1A;">{{ $booking->offer->title }}</strong> est terminée.
    Nous espérons qu'elle vous a laissé de beaux souvenirs et que vous avez découvert
    le meilleur de <strong style="color:#1A1A1A;">{{ $booking->offer->city->name ?? 'notre destination' }}</strong>.
  </p>

  {{-- RECAP CARD --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E8E8E8;border-radius:6px;overflow:hidden;margin-bottom:24px;">
    <tr><td style="background:#F7F7F7;padding:12px 20px;border-bottom:1px solid #E8E8E8;">
      <p style="margin:0;font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:#999999;">Expérience terminée</p>
    </td></tr>
    <tr><td style="padding:0;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Référence</td>
          <td style="padding:14px 20px;text-align:right;">
            <span style="font-family:'Courier New',monospace;font-weight:700;color:#D4A20F;background:#FFF8E6;padding:3px 8px;border-radius:3px;">{{ $booking->reference }}</span>
          </td>
        </tr>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Expérience</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ $booking->offer->title }}</td>
        </tr>
        <tr>
          <td style="padding:14px 20px;font-size:13px;color:#888888;">Date</td>
          <td style="padding:14px 20px;font-size:13px;color:#1A1A1A;font-weight:600;text-align:right;">{{ \Carbon\Carbon::parse($booking->booking_date)->locale('fr')->isoFormat('D MMMM YYYY') }}</td>
        </tr>
      </table>
    </td></tr>
  </table>

  {{-- ALERT --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
    <tr><td style="background:#F0FAF4;border-left:3px solid #34A85A;border-radius:0 5px 5px 0;padding:14px 18px;">
      <p style="margin:0;font-size:13px;color:#1E7A45;line-height:1.7;">
        <strong>Votre avis compte !</strong> Partagez votre expérience pour aider d'autres voyageurs à explorer l'Afrique autrement.
      </p>
    </td></tr>
  </table>

  {{-- CTA PRINCIPAL --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:16px;">
    <tr><td align="center">
      <a href="{{ route('account.bookings') }}"
         style="display:inline-block;padding:15px 44px;background:#D4A20F;color:#FFFFFF;text-decoration:none;border-radius:5px;font-size:14px;font-weight:600;">
        Laisser mon avis
      </a>
    </td></tr>
  </table>

  {{-- CTA SECONDAIRE --}}
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
    <tr><td align="center">
      <a href="{{ route('offers.index') }}"
         style="display:inline-block;padding:13px 36px;border:1.5px solid #1A1A1A;color:#1A1A1A;text-decoration:none;border-radius:5px;font-size:13px;font-weight:600;">
        Explorer d'autres expériences
      </a>
    </td></tr>
  </table>

  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #F0F0F0;">
    <tr><td align="center" style="padding-top:24px;padding-bottom:32px;">
      <p style="margin:0;font-size:12px;color:#AAAAAA;">
        Merci de voyager avec DiscovTrip 🌍 &nbsp;·&nbsp;
        <a href="{{ route('contact') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Nous contacter</a>
      </p>
    </td></tr>
  </table>
</td></tr>

</x-emails.layout>
HTML);
echo "booking-thankyou.blade.php OK\n";

echo "\n✓ Tous les 7 templates déployés avec succès !\n";
