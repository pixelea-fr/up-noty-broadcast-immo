# Shortcodes UP Noty Broadcast Immo

Ce document liste les shortcodes disponibles dans le plugin **UP Noty Broadcast Immo** et explique leur utilisation.

---

## `[noty_annonces]`

Affiche une liste d'annonces immobilières sous forme de cards.

### Attributs disponibles

| Attribut | Type | Défaut | Description |
|----------|------|--------|-------------|
| `limit` | integer | `12` | Nombre maximum d'annonces à afficher |
| `nature` | string | `''` | Filtre par slug de la taxonomie `noty_nature` (ex: `appartement`, `maison`) |
| `ville` | string | `''` | Filtre par slug de la taxonomie `noty_ville` (ex: `paris`, `lyon`) |
| `transaction` | string | `''` | Filtre par slug de la taxonomie `noty_transaction` (ex: `vente`, `location`) |

### Exemples d'utilisation

```
[noty_annonces]
```
Affiche les 12 dernières annonces.

```
[noty_annonces limit="6" transaction="location"]
```
Affiche les 6 dernières annonces en location.

```
[noty_annonces limit="9" ville="lyon" nature="maison"]
```
Affiche jusqu'à 9 maisons à Lyon.

```
[noty_annonces nature="appartement" transaction="vente"]
```
Affiche les appartements en vente.

### Templates utilisés

Le shortcode utilise le template suivant :

1. `card.php`

Les templates sont cherchés dans le dossier `templates/` du plugin.

---

## `[noty_annonce]`

Affiche une annonce complète (template single, avec variantes selon la transaction si disponible).

### Attributs disponibles

| Attribut | Type | Défaut | Description |
|----------|------|--------|-------------|
| `id` | integer | `0` | ID du post WordPress (CPT `noty_annonce`) |
| `uuid` | string | `''` | UUID Noty de l'annonce (recherche sur la meta `up_uuid` puis fallback `_noty_uuid`) |
| `template` | string | `'single'` | Type de template à utiliser : `single` ou `card` |

### Exemples d'utilisation

```
[noty_annonce]
```
Affiche l'annonce courante avec le template single (par défaut).

```
[noty_annonce id="123"]
```
Affiche l'annonce avec l'ID WordPress 123, template single.

```
[noty_annonce uuid="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"]
```
Affiche l'annonce correspondant à cet UUID Noty, template single.

```
[noty_annonce id="123" template="card"]
```
Affiche l'annonce sous forme de card (utilise le template card).

```
[noty_annonce uuid="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" template="card"]
```
Affiche l'annonce avec l'UUID sous forme de card.

### Templates utilisés

Le shortcode utilise le template suivant :

1. `single.php`

Les templates sont cherchés dans le dossier `templates/` du plugin.

---

## Taxonomies utilisées

Les shortcodes utilisent les taxonomies suivantes pour le filtrage :

| Taxonomie | Slug | Description |
|-----------|------|-------------|
| Nature du bien | `noty_nature` | Type de bien (appartement, maison, studio...) |
| Type de transaction | `noty_transaction` | Type de transaction (vente, location, viager...) |
| Ville | `noty_ville` | Ville ou commune du bien |
| État du bien | `noty_etat` | État général du bâtiment (neuf, bon état...) |

### Obtenir les slugs disponibles

Pour connaître les slugs disponibles pour chaque taxonomie, vous pouvez utiliser :

```php
// Récupérer tous les termes d'une taxonomie
$terms = get_terms(array(
    'taxonomy' => 'noty_nature',
    'hide_empty' => false,
));

foreach ($terms as $term) {
    echo $term->slug . ' : ' . $term->name;
}
```

---

## Custom Post Type

Les shortcodes utilisent le CPT suivant :

| CPT | Slug | Description |
|-----|------|-------------|
| Annonces Noty | `noty_annonce` | Contenu principal pour les annonces immobilières synchronisées avec Noty |

---

## Champs personnalisés (Meta)

Les annonces stockent des métadonnées qui peuvent être utiles :

| Meta Key | Description |
|----------|-------------|
| `up_uuid` / `_noty_uuid` | UUID unique de l'annonce Noty |
| `up_loyer` / `_noty_loyer` | Montant du loyer |
| `up_photo_ids` / `_noty_photo_ids` | IDs des photos (tableau sérialisé) |

---

## Personnalisation des templates

Pour personnaliser l'affichage, vous pouvez créer vos propres templates dans votre thème enfant en copiant les fichiers du dossier `templates/` du plugin.

### Emplacement des templates personnalisés

Placez vos templates personnalisés dans votre thème enfant :
```
votre-theme-enfant/
  └── up-noty-broadcast-immo/
      ├── card.php
      ├── card-vente.php
      ├── single.php
      └── single-location.php
```

### Variables disponibles dans les templates

#### Templates card (`card.php`, `card-{transaction}.php`)

- `$post_id` - ID du post WordPress
- Fonctions WordPress standards : `get_the_title()`, `get_the_post_thumbnail_url()`, `get_post_meta()`, etc.

#### Templates single (`single.php`, `single-{transaction}.php`)

- `$post_id` - ID du post WordPress
- Fonctions WordPress standards : `the_title()`, `the_content()`, `get_post_meta()`, etc.

---

## Dépannage

### Aucune annonce ne s'affiche

1. Vérifiez qu'une synchronisation a été effectuée (page Configuration du plugin)
2. Vérifiez que le jeton API et l'URL sont correctement configurés
3. Vérifiez les filtres utilisés dans le shortcode (slugs de taxonomie corrects)

### L'annonce par UUID ne s'affiche pas

1. Vérifiez que l'UUID est correct (format : `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`)
2. Vérifiez que l'annonce existe dans la base WordPress
3. La recherche se fait d'abord sur `up_uuid`, puis sur `_noty_uuid` en fallback

### Problèmes de templates

1. Vérifiez que les fichiers existent dans le dossier `templates/`
2. Pour les templates spécifiques par transaction, vérifiez que le slug de la transaction correspond exactement au nom du fichier (ex: `vente` → `card-vente.php`)
