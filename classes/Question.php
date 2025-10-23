<?php
/**
 * Classe abstraite Question
 *
 * 📚 CONCEPT POO : ABSTRACTION
 * Une classe abstraite est un "modèle" qui définit un contrat
 * que toutes les classes filles DOIVENT respecter.
 *
 * On ne peut PAS instancier directement une classe abstraite :
 * ❌ $q = new Question(); // ERREUR !
 * ✅ $q = new QuestionTexte(); // OK !
 *
 * POURQUOI UTILISER UNE CLASSE ABSTRAITE ?
 * - Définir une structure commune à tous les types de questions
 * - Forcer les classes filles à implémenter certaines méthodes
 * - Éviter la duplication de code
 */

abstract class Question {
    // 📚 CONCEPT POO : ENCAPSULATION avec protected
    // protected = accessible dans cette classe ET dans les classes qui héritent
    // private = accessible uniquement dans cette classe
    // public = accessible partout

    // 📚 TYPAGE STRICT (PHP 7.4+)
    // On spécifie le type de chaque propriété pour plus de sécurité
    protected int $id;                    // Identifiant unique en BDD
    protected string $texteQuestion;       // Le texte de la question
    protected array $reponses;             // Tableau des 4 réponses possibles
    protected int $bonneReponse;           // Index de la bonne réponse (0, 1, 2 ou 3)
    protected ?string $explication;        // Explication (peut être null grâce au ?)

    /**
     * Constructeur de la classe
     *
     * 📚 CONCEPT : LE CONSTRUCTEUR
     * Méthode spéciale appelée automatiquement lors de la création d'un objet
     * Permet d'initialiser les propriétés avec des valeurs
     *
     * @param int $id Identifiant de la question en BDD
     * @param string $texteQuestion Le texte de la question
     * @param array $reponses Tableau des 4 réponses possibles
     * @param int $bonneReponse Index de la bonne réponse (0 à 3)
     * @param string|null $explication Explication optionnelle
     */
    public function __construct(
        int $id,
        string $texteQuestion,
        array $reponses,
        int $bonneReponse,
        ?string $explication = null  // 📚 Le ? signifie "peut être null"
    ) {
        // 📚 CONCEPT : $this
        // $this fait référence à l'instance courante de l'objet
        // $this->id signifie "la propriété $id de CET objet"
        $this->id = $id;
        $this->texteQuestion = $texteQuestion;
        $this->reponses = $reponses;
        $this->bonneReponse = $bonneReponse;
        $this->explication = $explication;
    }

    // 📚 CONCEPT POO : GETTERS (accesseurs)
    // Les getters permettent d'accéder aux propriétés private/protected
    // depuis l'extérieur de la classe, de manière contrôlée

    /**
     * Retourne l'ID de la question
     *
     * POURQUOI UN GETTER ?
     * Au lieu de faire : $question->id (impossible car protected)
     * On fait : $question->getId() (possible car public)
     */
    public function getId(): int {
        return $this->id;
    }

    public function getTexteQuestion(): string {
        return $this->texteQuestion;
    }

    public function getReponses(): array {
        return $this->reponses;
    }

    public function getBonneReponse(): int {
        return $this->bonneReponse;
    }

    public function getExplication(): ?string {
        return $this->explication;
    }

    /**
     * Vérifie si la réponse de l'utilisateur est correcte
     *
     * 📚 CONCEPT : MÉTHODE MÉTIER
     * Une méthode métier encapsule une logique spécifique
     * Ici, la vérification d'une réponse
     *
     * @param int $reponseUtilisateur L'index choisi par l'utilisateur
     * @return bool true si correct, false sinon
     */
    public function estCorrect(int $reponseUtilisateur): bool {
        return $reponseUtilisateur === $this->bonneReponse;
    }




    /**
     * Génère le code HTML pour afficher la question
     *
     * 📚 CONCEPT POO : MÉTHODE ABSTRAITE
     * abstract = cette méthode n'a PAS d'implémentation ici
     * Chaque classe fille DOIT l'implémenter avec son propre code
     *
     * POURQUOI ?
     * Parce que l'affichage est différent selon le type de question :
     * - QuestionTexte affiche juste du texte
     * - QuestionImage affiche une image
     * - QuestionAudio affiche un lecteur audio
     *
     * @param int $index Position de la question dans le quiz (pour l'affichage)
     * @return string Code HTML généré
     */
    abstract public function afficherHTML(int $index): string;

    /**
     * Retourne le type de question
     *
     * 📚 CONCEPT : POLYMORPHISME
     * Chaque classe fille retournera son propre type
     * Cela permet de traiter différemment chaque type
     *
     * @return string 'texte', 'image' ou 'audio'
     */
    abstract public function getType(): string;
}
