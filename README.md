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
