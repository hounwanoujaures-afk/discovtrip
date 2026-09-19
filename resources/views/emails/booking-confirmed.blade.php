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