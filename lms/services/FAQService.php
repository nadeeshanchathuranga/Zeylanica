<?php
/**
 * FAQ Service Class
 * Handles FAQ CRUD operations
 */
class FAQService {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAllFAQs($activeOnly = true) {
        $sql = "SELECT * FROM faqs";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY display_order ASC, id ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getFAQById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM faqs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createFAQ($question, $answer, $displayOrder = 0, $createdBy = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO faqs (question, answer, display_order, created_by) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$question, $answer, $displayOrder, $createdBy]);
    }
    
    public function updateFAQ($id, $question, $answer, $displayOrder = null, $isActive = null) {
        $sql = "UPDATE faqs SET question = ?, answer = ?";
        $params = [$question, $answer];
        
        if ($displayOrder !== null) {
            $sql .= ", display_order = ?";
            $params[] = $displayOrder;
        }
        
        if ($isActive !== null) {
            $sql .= ", is_active = ?";
            $params[] = $isActive;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deleteFAQ($id) {
        $stmt = $this->pdo->prepare("DELETE FROM faqs WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function toggleFAQStatus($id) {
        $stmt = $this->pdo->prepare("UPDATE faqs SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>