<?php


require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
require_once '../../classes/Category.php';


Security::requireStudent();


$currentPage = 'categories';
$pageTitle = 'Catégories';


$studentId = $_SESSION['user_id'];
$userName = $_SESSION['user_nom'];

$categoryObj = new Category();
$categories = $categoryObj->getCategoriesWithActiveQuizzes();
?>
<?php include '../partials/header.php'; ?>

<?php include '../partials/nav_student.php'; ?>

<!-- Main Content -->
<div class="pt-16">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-4xl font-bold mb-4">
                <i class="fas fa-folder-open mr-3"></i>Explorer les Catégories
            </h1>
            <p class="text-xl text-blue-100">Choisissez une catégorie pour voir les quiz disponibles</p>
        </div>
    </div>

    <!-- Catégories Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (empty($categories)): ?>
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <i class="fas fa-folder-open text-8xl text-gray-300 mb-6"></i>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">Aucune catégorie disponible</h3>
                <p class="text-gray-600 text-lg mb-6">Il n'y a pas encore de quiz disponibles. Revenez plus tard !</p>
                <a href="dashboard.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    <i class="fas fa-home mr-2"></i>Retour à l'accueil
                </a>
            </div>
        <?php else: ?>
            <div class="mb-6">
                <p class="text-gray-600">
                    <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                    <?= count($categories) ?> catégorie<?= count($categories) > 1 ? 's' : '' ?> disponible<?= count($categories) > 1 ? 's' : '' ?>
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php 
                $colors = ['blue', 'purple', 'green', 'red', 'yellow', 'pink', 'indigo', 'teal', 'orange', 'cyan'];
                foreach ($categories as $index => $category): 
                   $color = $colors[$index % count($colors)];
                ?>
                    <a href="quizzes.php?category_id=<?= $category['id'] ?>" 
                       class="group bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border-l-4 border-<?= $color ?>-500">
                        <!-- Header -->
                        <div class="bg-gradient-to-br from-<?= $color ?>-50 to-white p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-16 h-16 bg-<?= $color ?>-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <i class="fas fa-folder text-<?= $color ?>-600 text-3xl"></i>
                                </div>
                                <span class="px-4 py-2 bg-<?= $color ?>-100 text-<?= $color ?>-700 text-sm font-bold rounded-full">
                                    <?= $category['active_quiz_count'] ?> quiz
                                </span>
                            </div>
                            
                            <h3 class="text-2xl font-bold text-gray-900 mb-2 group-hover:text-<?= $color ?>-600 transition">
                                <?= htmlspecialchars($category['nom']) ?>
                            </h3>
                            
                            <p class="text-gray-600 text-sm line-clamp-2">
                                <?= htmlspecialchars($category['description']) ?>
                            </p>
                        </div>
                        
                        <!-- Footer -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-clipboard-list mr-2"></i>Quiz disponibles
                                </span>
                                <span class="text-<?= $color ?>-600 font-semibold group-hover:translate-x-2 transition-transform inline-flex items-center">
                                    Explorer
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

           
        <?php endif; ?>
    

<?php include '../partials/footer.php'; ?>