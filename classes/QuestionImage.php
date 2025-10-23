<?php
/**
 * Classe QuestionImage
 *
 * 📚 HÉRITAGE :
 * Hérite de la classe abstraite Question et ajoute l'affichage d'une image.
 * Sert à afficher des questions avec une "pochette" (cover, artiste, etc.).
 */

class QuestionImage extends Question {
    private string $mediaUrl; // URL ou chemin vers l'image

    /**
     * Constructeur
     */
    public function __construct(
        int $id,
        string $texteQuestion,
        array $reponses,
        int $bonneReponse,
        string $mediaUrl,
        ?string $explication = null
    ) {
        parent::__construct($id, $texteQuestion, $reponses, $bonneReponse, $explication);
        $this->mediaUrl = $mediaUrl;
    }

    /**
     * Getter pour l'URL du média
     */
    public function getMediaUrl(): string {
        return $this->mediaUrl;
    }

    /**
     * Génère le HTML complet pour une question avec image
     */
    public function afficherHTML(int $index): string {
        $html = '';

        // Conteneur principal
        $html .= '<div class="bg-white rounded-2xl shadow-xl p-6 mb-6">';

        // En-tête question
        $html .= '<div class="flex items-start gap-4 mb-6">';
        $html .= '<span class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm flex-shrink-0">';
        $html .= ($index + 1);
        $html .= '</span>';
        $html .= '<h3 class="text-xl font-semibold text-gray-800 leading-relaxed">';
        $html .= htmlspecialchars($this->texteQuestion);
        $html .= '</h3>';
        $html .= '</div>';

        // Image
        $html .= '<div class="mb-6 flex justify-center">';
        $html .= '<img src="' . htmlspecialchars($this->mediaUrl) . '" alt="illustration" ';
        $html .= 'class="max-w-md w-full rounded-xl shadow-lg object-cover">';
        $html .= '</div>';

        // Réponses
        $html .= '<div class="space-y-3 ml-2">';
        $lettres = ['A', 'B', 'C', 'D'];

        foreach ($this->reponses as $idx => $reponse) {
            $lettre = $lettres[$idx] ?? '?';

            $html .= '<label class="flex items-center p-4 rounded-xl border-2 border-gray-200 hover:border-purple-300 hover:bg-purple-50 cursor-pointer transition-all duration-200 group">';
            $html .= '<input type="radio" ';
            $html .= 'name="reponses[' . $index . ']" ';
            $html .= 'value="' . $idx . '" ';
            $html .= 'class="w-5 h-5 text-purple-600 border-gray-300 focus:ring-purple-500 focus:ring-2" ';
            $html .= 'required>';
            $html .= '<span class="ml-3 text-gray-700 group-hover:text-purple-700 font-medium">';
            $html .= '<span class="font-bold mr-1">' . $lettre . '.</span> ';
            $html .= htmlspecialchars($reponse);
            $html .= '</span>';
            $html .= '</label>';
        }

        $html .= '</div>'; // Fin réponses
        $html .= '</div>'; // Fin carte

        return $html;
    }

    public function getType(): string {
        return 'image';
    }
}
