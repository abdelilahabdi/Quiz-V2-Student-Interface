<?php


require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
require_once '../../classes/Category.php';
require_once '../../classes/Quiz.php';


Security::requireStudent();


$currentPage = 'categories';
$pageTitle = 'Quiz';


$studentId = $_SESSION['user_id'];
$userName = $_SESSION['user_nom'];
$categoryId = intval($_GET['category_id'] ?? 0);

if ($categoryId <= 0) {
    $_SESSION['quiz_error'] = 'Catégorie invalide';
    header('Location: categories.php');
    exit();
}

$categoryObj = new Category();
$quizObj = new Quiz();

$category = $categoryObj->getCategoryWithActiveQuizCount($categoryId);
$quizzes = $quizObj->getActiveQuizzesByCategory($categoryId);


if (!$category) {
    $_SESSION['quiz_error'] = 'Catégorie non trouvée';
    header('Location: categories.php');
    exit();
}
?>
<?php include '../partials/header.php'; ?>

<?php include '../partials/nav_student.php'; ?>

<!-- Main Content -->
<div class="pt-16">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <nav class="mb-4">
                <a href="categories.php" class="text-blue-100 hover:text-white transition">
                    <i class="fas fa-arrow-left mr-2"></i>Retour aux catégories
                </a>
            </nav>
            
            <div class="flex items-center mb-4">
                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mr-4">
                    <i class="fas fa-folder text-white text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-4xl font-bold mb-2"><?= htmlspecialchars($category['nom']) ?></h1>
                    <p class="text-xl text-blue-100"><?= htmlspecialchars($category['description']) ?></p>
                </div>
            </div>
            
            <div class="flex items-center gap-6 mt-6">
                <div class="flex items-center">
                    <i class="fas fa-clipboard-list mr-2 text-blue-200"></i>
                    <span class="text-lg"><?= $category['active_quiz_count'] ?> quiz disponible<?= $category['active_quiz_count'] > 1 ? 's' : '' ?></span>
                </div>
            </div>
        </div>
    </div>

 
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (empty($quizzes)): ?>
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <i class="fas fa-clipboard-list text-8xl text-gray-300 mb-6"></i>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">Aucun quiz disponible</h3>
                <p class="text-gray-600 text-lg mb-6">
                    Il n'y a pas encore de quiz actifs dans cette catégorie. Revenez plus tard !
                </p>
                <a href="categories.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Retour aux catégories
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($quizzes as $quiz): ?>
                    <?php 
                    $alreadyTaken = $quizObj->hasStudentTakenQuiz($quiz['id'], $studentId);
                    ?>
                    <div class="bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden">
                        
                        <?php if ($alreadyTaken): ?>
                            <div class="bg-green-500 text-white text-center py-2 text-sm font-semibold">
                                <i class="fas fa-check-circle mr-2"></i>Quiz terminé
                            </div>
                        <?php else: ?>
                            <div class="bg-blue-500 text-white text-center py-2 text-sm font-semibold">
                                <i class="fas fa-star mr-2"></i>Nouveau
                            </div>
                        <?php endif; ?>
                        
                        
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-3">
                                <?= htmlspecialchars($quiz['titre']) ?>
                            </h3>
                            
                            <p class="text-gray-600 mb-4 text-sm line-clamp-3">
                                <?= htmlspecialchars($quiz['description']) ?>
                            </p>
                            
                            
                            <div class="space-y-2 mb-6">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-question-circle mr-3 text-blue-600 w-5"></i>
                                    <span><?= $quiz['questions_count'] ?> question<?= $quiz['questions_count'] > 1 ? 's' : '' ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-calendar mr-3 text-blue-600 w-5"></i>
                                    <span>Créé le <?= date('d/m/Y', strtotime($quiz['created_at'])) ?></span>
                                </div>
                            </div>
                            
                           
                            <?php if ($alreadyTaken): ?>
                                <div class="space-y-2">
                                    <button disabled class="w-full bg-gray-200 text-gray-600 py-3 rounded-lg font-semibold cursor-not-allowed">
                                        <i class="fas fa-check mr-2"></i>Déjà passé
                                    </button>
                                    <a href="my_results.php" 
                                       class="block w-full text-center border-2 border-blue-600 text-blue-600 py-3 rounded-lg font-semibold hover:bg-blue-50 transition">
                                        <i class="fas fa-chart-line mr-2"></i>Voir mes résultats
                                    </a>
                                </div>
                            <?php else: ?>
                                <a href="quiz_take.php?id=<?= $quiz['id'] ?>" 
                                   class="block w-full text-center bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition group">
                                    <i class="fas fa-play-circle mr-2"></i>Commencer le quiz
                                    <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform inline-block"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

           
        <?php endif; ?>
  


<?php include '../partials/footer.php'; ?>