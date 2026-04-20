# Auto SNH & MEL – Structure du projet

## 📁 Fichiers

| Fichier               | Rôle                                                  |
|-----------------------|-------------------------------------------------------|
| `index.html`          | Page d'accueil avec les 3 catégories                  |
| `vip.html`            | Catalogue voitures VIP                                |
| `sport.html`          | Catalogue voitures Sport                              |
| `familiale.html`      | Catalogue voitures Familiales                         |
| `contact.html`        | Formulaire de contact + validation JS + WhatsApp      |
| `contact_handler.php` | Backend PHP – envoi email via `mail()`                |
| `style.css`           | Feuille de style commune (CSS centralisé)             |

---

## ✅ Corrections apportées

### HTML / CSS
- Titres `<title>` corrigés (sport/familiale avaient encore "Auto VIP")
- Balises `<br>` inutiles supprimées dans le formulaire
- `box-sizing: border-box` ajouté → `input width:100%` ne déborde plus
- CSS centralisé dans `style.css` → une seule source de vérité
- Navigation cohérente sur toutes les pages avec lien actif surligné en or
- Responsive mobile (header empilé, contact-image en haut sur petit écran)

### JavaScript (contact.html)
- Validation côté client **avant** envoi :
  - Nom & prénom non vides
  - Format email (regex)
  - Format téléphone (international ou marocain)
- Messages d'erreur inline sous chaque champ
- Erreurs effacées en temps réel quand l'utilisateur corrige
- Popup de succès animée avant ouverture WhatsApp
- Pré-remplissage du sélecteur voiture via `?voiture=BMW+X5` (depuis les pages catalogue)

### PHP (contact_handler.php)
- Reçoit les données du formulaire via `$_POST`
- Nettoyage : `strip_tags`, `htmlspecialchars`, `trim`
- Validation serveur (nom, prénom, email, téléphone) en doublon du JS
- Envoi email via `mail()` avec headers corrects (UTF-8, Reply-To)
- Compatible **mode AJAX** (retourne JSON si `X-Requested-With` est présent)
- Compatible **mode classique** (redirection vers `?success=1` ou `?error=1`)

---

## ⚙️ Configuration PHP

Ouvrez `contact_handler.php` et modifiez les constantes en haut :

```php
define('DESTINATAIRE',  'votre-email@exemple.com');  // Email qui reçoit les messages
define('EXPEDITEUR',    'noreply@snh-mel.ma');        // Email affiché comme expéditeur
```

> **Prérequis serveur** : PHP 7.4+ avec la fonction `mail()` activée
> (ou configurez un SMTP via PHPMailer pour plus de fiabilité).

---

## 📱 Numéro WhatsApp

Dans `contact.html`, ligne ~76 du script :
```js
var numero = "212632156163"; // ← Modifiez ici
```
Format : code pays sans `+` suivi du numéro (ex : `212612345678`).
