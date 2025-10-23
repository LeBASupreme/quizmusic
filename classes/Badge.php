<?php
require_once 'Database.php';

class Badge {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnexion();
    }

    // Vérifie et attribue les badges à un utilisateur
    public function verifierBadges($userId) {
        $this->badgePremierPas($userId);
        $this->badgeExplorateur($userId);
        $this->badgePerfectionniste($userId);
        $this->badgeMarathon($userId);
    }

    private function badgePremierPas($userId) {
        $sql = "SELECT COUNT(*) FROM scores WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $count = $stmt->fetchColumn();

        if ($count >= 1) {
            $this->attribuerBadge($userId, 'Premier pas');
        }
    }

    private function badgeExplorateur($userId) {
        $sql = "SELECT COUNT(DISTINCT questionnaire_id) FROM scores WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $count = $stmt->fetchColumn();

        $sql2 = "SELECT COUNT(*) FROM questionnaires";
        $totalThemes = $this->pdo->query($sql2)->fetchColumn();

        if ($count == $totalThemes) {
            $this->attribuerBadge($userId, 'Explorateur');
        }
    }

    private function badgePerfectionniste($userId) {
        $sql = "SELECT MAX(score) FROM scores WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $maxScore = $stmt->fetchColumn();

        if ($maxScore == 10) {
            $this->attribuerBadge($userId, 'Perfectionniste');
        }
    }

    private function badgeMarathon($userId) {
        $sql = "SELECT COUNT(*) FROM scores WHERE user_id = ? AND DATE(date_jeu) = CURDATE()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $count = $stmt->fetchColumn();

        if ($count >= 10) {
            $this->attribuerBadge($userId, 'Marathon');
        }
    }

    private function attribuerBadge($userId, $nomBadge) {
        $stmt = $this->pdo->prepare("SELECT id FROM badges WHERE nom = ?");
        $stmt->execute([$nomBadge]);
        $badgeId = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM user_badges WHERE user_id = ? AND badge_id = ?");
        $stmt->execute([$userId, $badgeId]);
        if ($stmt->fetchColumn() == 0) {
            $insert = $this->pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
            $insert->execute([$userId, $badgeId]);
        }
    }
}
