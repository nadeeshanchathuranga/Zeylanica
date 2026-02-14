<?php
/**
 * Profile Photo Helper
 * Utility functions for handling Base64 profile photo display
 */

class ProfilePhotoHelper {
    
    /**
     * Get profile photo URL (Base64 data URI)
     */
    public static function getProfilePhotoUrl($profilePhoto, $useThumb = false) {
        if (empty($profilePhoto)) {
            return self::getDefaultAvatarUrl();
        }
        
        // If it's already a data URI, return as is
        if (strpos($profilePhoto, 'data:') === 0) {
            return $profilePhoto;
        }
        
        return self::getDefaultAvatarUrl();
    }
    
    /**
     * Get default avatar URL
     */
    public static function getDefaultAvatarUrl() {
        return 'data:image/svg+xml;base64,' . base64_encode('
            <svg width="150" height="150" viewBox="0 0 150 150" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="150" height="150" fill="#E5E7EB"/>
                <circle cx="75" cy="60" r="25" fill="#9CA3AF"/>
                <path d="M30 130c0-25 20-45 45-45s45 20 45 45" fill="#9CA3AF"/>
            </svg>
        ');
    }
    
    /**
     * Display profile photo with fallback
     */
    public static function displayProfilePhoto($profilePhoto, $alt = 'Profile Photo', $class = '', $useThumb = true) {
        $url = self::getProfilePhotoUrl($profilePhoto, $useThumb);
        $defaultUrl = self::getDefaultAvatarUrl();
        
        return sprintf(
            '<img src="%s" alt="%s" class="%s" onerror="this.src=\'%s\'" loading="lazy">',
            htmlspecialchars($url),
            htmlspecialchars($alt),
            htmlspecialchars($class),
            $defaultUrl
        );
    }
    
    /**
     * Check if profile photo exists (has Base64 data)
     */
    public static function profilePhotoExists($profilePhoto) {
        return !empty($profilePhoto) && strpos($profilePhoto, 'data:') === 0;
    }
    
    /**
     * Get image size from Base64 data
     */
    public static function getBase64ImageSize($base64Data) {
        if (empty($base64Data) || strpos($base64Data, 'data:') !== 0) {
            return 0;
        }
        
        // Extract Base64 part
        $data = explode(',', $base64Data, 2);
        if (count($data) !== 2) {
            return 0;
        }
        
        return strlen(base64_decode($data[1]));
    }
}
?>