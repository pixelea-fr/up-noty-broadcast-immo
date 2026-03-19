# Filtre Meta Value du Plugin Noty Broadcast

Guide pour modifier les valeurs dans `meta-filters.php`

## Filtre Disponible

### `noty_immo_meta_value_{meta}`

Modifie la valeur d'un champ meta spécifique dans le rendu.

```php
add_filter(
    'noty_immo_meta_value_' . $meta,
    function( $value, $row, $bien, $annonce, $post_id ) {
        return $value;
    },
    10,
    5
);
```

## Paramètres

- **`$value`** : Valeur actuelle du champ meta
- **`$row`** : Données brutes de la ligne API
- **`$bien`** : Objet bien traité
- **`$annonce`** : Objet annonce traité
- **`$post_id`** : ID du post WordPress

## Exemples

### Formater le prix
```php
add_filter(
    'noty_immo_meta_value_prix',
    function( $value, $row, $bien, $annonce, $post_id ) {
        return number_format($value, 0, ',', ' ') . ' €';
    },
    10,
    5
);
```

### Nettoyer la description
```php
add_filter(
    'noty_immo_meta_value_description',
    function( $value, $row, $bien, $annonce, $post_id ) {
        return wp_strip_all_tags($value);
    },
    10,
    5
);
```

### Traduire le type de transaction
```php
add_filter(
    'noty_immo_meta_value_transaction_type',
    function( $value, $row, $bien, $annonce, $post_id ) {
        $traductions = ['sale' => 'Vente', 'rent' => 'Location'];
        return $traductions[$value] ?? $value;
    },
    10,
    5
);
```

## Utilisation

Remplacez `{meta}` par le nom du champ à modifier dans `meta-filters.php`.
