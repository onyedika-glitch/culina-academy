<?php

namespace App\Services;

class LegacyLocaleMap
{
    /**
     * Localized legacy public paths (no .html, no language prefix).
     * Use null when the legacy site has no working page for that language.
     *
     * @var array<string, array<string, string|null>>
     */
    public const LEGACY_PATHS = [
        'home' => [
            'en' => '',
            'fr' => '',
            'es' => '',
            'it' => '',
            'zh' => '',
            'zh-tw' => '',
        ],
        'about-us' => [
            'en' => 'about-us',
            'fr' => 'a-propos-de-nous',
            'es' => 'quienes-somos',
            'it' => 'chi-siamo',
            'zh' => 'about-us',
            'zh-tw' => 'about-us',
        ],
        'careers' => [
            'en' => 'about-us/careers',
            'fr' => 'a-propos-de-nous/carrieres',
            'es' => 'quienes-somos/bolsa-de-trabajo',
            'it' => 'chi-siamo/centro-impiego',
            'zh' => null,
            'zh-tw' => 'about-us/careers',
        ],
        'how-to-apply' => [
            'en' => 'about-us/apply-now',
            'fr' => 'a-propos-de-nous/comment-s-inscrire',
            'es' => 'quienes-somos/como-matricularse',
            'it' => 'chi-siamo/come-iscriversi',
            'zh' => 'about-us/apply-now',
            'zh-tw' => 'about-us/apply-now',
        ],
        'faqs' => [
            'en' => 'about-us/frequently-asked-questions',
            'fr' => 'a-propos-de-nous/questions-frequentes',
            'es' => 'quienes-somos/preguntas-frecuentes',
            'it' => 'chi-siamo/domande-frequenti',
            'zh' => 'about-us/frequently-asked-questions',
            'zh-tw' => 'about-us/frequently-asked-questions',
        ],
        'hospitality-development' => [
            'en' => 'about-us/our-schools',
            'fr' => 'a-propos-de-nous/nos-ecoles',
            'es' => 'quienes-somos/nuestras-escuelas',
            'it' => 'chi-siamo/le-nostre-scuole',
            'zh' => 'about-us/our-schools',
            'zh-tw' => 'about-us/our-schools',
        ],
        'cooking-courses' => [
            'en' => 'cooking-courses',
            'fr' => 'cours',
            'es' => 'cursos',
            'it' => 'corsi',
            'zh' => 'cooking-courses',
            'zh-tw' => 'cooking-courses',
        ],
        'restaurant-partners' => [
            'en' => 'restaurant-partners',
            'fr' => 'restaurants-partenaires',
            'es' => 'restaurantes-asociados',
            'it' => 'ristoranti-associati',
            'zh' => 'restaurant-partners',
            'zh-tw' => 'restaurant-partners',
        ],
        'reviews' => [
            'en' => 'reviews',
            'fr' => null,
            'es' => 'resenas',
            'it' => 'recensioni',
            'zh' => null,
            'zh-tw' => 'reviews',
        ],
        'online-courses' => [
            'en' => 'online-courses',
            'fr' => null,
            'es' => null,
            'it' => null,
            'zh' => null,
            'zh-tw' => null,
        ],
        'international-students' => [
            'en' => 'asic-international-students',
            'fr' => 'etudiants-internationaux',
            'es' => 'estudiantes-internacionales',
            'it' => 'studenti-internazionali',
            'zh' => 'asic-international-students',
            'zh-tw' => 'asic-international-students',
        ],
        'contact-us' => [
            'en' => 'contact-us',
            'fr' => 'contacts',
            'es' => 'contacto',
            'it' => 'contatti',
            'zh' => 'contact-us',
            'zh-tw' => 'contact-us',
        ],
        'blog' => [
            'en' => 'blog',
            'fr' => 'blog',
            'es' => 'blog',
            'it' => 'blog',
            'zh' => 'blog',
            'zh-tw' => 'blog',
        ],
        'privacy-policy' => [
            'en' => 'privacy-policy',
            'fr' => 'politique-de-confidentialite',
            'es' => 'politica-de-privacidad',
            'it' => 'informativa-sulla-privacy',
            'zh' => 'privacy-policy',
            'zh-tw' => 'privacy-policy',
        ],
        'terms-and-conditions' => [
            'en' => 'terms-conditions',
            'fr' => 'conditions-generales',
            'es' => 'terminos-y-condiciones',
            'it' => 'termini-e-condizioni',
            'zh' => 'terms-conditions',
            'zh-tw' => 'terms-conditions',
        ],
        'cookies-policy' => [
            'en' => 'cookies-policy',
            'fr' => 'informations-sur-les-cookies',
            'es' => 'informacion-sobre-cookies',
            'it' => 'informativa-sui-cookies',
            'zh' => 'cookies-policy',
            'zh-tw' => 'cookies-policy',
        ],
        'complaints-policy-and-procedure' => [
            'en' => 'complaints-policy-and-procedure',
            'fr' => 'plaintes-protocole-et-marche-a-suivre',
            'es' => 'politica-de-quejas-y-reclamos',
            'it' => 'politica-e-procedura-per-reclami',
            'zh' => 'complaints-policy-and-procedure',
            'zh-tw' => 'complaints-policy-and-procedure',
        ],
        'attendance-and-punctuality-policy' => [
            'en' => 'attendance-and-punctuality-policy',
            'fr' => 'presence-aux-cours-et-ponctualite',
            'es' => 'politica-de-asistencia-y-puntualidad',
            'it' => 'politica-di-assistenza-e-puntualita',
            'zh' => 'attendance-and-punctuality-policy',
            'zh-tw' => 'attendance-and-punctuality-policy',
        ],
    ];

    /** @return array<int, string> */
    public static function languages(): array
    {
        return array_map(
            static fn (array $language): string => $language['slug'],
            PolylangService::getLanguages(),
        );
    }

    public static function legacyPathForSlug(string $slug, ?string $lang = null): ?string
    {
        $lang = PolylangService::normalizeCode($lang ?? 'en');
        $paths = static::LEGACY_PATHS[$slug] ?? null;

        if ($paths === null) {
            return null;
        }

        if (array_key_exists($lang, $paths)) {
            return $paths[$lang];
        }

        return $paths['en'] ?? null;
    }

    public static function legacyPathForEnglishPath(string $englishLegacyPath, ?string $lang = null): ?string
    {
        $englishLegacyPath = trim($englishLegacyPath, '/');

        foreach (static::LEGACY_PATHS as $slug => $paths) {
            if (($paths['en'] ?? null) === $englishLegacyPath) {
                return static::legacyPathForSlug($slug, $lang);
            }
        }

        if (in_array($lang, ['zh', 'zh-tw'], true)) {
            return $englishLegacyPath;
        }

        return null;
    }

    /** @return array<int, string> */
    public static function legacyLanguagePrefixes(): array
    {
        return ['en', 'fr', 'es', 'it', 'zh', 'zh-tw'];
    }
}