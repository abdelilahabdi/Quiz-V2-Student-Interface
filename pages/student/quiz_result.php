<?php


require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
require_once '../../classes/Result.php';


Security::requireStudent();


$studentId = $_SESSION['user_id'];
$userName = $_SESSION['user_nom'];
$resultId = intval($_GET['id'] ?? 0);


$quizResult = $_SESSION['quiz_result'] ?? null;

if (!$quizResult || $resultId <= 0) {
    $_SESSION['quiz_error'] = 'Résultat non trouvé';
    header('Location: my_results.php');
    exit();
}

$resultObj = new Result();
$result = $resultObj->getById($resultId, $studentId);


if (!$result || $result['etudiant_id'] != $studentId) {
    $_SESSION['quiz_error'] = 'Accès refusé';
    header('Location: my_results.php');
    exit();
}


$score = $result['score'];
$total = $result['total_questions'];
$percentage = round(($score / $total) * 100, 1);


if ($percentage >= 80) {
    $resultClass = 'success';
    $resultColor = 'green';
    $resultIcon = 'fa-trophy';
    $resultMessage = 'Excellent travail !';
    $resultDescription = 'Vous avez une excellente maîtrise du sujet.';
} elseif ($percentage >= 60) {
    $resultClass = 'good';
    $resultColor = 'blue';
    $resultIcon = 'fa-thumbs-up';
    $resultMessage = 'Bon travail !';
    $resultDescription = 'Vous avez une bonne compréhension du sujet.';
} elseif ($percentage >= 40) {
    $resultClass = 'average';
    $resultColor = 'yellow';
    $resultIcon = 'fa-star-half-alt';
    $resultMessage = 'Peut mieux faire';
    $resultDescription = 'Continuez à pratiquer pour améliorer vos résultats.';
} else {
    $resultClass = 'fail';
    $resultColor = 'red';
    $resultIcon = 'fa-times-circle';
    $resultMessage = 'Besoin de révision';
    $resultDescription = 'N\'abandonnez pas ! La pratique mène à la perfection.';
}


unset($_SESSION['quiz_result']);

$currentPage = 'results';
$pageTitle = 'Résultat du Quiz';
?>
<?php include '../partials/header.php'; ?>

<?php include '../partials/nav_student.php'; ?>

<!-- Main Content -->
<div class="pt-16 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Confetti Animation (Optional) -->
        <?php if ($percentage >= 80): ?>
        <div class="fixed inset-0 pointer-events-none z-50" id="confetti"></div>
        <?php endif; ?>

        <!-- Result Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header avec couleur selon le score -->
            <div class="bg-gradient-to-r from-<?= $resultColor ?>-500 to-<?= $resultColor ?>-600 text-white p-8 text-center">
                <div class="mb-4">
                    <i class="fas <?= $resultIcon ?> text-7xl mb-4 animate-bounce"></i>
                </div>
                <h1 class="text-4xl font-bold mb-2"><?= $resultMessage ?></h1>
                <p class="text-xl text-<?= $resultColor ?>-100"><?= $resultDescription ?></p>
            </div>

            <!-- Score Display -->
            <div class="p-8">
                <!-- Quiz Title -->
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        <?= htmlspecialchars($result['quiz_titre']) ?>
                    </h2>
                    <p class="text-gray-600">Complété le <?= date('d/m/Y à H:i', strtotime($result['created_at'])) ?></p>
                </div>

                <!-- Score Circle -->
                <div class="flex justify-center mb-8">
                    <div class="relative w-64 h-64">
                        <!-- Cercle de progression -->
                        <svg class="transform -rotate-90 w-64 h-64">
                            <circle cx="128" cy="128" r="120" stroke="#e5e7eb" stroke-width="16" fill="transparent"/>
                            <circle cx="128" cy="128" r="120" 
                                    stroke="<?= $percentage >= 80 ? '#10b981' : ($percentage >= 60 ? '#3b82f6' : ($percentage >= 40 ? '#fbbf24' : '#ef4444')) ?>" 
                                    stroke-width="16" 
                                    fill="transparent"
                                    stroke-dasharray="<?= 2 * 3.14159 * 120 ?>"
                                    stroke-dashoffset="<?= 2 * 3.14159 * 120 * (1 - $percentage / 100) ?>"
                                    stroke-linecap="round"
                                    class="transition-all duration-1000"/>
                        </svg>
                        
                        <!-- Texte au centre -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-6xl font-bold text-gray-900"><?= $percentage ?>%</span>
                            <span class="text-gray-600 mt-2"><?= $score ?> / <?= $total ?></span>
                        </div>
                    </div>
                </div>

                <!-- Statistiques détaillées -->
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="bg-green-50 rounded-xl p-4 text-center border-2 border-green-200">
                        <i class="fas fa-check-circle text-green-600 text-2xl mb-2"></i>
                        <p class="text-3xl font-bold text-green-900"><?= $score ?></p>
                        <p class="text-sm text-green-700">Correctes</p>
                    </div>
                    
                    <div class="bg-red-50 rounded-xl p-4 text-center border-2 border-red-200">
                        <i class="fas fa-times-circle text-red-600 text-2xl mb-2"></i>
                        <p class="text-3xl font-bold text-red-900"><?= $total - $score ?></p>
                        <p class="text-sm text-red-700">Incorrectes</p>
                    </div>
                    
                    <div class="bg-blue-50 rounded-xl p-4 text-center border-2 border-blue-200">
                        <i class="fas fa-clipboard-list text-blue-600 text-2xl mb-2"></i>
                        <p class="text-3xl font-bold text-blue-900"><?= $total ?></p>
                        <p class="text-sm text-blue-700">Total</p>
                    </div>
                </div>

             

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="categories.php" 
                       class="flex-1 text-center bg-blue-600 text-white py-4 rounded-xl font-semibold hover:bg-blue-700 transition shadow-lg">
                        <i class="fas fa-redo mr-2"></i>Passer un autre quiz
                    </a>
                    
                    <a href="my_results.php" 
                       class="flex-1 text-center border-2 border-blue-600 text-blue-600 py-4 rounded-xl font-semibold hover:bg-blue-50 transition">
                        <i class="fas fa-chart-line mr-2"></i>Voir tous mes résultats
                    </a>
                    
                    <a href="dashboard.php" 
                       class="flex-1 text-center border-2 border-gray-300 text-gray-700 py-4 rounded-xl font-semibold hover:bg-gray-50 transition">
                        <i class="fas fa-home mr-2"></i>Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>

        

<?php if ($percentage >= 80): ?>

<?php endif; ?>

<?php include '../partials/footer.php'; ?>