# DEV — Query Loop + Shortcodes : filtre `render_block`

## Contexte

Dans Gutenberg, les Query Loop (bloc `core/query` + `core/post-template`) rendent le contenu via le système de **blocs**.

Dans certains cas (patterns, contenu synchronisé en base, compatibilités thème/plugins), un shortcode présent dans le contenu rendu peut :

- être affiché **tel quel** (ex: `[`noty_annonce` template="card"]`)
- ne pas être exécuté au bon moment
- ne pas avoir le bon **contexte de boucle** (post courant)

Pour rendre l’exécution des shortcodes **fiable** à l’intérieur d’une Query Loop, on peut appliquer un filtre sur le rendu des blocs.

---

## Principe de la technique

WordPress fournit le hook :

- `render_block`

Ce hook est appelé **pour chaque bloc** lorsque WordPress produit le HTML final.

L’idée est :

1. Intercepter le HTML produit par chaque bloc (`$block_content`)
2. Détecter si ce HTML contient nos shortcodes
3. Si oui, exécuter `do_shortcode()` sur ce HTML

Ainsi, même si Gutenberg n’a pas exécuté un bloc `core/shortcode` ou si un shortcode se retrouve dans du texte, on le force à être interprété **au moment du rendu final**.

---

## Implémentation (plugin)

Dans `includes/class-noty-shortcode.php` :

- On ajoute un filtre dans le constructeur :

```php
add_filter( 'render_block', array( $this, 'render_block_shortcodes' ), 9, 2 );
```

- Puis on implémente la méthode :

```php
public function render_block_shortcodes( $block_content, $block ) {
    if ( ! is_string( $block_content ) || $block_content === '' ) {
        return $block_content;
    }

    if ( strpos( $block_content, '[noty_annonce' ) === false && strpos( $block_content, '[noty_annonces' ) === false ) {
        return $block_content;
    }

    return do_shortcode( $block_content );
}
```

### Pourquoi la priorité `9` ?

`render_block` peut être utilisé par d’autres plugins.

- Priorité plus basse (ex: `9`) = on passe **un peu avant** la plupart des traitements à `10`.
- Ce n’est pas obligatoire, mais ça évite parfois des interactions inattendues.

---

## Points d’attention

### 1) Ne pas exécuter tous les shortcodes

Faire `do_shortcode()` sur **tout** le contenu de tous les blocs peut :

- dégrader les performances
- exécuter des shortcodes non désirés

C’est pour ça qu’on limite à **nos shortcodes** via `strpos()`.

### 2) Risque de double exécution

Si un shortcode a déjà été exécuté plus tôt, `do_shortcode()` ne le ré-exécutera pas (puisqu’il n’y aura plus la chaîne `[noty_...]` dans le HTML final).

Donc la protection `strpos()` limite aussi ce risque.

### 3) Contexte de boucle

L’intérêt majeur dans une Query Loop :

- `render_block` est appelé pendant que WordPress itère sur les posts du `post-template`
- donc `get_the_ID()` / `get_post()` a plus de chances de renvoyer le **bon post courant**

---

## Quand utiliser cette technique

- Quand un shortcode doit fonctionner dans un Query Loop Gutenberg
- Quand le shortcode apparaît en texte brut au rendu
- Quand l’ID de post courant n’est pas correctement récupéré

---

## Alternatives possibles

- Utiliser un bloc `core/shortcode` classique (quand il fonctionne)
- Créer un bloc Gutenberg dédié (solution la plus propre à long terme)
- Utiliser un bloc dynamique (`render_callback`) pour éviter totalement les shortcodes

---

## Résumé

- On utilise `render_block` pour post-traiter le HTML rendu des blocs.
- On détecte la présence de nos shortcodes.
- On applique `do_shortcode()` uniquement si nécessaire.
- Résultat : shortcodes exécutés de manière fiable dans les Query Loops Gutenberg.
