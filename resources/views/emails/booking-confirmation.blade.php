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