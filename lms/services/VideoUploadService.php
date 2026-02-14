<?php

class VideoUploadService {
    
    public function getVimeoEmbedData($vimeoId) {
        // Extract video ID from various Vimeo URL formats
        $videoId = $this->extractVimeoId($vimeoId);
        
        return [
            'vimeo_id' => $videoId,
            'vimeo_url' => "https://vimeo.com/{$videoId}",
            'embed_url' => "https://player.vimeo.com/video/{$videoId}",
            'duration' => 0, // Will be updated manually if needed
            'width' => 1920,
            'height' => 1080
        ];
    }
    
    public function getSecureEmbedUrl($vimeoId, $lessonId, $studentId) {
        $videoId = $this->extractVimeoId($vimeoId);
        return "https://player.vimeo.com/video/{$videoId}?badge=0&autopause=0";
    }
    
    private function extractVimeoId($input) {
        // Handle various Vimeo URL formats
        if (preg_match('/vimeo\.com\/(\d+)/', $input, $matches)) {
            return $matches[1];
        }
        // If it's just a number, return as is
        if (is_numeric($input)) {
            return $input;
        }
        // Extract from embed URLs
        if (preg_match('/player\.vimeo\.com\/video\/(\d+)/', $input, $matches)) {
            return $matches[1];
        }
        return $input;
    }
    
    public function deleteVideo($vimeoId) {
        // No action needed for manual Vimeo IDs
        return true;
    }
}