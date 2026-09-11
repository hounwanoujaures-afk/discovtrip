<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Sanitiseur HTML minimal, sans dépendance Composer.
 *
 * Contexte (audit sept. 2026) : blog/show, faq et offers/show affichent du HTML
 * saisi par un admin via Filament RichEditor avec {!! !!}, sans échappement.
 * strip_tags() seul ne suffit pas : il retire les balises interdites mais laisse
 * passer TOUS les attributs des balises autorisées — un <a onmouseover="..."> ou
 * <a href="javascript:..."> passe intact au travers d'un strip_tags('<a>...').
 *
 * Cette classe retire en plus, sur les balises qu'on choisit de garder :
 * - tous les attributs non explicitement listés dans ALLOWED_ATTRIBUTES
 *   (donc tous les on*, style, class... sont supprimés par défaut)
 * - les schémas d'URL dangereux (javascript:, data:) dans href/src
 * et supprime entièrement (avec leur contenu) les balises intrinsèquement
 * dangereuses (script, iframe, object, embed, form...), même si un admin les
 * avait mises dans la liste autorisée par erreur.
 *
 * Ce n'est pas un remplacement complet d'une librairie comme HTMLPurifier,
 * mais ça couvre le vecteur XSS réel identifié dans l'audit sans ajouter de
 * dépendance à installer via composer avant le déploiement.
 *
 * ⚠️ Pas de PHP disponible dans l'environnement où ce fichier a été écrit :
 * à tester en local (php artisan tinker) avant déploiement, voir exemples
 * en bas de fichier.
 */
class Html
{
    /** Balises toujours supprimées avec tout leur contenu, jamais autorisables. */
    private const ALWAYS_STRIP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'svg', 'link', 'meta',
    ];

    /** Attributs autorisés par balise. Tout ce qui n'est pas listé ici est retiré. */
    private const ALLOWED_ATTRIBUTES = [
        'a'   => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
    ];

    /** Schémas d'URL autorisés dans href/src. */
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    /**
     * @param  string|null  $html          Le HTML brut saisi via l'éditeur riche.
     * @param  array<string> $allowedTags  Balises à conserver, ex. ['p','br','strong','a'].
     */
    public static function clean(?string $html, array $allowedTags): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument();

        // libxml est bruyant sur du HTML5 "moderne" (balises inconnues, etc.) —
        // on capture les erreurs pour ne pas les laisser remonter, sans planter.
        $previous = libxml_use_internal_errors(true);

        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementsByTagName('div')->item(0);

        if (! $root) {
            return '';
        }

        self::cleanChildren($root, $allowedTags);

        $inner = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $inner .= $dom->saveHTML($child);
        }

        return trim($inner);
    }

    private static function cleanChildren(DOMNode $node, array $allowedTags): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMText) {
                continue;
            }

            if (! $child instanceof DOMElement) {
                // Commentaires HTML, CDATA, etc. — on ne garde que texte et éléments.
                $node->removeChild($child);
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::ALWAYS_STRIP_WITH_CONTENT, true)) {
                $node->removeChild($child);
                continue;
            }

            if (! in_array($tag, $allowedTags, true)) {
                // Balise non autorisée : on garde le texte/les enfants, on retire
                // juste la balise elle-même (même logique que strip_tags()).
                self::cleanChildren($child, $allowedTags);

                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            self::stripDangerousAttributes($child, $tag);
            self::cleanChildren($child, $allowedTags);
        }
    }

    private static function stripDangerousAttributes(DOMElement $el, string $tag): void
    {
        $allowed = self::ALLOWED_ATTRIBUTES[$tag] ?? [];

        foreach (iterator_to_array($el->attributes ?? []) as $attr) {
            $name = strtolower($attr->name);

            // Tout ce qui n'est pas explicitement autorisé disparaît :
            // on*, style, class, onmouseover, etc. sont retirés par défaut ici.
            if (! in_array($name, $allowed, true)) {
                $el->removeAttribute($attr->name);
                continue;
            }

            if (in_array($name, ['href', 'src'], true)) {
                $value = trim($attr->value);
                $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

                if ($scheme !== '' && ! in_array($scheme, self::ALLOWED_SCHEMES, true)) {
                    $el->removeAttribute($attr->name);
                }
            }
        }

        // Sécurité UX : un lien qui s'ouvre dans un nouvel onglet doit couper
        // la référence vers window.opener (tabnabbing).
        if ($tag === 'a' && $el->getAttribute('target') === '_blank') {
            $el->setAttribute('rel', 'noopener noreferrer');
        }
    }
}

/*
 * Exemples de test manuel (php artisan tinker), à faire avant déploiement :
 *
 * >>> App\Support\Html::clean('<p>Salut <strong onclick="alert(1)">monde</strong></p>', ['p','strong'])
 * => "<p>Salut <strong>monde</strong></p>"     (onclick supprimé)
 *
 * >>> App\Support\Html::clean('<a href="javascript:alert(1)">clique</a>', ['a'])
 * => "<a>clique</a>"                            (href javascript: supprimé)
 *
 * >>> App\Support\Html::clean('<a href="https://discovtrip.com">ok</a><script>alert(1)</script>', ['a'])
 * => "<a href=\"https://discovtrip.com\">ok</a>" (script entièrement supprimé)
 */
