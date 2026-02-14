<?php

class LessonAnalyticsService {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getLessonAnalytics($lessonId, $dateRange = null) {
        $whereClause = "WHERE la.lesson_id = ?";
        $params = [$lessonId];
        
        if ($dateRange) {
            $whereClause .= " AND la.first_viewed_at >= ? AND la.first_viewed_at <= ?";
            $params[] = $dateRange['start'];
            $params[] = $dateRange['end'];
        }
        
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(DISTINCT la.student_id) as total_students,
                SUM(la.view_count) as total_views,
                AVG(la.view_count) as avg_views_per_student,
                SUM(la.total_time_spent) as total_watch_time,
                AVG(la.total_time_spent) as avg_watch_time_per_student,
                COUNT(CASE WHEN la.completion_status = 1 THEN 1 END) as completed_students,
                (COUNT(CASE WHEN la.completion_status = 1 THEN 1 END) * 100.0 / COUNT(DISTINCT la.student_id)) as completion_rate,
                l.video_duration,
                l.title as lesson_title
            FROM lesson_analytics la
            JOIN lessons l ON la.lesson_id = l.id
            $whereClause
            GROUP BY la.lesson_id
        ");
        
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getCourseAnalytics($courseId, $dateRange = null) {
        $whereClause = "WHERE l.course_id = ?";
        $params = [$courseId];
        
        if ($dateRange) {
            $whereClause .= " AND la.first_viewed_at >= ? AND la.first_viewed_at <= ?";
            $params[] = $dateRange['start'];
            $params[] = $dateRange['end'];
        }
        
        $stmt = $this->pdo->prepare("
            SELECT 
                l.id as lesson_id,
                l.title as lesson_title,
                l.lesson_order,
                l.video_duration,
                COUNT(DISTINCT la.student_id) as unique_students,
                SUM(la.view_count) as total_views,
                SUM(la.total_time_spent) as total_watch_time,
                COUNT(CASE WHEN la.completion_status = 1 THEN 1 END) as completed_students,
                (COUNT(CASE WHEN la.completion_status = 1 THEN 1 END) * 100.0 / NULLIF(COUNT(DISTINCT la.student_id), 0)) as completion_rate
            FROM lessons l
            LEFT JOIN lesson_analytics la ON l.id = la.lesson_id
            $whereClause
            GROUP BY l.id
            ORDER BY l.lesson_order
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getStudentProgress($studentId, $courseId = null) {
        $whereClause = "WHERE la.student_id = ?";
        $params = [$studentId];
        
        if ($courseId) {
            $whereClause .= " AND l.course_id = ?";
            $params[] = $courseId;
        }
        
        $stmt = $this->pdo->prepare("
            SELECT 
                l.id as lesson_id,
                l.title as lesson_title,
                l.lesson_order,
                l.video_duration,
                c.title as course_title,
                la.view_count,
                la.total_time_spent,
                la.completion_status,
                la.last_watched_position,
                la.first_viewed_at,
                la.last_viewed_at,
                la.completed_at,
                (la.total_time_spent * 100.0 / l.video_duration) as watch_percentage
            FROM lesson_analytics la
            JOIN lessons l ON la.lesson_id = l.id
            JOIN courses c ON l.course_id = c.id
            $whereClause
            ORDER BY c.title, l.lesson_order
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getEngagementMetrics($lessonId, $timeframe = '7 days') {
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(lvs.session_start) as date,
                COUNT(DISTINCT lvs.student_id) as unique_viewers,
                COUNT(lvs.id) as total_sessions,
                SUM(lvs.watch_duration) as total_watch_time,
                AVG(lvs.watch_duration) as avg_session_duration
            FROM lesson_view_sessions lvs
            WHERE lvs.lesson_id = ? 
            AND lvs.session_start >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(lvs.session_start)
            ORDER BY date DESC
        ");
        
        $timeframeDays = $this->parseTimeframe($timeframe);
        $stmt->execute([$lessonId, $timeframeDays]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getDropOffAnalysis($lessonId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                FLOOR(lvs.end_position / 30) * 30 as time_segment,
                COUNT(*) as sessions_ended,
                COUNT(DISTINCT lvs.student_id) as unique_students
            FROM lesson_view_sessions lvs
            WHERE lvs.lesson_id = ? 
            AND lvs.end_position > 0
            AND lvs.session_end IS NOT NULL
            GROUP BY FLOOR(lvs.end_position / 30)
            ORDER BY time_segment
        ");
        
        $stmt->execute([$lessonId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTopPerformingLessons($courseId = null, $limit = 10) {
        $whereClause = $courseId ? "WHERE l.course_id = ?" : "";
        $params = $courseId ? [$courseId] : [];
        $params[] = $limit;
        
        $stmt = $this->pdo->prepare("
            SELECT 
                l.id as lesson_id,
                l.title as lesson_title,
                c.title as course_title,
                COUNT(DISTINCT la.student_id) as unique_students,
                SUM(la.view_count) as total_views,
                AVG(la.total_time_spent) as avg_watch_time,
                (COUNT(CASE WHEN la.completion_status = 1 THEN 1 END) * 100.0 / COUNT(DISTINCT la.student_id)) as completion_rate,
                (SUM(la.total_time_spent) / SUM(l.video_duration)) as engagement_score
            FROM lessons l
            JOIN courses c ON l.course_id = c.id
            LEFT JOIN lesson_analytics la ON l.id = la.lesson_id
            $whereClause
            GROUP BY l.id
            HAVING unique_students > 0
            ORDER BY engagement_score DESC, completion_rate DESC
            LIMIT ?
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getStudentEngagementReport($studentId, $courseId = null) {
        $whereClause = "WHERE la.student_id = ?";
        $params = [$studentId];
        
        if ($courseId) {
            $whereClause .= " AND l.course_id = ?";
            $params[] = $courseId;
        }
        
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(DISTINCT l.id) as lessons_accessed,
                COUNT(CASE WHEN la.completion_status = 1 THEN 1 END) as lessons_completed,
                SUM(la.total_time_spent) as total_study_time,
                AVG(la.view_count) as avg_views_per_lesson,
                MAX(la.last_viewed_at) as last_activity,
                (COUNT(CASE WHEN la.completion_status = 1 THEN 1 END) * 100.0 / COUNT(DISTINCT l.id)) as completion_rate
            FROM lesson_analytics la
            JOIN lessons l ON la.lesson_id = l.id
            $whereClause
        ");
        
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateWatchProgress($sessionId, $currentPosition, $watchDuration) {
        try {
            $this->pdo->beginTransaction();
            
            // Update session
            $stmt = $this->pdo->prepare("
                UPDATE lesson_view_sessions 
                SET end_position = ?, 
                    watch_duration = ?,
                    session_end = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$currentPosition, $watchDuration, $sessionId]);
            
            // Get session details
            $stmt = $this->pdo->prepare("
                SELECT lesson_id, student_id FROM lesson_view_sessions WHERE id = ?
            ");
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($session) {
                // Update analytics
                $stmt = $this->pdo->prepare("
                    UPDATE lesson_analytics 
                    SET total_time_spent = total_time_spent + ?,
                        last_watched_position = ?,
                        last_viewed_at = NOW()
                    WHERE lesson_id = ? AND student_id = ?
                ");
                $stmt->execute([
                    $watchDuration,
                    $currentPosition,
                    $session['lesson_id'],
                    $session['student_id']
                ]);
                
                // Check for completion
                $stmt = $this->pdo->prepare("
                    UPDATE lesson_analytics la
                    JOIN lessons l ON la.lesson_id = l.id
                    SET la.completion_status = 1,
                        la.completed_at = CASE WHEN la.completed_at IS NULL THEN NOW() ELSE la.completed_at END
                    WHERE la.lesson_id = ? 
                    AND la.student_id = ? 
                    AND ? >= (l.video_duration * 0.9)
                    AND la.completion_status = 0
                ");
                $stmt->execute([
                    $session['lesson_id'],
                    $session['student_id'],
                    $currentPosition
                ]);
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception('Failed to update watch progress: ' . $e->getMessage());
        }
    }
    
    public function generateLessonReport($lessonId, $format = 'array') {
        $analytics = $this->getLessonAnalytics($lessonId);
        $engagement = $this->getEngagementMetrics($lessonId);
        $dropOff = $this->getDropOffAnalysis($lessonId);
        
        $report = [
            'lesson_analytics' => $analytics,
            'engagement_metrics' => $engagement,
            'drop_off_analysis' => $dropOff,
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($format === 'json') {
            return json_encode($report, JSON_PRETTY_PRINT);
        }
        
        return $report;
    }
    
    private function parseTimeframe($timeframe) {
        $timeframeDays = 7; // default
        
        if (preg_match('/(\d+)\s*(day|week|month)s?/', strtolower($timeframe), $matches)) {
            $number = intval($matches[1]);
            $unit = $matches[2];
            
            switch ($unit) {
                case 'day':
                    $timeframeDays = $number;
                    break;
                case 'week':
                    $timeframeDays = $number * 7;
                    break;
                case 'month':
                    $timeframeDays = $number * 30;
                    break;
            }
        }
        
        return $timeframeDays;
    }
}