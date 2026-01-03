<?php


require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
require_once '../../classes/Quiz.php';
require_once '../../classes/Question.php';


Security::requireStudent();


$studentId = $_SESSION['user_id'];
$userName = $_SESSION['user_nom'];
$quizId = intval($_GET['id'] ?? 0);

if ($quizId <= 0) {
    $_SESSION['quiz_error'] = 'Quiz invalide';
    header('Location: categories.php');
    exit();
}

$quizObj = new Quiz();
$questionObj = new Question();


$quiz = $quizObj->getActiveQuizById($quizId);


if (!$quiz) {
    $_SESSION['quiz_error'] = 'Quiz non trouvé ou inactif';
    header('Location: categories.php');
    exit();
}


if ($quizObj->hasStudentTakenQuiz($quizId, $studentId)) {
    $_SESSION['quiz_error'] = 'Vous avez déjà passé ce quiz';
    header('Location: my_results.php');
    exit();
}


$questions = $questionObj->getAllByQuiz($quizId);

if (empty($questions)) {
    $_SESSION['quiz_error'] = 'Ce quiz ne contient aucune question';
    header('Location: quizzes.php?category_id=' . $quiz['categorie_id']);
    exit();
}

$currentPage = 'quiz_take';
$pageTitle = $quiz['titre'];
?>
<?php include '../partials/header.php'; ?>

<?php include '../partials/nav_student.php'; ?>

<!-- Main Content -->
<div class="pt-16 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center mb-3">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm font-semibold rounded-full">
                            <?= htmlspecialchars($quiz['categorie_nom']) ?>
                        </span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        <?= htmlspecialchars($quiz['titre']) ?>
                    </h1>
                    <p class="text-gray-600 mb-4"><?= htmlspecialchars($quiz['description']) ?></p>
                    
                    <div class="flex items-center gap-6 text-sm text-gray-600">
                        <span><i class="fas fa-question-circle mr-2 text-blue-600"></i><?= count($questions) ?> questions</span>
                        <span><i class="fas fa-clock mr-2 text-blue-600"></i>Temps libre</span>
                    </div>
                </div>
                
                <div class="ml-6">
                    <div class="w-20 h-20 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-blue-600 text-4xl"></i>
                    </div>
                </div>
            </div>
        </div>

       

        <!-- Quiz Form -->
        <form action="../../actions/quiz_submit.php" method="POST" id="quizForm" onsubmit="return validateForm()">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
            
            <!-- Questions -->
            <div class="space-y-6">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="bg-white rounded-xl shadow-md p-6 question-card" id="question-<?= $index + 1 ?>">
                        <!-- Question Number -->
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold mr-3">
                                <?= $index + 1 ?>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Question <?= $index + 1 ?></h3>
                        </div>
                        
                        <!-- Question Text -->
                        <p class="text-gray-900 text-lg mb-6 font-medium pl-13">
                            <?= htmlspecialchars($question['question']) ?>
                        </p>
                        
                        <!-- Options -->
                        <div class="space-y-3 pl-13">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition group">
                                    <input type="radio" 
                                           name="answers[<?= $question['id'] ?>]" 
                                           value="<?= $i ?>" 
                                           class="w-5 h-5 text-blue-600 focus:ring-blue-500"
                                           required>
                                    <span class="ml-4 text-gray-900 group-hover:text-blue-700 flex-1">
                                        <span class="font-semibold mr-2">Option <?= $i ?>:</span>
                                        <?= htmlspecialchars($question['option' . $i]) ?>
                                    </span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Submit Section -->
            <div class="sticky bottom-0 bg-white border-t-4 border-blue-600 rounded-xl shadow-2xl p-6 mt-8">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">
                            <i class="fas fa-check-circle mr-2 text-green-600"></i>
                            <span id="answeredCount">0</span> / <?= count($questions) ?> questions répondues
                        </p>
                        <p class="text-xs text-gray-500">Assurez-vous d'avoir répondu à toutes les questions</p>
                    </div>
                    
                    <div class="flex gap-4">
                        <a href="quizzes.php?category_id=<?= $quiz['categorie_id'] ?>" 
                           class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition"
                           onclick="return confirm('Êtes-vous sûr de vouloir abandonner ce quiz ?')">
                            <i class="fas fa-times mr-2"></i>Annuler
                        </a>
                        
                        <button type="submit" 
                                class="px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition shadow-lg hover:shadow-xl">
                            <i class="fas fa-paper-plane mr-2"></i>Soumettre le quiz
                        </button>
                    </div>
                </div>
            </div>
        </form>

        



<?php include '../partials/footer.php'; ?>