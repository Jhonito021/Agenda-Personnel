# Agenda Personnel - Mode Hors Ligne

Ce document explique comment l'application Agenda Personnel peut fonctionner en mode hors ligne.

## Ressources disponibles localement

Les ressources suivantes ont été téléchargées et sont disponibles localement :

### Bootstrap 5
- CSS : `assets/vendor/bootstrap/bootstrap.min.css`
- JavaScript : `assets/vendor/bootstrap/bootstrap.bundle.min.js`

### Font Awesome 6
- CSS : `assets/vendor/fontawesome/all.min.css`
- Webfonts :
  - `assets/vendor/fontawesome/webfonts/fa-solid-900.woff2`
  - `assets/vendor/fontawesome/webfonts/fa-regular-400.woff2`
  - `assets/vendor/fontawesome/webfonts/fa-brands-400.woff2`

### Animate.css
- CSS : `assets/vendor/animate/animate.min.css`

## Ressources encore en ligne

Les ressources suivantes sont toujours chargées depuis Internet :

- Google Fonts (Poppins) : Si vous souhaitez une expérience complètement hors ligne, vous devrez télécharger et installer cette police localement.

## Configuration

Les fichiers suivants ont été modifiés pour utiliser les ressources locales :

- `includes/header.php` : Utilise les versions locales de Bootstrap CSS, Font Awesome et Animate.css
- `includes/footer.php` : Utilise la version locale de Bootstrap JavaScript

## Fonctionnement hors ligne

Pour utiliser l'application complètement hors ligne :

1. Assurez-vous que le serveur web local (XAMPP) est installé et configuré correctement
2. Lancez Apache et MySQL depuis le panneau de contrôle XAMPP
3. Accédez à l'application via `http://localhost/agenda_perso/`

## Téléchargement de Google Fonts (optionnel)

Si vous souhaitez rendre Google Fonts disponible hors ligne :

1. Créez un dossier `assets/vendor/fonts`
2. Téléchargez les fichiers de police Poppins
3. Modifiez le fichier `includes/header.php` pour pointer vers les fichiers locaux

## Maintenance

Pour mettre à jour les bibliothèques :

1. Téléchargez la nouvelle version de la bibliothèque
2. Remplacez le fichier correspondant dans le dossier `assets/vendor/`
3. Testez l'application pour vous assurer que tout fonctionne correctement 