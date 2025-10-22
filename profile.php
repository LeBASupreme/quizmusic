<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: profile.php?updated=1");
    exit;
}
$user = new User($_SESSION['user_id'], $_SESSION['user_pseudo'], $_SESSION['user_email'] ?? '');

$pdo = Database::getConnexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_pseudo'])) {
    $newPseudo = trim($_POST['new_pseudo']);

    if ($newPseudo !== '' && $newPseudo !== $_SESSION['user_pseudo']) {

        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE pseudo = ?");
        $check->execute([$newPseudo]);

        if ($check->fetchColumn() > 0) {
            $message = "Ce pseudo est déjà pris, choisis-en un autre.";
            $messageType = "error";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET pseudo = ? WHERE id = ?");
            $stmt->execute([$newPseudo, $_SESSION['user_id']]);

            $_SESSION['user_pseudo'] = $newPseudo;
            header("Location: profile.php?updated=1");
            exit;
        }
    }
}

$stmt = $pdo->prepare("
    SELECT email, created_at
    FROM users
    WHERE id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$info = $stmt->fetch();

$stmt2 = $pdo->prepare("
    SELECT COUNT(*) FROM scores WHERE user_id = ?
");
$stmt2->execute([$_SESSION['user_id']]);
$totalParties = $stmt2->fetchColumn();

$stmt3 = $pdo->prepare("
    SELECT q.titre, COUNT(*) as nb
    FROM scores s
    JOIN questionnaires q ON s.questionnaire_id = q.id
    WHERE s.user_id = ?
    GROUP BY q.titre
    ORDER BY nb DESC
    LIMIT 1
");
$stmt3->execute([$_SESSION['user_id']]);
$themePref = $stmt3->fetchColumn() ?: 'Aucun';




$stmt = $pdo->prepare("
    SELECT b.nom, b.description, b.icone, ub.date_obtenu
    FROM user_badges ub
    JOIN badges b ON ub.badge_id = b.id
    WHERE ub.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$badges = $stmt->fetchAll();
?>



<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon Profil - QuizMusic</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 min-h-screen text-white">
  <div class="container mx-auto px-6 py-12 max-w-3xl">
    <h1 class="text-4xl font-bold mb-8">👤 Mon profil</h1>

    <?php if (isset($_GET['updated'])): ?>
      <div class="bg-green-600/40 text-green-200 p-4 rounded-xl mb-4">
        ✅ Pseudo mis à jour avec succès !
      </div>
    <?php endif; ?>

    <div class="bg-white/10 rounded-2xl p-6 mb-6">
      <p><strong>Pseudo :</strong> <?= htmlspecialchars($_SESSION['user_pseudo']) ?></p>
      <p><strong>Email :</strong> <?= htmlspecialchars($info['email']) ?></p>
      <p><strong>Date d'inscription :</strong> <?= htmlspecialchars($info['created_at']) ?></p>
      <p><strong>Parties jouées :</strong> <?= $totalParties ?></p>
      <p><strong>Thème préféré :</strong> <?= htmlspecialchars($themePref) ?></p>
    </div>

    <form method="POST" class="bg-white/10 p-6 rounded-2xl">
      <label class="block mb-2 font-semibold">Modifier mon pseudo :</label>
      <input 
        type="text" 
        name="new_pseudo" 
        class="w-full p-2 rounded text-black mb-4"
        value="<?= htmlspecialchars($_SESSION['user_pseudo']) ?>"
        placeholder="Nouveau pseudo"
      >
      <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
        Mettre à jour
      </button>
    </form>

    
<div class="bg-white/10 rounded-2xl p-6 mt-6">
  <h2 class="text-2xl font-bold mb-4">🏅 Mes badges</h2>
  <?php if (empty($badges)): ?>
    <p>Aucun badge débloqué pour le moment 😅</p>
  <?php else: ?>
    <div class="flex flex-wrap gap-4">
      <?php foreach ($badges as $badge): ?>
        <div class="bg-white/10 p-4 rounded-xl shadow text-center">
          <div class="text-3xl"><?= htmlspecialchars($badge['icone']) ?></div>
          <p class="font-semibold"><?= htmlspecialchars($badge['nom']) ?></p>
          <p class="text-sm text-white/60"><?= htmlspecialchars($badge['description']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

    <div class="mt-6">
      <a href="index.php" class="text-purple-300 hover:text-white">← Retour à l'accueil</a>
    </div>
  </div>
</body>
</html>
