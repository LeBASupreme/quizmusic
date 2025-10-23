<?php
class Quiz {
    private array $questions = [];
    private string $themeCode;
    private array $themeInfo;

    public function __construct(string $themeCode, ?array $questionIds = null) {
        $this->themeCode = $themeCode;
        $this->chargerTheme();
        if (!empty($questionIds)) {
            $this->chargerQuestionsParIds($questionIds);
        } else {
            $this->chargerQuestionsAleatoires();
        }
    }

    private function chargerTheme(): void {
        $pdo = Database::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM questionnaires WHERE code = ? AND actif = 1");
        $stmt->execute([$this->themeCode]);
        $this->themeInfo = $stmt->fetch();
        if (!$this->themeInfo) {
            throw new Exception("❌ Questionnaire introuvable : " . htmlspecialchars($this->themeCode));
        }
    }

    private function normaliserBonneReponse($raw): int {
        $val = strtoupper(trim((string)$raw));
        if (is_numeric($val)) return (int)$val;
        $map = ['A'=>0,'B'=>1,'C'=>2,'D'=>3];
        return $map[$val] ?? 0;
    }

    private function instancierQuestion(array $ligne): void {
        $reponses = [
            $ligne['reponse_a'],
            $ligne['reponse_b'],
            $ligne['reponse_c'],
            $ligne['reponse_d']
        ];
        $bonne = $this->normaliserBonneReponse($ligne['bonne_reponse']);

        switch ($ligne['type_question']) {
            case 'image':
                $q = new QuestionImage(
                    (int)$ligne['id'], $ligne['question'],
                    $reponses, $bonne, $ligne['media_url'],
                    $ligne['explication']
                );
                break;
            case 'audio':
                $q = new QuestionAudio(
                    (int)$ligne['id'], $ligne['question'],
                    $reponses, $bonne, $ligne['media_url'],
                    $ligne['explication']
                );
                break;
            default:
                $q = new QuestionTexte(
                    (int)$ligne['id'], $ligne['question'],
                    $reponses, $bonne,
                    $ligne['explication']
                );
        }
        $this->questions[] = $q;
    }

    private function chargerQuestionsAleatoires(): void {
        $pdo = Database::getConnexion();
        $stmt = $pdo->prepare("
            SELECT * FROM questions
            WHERE questionnaire_id = ?
            ORDER BY RAND()
            LIMIT 5
        ");
        $stmt->execute([$this->themeInfo['id']]);
        foreach ($stmt->fetchAll() as $ligne) {
            $this->instancierQuestion($ligne);
        }
    }

    // ➕ NOUVELLE : charger exactement les mêmes questions, dans le même ordre
    private function chargerQuestionsParIds(array $ids): void {
        $pdo = Database::getConnexion();
        if (empty($ids)) return;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("
            SELECT * FROM questions
            WHERE questionnaire_id = ? AND id IN ($placeholders)
        ");
        $params = array_merge([$this->themeInfo['id']], $ids);
        $stmt->execute($params);

        $byId = [];
        foreach ($stmt->fetchAll() as $l) $byId[(int)$l['id']] = $l;

        foreach ($ids as $qid) {
            if (!isset($byId[$qid])) continue;
            $this->instancierQuestion($byId[$qid]);
        }
    }

    // Score simple
    public function calculerScore(array $reponsesUtilisateur): int {
        $score = 0;
        foreach ($this->questions as $index => $question) {
            $user = isset($reponsesUtilisateur[$index]) ? (int)$reponsesUtilisateur[$index] : -1;
            if ($question->estCorrect($user)) $score++;
        }
        return $score;
    }

    // ➕ NOUVELLE : correction détaillée pour debug & affichage
    public function corriger(array $reponsesUtilisateur): array {
        $details = [];
        $score = 0;
        $letters = ['A','B','C','D'];

        foreach ($this->questions as $index => $question) {
            $userIdx = isset($reponsesUtilisateur[$index]) ? (int)$reponsesUtilisateur[$index] : -1;
            $ok = $question->estCorrect($userIdx);
            if ($ok) $score++;

            $repText = $question->getReponses();
            $details[] = [
                'index' => $index,
                'question_id' => $question->getId(),
                'type' => $question->getType(),
                'question' => $question->getTexteQuestion(),
                'user_index' => $userIdx,
                'user_letter' => $letters[$userIdx] ?? '?',
                'user_text' => $repText[$userIdx] ?? null,
                'good_index' => $question->getBonneReponse(),
                'good_letter' => $letters[$question->getBonneReponse()] ?? '?',
                'good_text' => $repText[$question->getBonneReponse()] ?? null,
                'is_correct' => $ok,
            ];
        }

        return ['score' => $score, 'total' => count($this->questions), 'details' => $details];
    }

    public function sauvegarderScore(int $userId, int $score, ?int $tempsSecondes = null): void {
        $pdo = Database::getConnexion();
        $stmt = $pdo->prepare("
            INSERT INTO scores (user_id, questionnaire_id, score, total_questions, temps_seconde)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $this->themeInfo['id'], $score, count($this->questions), $tempsSecondes]);
    }

    public function getQuestions(): array { return $this->questions; }
    public function getThemeInfo(): array { return $this->themeInfo; }
}