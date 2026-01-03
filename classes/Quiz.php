<?php
/**
 * Classe Quiz
 * Gère les opérations CRUD sur les quiz
 */

class Quiz {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    

    public function getall () {
        $sql = "SELECT * FROM quiz ";
        $resulta = $this -> db -> query ($sql);
        return $resulta -> fetchAll ();
    }

    public function affichagebyCateg ($category_id)
    {
        if ($category_id === null) {
            return [];
        }

        $sql = "SELECT * FROM quiz WHERE categorie_id = ?";
        $result = $this->db->query($sql, [$category_id]);

        return $result->fetchAll();
        
    }
   
     // Crée un nouveau quiz
   
     
    public function create($titre, $description, $categorieId, $enseignantId) {
        if (empty($titre) || empty($categorieId) || empty($enseignantId)) {
            return false;
        }
        
        $sql = "INSERT INTO quiz (titre, description, categorie_id, enseignant_id) 
                VALUES (?, ?, ?, ?)";
        try {
            $this->db->query($sql, [$titre, $description, $categorieId, $enseignantId]);
            return $this->db->getConnection()->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }
    
    //  Récupère tous les quiz d'un enseignant
     
    public function getAllByTeacher($teacherId) {
        $sql = "SELECT q.*, c.nom as categorie_nom,
                       COUNT(DISTINCT qu.id) as questions_count,
                       COUNT(DISTINCT r.id) as participants_count
                FROM quiz q
                LEFT JOIN categories c ON q.categorie_id = c.id
                LEFT JOIN questions qu ON q.id = qu.quiz_id
                LEFT JOIN results r ON q.id = r.quiz_id
                WHERE q.enseignant_id = ?
                GROUP BY q.id
                ORDER BY q.created_at DESC";
        
        $result = $this->db->query($sql, [$teacherId]);
        return $result->fetchAll();
    }
    
    // Récupère un quiz par ID
    
   
    public function getById($id) {
        $sql = "SELECT q.*, c.nom as categorie_nom
                FROM quiz q
                LEFT JOIN categories c ON q.categorie_id = c.id
                WHERE q.id = ?";
        $result = $this->db->query($sql, [$id]);
        return $result->fetch();
    }
    
    // Vérifie si l'enseignant est propriétaire du quiz
    
    public function isOwner($quizId, $teacherId) {
        $sql = "SELECT id FROM quiz WHERE id = ? AND enseignant_id = ?";
        $result = $this->db->query($sql, [$quizId, $teacherId]);
        return $result->rowCount() > 0;
    }
    
    // Met à jour un quiz
 
    public function update($id, $titre, $description, $categorieId, $teacherId) {
        if (!$this->isOwner($id, $teacherId)) {
            return false;
        }
        
        $sql = "UPDATE quiz SET titre = ?, description = ?, categorie_id = ? 
                WHERE id = ?";
        try {
            $this->db->query($sql, [$titre, $description, $categorieId, $id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Active/désactive un quiz
    
    public function toggleActive($id, $isActive, $teacherId) {
        if (!$this->isOwner($id, $teacherId)) {
            return false;
        }
        
        $sql = "UPDATE quiz SET is_active = ? WHERE id = ?";
        try {
            $this->db->query($sql, [$isActive, $id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Supprime un quiz et ses questions
  
    public function delete($id, $teacherId) {
        if (!$this->isOwner($id, $teacherId)) {
            return false;
        }
        
        $sql = "DELETE FROM quiz WHERE id = ?";
        try {
            $this->db->query($sql, [$id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Recuperer les statistiques d'un quiz
     
    public function getStats($quizId) {
        $sql = "SELECT 
                    COUNT(DISTINCT r.id) as total_attempts,
                    AVG(r.score / r.total_questions * 100) as avg_score,
                    COUNT(DISTINCT r.etudiant_id) as unique_students
                FROM results r
                WHERE r.quiz_id = ?";
        
        $result = $this->db->query($sql, [$quizId]);
        return $result->fetch();
    }









    /**
     * Récupère les quiz actifs d'une catégorie (pour étudiants)
     */
    public function getActiveQuizzesByCategory($categoryId) {
        $sql = "SELECT q.id, q.titre, q.description, q.created_at,
                       COUNT(qu.id) as questions_count,
                       c.nom as categorie_nom
                FROM quiz q
                LEFT JOIN questions qu ON q.id = qu.quiz_id
                LEFT JOIN categories c ON q.categorie_id = c.id
                WHERE q.categorie_id = ? AND q.is_active = 1
                GROUP BY q.id
                ORDER BY q.created_at DESC";
        
        $result = $this->db->query($sql, [$categoryId]);
        return $result->fetchAll();
    }
    
    /**
     * Récupère un quiz actif avec ses détails
     */
    public function getActiveQuizById($quizId) {
        $sql = "SELECT q.*, c.nom as categorie_nom,
                       COUNT(qu.id) as questions_count
                FROM quiz q
                LEFT JOIN categories c ON q.categorie_id = c.id
                LEFT JOIN questions qu ON q.id = qu.quiz_id
                WHERE q.id = ? AND q.is_active = 1
                GROUP BY q.id";
        
        $result = $this->db->query($sql, [$quizId]);
        return $result->fetch();
    }
    
    /**
     * Vérifie si un étudiant a déjà passé ce quiz
     */
    public function hasStudentTakenQuiz($quizId, $studentId) {
        $sql = "SELECT id FROM results 
                WHERE quiz_id = ? AND etudiant_id = ?";
        
        $result = $this->db->query($sql, [$quizId, $studentId]);
        return $result->rowCount() > 0;
    }
    
    /**
     * Récupère tous les quiz actifs (pour dashboard étudiant)
     */
    public function getAllActiveQuizzes($limit = 10) {
        $sql = "SELECT q.*, c.nom as categorie_nom,
                       COUNT(qu.id) as questions_count
                FROM quiz q
                LEFT JOIN categories c ON q.categorie_id = c.id
                LEFT JOIN questions qu ON q.id = qu.quiz_id
                WHERE q.is_active = 1
                GROUP BY q.id
                ORDER BY q.created_at DESC
                LIMIT ?";
        
        $result = $this->db->query($sql, [$limit]);
        return $result->fetchAll();
    }
}


