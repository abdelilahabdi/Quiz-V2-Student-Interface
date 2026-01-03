<?php
/**
 * Action: Soumettre un Quiz
 * Traite les réponses, calcule le score et enregistre le résultat
 */

require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/Security.php';
require_once '../classes/Quiz.php';
require_once '../classes/Question.php';
require_once '../classes/Result.php';

// Vérifier que l'utilisateur est étudiant
Security::requireStudent();

// Vérifier la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/student/categories.php');
    exit();
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
    $_SESSION['quiz_error'] = 'Token de sécurité invalide';
    header('Location: ../pages/student/categories.php');
    exit();
}

// Récupérer les données
$studentId = $_SESSION['user_id'];
$quizId = intval($_POST['quiz_id'] ?? 0);
$answers = $_POST['answers'] ?? [];

// Validation
if ($quizId <= 0) {
    $_SESSION['quiz_error'] = 'Quiz invalide';
    header('Location: ../pages/student/categories.php');
    exit();
}

if (empty($answers)) {
    $_SESSION['quiz_error'] = 'Aucune réponse fournie';
    header('Location: ../pages/student/quiz_take.php?id=' . $quizId);
    exit();
}

// Créer les objets
$quizObj = new Quiz();
$questionObj = new Question();
$resultObj = new Result();

// Vérifier que le quiz est actif
$quiz = $quizObj->getActiveQuizById($quizId);
if (!$quiz) {
    $_SESSION['quiz_error'] = 'Quiz non trouvé ou inactif';
    header('Location: ../pages/student/categories.php');
    exit();
}

// Vérifier si déjà passé
if ($quizObj->hasStudentTakenQuiz($quizId, $studentId)) {
    $_SESSION['quiz_error'] = 'Vous avez déjà passé ce quiz';
    header('Location: ../pages/student/my_results.php');
    exit();
}

// Récupérer toutes les questions du quiz
$questions = $questionObj->getAllByQuiz($quizId);

if (empty($questions)) {
    $_SESSION['quiz_error'] = 'Ce quiz ne contient aucune question';
    header('Location: ../pages/student/categories.php');
    exit();
}

// Vérifier que toutes les questions ont été répondues
$totalQuestions = count($questions);
if (count($answers) < $totalQuestions) {
    $_SESSION['quiz_error'] = 'Vous devez répondre à toutes les questions';
    header('Location: ../pages/student/quiz_take.php?id=' . $quizId);
    exit();
}

// ============================================
// CALCUL DU SCORE
// ============================================

$score = 0;
$detailedResults = [];

foreach ($questions as $question) {
    $questionId = $question['id'];
    $correctOption = $question['correct_option'];
    $studentAnswer = intval($answers[$questionId] ?? 0);
    
    // Vérifier si la réponse est correcte
    $isCorrect = ($studentAnswer === $correctOption);
    
    if ($isCorrect) {
        $score++;
    }
    
    // Stocker les détails pour affichage (optionnel)
    $detailedResults[] = [
        'question_id' => $questionId,
        'question' => $question['question'],
        'student_answer' => $studentAnswer,
        'correct_answer' => $correctOption,
        'is_correct' => $isCorrect
    ];
}

// ============================================
// ENREGISTREMENT DU RÉSULTAT
// ============================================

$resultId = $resultObj->save($quizId, $studentId, $score, $totalQuestions);

if (!$resultId) {
    $_SESSION['quiz_error'] = 'Erreur lors de l\'enregistrement du résultat';
    header('Location: ../pages/student/categories.php');
    exit();
}

// ============================================
// ENREGISTREMENT DES RÉPONSES (OPTIONNEL)
// ============================================
// Si vous voulez garder une trace des réponses individuelles
/*
foreach ($answers as $questionId => $answer) {
    $questionObj->saveAnswer($studentId, $quizId, $questionId, $answer);
}
*/

// ============================================
// REDIRECTION VERS LA PAGE DE RÉSULTAT
// ============================================

$_SESSION['quiz_result'] = [
    'quiz_id' => $quizId,
    'quiz_titre' => $quiz['titre'],
    'score' => $score,
    'total' => $totalQuestions,
    'percentage' => round(($score / $totalQuestions) * 100, 1),
    'result_id' => $resultId
];

$_SESSION['quiz_success'] = 'Quiz soumis avec succès !';
header('Location: ../pages/student/quiz_result.php?id=' . $resultId);
exit();