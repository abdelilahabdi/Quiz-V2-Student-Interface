<?php


require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/Security.php';
require_once '../classes/Quiz.php';
require_once '../classes/Question.php';
require_once '../classes/Result.php';

// verifier utilisateur etudiant
Security::requireStudent();

// verifier methode post
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/student/categories.php');
    exit();
}

// verifier le token crsf
if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
    $_SESSION['quiz_error'] = 'Token de sécurité invalide';
    header('Location: ../pages/student/categories.php');
    exit();
}

// recuperer les donnes
$studentId = $_SESSION['user_id'];
$quizId = intval($_POST['quiz_id'] ?? 0);
$answers = $_POST['answers'] ?? [];

// validation
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

// creer les objets
$quizObj = new Quiz();
$questionObj = new Question();
$resultObj = new Result();

// verifier que le quiz actif
$quiz = $quizObj->getActiveQuizById($quizId);
if (!$quiz) {
    $_SESSION['quiz_error'] = 'Quiz non trouvé ou inactif';
    header('Location: ../pages/student/categories.php');
    exit();
}

// verifier si deja passe
if ($quizObj->hasStudentTakenQuiz($quizId, $studentId)) {
    $_SESSION['quiz_error'] = 'Vous avez déjà passé ce quiz';
    header('Location: ../pages/student/my_results.php');
    exit();
}

// recuperer toute les questions du quiz
$questions = $questionObj->getAllByQuiz($quizId);

if (empty($questions)) {
    $_SESSION['quiz_error'] = 'Ce quiz ne contient aucune question';
    header('Location: ../pages/student/categories.php');
    exit();
}

// verifier que toutes les questions ont ete repondues
$totalQuestions = count($questions);
if (count($answers) < $totalQuestions) {
    $_SESSION['quiz_error'] = 'Vous devez répondre à toutes les questions';
    header('Location: ../pages/student/quiz_take.php?id=' . $quizId);
    exit();
}


// calcul du score

$score = 0;
$detailedResults = [];

foreach ($questions as $question) {
    $questionId = $question['id'];
    $correctOption = $question['correct_option'];
    $studentAnswer = intval($answers[$questionId] ?? 0);
    
    // verifier reponse correcte
    $isCorrect = ($studentAnswer === $correctOption);
    
    if ($isCorrect) {
        $score++;
    }
    
    // stocker les details pour affichage (optionnel)
    $detailedResults[] = [
        'question_id' => $questionId,
        'question' => $question['question'],
        'student_answer' => $studentAnswer,
        'correct_answer' => $correctOption,
        'is_correct' => $isCorrect
    ];
}



// enregistrement du resultat 

$resultId = $resultObj->save($quizId, $studentId, $score, $totalQuestions);

if (!$resultId) {
    $_SESSION['quiz_error'] = 'Erreur lors de l\'enregistrement du résultat';
    header('Location: ../pages/student/categories.php');
    exit();
}





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