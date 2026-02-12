# Changelog

## 1.2.0

- Le shortcode `[noty_annonce]` n'affiche plus le contenu (`the_content`) : sortie basée uniquement sur les métas/taxonomies/photos.
- Ajout d'une protection anti-récursion pour éviter les boucles si `[noty_annonce]` est utilisé dans le contenu d'une annonce.

## 1.1.0

- Passage des métadonnées du plugin vers le préfixe `up_` (sans underscore initial) + rétro-compatibilité de lecture avec les anciennes métas `_noty_*`.
- Synchronisation des photos améliorée (téléchargement unique par UUID, liaison via métas, mise à jour de la galerie et de l’image mise en avant).
- Ajout d’un dump JSON debug optionnel (uploads) pour faciliter le diagnostic.
- Ajout de la page de configuration pour sélectionner les champs JSON à enregistrer en métas WordPress.
- Ajout / amélioration des metaboxes admin (données complètes, photos, location/charges) et colonnes de listing.
- Shortcodes `[noty_annonces]` et `[noty_annonce]` basés sur templates avec variantes par transaction.
- Refactor UI front : suppression des styles inline, adoption des classes BEM `up-immo-*`, ajout de SCSS par template + `style.scss` global.
- Enregistrement/chargement du `style.css` à la racine du plugin côté front.

