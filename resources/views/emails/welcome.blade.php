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