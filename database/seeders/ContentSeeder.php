<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['general', 'site.name', 'ChiChoc-Habitat', 'text', 'Site name'],
            ['general', 'site.tagline', 'Verified living', 'text', 'Short tagline'],

            // Hero
            ['hero', 'hero.eyebrow', 'Verified marketplace · Côte d’Ivoire', 'text', 'Hero eyebrow'],
            ['hero', 'hero.title_line1', 'Find Your', 'text', 'Headline — first line'],
            ['hero', 'hero.title_line2', 'Verified Habitat.', 'text', 'Headline — second line'],
            ['hero', 'hero.subtitle', 'Homes, land, and commercial space you can actually trust — every photo captured on site, every agent identity-checked, every listing approved before it reaches you.', 'textarea', 'Hero paragraph'],

            // Stats (real counts are merged in at render time)
            ['stats', 'stats.listings', '12.4k', 'text', 'Verified listings label'],
            ['stats', 'stats.agents', '1,860', 'text', 'Checked agents label'],
            ['stats', 'stats.approval', '98.7', 'text', 'Approval rate %'],
            ['stats', 'stats.response', '4 min', 'text', 'Average response time'],

            // Footer / contact
            ['footer', 'footer.tagline', 'Verified homes, land, and commercial space across Côte d’Ivoire — every listing checked before it reaches you.', 'textarea', 'Footer description'],
            ['footer', 'contact.email', 'hello@chichochabitat.ci', 'text', 'Contact email'],
            ['footer', 'contact.phone', '+225 07 00 00 00', 'text', 'Contact phone'],
            ['footer', 'contact.address', 'Cocody, Abidjan, Côte d’Ivoire', 'text', 'Head office'],

            // Legal identity
            ['legal', 'legal.company_name', 'ChiChoc-Habitat SARL', 'text', 'Registered company name'],
            ['legal', 'legal.registration', 'CI-ABJ-2026-B-00000', 'text', 'Company registration number'],
            ['legal', 'legal.updated_at', '21 October 2026', 'text', 'Legal documents last updated'],
        ];

        // French translations of the strings above. Anything omitted keeps the
        // English value, so a half-translated site never renders empty text.
        $frenchSettings = [
            'site.tagline' => 'Habitat vérifié',
            'hero.eyebrow' => 'Marché vérifié · Côte d’Ivoire',
            'hero.title_line1' => 'Trouvez votre',
            'hero.title_line2' => 'habitat vérifié.',
            'hero.subtitle' => 'Des maisons, des terrains et des locaux commerciaux réellement fiables — chaque photo prise sur place, chaque agent contrôlé, chaque annonce approuvée avant de vous parvenir.',
            'stats.listings' => '12,4 k',
            'stats.agents' => '1 860',
            'stats.approval' => '98,7',
            'stats.response' => '4 min',
            'footer.tagline' => 'Des maisons, terrains et locaux commerciaux vérifiés partout en Côte d’Ivoire — chaque annonce contrôlée avant de vous parvenir.',
            'contact.address' => 'Cocody, Abidjan, Côte d’Ivoire',
            'legal.updated_at' => '21 octobre 2026',
        ];

        foreach ($settings as [$group, $key, $value, $type, $label]) {
            SiteSetting::updateOrCreate(
                ['key' => $key, 'locale' => 'en'],
                compact('group', 'value', 'type', 'label')
            );

            SiteSetting::updateOrCreate(
                ['key' => $key, 'locale' => 'fr'],
                [
                    'group' => $group,
                    'value' => $frenchSettings[$key] ?? $value,
                    'type' => $type,
                    'label' => $label,
                ]
            );
        }

        $pages = [
            [
                'slug' => 'how-verification-works',
                'title' => 'How verification works',
                'group' => 'trust',
                'sort' => 1,
                'body' => "Every listing passes four checks before it can appear on ChiChoc-Habitat.\n\n**Agent identity.** The person listing the property submits a government ID and completes a liveness check. Our trust team compares the face to the document and approves the agent before they can list anything.\n\n**Location.** Photos may only be captured inside the app, and capture is blocked unless the device is within 100 metres of the stated address. The recorded coordinates are stored with each photograph.\n\n**Photography.** A checklist generated from the property's claimed features must be completed. A house with three bedrooms needs photographs of each bedroom. Capture cannot be skipped or uploaded from a gallery.\n\n**Documents.** Title, ownership, and where relevant an ADU certificate are reviewed by the legal team and routed to the correct department automatically.\n\nOnly after all four stages does a reviewer publish the listing. The decision is recorded permanently and cannot be edited.",
            ],
            [
                'slug' => 'agent-standards',
                'title' => 'Agent standards',
                'group' => 'trust',
                'sort' => 2,
                'body' => "Agents on ChiChoc-Habitat agree to a higher standard than a typical listing portal.\n\n- List only properties you are authorised to represent.\n- Complete KYC before your first listing, and keep it current.\n- Capture every photograph on site. Stock imagery is grounds for immediate removal.\n- Respond to customer enquiries within one business day.\n- Never request payment outside the platform.\n\nRepeated or serious breaches result in suspension. Every action we take is logged and reviewable.",
            ],
            [
                'slug' => 'document-checks',
                'title' => 'Document checks',
                'group' => 'trust',
                'sort' => 3,
                'body' => "Land and property documents are checked by our legal department before a listing goes live.\n\nWe review the **ADU** (Attestation de Droit d'Usage) and, where applicable, the **IDUFCI** parcel identifier. Ownership documents must match the identity of the person listing the property, or a valid mandate must be on file.\n\nDocuments are encrypted at rest, visible only to the legal team for the property in question, and never shared with buyers beyond the verification badge and the facts we publish.",
            ],
            [
                'slug' => 'security',
                'title' => 'Security',
                'group' => 'trust',
                'sort' => 4,
                'body' => "Security is a product requirement, not a feature.\n\n- All traffic is served over TLS 1.3.\n- Passwords are hashed with Argon2id.\n- Documents and identity files are encrypted at rest with AES-256.\n- VPN and proxy traffic is blocked at the edge to reduce fraud.\n- Every back-office action is written to an append-only audit log that cannot be deleted, including by an administrator.\n- Back-office access requires multi-factor authentication and a role-based permission grant.\n\nIf you believe you have found a vulnerability, contact security@chichochabitat.ci.",
            ],
            [
                'slug' => 'about',
                'title' => 'About ChiChoc-Habitat',
                'group' => 'company',
                'sort' => 1,
                'body' => "ChiChoc-Habitat exists because buying property in Côte d'Ivoire should not require blind trust.\n\nWe built a marketplace where the physical reality of a property is confirmed before a buyer ever sees it: photographs taken on site with GPS proof, agents whose identity has been verified, and documents reviewed by lawyers.\n\nWe are headquartered in Abidjan and designed from the start to operate across multiple countries and currencies.",
            ],
            [
                'slug' => 'careers',
                'title' => 'Careers',
                'group' => 'company',
                'sort' => 2,
                'body' => "We hire people who care about getting the detail right.\n\nThere are no open roles advertised at the moment. If you work in verification, legal property review, or full-stack engineering and want to be considered for future openings, write to careers@chichochabitat.ci with a short note about what you would improve.",
            ],
            [
                'slug' => 'press',
                'title' => 'Press',
                'group' => 'company',
                'sort' => 3,
                'body' => "For interviews, data requests, or brand assets, contact press@chichochabitat.ci.\n\nWe publish verified market data quarterly. Journalists may request aggregate figures on listings, approval rates, and regional pricing without customer-identifying information.",
            ],
            [
                'slug' => 'contact',
                'title' => 'Contact',
                'group' => 'company',
                'sort' => 4,
                'body' => "We answer every message.\n\n**General enquiries** — hello@chichochabitat.ci\n**Trust and safety** — trust@chichochabitat.ci\n**Security disclosures** — security@chichochabitat.ci\n**Press** — press@chichochabitat.ci\n\nOur office is in Cocody, Abidjan. Visits are by appointment.",
            ],
            [
                'slug' => 'terms',
                'title' => 'Terms & Conditions',
                'group' => 'legal',
                'sort' => 1,
                'body' => "These terms govern your use of ChiChoc-Habitat. By creating an account you accept them.\n\n**1. Accounts.** You must provide accurate information and keep your credentials secure. You are responsible for activity under your account.\n\n**2. Listings.** Agents warrant that they are authorised to list a property and that all information and media is accurate. Listings are published only after verification. We may remove any listing that fails review.\n\n**3. Fees.** The price shown to a customer includes the platform fee. Agents are told their payout. Fees are configured per property and transaction type and are displayed before an offer is made.\n\n**4. Offers.** An accepted offer creates a binding agreement between buyer and seller or landlord and tenant. We record the acceptance permanently; the chat closes but the record remains.\n\n**5. Prohibited conduct.** No fraudulent listings, no off-platform payment requests, no scraping, no attempts to circumvent verification.\n\n**6. Liability.** We verify identity, location, and documents in good faith but do not guarantee a property's condition. Independent inspection is always recommended.\n\n**7. Governing law.** These terms are governed by the laws of Côte d'Ivoire.",
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'group' => 'legal',
                'sort' => 2,
                'body' => "We collect only what verification requires.\n\n**What we collect.** Account details (name, email, phone), identity documents and liveness captures for agents, property media including GPS coordinates, and usage logs.\n\n**Why.** To verify identity, confirm that a property exists where it is claimed to be, prevent fraud, and meet legal obligations.\n\n**Who sees it.** Identity documents are visible only to the verification team for the property in question. Buyers see the verification outcome, never the underlying document.\n\n**Retention.** Verification records are retained for the life of the account plus the period required by Ivorian law, because they underpin the audit log.\n\n**Your rights.** You may request access to, correction of, or deletion of your personal data, subject to legal retention requirements. Write to privacy@chichochabitat.ci.",
            ],
            [
                'slug' => 'cookies',
                'title' => 'Cookie Policy',
                'group' => 'legal',
                'sort' => 3,
                'body' => "We use a small number of cookies and local storage entries, all of which are necessary for the site to work.\n\n- **Session cookie** — keeps you signed in.\n- **CSRF token** — protects forms against cross-site request forgery.\n- **Language preference** — remembers whether you chose French or English, so the site opens in your language next time.\n- **Currency preference** — remembers your chosen display currency.\n\nWe do not use advertising cookies and we do not sell data to third parties.",
            ],
        ];

        // French translation of every published page. Titles and bodies only —
        // group, sorting and publication state are shared with the English row.
        $frenchPages = [
            'how-verification-works' => [
                'title' => 'Comment fonctionne la vérification',
                'body' => "Chaque annonce passe quatre contrôles avant d'apparaître sur ChiChoc-Habitat.\n\n**Identité de l'agent.** La personne qui publie le bien fournit une pièce d'identité officielle et réalise un contrôle de présence réelle. Notre équipe confiance compare le visage au document et approuve l'agent avant toute publication.\n\n**Localisation.** Les photos ne peuvent être prises que depuis l'application, et la capture est bloquée si l'appareil se trouve à plus de 100 mètres de l'adresse indiquée. Les coordonnées enregistrées sont conservées avec chaque photographie.\n\n**Photographie.** Une liste de photos générée à partir des caractéristiques annoncées du bien doit être complétée. Une maison de trois chambres exige une photo de chaque chambre. Aucune capture ne peut être ignorée ni importée depuis une galerie.\n\n**Documents.** Le titre, la propriété et, le cas échéant, l'ADU sont examinés par l'équipe juridique et transmis automatiquement au service compétent.\n\nCe n'est qu'après ces quatre étapes qu'un vérificateur publie l'annonce. La décision est enregistrée de façon permanente et ne peut pas être modifiée.",
            ],
            'agent-standards' => [
                'title' => 'Exigences pour les agents',
                'body' => "Les agents de ChiChoc-Habitat s'engagent à un niveau d'exigence supérieur à celui d'un portail d'annonces classique.\n\n- Ne publiez que des biens que vous êtes autorisé à représenter.\n- Réalisez votre KYC avant votre première annonce et maintenez-le à jour.\n- Prenez chaque photographie sur place. Les images de stock entraînent un retrait immédiat.\n- Répondez aux demandes des clients sous un jour ouvré.\n- Ne demandez jamais de paiement en dehors de la plateforme.\n\nTout manquement grave ou répété entraîne une suspension. Chacune de nos décisions est journalisée et peut être examinée.",
            ],
            'document-checks' => [
                'title' => 'Vérification des documents',
                'body' => "Les documents fonciers et immobiliers sont contrôlés par notre service juridique avant la mise en ligne d'une annonce.\n\nNous examinons l'**ADU** (Attestation de Droit d'Usage) et, le cas échéant, l'identifiant de parcelle **IDUFCI**. Les documents de propriété doivent correspondre à l'identité de la personne qui publie le bien, ou un mandat valide doit être au dossier.\n\nLes documents sont chiffrés au repos, visibles uniquement par l'équipe juridique en charge du bien concerné, et jamais communiqués aux acheteurs au-delà du badge de vérification et des informations que nous publions.",
            ],
            'security' => [
                'title' => 'Sécurité',
                'body' => "La sécurité est une exigence produit, pas une option.\n\n- Tout le trafic est servi en TLS 1.3.\n- Les mots de passe sont hachés avec Argon2id.\n- Les documents et fichiers d'identité sont chiffrés au repos en AES-256.\n- Le trafic VPN et proxy est bloqué en amont pour réduire la fraude.\n- Chaque action d'administration est écrite dans un journal d'audit non modifiable, y compris par un administrateur.\n- L'accès à l'administration exige l'authentification multifacteur et une autorisation fondée sur les rôles.\n\nSi vous pensez avoir découvert une faille, écrivez à security@chichochabitat.ci.",
            ],
            'about' => [
                'title' => 'À propos de ChiChoc-Habitat',
                'body' => "ChiChoc-Habitat existe parce qu'acheter un bien en Côte d'Ivoire ne devrait pas exiger une confiance aveugle.\n\nNous avons construit une place de marché où la réalité physique d'un bien est confirmée avant même que l'acheteur ne la voie : des photographies prises sur place avec preuve GPS, des agents dont l'identité est vérifiée et des documents examinés par des juristes.\n\nNotre siège est à Abidjan et la plateforme est conçue dès le départ pour opérer dans plusieurs pays et plusieurs devises.",
            ],
            'careers' => [
                'title' => 'Carrières',
                'body' => "Nous recrutons des personnes attachées au souci du détail.\n\nAucun poste n'est ouvert au public pour le moment. Si vous travaillez dans la vérification, la revue juridique immobilière ou l'ingénierie full-stack et souhaitez être considéré pour de futures ouvertures, écrivez à careers@chichochabitat.ci avec une courte note sur ce que vous amélioreriez.",
            ],
            'press' => [
                'title' => 'Presse',
                'body' => "Pour les entretiens, les demandes de données ou les éléments de marque, contactez press@chichochabitat.ci.\n\nNous publions chaque trimestre des données de marché vérifiées. Les journalistes peuvent demander des chiffres agrégés sur les annonces, les taux d'approbation et les prix régionaux, sans information permettant d'identifier un client.",
            ],
            'contact' => [
                'title' => 'Contact',
                'body' => "Nous répondons à chaque message.\n\n**Demandes générales** — hello@chichochabitat.ci\n**Confiance et sécurité** — trust@chichochabitat.ci\n**Signalement de faille** — security@chichochabitat.ci\n**Presse** — press@chichochabitat.ci\n\nNotre bureau se trouve à Cocody, Abidjan. Les visites se font sur rendez-vous.",
            ],
            'terms' => [
                'title' => 'Conditions générales',
                'body' => "Les présentes conditions régissent votre utilisation de ChiChoc-Habitat. En créant un compte, vous les acceptez.\n\n**1. Comptes.** Vous devez fournir des informations exactes et garder vos identifiants confidentiels. Vous êtes responsable de l'activité effectuée avec votre compte.\n\n**2. Annonces.** Les agents garantissent qu'ils sont autorisés à publier un bien et que toutes les informations et tous les médias sont exacts. Les annonces ne sont publiées qu'après vérification. Nous pouvons retirer toute annonce qui échoue au contrôle.\n\n**3. Frais.** Le prix affiché au client inclut les frais de plateforme. Les agents connaissent leur reversement. Les frais sont configurés par type de bien et de transaction et affichés avant toute offre.\n\n**4. Offres.** Une offre acceptée crée un accord ferme entre acheteur et vendeur ou bailleur et locataire. Nous enregistrons l'acceptation de façon permanente : la conversation se ferme mais l'enregistrement demeure.\n\n**5. Conduites interdites.** Aucune annonce frauduleuse, aucune demande de paiement hors plateforme, aucune extraction automatisée de données, aucune tentative de contourner la vérification.\n\n**6. Responsabilité.** Nous vérifions l'identité, la localisation et les documents de bonne foi, mais ne garantissons pas l'état d'un bien. Une inspection indépendante reste toujours recommandée.\n\n**7. Droit applicable.** Les présentes conditions sont régies par le droit ivoirien.",
            ],
            'privacy' => [
                'title' => 'Politique de confidentialité',
                'body' => "Nous ne collectons que ce que la vérification exige.\n\n**Ce que nous collectons.** Les données de compte (nom, e-mail, téléphone), les pièces d'identité et captures de présence réelle pour les agents, les médias des biens y compris les coordonnées GPS, ainsi que les journaux d'utilisation.\n\n**Pourquoi.** Pour vérifier l'identité, confirmer qu'un bien existe bien à l'endroit annoncé, prévenir la fraude et respecter nos obligations légales.\n\n**Qui y a accès.** Les pièces d'identité ne sont visibles que par l'équipe de vérification en charge du bien concerné. Les acheteurs voient le résultat de la vérification, jamais le document lui-même.\n\n**Conservation.** Les dossiers de vérification sont conservés pendant la durée de vie du compte, puis pendant la période exigée par la loi ivoirienne, car ils fondent le journal d'audit.\n\n**Vos droits.** Vous pouvez demander l'accès à vos données personnelles, leur rectification ou leur suppression, sous réserve des obligations légales de conservation. Écrivez à privacy@chichochabitat.ci.",
            ],
            'cookies' => [
                'title' => 'Politique relative aux cookies',
                'body' => "Nous utilisons un petit nombre de cookies et d'entrées de stockage local, tous nécessaires au fonctionnement du site.\n\n- **Cookie de session** — vous garde connecté.\n- **Jeton CSRF** — protège les formulaires contre les requêtes intersites.\n- **Préférence de langue** — mémorise votre choix entre le français et l'anglais, afin que le site s'ouvre dans votre langue lors de votre prochaine visite.\n- **Préférence de devise** — mémorise la devise d'affichage choisie.\n\nNous n'utilisons pas de cookies publicitaires et ne vendons aucune donnée à des tiers.",
            ],
        ];

        foreach ($pages as $page) {
            $slug = $page['slug'];

            Page::updateOrCreate(
                ['slug' => $slug, 'locale' => 'en'],
                array_merge($page, ['locale' => 'en', 'is_published' => true])
            );

            $french = $frenchPages[$slug] ?? [];

            Page::updateOrCreate(
                ['slug' => $slug, 'locale' => 'fr'],
                array_merge($page, [
                    'locale' => 'fr',
                    'is_published' => true,
                    'title' => $french['title'] ?? $page['title'],
                    'body' => $french['body'] ?? $page['body'],
                ])
            );
        }

        $this->command?->info('Content seeded: '.count($settings).' settings, '.count($pages).' pages.');
    }
}
