<?php

$base = '/home/u848872852/domains/discovtrip.com/discovtrip_app/resources/views/emails';

// ─── LAYOUT ───────────────────────────────────────────────────────────────────
file_put_contents("$base/layout.blade.php", '<!DOCTYPE html>
<html lang="fr" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
<title>{{ $title ?? \'DiscovTrip\' }}</title>
<!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
<style>
*,*::before,*::after{box-sizing:border-box}
body,table,td,a{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}
table,td{mso-table-lspace:0pt;mso-table-rspace:0pt}
img{border:0;height:auto;line-height:100%;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic}
body{margin:0;padding:0;background-color:#F4F4F4;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Arial,sans-serif;color:#1a1a1a}
.ew{background-color:#F4F4F4;padding:40px 16px}
.ec{max-width:580px;margin:0 auto;background:#fff;border-radius:4px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.08)}
.eh{background:#fff;padding:24px 40px;border-bottom:1px solid #EFEFEF;text-align:center}
.eh-tag{display:block;font-size:10px;color:#bbb;letter-spacing:0.12em;margin-top:4px;text-transform:uppercase}
.hero{background:#1a1a1a;padding:36px 40px;text-align:center}
.hero-icon{font-size:34px;margin-bottom:12px;display:block}
.hero-title{font-size:22px;font-weight:700;color:#fff;line-height:1.3;margin:0 0 6px;letter-spacing:-0.3px}
.hero-title em{color:#D4A20F;font-style:normal}
.hero-sub{font-size:13px;color:rgba(255,255,255,0.5);line-height:1.6;margin:0}
.eb{padding:36px 40px 32px;background:#fff}
.greeting{font-size:15px;color:#1a1a1a;font-weight:600;margin:0 0 14px}
.ep{font-size:14px;line-height:1.75;color:#555;margin:0 0 16px}
.ep strong{color:#1a1a1a}
.card{background:#FAFAFA;border:1px solid #EBEBEB;border-radius:4px;margin:20px 0;overflow:hidden}
.card-head{font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:#999;padding:12px 20px 10px;border-bottom:1px solid #EBEBEB;background:#F7F7F7}
.row{display:flex;justify-content:space-between;align-items:center;padding:11px 20px;border-bottom:1px solid #F0F0F0;font-size:13px}
.row:last-child{border-bottom:none}
.rl{color:#888}
.rv{color:#1a1a1a;font-weight:600;text-align:right;max-width:62%}
.ref{font-family:\'Courier New\',monospace;font-size:12px;font-weight:700;color:#D4A20F;background:#FFF8E6;padding:2px 7px;border-radius:3px;letter-spacing:0.04em}
.badge-ok{background:#E8F5EE;color:#1E7A45;padding:2px 9px;border-radius:3px;font-size:11px;font-weight:600}
.badge-wait{background:#FFF3CD;color:#7A5C00;padding:2px 9px;border-radius:3px;font-size:11px;font-weight:600}
.badge-cancel{background:#FDECEA;color:#B03520;padding:2px 9px;border-radius:3px;font-size:11px;font-weight:600}
.amount{color:#D4A20F;font-size:15px;font-weight:700}
.alert{border-radius:0 4px 4px 0;padding:12px 16px;margin:18px 0;font-size:13px;line-height:1.65;border-left:3px solid}
.alert-info{background:#F5F5F5;border-color:#CCC;color:#555}
.alert-green{background:#F0FAF4;border-color:#34A85A;color:#1E7A45}
.alert-amber{background:#FFFBF0;border-color:#D4A20F;color:#7A5C00}
.cta-wrap{text-align:center;margin:28px 0}
.cta{display:inline-block;padding:13px 38px;background:#1a1a1a;color:#fff !important;text-decoration:none;border-radius:4px;font-size:14px;font-weight:600;letter-spacing:0.01em}
.cta-gold{display:inline-block;padding:13px 38px;background:#D4A20F;color:#fff !important;text-decoration:none;border-radius:4px;font-size:14px;font-weight:600}
.cta-outline{display:inline-block;padding:11px 32px;border:1.5px solid #1a1a1a;color:#1a1a1a !important;text-decoration:none;border-radius:4px;font-size:13px;font-weight:600}
.divider{height:1px;background:#EFEFEF;margin:24px 0}
.ef{background:#1a1a1a;padding:26px 40px;text-align:center}
.ef-links{margin-bottom:14px}
.ef-links a{color:rgba(255,255,255,0.35);text-decoration:none;font-size:11px;margin:0 9px}
.ef-copy{font-size:10px;color:rgba(255,255,255,0.2);line-height:1.7;margin:0}
.ef-copy a{color:rgba(255,255,255,0.25);text-decoration:none}
@media(max-width:600px){
.eh,.hero,.eb,.ef{padding-left:22px !important;padding-right:22px !important}
.hero-title{font-size:19px !important}
.row{flex-direction:column;align-items:flex-start;gap:2px}
.rv{text-align:left;max-width:100%}
}
</style>
</head>
<body>
@if(isset($preheader))
<div style="display:none;font-size:1px;color:#F4F4F4;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">{{ $preheader }}</div>
@endif
<div class="ew"><div class="ec">

<div class="eh">
  <a href="{{ config(\'app.url\') }}" style="text-decoration:none;">
    <img src="{{ config(\'app.url\') }}/images/logo.png" alt="DiscovTrip" style="height:38px;width:auto;">
  </a>
  <span class="eh-tag">Explorer l\'Afrique autrement</span>
</div>

{{ $slot }}

<div class="ef">
  <div class="ef-links">
    <a href="{{ route(\'home\') }}">Accueil</a>
    <a href="{{ route(\'destinations\') }}">Destinations</a>
    <a href="{{ route(\'offers.index\') }}">Expériences</a>
    <a href="{{ route(\'contact\') }}">Contact</a>
    <a href="{{ route(\'faq\') }}">FAQ</a>
  </div>
  <p class="ef-copy">
    © {{ date(\'Y\') }} DiscovTrip · Cotonou, Bénin<br>
    Vous recevez cet email car vous avez un compte sur DiscovTrip.<br>
    <a href="{{ route(\'account.profile\') }}">Gérer mes préférences</a>
  </p>
</div>

</div></div>
</body>
</html>');
echo "layout.blade.php OK\n";

// ─── WELCOME ──────────────────────────────────────────────────────────────────
file_put_contents("$base/welcome.blade.php", '<x-emails.layout
    title="Bienvenue sur DiscovTrip"
    preheader="Bienvenue {{ $user->first_name }} ! Votre compte est prêt. Explorez l\'Afrique autrement.">

<div class="hero">
  <span class="hero-icon">🌍</span>
  <h1 class="hero-title">Bienvenue, <em>{{ $user->first_name }}</em></h1>
  <p class="hero-sub">Votre compte est activé. L\'aventure commence maintenant.</p>
</div>

<div class="eb">
  <p class="greeting">Bonjour {{ $user->first_name }},</p>
  <p class="ep">Merci de rejoindre DiscovTrip. Explorez dès maintenant nos destinations et réservez vos premières expériences au Bénin et au Togo.</p>

  <div class="card">
    <div class="card-head">Ce qui vous attend</div>
    <div class="row"><span class="rl">Destinations</span><span class="rv">Cotonou, Ganvié, Ouidah, Abomey…</span></div>
    <div class="row"><span class="rl">Guides certifiés</span><span class="rv">Expériences 100 % authentiques</span></div>
    <div class="row"><span class="rl">Annulation gratuite</span><span class="rv">Jusqu\'à 48h avant chaque visite</span></div>
    <div class="row"><span class="rl">Paiement</span><span class="rv">CB, Mobile Money, sur place</span></div>
  </div>

  <div class="cta-wrap">
    <a href="{{ route(\'destinations\') }}" class="cta">Explorer les destinations</a>
  </div>

  <div class="divider"></div>
  <p class="ep" style="font-size:12px;color:#aaa;text-align:center;margin:0;">
    Des questions ?
    <a href="{{ route(\'contact\') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Nous contacter</a>
    &nbsp;·&nbsp;
    <a href="{{ route(\'faq\') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">FAQ</a>
  </p>
</div>

</x-emails.layout>');
echo "welcome.blade.php OK\n";

// ─── BOOKING CONFIRMATION (paiement sur place) ────────────────────────────────
file_put_contents("$base/booking-confirmation.blade.php", '<x-emails.layout
    title="Demande reçue · #{{ $booking->reference }}"
    preheader="Votre demande de réservation {{ $booking->offer->title }} a bien été reçue. Paiement sur place.">

<div class="hero">
  <span class="hero-icon">📋</span>
  <h1 class="hero-title">Demande <em>bien reçue</em></h1>
  <p class="hero-sub">Paiement à régler sur place le jour de votre expérience.</p>
</div>

<div class="eb">
  <p class="greeting">Bonjour {{ $booking->user->first_name }},</p>
  <p class="ep">Votre demande a bien été enregistrée. Notre équipe la confirmera sous 24h.</p>

  <div class="card">
    <div class="card-head">Récapitulatif</div>
    <div class="row"><span class="rl">Référence</span><span class="rv"><span class="ref">{{ $booking->reference }}</span></span></div>
    <div class="row"><span class="rl">Expérience</span><span class="rv">{{ $booking->offer->title }}</span></div>
    <div class="row"><span class="rl">Destination</span><span class="rv">{{ $booking->offer->city->name ?? \'Bénin\' }}</span></div>
    <div class="row"><span class="rl">Date</span><span class="rv">{{ \Carbon\Carbon::parse($booking->booking_date)->locale(\'fr\')->isoFormat(\'dddd D MMMM YYYY\') }}</span></div>
    @if($booking->booking_time)
    <div class="row"><span class="rl">Heure</span><span class="rv">{{ $booking->booking_time }}</span></div>
    @endif
    <div class="row"><span class="rl">Participants</span><span class="rv">{{ $booking->total_participants ?? $booking->participants }} personne(s)</span></div>
    <div class="row"><span class="rl">Montant à régler</span><span class="rv"><span class="amount">{{ number_format($booking->total_price, 0, \',\', \' \') }} FCFA</span></span></div>
    <div class="row"><span class="rl">Paiement</span><span class="rv"><span class="badge-wait">Sur place</span></span></div>
  </div>

  <div class="alert alert-amber">
    <strong>Comment ça marche ?</strong> Vous recevrez un email de confirmation dès validation de votre réservation par notre équipe.
  </div>
  <div class="alert alert-green">
    <strong>Annulation gratuite</strong> jusqu\'à 48h avant votre expérience, depuis votre espace client.
  </div>

  <div class="cta-wrap">
    <a href="{{ route(\'account.bookings\') }}" class="cta">Voir ma réservation</a>
  </div>

  <div class="divider"></div>
  <p class="ep" style="font-size:12px;color:#aaa;text-align:center;margin:0;">
    <a href="{{ route(\'contact\') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Nous contacter</a>
    &nbsp;·&nbsp;
    <a href="{{ route(\'faq\') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">FAQ</a>
  </p>
</div>

</x-emails.layout>');
echo "booking-confirmation.blade.php OK\n";

// ─── BOOKING CONFIRMED (paiement effectué) ────────────────────────────────────
file_put_contents("$base/booking-confirmed.blade.php", '<x-emails.layout
    title="Réservation confirmée · #{{ $booking->reference }}"
    preheader="Votre réservation {{ $booking->offer->title }} est confirmée ! Rendez-vous le {{ \Carbon\Carbon::parse($booking->booking_date)->locale(\'fr\')->isoFormat(\'D MMMM YYYY\') }}.">

<div class="hero">
  <span class="hero-icon">✅</span>
  <h1 class="hero-title">Réservation <em>confirmée</em></h1>
  <p class="hero-sub">Tout est prêt. Il ne vous reste qu\'à profiter.</p>
</div>

<div class="eb">
  <p class="greeting">Bonjour {{ $booking->user->first_name }},</p>
  <p class="ep">Votre réservation est confirmée. Voici le récapitulatif de votre expérience.</p>

  <div class="card">
    <div class="card-head">Récapitulatif de réservation</div>
    <div class="row"><span class="rl">Référence</span><span class="rv"><span class="ref">{{ $booking->reference }}</span></span></div>
    <div class="row"><span class="rl">Expérience</span><span class="rv">{{ $booking->offer->title }}</span></div>
    <div class="row"><span class="rl">Destination</span><span class="rv">{{ $booking->offer->city->name ?? \'Bénin\' }}</span></div>
    <div class="row"><span class="rl">Date</span><span class="rv">{{ $booking->booking_date->locale(\'fr\')->isoFormat(\'dddd D MMMM YYYY\') }}</span></div>
    @if($booking->booking_time)
    <div class="row"><span class="rl">Heure</span><span class="rv">{{ $booking->booking_time }}</span></div>
    @endif
    <div class="row"><span class="rl">Participants</span><span class="rv">{{ $booking->total_participants ?? $booking->participants }} personne(s)</span></div>
    <div class="row"><span class="rl">Montant total</span><span class="rv"><span class="amount">{{ number_format($booking->total_price, 0, \',\', \' \') }} FCFA</span></span></div>
    <div class="row"><span class="rl">Statut</span><span class="rv"><span class="badge-ok">Confirmée</span></span></div>
  </div>

  <div class="alert alert-amber">
    <strong>Annulation gratuite</strong> jusqu\'au {{ $booking->booking_date->subHours(48)->locale(\'fr\')->isoFormat(\'D MMMM [à] HH[h]mm\') }}. Passé ce délai, des frais s\'appliquent.
  </div>

  <div class="cta-wrap">
    <a href="{{ route(\'account.bookings\') }}" class="cta">Voir ma réservation</a>
  </div>

  <div class="divider"></div>
  <p class="ep" style="font-size:12px;color:#aaa;text-align:center;margin:0;">
    Besoin de modifier votre réservation ?
    <a href="{{ route(\'account.bookings\') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Espace client</a>
    &nbsp;·&nbsp;
    <a href="{{ route(\'contact\') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Nous contacter</a>
  </p>
</div>

</x-emails.layout>');
echo "booking-confirmed.blade.php OK\n";

// ─── BOOKING CANCELLED ────────────────────────────────────────────────────────
file_put_contents("$base/booking-cancelled.blade.php", '<x-emails.layout
    title="Réservation annulée · #{{ $booking->reference }}"
    preheader="Votre réservation {{ $booking->offer->title }} a été annulée.">

<div class="hero">
  <span class="hero-icon">❌</span>
  <h1 class="hero-title">Réservation <em style="color:#E05C5C;">annulée</em></h1>
  <p class="hero-sub">Votre annulation a bien été prise en compte.</p>
</div>

<div class="eb">
  <p class="greeting">Bonjour {{ $booking->user->first_name }},</p>
  <p class="ep">Votre réservation a été annulée. Nous espérons vous revoir bientôt.</p>

  <div class="card">
    <div class="card-head">Réservation annulée</div>
    <div class="row"><span class="rl">Référence</span><span class="rv"><span class="ref">{{ $booking->reference }}</span></span></div>
    <div class="row"><span class="rl">Expérience</span><span class="rv">{{ $booking->offer->title }}</span></div>
    <div class="row"><span class="rl">Date prévue</span><span class="rv">{{ \Carbon\Carbon::parse($booking->booking_date)->locale(\'fr\')->isoFormat(\'D MMMM YYYY\') }}</span></div>
    <div class="row"><span class="rl">Statut</span><span class="rv"><span class="badge-cancel">Annulée</span></span></div>
  </div>

  <div class="alert alert-info">
    Nous espérons vous revoir bientôt sur DiscovTrip pour vivre une nouvelle expérience authentique en Afrique de l\'Ouest.
  </div>

  <div class="cta-wrap">
    <a href="{{ route(\'offers.index\') }}" class="cta">Explorer d\'autres expériences</a>
  </div>

  <div class="divider"></div>
  <p class="ep" style="font-size:12px;color:#aaa;text-align:center;margin:0;">
    Des questions ?
    <a href="{{ route(\'contact\') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Nous contacter</a>
  </p>
</div>

</x-emails.layout>');
echo "booking-cancelled.blade.php OK\n";

// ─── BOOKING REMINDER ─────────────────────────────────────────────────────────
file_put_contents("$base/booking-reminder.blade.php", '<x-emails.layout
    title="Rappel · Votre expérience demain"
    preheader="Rappel : votre expérience {{ $booking->offer->title }} a lieu demain. Tout est prêt ?">

<div class="hero">
  <span class="hero-icon">🔔</span>
  <h1 class="hero-title">Votre expérience <em>demain</em></h1>
  <p class="hero-sub">Tout est prêt ? Voici un rappel de votre réservation.</p>
</div>

<div class="eb">
  <p class="greeting">Bonjour {{ $booking->user->first_name }},</p>
  <p class="ep">Votre expérience a lieu <strong>demain</strong>. Voici les informations utiles pour bien préparer votre journée.</p>

  <div class="card">
    <div class="card-head">Votre réservation</div>
    <div class="row"><span class="rl">Référence</span><span class="rv"><span class="ref">{{ $booking->reference }}</span></span></div>
    <div class="row"><span class="rl">Expérience</span><span class="rv">{{ $booking->offer->title }}</span></div>
    <div class="row"><span class="rl">Destination</span><span class="rv">{{ $booking->offer->city->name ?? \'Bénin\' }}</span></div>
    <div class="row"><span class="rl">Date</span><span class="rv">{{ \Carbon\Carbon::parse($booking->booking_date)->locale(\'fr\')->isoFormat(\'dddd D MMMM YYYY\') }}</span></div>
    @if($booking->booking_time)
    <div class="row"><span class="rl">Heure de départ</span><span class="rv">{{ $booking->booking_time }}</span></div>
    @endif
    <div class="row"><span class="rl">Point de rendez-vous</span><span class="rv">{{ $booking->offer->meeting_point ?? \'Communiqué par votre guide\' }}</span></div>
  </div>

  <div class="alert alert-green">
    <strong>Conseils pratiques :</strong> Prévoyez une tenue légère, de l\'eau et un appareil photo. Votre guide sera ponctuel au point de rendez-vous.
  </div>

  <div class="cta-wrap">
    <a href="{{ route(\'account.bookings\') }}" class="cta">Voir ma réservation</a>
  </div>

  <div class="divider"></div>
  <p class="ep" style="font-size:12px;color:#aaa;text-align:center;margin:0;">
    Un empêchement ?
    <a href="{{ route(\'account.bookings\') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Gérer ma réservation</a>
    &nbsp;·&nbsp;
    <a href="{{ route(\'contact\') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Nous contacter</a>
  </p>
</div>

</x-emails.layout>');
echo "booking-reminder.blade.php OK\n";

// ─── BOOKING THANKYOU ─────────────────────────────────────────────────────────
file_put_contents("$base/booking-thankyou.blade.php", '<x-emails.layout
    title="Merci pour votre expérience"
    preheader="Nous espérons que {{ $booking->offer->title }} vous a enchanté. Partagez votre avis !">

<div class="hero">
  <span class="hero-icon">🌟</span>
  <h1 class="hero-title">Merci, <em>{{ $booking->user->first_name }}</em></h1>
  <p class="hero-sub">Nous espérons que votre expérience vous a enchanté.</p>
</div>

<div class="eb">
  <p class="greeting">Bonjour {{ $booking->user->first_name }},</p>
  <p class="ep">
    Votre expérience <strong>{{ $booking->offer->title }}</strong> est terminée.
    Nous espérons qu\'elle vous a laissé de beaux souvenirs et que vous avez découvert
    le meilleur de <strong>{{ $booking->offer->city->name ?? \'notre destination\' }}</strong>.
  </p>

  <div class="card">
    <div class="card-head">Expérience terminée</div>
    <div class="row"><span class="rl">Référence</span><span class="rv"><span class="ref">{{ $booking->reference }}</span></span></div>
    <div class="row"><span class="rl">Expérience</span><span class="rv">{{ $booking->offer->title }}</span></div>
    <div class="row"><span class="rl">Date</span><span class="rv">{{ \Carbon\Carbon::parse($booking->booking_date)->locale(\'fr\')->isoFormat(\'D MMMM YYYY\') }}</span></div>
  </div>

  <div class="alert alert-green">
    <strong>Votre avis compte !</strong> Partagez votre expérience pour aider d\'autres voyageurs à explorer l\'Afrique autrement.
  </div>

  <div class="cta-wrap">
    <a href="{{ route(\'account.bookings\') }}" class="cta-gold">Laisser mon avis</a>
  </div>

  <div class="divider"></div>
  <p class="ep" style="text-align:center;font-size:13px;color:#888;margin:0 0 16px;">Prêt pour une nouvelle aventure ?</p>
  <div class="cta-wrap" style="margin-top:0;">
    <a href="{{ route(\'offers.index\') }}" class="cta-outline">Explorer d\'autres expériences</a>
  </div>

  <div class="divider"></div>
  <p class="ep" style="font-size:12px;color:#aaa;text-align:center;margin:0;">
    Merci de voyager avec DiscovTrip 🌍 &nbsp;·&nbsp;
    <a href="{{ route(\'contact\') }}" style="color:#D4A20F;font-weight:600;text-decoration:none;">Nous contacter</a>
  </p>
</div>

</x-emails.layout>');
echo "booking-thankyou.blade.php OK\n";

echo "\n✓ Tous les templates déployés !\n";
