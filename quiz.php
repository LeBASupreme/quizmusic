<?php
/**
 * Page Quiz – version refactorisée orientée objet
 * Utilise les classes Quiz, Question et leurs dérivées
 */

session_start();

// 🔒 Vérification de connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 📦 Chargement des classes
require_once 'classes/Database.php';
require_once 'classes/Question.php';
require_once 'classes/QuestionTexte.php';
require_once 'classes/QuestionImage.php';
require_once 'classes/QuestionAudio.php';
require_once 'classes/Quiz.php';
require_once 'classes/Badge.php';

// 📚 Récupération du thème
$theme = $_GET['theme'] ?? '';
if (empty($theme)) {
    header('Location: index.php');
    exit;
}

try {
    $badgeSystem = new Badge();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reponsesUtilisateur = $_POST['reponses'] ?? [];
        $tempsTotal = isset($_POST['temps_total']) ? (int)$_POST['temps_total'] : null;

        // 1) récupérer les mêmes IDs
        $idsCsv = trim($_POST['question_ids'] ?? '');
        $ids = $idsCsv !== '' ? array_map('intval', explode(',', $idsCsv)) : [];

        // 2) reconstruire le quiz AVEC ces IDs (pas d'aléatoire ici)
        $quiz = new Quiz($theme, $ids);

        // 3) correction + debug
        $resultat = $quiz->corriger($reponsesUtilisateur);
        $score = $resultat['score'];

        // 4) save DB
        $quiz->sauvegarderScore($_SESSION['user_id'], $score, $tempsTotal);

        // 5) session pour resultat.php (avec détails)
        $_SESSION['dernier_score'] = $score;
        $_SESSION['dernier_theme'] = $theme;
        $_SESSION['total_questions'] = $resultat['total'];
        $_SESSION['details_questions'] = $resultat['details']; // 💡 pour debug/affichage
        $_SESSION['question_ids'] = $ids; // trace

        header('Location: resultat.php');
        exit;
    }

    // GET : première arrivée -> tirer 5 questions
    $quiz = new Quiz($theme);
    $badgeSystem->verifierBadges($_SESSION['user_id']);
    $themeInfo = $quiz->getThemeInfo();
    $questions = $quiz->getQuestions();

    // ➜ Figer l'ordre des questions affichées
    $ids = array_map(fn($q) => $q->getId(), $questions);
    $idsCsv = implode(',', $ids);

} catch (Exception $e) {
    die("Erreur : " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz <?php echo htmlspecialchars($themeInfo['titre']); ?> - QuizMusic 🎵</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">

        <!-- 🏁 En-tête -->
        <header class="text-center mb-8">
            <a href="index.php" class="inline-block text-purple-300 hover:text-white transition-colors mb-4">
                ← Retour à l'accueil
            </a>

            <div class="bg-gradient-to-r <?php echo $themeInfo['couleur']; ?> rounded-2xl p-6 text-white mb-8 shadow-xl">
                <div class="text-4xl mb-2"><?php echo $themeInfo['emoji']; ?></div>
                <h1 class="text-3xl font-bold mb-2">
                    Quiz <?php echo htmlspecialchars($themeInfo['titre']); ?>
                </h1>
                <p class="text-white/80">
                    Bonjour <?php echo htmlspecialchars($_SESSION['user_pseudo']); ?> !<br>
                    Répondez aux 5 questions suivantes :
                </p>
            </div>
        </header>

        <!-- 🎯 Contenu principal -->
        <main>
            <form method="POST" class="space-y-8">
                <input type="hidden" name="temps_total" id="temps_total" value="0">
                <input type="hidden" name="question_ids" value="<?php echo htmlspecialchars($idsCsv); ?>">
                <?php foreach ($questions as $index => $question): ?>
                    <?php echo $question->afficherHTML($index); ?>
                <?php endforeach; ?>

                <div class="text-center">
                    <button type="submit"
                        class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-bold py-4 px-8 rounded-2xl text-lg shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-200">
                        🏆 Voir mes résultats !
                    </button>
                </div>
            </form>
        </main>
    </div>

    <!-- ⏱️ Script chronomètre -->
    <script>
        const debut = Date.now();
        document.querySelector('form').addEventListener('submit', () => {
            const tempsTotal = Math.round((Date.now() - debut) / 1000);
            document.getElementById('temps_total').value = tempsTotal;
        });
    </script>
</body>
</html>
