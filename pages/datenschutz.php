<?php
/**
 * GDPR / Ochrana osobních údajů / Datenschutz / Privacy Policy
 * Content localized inline via $lang; company details pulled from $site_info.
 */
$company = htmlspecialchars($site_info['studio_name']);
$owner   = htmlspecialchars($site_info['owner_name']);
$street  = htmlspecialchars($site_info['street']);
$city    = htmlspecialchars($site_info['city_postal']);
$phone   = htmlspecialchars($site_info['phone']);
$email   = htmlspecialchars($site_info['email']);
$updated = '10. 3. 2026';
?>
<div class="legal-wrap">
  <?php if ($lang === 'cz'): ?>
    <h1 class="legal-title">Ochrana osobních údajů</h1>
    <p class="legal-updated">Aktualizováno: <?= $updated ?></p>

    <section class="legal-section">
      <h2>1. Správce údajů</h2>
      <p>Správcem osobních údajů podle Nařízení Evropského parlamentu a Rady (EU) 2016/679 (dále „GDPR") je:</p>
      <address>
        <?= $company ?><br>
        <?= $owner ?><br>
        <?= $street ?>, <?= $city ?><br>
        Tel.: <a href="tel:<?= preg_replace('/[^\d+]/', '', $site_info['phone']) ?>"><?= $phone ?></a><br>
        E-mail: <a href="mailto:<?= $email ?>"><?= $email ?></a>
      </address>
    </section>

    <section class="legal-section">
      <h2>2. Jaké údaje zpracováváme</h2>
      <ul>
        <li><strong>Identifikační a kontaktní údaje</strong> — jméno, e-mail, telefon, doručovací adresa.</li>
        <li><strong>Údaje o objednávkách</strong> — historie objednávek, částky, způsob doručení a platby.</li>
        <li><strong>Přihlašovací údaje</strong> — e-mail a šifrované heslo (bcrypt).</li>
        <li><strong>Údaje pro rezervaci</strong> — preferovaný termín a důvod návštěvy studia.</li>
        <li><strong>Technické údaje</strong> — IP adresa, cookies, informace o prohlížeči (pouze pro provoz webu a analytiku).</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>3. Účel a právní základ</h2>
      <ul>
        <li><strong>Plnění smlouvy</strong> (čl. 6 odst. 1 písm. b GDPR) — zpracování objednávky, doručení, komunikace.</li>
        <li><strong>Zákonná povinnost</strong> (čl. 6 odst. 1 písm. c GDPR) — účetní a daňové doklady po dobu 10 let.</li>
        <li><strong>Oprávněný zájem</strong> (čl. 6 odst. 1 písm. f GDPR) — zabezpečení webu, prevence podvodů.</li>
        <li><strong>Souhlas</strong> (čl. 6 odst. 1 písm. a GDPR) — marketingová komunikace, nepovinné cookies.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>4. Doba uchovávání</h2>
      <p>Údaje uchováváme pouze po dobu nezbytnou pro daný účel:</p>
      <ul>
        <li>Údaje z objednávek — 10 let (zákonná lhůta).</li>
        <li>Registrovaný účet — do zrušení účtu.</li>
        <li>Rezervace — 12 měsíců od termínu.</li>
        <li>Cookies — dle nastavení prohlížeče, maximálně 12 měsíců.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>5. Předání třetím stranám</h2>
      <p>Údaje předáváme pouze subjektům nezbytným pro splnění služby:</p>
      <ul>
        <li>Přepravce Balíkovna (doručení zásilky).</li>
        <li>Poskytovatel hostingu (Wedos Internet, a.s.).</li>
        <li>Účetní kancelář (zákonné povinnosti).</li>
        <li>Google Analytics — anonymizovaná statistika návštěvnosti.</li>
      </ul>
      <p>Údaje nepředáváme mimo Evropský hospodářský prostor.</p>
    </section>

    <section class="legal-section">
      <h2>6. Vaše práva</h2>
      <p>Podle GDPR máte právo:</p>
      <ul>
        <li>na přístup k údajům a jejich kopii,</li>
        <li>na opravu nepřesných údajů,</li>
        <li>na výmaz (právo být zapomenut),</li>
        <li>na omezení zpracování,</li>
        <li>na přenositelnost údajů,</li>
        <li>vznést námitku proti zpracování,</li>
        <li>odvolat souhlas kdykoli bez vlivu na předchozí zpracování,</li>
        <li>podat stížnost u Úřadu pro ochranu osobních údajů (<a href="https://www.uoou.cz" target="_blank" rel="noopener">www.uoou.cz</a>).</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>7. Cookies</h2>
      <p>Web používá technické cookies nezbytné pro provoz (přihlášení, košík) a analytické cookies (Google Analytics) pro měření návštěvnosti. Souhlas s nepovinnými cookies můžete kdykoli odvolat v nastavení prohlížeče.</p>
    </section>

    <section class="legal-section">
      <h2>8. Kontakt</h2>
      <p>Ve věcech ochrany osobních údajů nás kontaktujte na <a href="mailto:<?= $email ?>"><?= $email ?></a>.</p>
    </section>

  <?php elseif ($lang === 'en'): ?>
    <h1 class="legal-title">Privacy Policy</h1>
    <p class="legal-updated">Updated: <?= $updated ?></p>

    <section class="legal-section">
      <h2>1. Data controller</h2>
      <p>The data controller under Regulation (EU) 2016/679 (GDPR) is:</p>
      <address>
        <?= $company ?><br>
        <?= $owner ?><br>
        <?= $street ?>, <?= $city ?><br>
        Phone: <a href="tel:<?= preg_replace('/[^\d+]/', '', $site_info['phone']) ?>"><?= $phone ?></a><br>
        Email: <a href="mailto:<?= $email ?>"><?= $email ?></a>
      </address>
    </section>

    <section class="legal-section">
      <h2>2. Data we process</h2>
      <ul>
        <li><strong>Identification and contact data</strong> — name, email, phone, shipping address.</li>
        <li><strong>Order data</strong> — order history, amounts, delivery and payment method.</li>
        <li><strong>Account credentials</strong> — email and hashed password (bcrypt).</li>
        <li><strong>Booking data</strong> — preferred date and reason for the studio visit.</li>
        <li><strong>Technical data</strong> — IP address, cookies, browser info (for site operation and analytics only).</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>3. Purpose and legal basis</h2>
      <ul>
        <li><strong>Contract performance</strong> (Art. 6(1)(b) GDPR) — order processing, delivery, communication.</li>
        <li><strong>Legal obligation</strong> (Art. 6(1)(c) GDPR) — accounting and tax records for 10 years.</li>
        <li><strong>Legitimate interest</strong> (Art. 6(1)(f) GDPR) — site security, fraud prevention.</li>
        <li><strong>Consent</strong> (Art. 6(1)(a) GDPR) — marketing communication, optional cookies.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>4. Retention period</h2>
      <ul>
        <li>Order data — 10 years (statutory).</li>
        <li>Registered account — until account deletion.</li>
        <li>Bookings — 12 months from the appointment date.</li>
        <li>Cookies — per browser settings, max 12 months.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>5. Third-party disclosure</h2>
      <p>We share data only with parties necessary for service delivery:</p>
      <ul>
        <li>Balíkovna carrier (shipment delivery).</li>
        <li>Hosting provider (Wedos Internet, a.s.).</li>
        <li>Accounting firm (statutory obligations).</li>
        <li>Google Analytics — anonymized visit statistics.</li>
      </ul>
      <p>We do not transfer data outside the European Economic Area.</p>
    </section>

    <section class="legal-section">
      <h2>6. Your rights</h2>
      <p>Under GDPR you have the right to:</p>
      <ul>
        <li>access your data and receive a copy,</li>
        <li>rectify inaccurate data,</li>
        <li>erasure (right to be forgotten),</li>
        <li>restrict processing,</li>
        <li>data portability,</li>
        <li>object to processing,</li>
        <li>withdraw consent at any time without affecting prior processing,</li>
        <li>lodge a complaint with the Czech Data Protection Authority (<a href="https://www.uoou.cz/en" target="_blank" rel="noopener">www.uoou.cz</a>).</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>7. Cookies</h2>
      <p>The site uses technical cookies required for operation (login, cart) and analytical cookies (Google Analytics) for traffic measurement. You may withdraw consent to optional cookies via your browser settings at any time.</p>
    </section>

    <section class="legal-section">
      <h2>8. Contact</h2>
      <p>For privacy matters contact us at <a href="mailto:<?= $email ?>"><?= $email ?></a>.</p>
    </section>

  <?php else: /* at — Deutsch */ ?>
    <h1 class="legal-title">Datenschutzerklärung</h1>
    <p class="legal-updated">Aktualisiert: <?= $updated ?></p>

    <section class="legal-section">
      <h2>1. Verantwortlicher</h2>
      <p>Verantwortlicher im Sinne der Datenschutz-Grundverordnung (DSGVO) ist:</p>
      <address>
        <?= $company ?><br>
        <?= $owner ?><br>
        <?= $street ?>, <?= $city ?><br>
        Tel.: <a href="tel:<?= preg_replace('/[^\d+]/', '', $site_info['phone']) ?>"><?= $phone ?></a><br>
        E-Mail: <a href="mailto:<?= $email ?>"><?= $email ?></a>
      </address>
    </section>

    <section class="legal-section">
      <h2>2. Verarbeitete Daten</h2>
      <ul>
        <li><strong>Identifikations- und Kontaktdaten</strong> — Name, E-Mail, Telefon, Lieferadresse.</li>
        <li><strong>Bestelldaten</strong> — Bestellhistorie, Beträge, Liefer- und Zahlungsart.</li>
        <li><strong>Kontodaten</strong> — E-Mail und verschlüsseltes Passwort (bcrypt).</li>
        <li><strong>Termindaten</strong> — Wunschtermin und Anlass des Studiobesuchs.</li>
        <li><strong>Technische Daten</strong> — IP-Adresse, Cookies, Browserinformationen (nur für Betrieb und Analyse).</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>3. Zweck und Rechtsgrundlage</h2>
      <ul>
        <li><strong>Vertragserfüllung</strong> (Art. 6 Abs. 1 lit. b DSGVO) — Bestellabwicklung, Lieferung, Kommunikation.</li>
        <li><strong>Rechtliche Verpflichtung</strong> (Art. 6 Abs. 1 lit. c DSGVO) — Buchhaltung und Steuerunterlagen, 10 Jahre.</li>
        <li><strong>Berechtigtes Interesse</strong> (Art. 6 Abs. 1 lit. f DSGVO) — Website-Sicherheit, Betrugsprävention.</li>
        <li><strong>Einwilligung</strong> (Art. 6 Abs. 1 lit. a DSGVO) — Marketingkommunikation, optionale Cookies.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>4. Speicherdauer</h2>
      <ul>
        <li>Bestelldaten — 10 Jahre (gesetzlich).</li>
        <li>Registriertes Konto — bis zur Kontolöschung.</li>
        <li>Termine — 12 Monate nach dem Termin.</li>
        <li>Cookies — laut Browser-Einstellungen, max. 12 Monate.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>5. Weitergabe an Dritte</h2>
      <p>Wir geben Daten nur an für die Leistungserbringung notwendige Stellen weiter:</p>
      <ul>
        <li>Versanddienstleister Balíkovna (Zustellung).</li>
        <li>Hosting-Anbieter (Wedos Internet, a.s.).</li>
        <li>Steuerberatung (gesetzliche Pflichten).</li>
        <li>Google Analytics — anonymisierte Besuchsstatistik.</li>
      </ul>
      <p>Eine Übermittlung außerhalb des EWR erfolgt nicht.</p>
    </section>

    <section class="legal-section">
      <h2>6. Ihre Rechte</h2>
      <p>Nach der DSGVO haben Sie das Recht auf:</p>
      <ul>
        <li>Auskunft und Kopie Ihrer Daten,</li>
        <li>Berichtigung unrichtiger Daten,</li>
        <li>Löschung (Recht auf Vergessenwerden),</li>
        <li>Einschränkung der Verarbeitung,</li>
        <li>Datenübertragbarkeit,</li>
        <li>Widerspruch gegen die Verarbeitung,</li>
        <li>Widerruf der Einwilligung jederzeit, ohne die Rechtmäßigkeit früherer Verarbeitung zu berühren,</li>
        <li>Beschwerde bei der Aufsichtsbehörde (<a href="https://www.uoou.cz" target="_blank" rel="noopener">www.uoou.cz</a>).</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>7. Cookies</h2>
      <p>Die Website verwendet technisch notwendige Cookies (Login, Warenkorb) und Analyse-Cookies (Google Analytics) zur Reichweitenmessung. Die Einwilligung zu optionalen Cookies können Sie jederzeit in den Browser-Einstellungen widerrufen.</p>
    </section>

    <section class="legal-section">
      <h2>8. Kontakt</h2>
      <p>Für Datenschutzanfragen kontaktieren Sie uns unter <a href="mailto:<?= $email ?>"><?= $email ?></a>.</p>
    </section>
  <?php endif; ?>
</div>
