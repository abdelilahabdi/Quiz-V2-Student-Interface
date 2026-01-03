<?php


require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
require_once '../../classes/Category.php';
require_once '../../classes/Quiz.php';
require_once '../../classes/Result.php';


Security::requireStudent();


$currentPage = 'dashboard';
$pageTitle = 'Tableau de bord';


$studentId = $_SESSION['user_id'];
$userName = $_SESSION['user_nom'];


$categoryObj = new Category();
$quizObj = new Quiz();
$resultObj = new Result();

$categories = $categoryObj->getCategoriesWithActiveQuizzes();
$recentQuizzes = $quizObj->getAllActiveQuizzes(6);
$myStats = $resultObj->getMyStats($studentId);


$success = $_SESSION['quiz_success'] ?? '';
$error = $_SESSION['quiz_error'] ?? '';
unset($_SESSION['quiz_success'], $_SESSION['quiz_error']);
?>
<?php include '../partials/header.php'; ?>

<?php include '../partials/nav_student.php'; ?>


<div class="pt-16">
    
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-4xl font-bold mb-4">
                <i class="fas fa-user-graduate mr-3"></i>Bienvenue, <?= htmlspecialchars(explode(' ', $userName)[0]) ?> !
            </h1>
            <p class="text-xl text-blue-100 mb-6">Prêt à tester vos connaissances aujourd'hui ?</p>
            <div class="flex gap-4">
                <a href="categories.php" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition">
                    <i class="fas fa-folder-open mr-2"></i>Parcourir les Catégories
                </a>
            </div>
        </div>
    </div>

    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php include '../partials/alerts.php'; ?>
        
        <h2 class="text-2xl font-bold text-gray-900 mb-6">
            <i class="fas fa-chart-bar mr-2 text-blue-600"></i>Mes Statistiques
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Quiz Passés</p>
                        <p class="text-3xl font-bold text-gray-900"><?= $myStats['total_quiz'] ?? 0 ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-clipboard-check text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Moyenne</p>
                        <p class="text-3xl font-bold text-gray-900">
                            <?= $myStats['moyenne'] ? round($myStats['moyenne'], 1) . '%' : '-' ?>
                        </p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-percentage text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Meilleur Score</p>
                        <p class="text-3xl font-bold text-gray-900">
                            <?= $myStats['meilleur_score'] ? round($myStats['meilleur_score'], 1) . '%' : '-' ?>
                        </p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <i class="fas fa-trophy text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-folder-open mr-2 text-blue-600"></i>Catégories Disponibles
                </h2>
                <a href="categories.php" class="text-blue-600 hover:text-blue-700 font-semibold">
                    Voir tout <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <?php if (empty($categories)): ?>
                <div class="bg-white rounded-xl shadow-md p-8 text-center">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Aucune catégorie disponible</h3>
                    <p class="text-gray-600">Les quiz seront bientôt disponibles !</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php 
                    $colors = ['blue', 'purple', 'green', 'red', 'yellow', 'pink', 'indigo', 'teal'];
                    foreach (array_slice($categories, 0, 4) as $index => $category): 
                        $color = $colors[$index % count($colors)];
                    ?>
                        <a href="quizzes.php?category_id=<?= $category['id'] ?>" 
                           class="bg-white rounded-xl shadow-md p-6 border-l-4 border-<?= $color ?>-500 hover:shadow-xl transition group">
                            <div class="flex items-center mb-3">
                                <div class="w-12 h-12 bg-<?= $color ?>-100 rounded-lg flex items-center justify-center group-hover:bg-<?= $color ?>-200 transition">
                                    <i class="fas fa-folder text-<?= $color ?>-600 text-xl"></i>
                                </div>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2"><?= htmlspecialchars($category['nom']) ?></h3>
                            <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars(substr($category['description'], 0, 60)) ?>...</p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-<?= $color ?>-600 font-semibold">
                                    <?= $category['active_quiz_count'] ?> quiz
                                </span>
                                <i class="fas fa-arrow-right text-<?= $color ?>-600 group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-clock mr-2 text-blue-600"></i>Quiz Récents
            </h2>
            
            <?php if (empty($recentQuizzes)): ?>
                <div class="bg-white rounded-xl shadow-md p-8 text-center">
                    <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Aucun quiz disponible</h3>
                    <p class="text-gray-600">Revenez plus tard pour découvrir de nouveaux quiz !</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($recentQuizzes as $quiz): ?>
                        <?php 
                        $alreadyTaken = $quizObj->hasStudentTakenQuiz($quiz['id'], $studentId);
                        ?>
                        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                                        <?= htmlspecialchars($quiz['categorie_nom']) ?>
                                    </span>
                                    <?php if ($alreadyTaken): ?>
                                        <span class="text-green-600 text-xs font-semibold">
                                            <i class="fas fa-check-circle"></i> Terminé
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <h3 class="text-xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($quiz['titre']) ?></h3>
                                <p class="text-gray-600 mb-4 text-sm"><?= htmlspecialchars(substr($quiz['description'], 0, 80)) ?>...</p>
                                
                                <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                    <span><i class="fas fa-question-circle mr-1"></i><?= $quiz['questions_count'] ?> questions</span>
                                    <span class="text-xs"><?= date('d/m/Y', strtotime($quiz['created_at'])) ?></span>
                                </div>
                                
                                <?php if ($alreadyTaken): ?>
                                    <button disabled class="w-full bg-gray-300 text-gray-600 py-2 rounded-lg font-semibold cursor-not-allowed">
                                        <i class="fas fa-check mr-2"></i>Déjà passé
                                    </button>
                                <?php else: ?>
                                    <a href="quiz_take.php?id=<?= $quiz['id'] ?>" 
                                       class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                                        <i class="fas fa-play-circle mr-2"></i>Commencer
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>