<?php
/**
 * QuizMusic - Page d'accueil refactorisée avec authentification
 * Jour 4 : Gestion des utilisateurs connectés
 */

// 📚 CONCEPT : Démarrage de session en premier
session_start();

// 📚 Chargement des classes nécessaires
require_once 'classes/Database.php';

// 📚 CONCEPT : Vérification de connexion
// Si l'utilisateur n'est pas connecté, on le redirige vers login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 📚 CONCEPT : Affichage d'un message de succès (Flash Message)
// Ce message s'affiche une seule fois puis disparaît
$messageSucces = '';
if (isset($_SESSION['message_succes'])) {
    $messageSucces = $_SESSION['message_succes'];
    // On supprime le message après l'avoir récupéré
    unset($_SESSION['message_succes']);
}

// 📚 Chargement des questionnaires depuis la BDD
// Au lieu d'avoir un tableau hard-codé, on les récupère de la BDD
$pdo = Database::getConnexion();
$stmt = $pdo->query("
    SELECT *
    FROM questionnaires
    WHERE actif = 1
    ORDER BY difficulte ASC
");
$questionnaires = $stmt->fetchAll();

// 📚 CONCEPT : Fonction pour afficher la difficulté
function afficherDifficulte($niveau) {
    $etoiles = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $niveau) {
            $etoiles .= '⭐';
        } else {
            $etoiles .= '☆';
        }
    }
    return $etoiles;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuizMusic 🎵 - Testez vos connaissances musicales !</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                backgroundImage: {
                    'gradient-quiz': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    'gradient-alt': 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'
                }
            }
        }
    }
    </script>
    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">

        <!-- 📚 NOUVEAU : Barre de navigation avec profil utilisateur -->
        <nav class="flex justify-between items-center mb-8">
            <div class="text-white">
                <h2 class="text-xl font-semibold">
                    👋 Bienvenue, <?php echo htmlspecialchars($_SESSION['user_pseudo']); ?> !
                </h2>
            </div>

            <div class="flex gap-4">
                <!-- Lien vers l'historique -->
                <a href="historique.php"
                   class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                    📊 Mon historique
                </a>

                <!-- Lien de déconnexion -->
                <a href="logout.php"
                   class="bg-red-500/80 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-all duration-200">
                    🚪 Déconnexion
                </a>
            </div>
        </nav>

        <!-- 📚 NOUVEAU : Affichage du message de succès (inscription réussie) -->
        <?php if ($messageSucces): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-xl mb-8 animate-pulse">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">✅</span>
                    <span class="font-medium"><?php echo htmlspecialchars($messageSucces); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- En-tête avec titre principal -->
        <header class="text-center mb-12">
            <h1 class="text-5xl md:text-6xl font-bold text-white mb-4">
                🎵 QuizMusic
            </h1>
            <p class="text-xl md:text-2xl text-purple-200 mb-2">
                Testez vos connaissances musicales !
            </p>
            <p class="text-lg text-purple-300">
                Choisissez votre thème et découvrez votre niveau musical
            </p>
        </header>

        <!-- Grille des questionnaires -->
        <main class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <?php
            // 📚 CONCEPT : Boucle sur les données de la BDD
            // Au lieu d'un tableau PHP, on parcourt les résultats SQL
            foreach ($questionnaires as $quiz):
            ?>
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden transform hover:scale-105 transition-all duration-300 hover:shadow-2xl">

                <!-- En-tête colorée de la card -->
                <div class="bg-gradient-to-r <?php echo $quiz['couleur']; ?> p-6 text-white">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-4xl"><?php echo $quiz['emoji']; ?></span>
                        <!-- 📚 NOTE : On pourrait afficher le nombre de questions depuis la BDD -->
                        <span class="text-sm font-medium bg-white/20 px-2 py-1 rounded-full">
                            5 questions
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($quiz['titre']); ?></h3>
                    <div class="text-sm opacity-90">
                        Difficulté : <?php echo afficherDifficulte($quiz['difficulte']); ?>
                    </div>
                </div>

                <!-- Contenu de la card -->
                <div class="p-6">
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        <?php echo htmlspecialchars($quiz['description']); ?>
                    </p>

                    <!-- Bouton pour démarrer le quiz -->
                    <a href="quiz.php?theme=<?php echo urlencode($quiz['code']); ?>"
                        class="block w-full bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl text-center transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        🎯 Commencer le quiz
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </main>

        <!-- Section informations -->
        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 text-center">
            <h2 class="text-2xl font-bold text-white mb-4">
                🏆 Comment ça marche ?
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-purple-200">
                <div>
                    <div class="text-3xl mb-2">🎯</div>
                    <h3 class="font-semibold mb-2">Choisissez</h3>
                    <p class="text-sm">Sélectionnez votre thème musical préféré</p>
                </div>
                <div>
                    <div class="text-3xl mb-2">🎵</div>
                    <h3 class="font-semibold mb-2">Jouez</h3>
                    <p class="text-sm">Répondez aux 5 questions sans stress</p>
                </div>
                <div>
                    <div class="text-3xl mb-2">🏅</div>
                    <h3 class="font-semibold mb-2">Progressez</h3>
                    <p class="text-sm">Découvrez votre niveau et consultez votre historique</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-8">
        <p class="text-purple-300 text-sm">
            🎓 QuizMusic - Projet pédagogique PHP Jour 4 (Architecture POO + BDD)
        </p>
    </footer>
</body>

</html>
