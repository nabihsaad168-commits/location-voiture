<?php
/**
 * contact_handler.php
 * Traitement du formulaire de contact – Auto SNH & MEL
 *
 * Utilisation : appel AJAX depuis contact.html (optionnel)
 * ou action directe du formulaire HTML.
 *
 * Configuration : modifiez les constantes ci-dessous.
 */

// ── Configuration ──────────────────────────────────────
define('DESTINATAIRE',  'votre-email@exemple.com');   // ← Votre adresse email
define('EXPEDITEUR',    'noreply@snh-mel.ma');         // ← Adresse expéditeur
define('NOM_SITE',      'Auto SNH & MEL');
define('URL_SUCCES',    'contact.html?success=1');
define('URL_ERREUR',    'contact.html?error=1');
// ───────────────────────────────────────────────────────

// Accepter uniquement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

// ── Fonctions utilitaires ──────────────────────────────
function nettoyer(string $val): string {
    return htmlspecialchars(trim(strip_tags($val)), ENT_QUOTES, 'UTF-8');
}

function validerEmail(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validerTelephone(string $tel): bool {
    return (bool) preg_match('/^[+\d][\d\s\-]{7,15}$/', $tel);
}

// ── Récupération & nettoyage des données ──────────────
$nom       = nettoyer($_POST['nom']       ?? '');
$prenom    = nettoyer($_POST['prenom']    ?? '');
$email     = nettoyer($_POST['email']     ?? '');
$telephone = nettoyer($_POST['telephone'] ?? '');
$voiture   = nettoyer($_POST['voiture']   ?? '');
$message   = nettoyer($_POST['message']   ?? '');

// ── Validation côté serveur ────────────────────────────
$erreurs = [];

if (empty($nom))                     $erreurs[] = "Le nom est requis.";
if (empty($prenom))                  $erreurs[] = "Le prénom est requis.";
if (!validerEmail($email))           $erreurs[] = "L'adresse email est invalide.";
if (!validerTelephone($telephone))   $erreurs[] = "Le numéro de téléphone est invalide.";

if (!empty($erreurs)) {
    // En mode AJAX, renvoyer JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'erreurs' => $erreurs]);
    } else {
        header('Location: ' . URL_ERREUR);
    }
    exit;
}

// ── Composition de l'email ────────────────────────────
$sujet  = "=?UTF-8?B?" . base64_encode("[" . NOM_SITE . "] Nouveau message de $nom $prenom") . "?=";

$corps  = "Nouveau message depuis le site " . NOM_SITE . "\n";
$corps .= str_repeat("─", 40) . "\n\n";
$corps .= "Nom       : $nom\n";
$corps .= "Prénom    : $prenom\n";
$corps .= "Email     : $email\n";
$corps .= "Téléphone : $telephone\n";
if (!empty($voiture)) {
    $corps .= "Voiture   : $voiture\n";
}
if (!empty($message)) {
    $corps .= "\nMessage :\n$message\n";
}
$corps .= "\n" . str_repeat("─", 40) . "\n";
$corps .= "Envoyé le : " . date('d/m/Y à H:i') . "\n";

// Headers email
$headers  = "From: " . NOM_SITE . " <" . EXPEDITEUR . ">\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "Content-Transfer-Encoding: 8bit\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// ── Envoi de l'email ──────────────────────────────────
$envoi = mail(DESTINATAIRE, $sujet, $corps, $headers);

// ── Réponse ───────────────────────────────────────────
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    // Réponse AJAX
    header('Content-Type: application/json; charset=utf-8');
    if ($envoi) {
        echo json_encode(['success' => true, 'message' => "Message envoyé avec succès !"]);
    } else {
        echo json_encode(['success' => false, 'erreurs' => ["Erreur lors de l'envoi. Réessayez."]]);
    }
} else {
    // Redirection classique
    header('Location: ' . ($envoi ? URL_SUCCES : URL_ERREUR));
}
exit;
