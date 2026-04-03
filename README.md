# Up Noty Broadcast Immo

Plugin WordPress pour la récupération et l'affichage des annonces immobilières depuis l'API Noty Broadcast.

## Fonctionnalités
- Synchronisation automatique quotidienne des annonces.
- Importation locale des photos dans la bibliothèque de médias WordPress.
- Custom Post Type `noty_annonce` pour une gestion facile.
- Shortcode `[noty_annonces]` pour l'affichage en frontend.
- Page de configuration pour le jeton API et l'URL (Test/Production).

## Installation

1. Allez dans l'administration WordPress > Extensions > Ajouter > Téléverser une extension.
2. Activez l'extension.
3. Allez dans **Annonces Noty > Configuration** pour saisir votre jeton d'accès.

## Utilisation du Shortcode
`[noty_annonces limit="6" type="maison"]`

- `limit` : Nombre d'annonces à afficher (défaut: 12).
- `type` : Filtrer par nature de bien (ex: maison, appartement).

## Utilisation des Blocs Gutenberg

Le plugin inclut deux blocs Gutenberg pour une intégration facile dans l'éditeur de blocs :

- **Bloc Carte** (`noty-broadcast-immo/card`) : Affiche une annonce au format carte. Utilise le template `card.php`.
- **Bloc Single** (`noty-broadcast-immo/single`) : Affiche une annonce au format détaillé. Utilise le template `single.php`.

Ces blocs récupèrent automatiquement l'ID du post depuis le contexte (Query Loop, Single Post, etc.) et affichent le contenu correspondant.

## Changelog

- 2026-04-03 · v1.4.8.2 · Correction des noms de méta pour correspondre aux vraies données de l'API Noty (up_bien__nb_pieces, up_bien__nb_chambres, up_bien__nb_sdb, up_bien__nb_salles_eau, up_bien__nb_niveaux, up_bien__piscine, etc.).
- 2026-04-03 · v1.4.8.1 · Centralisation du formatage de l'affichage des données dans meta-filters.php pour assurer la cohérence partout (transformation des valeurs brutes comme "vente_traditionnelle" → "Vente", "charge_vendeur" → "Charge vendeur", ajout automatique de "m²" pour les surfaces, etc.).
- 2026-04-03 · v1.4.8.0 · Ajout de nouveaux champs disponibles (surface terrain, piscine, salles d'eau/bain, ascenseur, niveaux) et implémentation d'une interface drag-and-drop pour ordonner facilement les champs affichés dans les détails par type de transaction.
- 2026-04-03 · v1.4.7.0 · Ajout d'une interface admin pour configurer les champs affichés dans les détails et détails résumés par type de transaction (location, vente traditionnelle, vente viager), avec possibilité de les ordonner.
- 2026-04-03 · v1.4.6.0 · Ajout d'un survol sur la note de prix pour afficher le type d'honoraires et le pourcentage issus de l'import.
- 2026-04-03 · v1.4.5.0 · Ajout d’un message optionnel à côté du prix affiché dans les cards et la single, configurable depuis l’administration.
- 2026-04-03 · v1.4.4.0 · Ajout d’une option globale pour choisir le prix affiché dans les cards et la single entre `prix` et `prix_hni`, avec fallback sur `prix_nv` si nécessaire.
- 2026-04-02 · v1.4.2.0 · Ajout de deux blocs Gutenberg : `noty-broadcast-immo/card` et `noty-broadcast-immo/single` pour utiliser les templates card et single directement dans l'éditeur de blocs.
- 2026-04-02 · v1.4.1.0 · Ajout d'options de configuration pour la gestion des biens lors de l'import : choix de l'action sur les biens non présents (garder, mettre en brouillon ou supprimer) et option de suppression des photos rattachées lors de la suppression d'un bien.
- 2026-04-02 · v1.4.0 · Améliorations de l'affichage : suppression des prix dans les cartes, ajout du prix dans la galerie des annonces single, amélioration du style des détails des cartes, optimisation du container width à 1680px.
- 2026-03-31 · v1.3.0 · Ajout d'un slider d'images (Swiper) avec navigation par vignettes et lightbox (Fancybox) pour l'affichage des annonces.
- 2025-01-01 · v1.2.0 · Version antérieure.
