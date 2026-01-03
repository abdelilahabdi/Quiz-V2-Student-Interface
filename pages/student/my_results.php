<?php


require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
require_once '../../classes/Result.php';


Security::requireStudent();


$currentPage = 'results';
$pageTitle = 'Mes Résultats';


$studentId = $_SESSION['user_id'];
$userName = $_SESSION['user_nom'];

$resultObj = new Result();
$myResults = $resultObj->getMyResults($studentId);
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
                <i class="fas fa-chart-line mr-3"></i>Mes Résultats
            </h1>
            <p class="text-xl text-blue-100">Suivez vos performances et votre progression</p>
        </div>
    </div>

    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php include '../partials/alerts.php'; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold">Quiz Passés</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2"><?= $myStats['total_quiz'] ?? 0 ?></p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-xl">
                        <i class="fas fa-clipboard-check text-blue-600 text-3xl"></i>
                    </div>
                </div>
            </div>
            
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold">Moyenne</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">
                            <?= $myStats['moyenne'] ? round($myStats['moyenne'], 1) . '%' : '-' ?>
                        </p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-xl">
                        <i class="fas fa-percentage text-green-600 text-3xl"></i>
                    </div>
                </div>
                <?php if ($myStats['moyenne']): ?>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: <?= $myStats['moyenne'] ?>%"></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold">Meilleur Score</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">
                            <?= $myStats['meilleur_score'] ? round($myStats['meilleur_score'], 1) . '%' : '-' ?>
                        </p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-xl">
                        <i class="fas fa-trophy text-yellow-600 text-3xl"></i>
                    </div>
                </div>
            </div>
            
            
            <div class="bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl shadow-md p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-semibold">Objectif</p>
                        <p class="text-4xl font-bold mt-2">80%</p>
                    </div>
                    <div class="bg-white bg-opacity-20 p-4 rounded-xl">
                        <i class="fas fa-bullseye text-3xl"></i>
                    </div>
                </div>
                <p class="text-xs mt-2 text-purple-100">Continuez pour atteindre l'excellence !</p>
            </div>
        </div>

        

        <!-- Historique -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-history mr-2 text-blue-600"></i>Historique Complet
                </h2>
            </div>
            
            <?php if (empty($myResults)): ?>
                <div class="p-12 text-center">
                    <i class="fas fa-inbox text-8xl text-gray-300 mb-6"></i>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Aucun résultat</h3>
                    <p class="text-gray-600 text-lg mb-6">Vous n'avez pas encore passé de quiz.</p>
                    <a href="categories.php" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        <i class="fas fa-play-circle mr-2"></i>Passer mon premier quiz
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Quiz
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Catégorie
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Score
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Pourcentage
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Statut
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($myResults as $result): ?>
                                <?php 
                                $percentage = ($result['score'] / $result['total_questions']) * 100;
                                
                                if ($percentage >= 80) {
                                    $statusClass = 'bg-green-100 text-green-800';
                                    $statusIcon = 'fa-trophy';
                                    $statusText = 'Excellent';
                                } elseif ($percentage >= 60) {
                                    $statusClass = 'bg-blue-100 text-blue-800';
                                    $statusIcon = 'fa-thumbs-up';
                                    $statusText = 'Bien';
                                } elseif ($percentage >= 40) {
                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                    $statusIcon = 'fa-star-half-alt';
                                    $statusText = 'Moyen';
                                } else {
                                    $statusClass = 'bg-red-100 text-red-800';
                                    $statusIcon = 'fa-times-circle';
                                    $statusText = 'Faible';
                                }
                                ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-clipboard-list text-blue-600 mr-3 text-xl"></i>
                                            <div>
                                                <div class="font-bold text-gray-900">
                                                    <?= htmlspecialchars($result['quiz_titre']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">
                                            <?= htmlspecialchars($result['categorie_nom'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-lg font-bold text-gray-900">
                                            <?= $result['score'] ?><span class="text-gray-500 text-sm">/<?= $result['total_questions'] ?></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-2xl font-bold text-gray-900 mb-1">
                                                <?= round($percentage, 1) ?>%
                                            </span>
                                            <div class="w-full bg-gray-200 rounded-full h-2 max-w-[100px]">
                                                <div class="h-2 rounded-full <?= $percentage >= 80 ? 'bg-green-600' : ($percentage >= 60 ? 'bg-blue-600' : ($percentage >= 40 ? 'bg-yellow-600' : 'bg-red-600')) ?>" 
                                                     style="width: <?= $percentage ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-4 py-2 <?= $statusClass ?> font-semibold rounded-full inline-flex items-center">
                                            <i class="fas <?= $statusIcon ?> mr-2"></i>
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 text-sm">
                                        <i class="fas fa-calendar mr-2"></i>
                                        <?= date('d/m/Y', strtotime($result['created_at'])) ?>
                                        <br>
                                        <span class="text-xs text-gray-500">
                                            <i class="fas fa-clock mr-1"></i>
                                            <?= date('H:i', strtotime($result['created_at'])) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        


<?php include '../partials/footer.php'; ?>