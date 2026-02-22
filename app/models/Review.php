<?php

class Review
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    // ดึงรีวิวของคอร์ส
    public function getReviewsByCourseId($course_id)
    {
        // Join กับตาราง user เพื่อดึงชื่อผู้รีวิว
        // เอา u.profile_picture ออก เพราะไม่มีในตาราง user
        $sql = "SELECT r.*, u.full_name
                FROM review_course r
                JOIN user u ON r.user_id = u.user_id
                WHERE r.course_id = ?
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$course_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ดึงค่าเฉลี่ยและจำนวนรีวิว
    public function getCourseRatingStats($course_id)
    {
        // ใช้ review_c_id แทน review_id
        $sql = "SELECT AVG(rating) as avg_rating, COUNT(review_c_id) as total_reviews 
                FROM review_course 
                WHERE course_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$course_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'rating' => round($result['avg_rating'] ?? 0, 1),
            'count' => intval($result['total_reviews'] ?? 0)
        ];
    }

    // เพิ่มรีวิว (เผื่อใช้ในอนาคต)
    public function addReview($booking_id, $user_id, $course_id, $rating, $comment, $image = null)
    {
        $sql = "INSERT INTO review_course (booking_id, user_id, course_id, rating, comment, review_image, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$booking_id, $user_id, $course_id, $rating, $comment, $image]);
    }
    
    // เช็คว่า user เคยรีวิวคอร์สนี้ไปหรือยัง (ผ่าน booking_id จะแม่นยำกว่าถ้ามี)
    public function hasReviewed($booking_id) {
        $sql = "SELECT COUNT(*) FROM review_course WHERE booking_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$booking_id]);
        return $stmt->fetchColumn() > 0;
    }
}
