<?php
/**
 * Smluvní podmínky / AGB / Terms & Conditions
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
    <h1 class="legal-title">Smluvní podmínky</h1>
    <p class="legal-updated">Účinnost: <?= $updated ?></p>

    <section class="legal-section">
      <h2>1. Prodávající</h2>
      <address>
        <?= $company ?><br>
        <?= $owner ?><br>
        <?= $street ?>, <?= $city ?><br>
        Tel.: <a href="tel:<?= preg_replace('/[^\d+]/', '', $site_info['phone']) ?>"><?= $phone ?></a><br>
        E-mail: <a href="mailto:<?= $email ?>"><?= $email ?></a>
      </address>
      <p>(dále jen „prodávající")</p>
    </section>

    <section class="legal-section">
      <h2>2. Úvodní ustanovení</h2>
      <p>Tyto smluvní podmínky (dále „Podmínky") upravují vzájemná práva a povinnosti prodávajícího a kupujícího vzniklé v souvislosti s kupní smlouvou uzavřenou prostřednictvím internetového obchodu <strong>www.expertoptic.eu</strong> podle § 1751 odst. 1 zákona č. 89/2012 Sb., občanský zákoník, a zákona č. 634/1992 Sb., o ochraně spotřebitele.</p>
    </section>

    <section class="legal-section">
      <h2>3. Objednávka a uzavření kupní smlouvy</h2>
      <p>Kupující vybírá zboží vložením do košíku a dokončuje objednávku vyplněním kontaktních a doručovacích údajů. Kupní smlouva je uzavřena okamžikem potvrzení objednávky prodávajícím na uvedený e-mail. Kupující objednávkou potvrzuje, že se seznámil s těmito Podmínkami.</p>
    </section>

    <section class="legal-section">
      <h2>4. Cena a platba</h2>
      <ul>
        <li>Ceny jsou uvedeny v eurech (EUR) nebo českých korunách (CZK) včetně DPH.</li>
        <li>Ceny jsou platné v okamžiku odeslání objednávky.</li>
        <li>Prodávající přijímá platby: bankovním převodem předem, hotově při osobním odběru, dobírkou u Balíkovny.</li>
        <li>Bezhotovostní platba musí být připsána na účet do 7 dnů od objednávky, jinak může být objednávka zrušena.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>5. Doprava a dodání</h2>
      <ul>
        <li><strong>Osobní odběr</strong> na prodejně — zdarma.</li>
        <li><strong>Balíkovna – výdejní místo</strong> — cena dle sazebníku (aktuálně 89 Kč / cca 3,60 €).</li>
        <li><strong>Balíkovna na adresu</strong> — cena dle sazebníku (aktuálně 119 Kč / cca 4,80 €).</li>
        <li>Dodací lhůta zpravidla 3–7 pracovních dní od potvrzení platby.</li>
        <li>Nebezpečí škody na zboží přechází na kupujícího převzetím.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>6. Odstoupení od smlouvy (14 dnů)</h2>
      <p>Spotřebitel má právo odstoupit od kupní smlouvy bez udání důvodu do <strong>14 dnů</strong> od převzetí zboží (§ 1829 obč. zák.). Odstoupení zašle e-mailem na <a href="mailto:<?= $email ?>"><?= $email ?></a> a zboží doručí zpět na adresu prodávajícího nepoškozené, nejlépe v originálním obalu. Kupní cena bude vrácena do 14 dnů od doručení zboží stejným způsobem, jakým byla přijata.</p>
      <p><strong>Právo na odstoupení se nevztahuje</strong> na zboží upravené podle přání kupujícího (dioptrická skla broušená na míru, individuální zakázky).</p>
    </section>

    <section class="legal-section">
      <h2>7. Reklamace a záruka</h2>
      <p>Prodávající odpovídá za vady zboží podle § 2161 obč. zák. Zákonná záruční doba pro spotřebitele je <strong>24 měsíců</strong>. Reklamaci uplatňuje kupující písemně nebo osobně u prodávajícího a musí obsahovat popis vady a doklad o koupi. Reklamace bude vyřízena bez zbytečného odkladu, nejpozději do 30 dnů.</p>
    </section>

    <section class="legal-section">
      <h2>8. Poukazy (vouchery)</h2>
      <ul>
        <li>Poukaz lze uplatnit pouze na produkty v e-shopu, není-li uvedeno jinak.</li>
        <li>Každý poukaz má stanovenou dobu platnosti a minimální hodnotu objednávky.</li>
        <li>Poukaz nelze směnit za hotovost.</li>
        <li>V jedné objednávce lze uplatnit pouze jeden poukaz.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>9. Ochrana osobních údajů</h2>
      <p>Zpracování osobních údajů se řídí <a href="?p=datenschutz&amp;lang=cz">Prohlášením o ochraně osobních údajů</a>.</p>
    </section>

    <section class="legal-section">
      <h2>10. Mimosoudní řešení sporů</h2>
      <p>K mimosoudnímu řešení spotřebitelských sporů je příslušná Česká obchodní inspekce (<a href="https://www.coi.cz" target="_blank" rel="noopener">www.coi.cz</a>). Spotřebitel může využít i platformu <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">ec.europa.eu/consumers/odr</a>.</p>
    </section>

    <section class="legal-section">
      <h2>11. Závěrečná ustanovení</h2>
      <p>Podmínky se řídí právním řádem České republiky. Prodávající si vyhrazuje právo Podmínky změnit; nové znění se vztahuje pouze na objednávky uzavřené po jeho zveřejnění.</p>
    </section>

  <?php elseif ($lang === 'en'): ?>
    <h1 class="legal-title">Terms &amp; Conditions</h1>
    <p class="legal-updated">Effective: <?= $updated ?></p>

    <section class="legal-section">
      <h2>1. Seller</h2>
      <address>
        <?= $company ?><br>
        <?= $owner ?><br>
        <?= $street ?>, <?= $city ?><br>
        Phone: <a href="tel:<?= preg_replace('/[^\d+]/', '', $site_info['phone']) ?>"><?= $phone ?></a><br>
        Email: <a href="mailto:<?= $email ?>"><?= $email ?></a>
      </address>
      <p>(the "Seller")</p>
    </section>

    <section class="legal-section">
      <h2>2. Introductory provisions</h2>
      <p>These Terms &amp; Conditions govern the rights and obligations between the Seller and the Buyer arising from a purchase contract concluded through the online shop <strong>www.expertoptic.eu</strong>, in accordance with Czech Civil Code (Act No. 89/2012 Coll., §&nbsp;1751(1)) and the Consumer Protection Act (Act No. 634/1992 Coll.).</p>
    </section>

    <section class="legal-section">
      <h2>3. Order and purchase contract</h2>
      <p>The Buyer selects goods by adding them to the cart and completes the order by providing contact and delivery details. The purchase contract is concluded upon the Seller's confirmation of the order sent to the Buyer's email. By placing the order, the Buyer confirms acceptance of these Terms.</p>
    </section>

    <section class="legal-section">
      <h2>4. Price and payment</h2>
      <ul>
        <li>Prices are shown in EUR or CZK, VAT included.</li>
        <li>Prices are valid at the moment the order is placed.</li>
        <li>Accepted payment methods: bank transfer in advance, cash at pickup, cash on delivery (Balíkovna).</li>
        <li>Bank transfer must be credited to the Seller's account within 7 days, otherwise the order may be cancelled.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>5. Delivery</h2>
      <ul>
        <li><strong>Store pickup</strong> — free of charge.</li>
        <li><strong>Balíkovna pickup point</strong> — per current tariff (currently CZK&nbsp;89 / approx. €&nbsp;3.60).</li>
        <li><strong>Balíkovna home delivery</strong> — per current tariff (currently CZK&nbsp;119 / approx. €&nbsp;4.80).</li>
        <li>Delivery usually 3–7 business days after payment confirmation.</li>
        <li>Risk of loss passes to the Buyer upon receipt.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>6. Right of withdrawal (14 days)</h2>
      <p>A consumer may withdraw from the contract without giving a reason within <strong>14 days</strong> of receiving the goods (§&nbsp;1829 of the Civil Code). Withdrawal must be sent by email to <a href="mailto:<?= $email ?>"><?= $email ?></a> and the goods returned to the Seller's address, undamaged and preferably in the original packaging. The purchase price will be refunded within 14 days of receiving the returned goods, using the same payment method.</p>
      <p><strong>The right of withdrawal does not apply</strong> to goods customized to the Buyer's specification (prescription lenses ground to measure, individual orders).</p>
    </section>

    <section class="legal-section">
      <h2>7. Complaints and warranty</h2>
      <p>The Seller is liable for defects in accordance with §&nbsp;2161 of the Civil Code. The statutory warranty period for consumers is <strong>24 months</strong>. Complaints must be submitted in writing or in person, including a defect description and proof of purchase, and will be handled without undue delay, no later than within 30 days.</p>
    </section>

    <section class="legal-section">
      <h2>8. Vouchers</h2>
      <ul>
        <li>Vouchers can be redeemed only on e-shop products unless stated otherwise.</li>
        <li>Each voucher has an expiry date and a minimum order value.</li>
        <li>Vouchers cannot be exchanged for cash.</li>
        <li>Only one voucher can be applied per order.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>9. Data protection</h2>
      <p>Personal data processing is governed by our <a href="?p=datenschutz&amp;lang=en">Privacy Policy</a>.</p>
    </section>

    <section class="legal-section">
      <h2>10. Out-of-court dispute resolution</h2>
      <p>Out-of-court settlement of consumer disputes is handled by the Czech Trade Inspection Authority (<a href="https://www.coi.cz/en" target="_blank" rel="noopener">www.coi.cz</a>). Consumers may also use the EU ODR platform: <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">ec.europa.eu/consumers/odr</a>.</p>
    </section>

    <section class="legal-section">
      <h2>11. Final provisions</h2>
      <p>These Terms are governed by the laws of the Czech Republic. The Seller reserves the right to amend the Terms; the amended version applies only to orders placed after its publication.</p>
    </section>

  <?php else: /* at — Deutsch */ ?>
    <h1 class="legal-title">Allgemeine Geschäftsbedingungen</h1>
    <p class="legal-updated">Gültig ab: <?= $updated ?></p>

    <section class="legal-section">
      <h2>1. Verkäufer</h2>
      <address>
        <?= $company ?><br>
        <?= $owner ?><br>
        <?= $street ?>, <?= $city ?><br>
        Tel.: <a href="tel:<?= preg_replace('/[^\d+]/', '', $site_info['phone']) ?>"><?= $phone ?></a><br>
        E-Mail: <a href="mailto:<?= $email ?>"><?= $email ?></a>
      </address>
      <p>(nachfolgend „Verkäufer")</p>
    </section>

    <section class="legal-section">
      <h2>2. Einleitende Bestimmungen</h2>
      <p>Diese Allgemeinen Geschäftsbedingungen regeln die Rechte und Pflichten zwischen Verkäufer und Käufer im Zusammenhang mit einem über den Online-Shop <strong>www.expertoptic.eu</strong> abgeschlossenen Kaufvertrag gemäß §&nbsp;1751 Abs.&nbsp;1 des tschechischen Bürgerlichen Gesetzbuchs (Gesetz Nr.&nbsp;89/2012 Slg.) und des Verbraucherschutzgesetzes (Nr.&nbsp;634/1992 Slg.).</p>
    </section>

    <section class="legal-section">
      <h2>3. Bestellung und Vertragsabschluss</h2>
      <p>Der Käufer wählt die Ware durch Hinzufügen zum Warenkorb aus und schließt die Bestellung durch Angabe der Kontakt- und Lieferdaten ab. Der Kaufvertrag kommt mit der Bestätigung der Bestellung durch den Verkäufer per E-Mail zustande. Mit der Bestellung bestätigt der Käufer die Kenntnisnahme dieser AGB.</p>
    </section>

    <section class="legal-section">
      <h2>4. Preise und Zahlung</h2>
      <ul>
        <li>Preise verstehen sich in Euro (EUR) oder Tschechischen Kronen (CZK) inklusive Umsatzsteuer.</li>
        <li>Gültig sind die Preise zum Zeitpunkt der Bestellung.</li>
        <li>Zahlungsarten: Vorauskasse per Überweisung, Barzahlung bei Selbstabholung, Nachnahme (Balíkovna).</li>
        <li>Überweisungen müssen binnen 7 Tagen gutgeschrieben werden, andernfalls kann die Bestellung storniert werden.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>5. Versand und Lieferung</h2>
      <ul>
        <li><strong>Selbstabholung</strong> im Studio — kostenlos.</li>
        <li><strong>Balíkovna Abholpunkt</strong> — laut aktuellem Tarif (derzeit 89 CZK / ca. 3,60 €).</li>
        <li><strong>Balíkovna Haustürlieferung</strong> — laut aktuellem Tarif (derzeit 119 CZK / ca. 4,80 €).</li>
        <li>Lieferzeit in der Regel 3–7 Werktage nach Zahlungseingang.</li>
        <li>Die Gefahr des Untergangs geht mit Übernahme auf den Käufer über.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>6. Widerrufsrecht (14 Tage)</h2>
      <p>Verbraucher haben das Recht, binnen <strong>14 Tagen</strong> nach Erhalt der Ware ohne Angabe von Gründen vom Vertrag zurückzutreten (§&nbsp;1829 BGB CZ). Der Widerruf ist per E-Mail an <a href="mailto:<?= $email ?>"><?= $email ?></a> zu richten; die Ware ist unbeschädigt, vorzugsweise in Originalverpackung, an die Anschrift des Verkäufers zurückzusenden. Der Kaufpreis wird binnen 14 Tagen nach Wareneingang auf demselben Weg erstattet.</p>
      <p><strong>Kein Widerrufsrecht</strong> besteht für individuell angefertigte Ware (nach Sehstärke geschliffene Gläser, Sonderanfertigungen).</p>
    </section>

    <section class="legal-section">
      <h2>7. Reklamation und Gewährleistung</h2>
      <p>Der Verkäufer haftet für Sachmängel gemäß §&nbsp;2161 BGB CZ. Die gesetzliche Gewährleistungsfrist für Verbraucher beträgt <strong>24 Monate</strong>. Reklamationen werden schriftlich oder persönlich unter Vorlage der Rechnung geltend gemacht und ohne unnötigen Aufschub, spätestens binnen 30 Tagen, bearbeitet.</p>
    </section>

    <section class="legal-section">
      <h2>8. Gutscheine</h2>
      <ul>
        <li>Gutscheine gelten nur für Produkte im Online-Shop, sofern nicht anders angegeben.</li>
        <li>Jeder Gutschein hat eine Gültigkeitsdauer und einen Mindestbestellwert.</li>
        <li>Gutscheine können nicht in bar ausgezahlt werden.</li>
        <li>Pro Bestellung kann nur ein Gutschein eingelöst werden.</li>
      </ul>
    </section>

    <section class="legal-section">
      <h2>9. Datenschutz</h2>
      <p>Die Verarbeitung personenbezogener Daten richtet sich nach unserer <a href="?p=datenschutz&amp;lang=at">Datenschutzerklärung</a>.</p>
    </section>

    <section class="legal-section">
      <h2>10. Außergerichtliche Streitbeilegung</h2>
      <p>Für außergerichtliche Streitbeilegung ist die Tschechische Handelsinspektion zuständig (<a href="https://www.coi.cz" target="_blank" rel="noopener">www.coi.cz</a>). Zusätzlich steht die EU-OS-Plattform zur Verfügung: <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">ec.europa.eu/consumers/odr</a>.</p>
    </section>

    <section class="legal-section">
      <h2>11. Schlussbestimmungen</h2>
      <p>Es gilt tschechisches Recht. Der Verkäufer behält sich vor, die AGB zu ändern; die neue Fassung gilt nur für nach ihrer Veröffentlichung eingegangene Bestellungen.</p>
    </section>
  <?php endif; ?>
</div>
