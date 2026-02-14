<?php
/**
 * File Upload Service
 * Handles file uploads with validation and security
 * Based on Unit 2 service design
 */

class FileUploadService {
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;
    
    public function __construct() {
        $this->uploadDir = __DIR__ . '/../uploads/';
        $this->allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $this->maxFileSize = 5 * 1024 * 1024; // 5MB
        
        $this->ensureUploadDirectory();
    }
    
    /**
     * Process profile photo to Base64 (simple version)
     */
    public function processProfilePhoto($file) {
        if (!$this->isValidUpload($file)) {
            throw new Exception('Invalid file upload');
        }
        
        // Validate file size (reduce to 1MB for Base64)
        if ($file['size'] > 1024 * 1024) {
            throw new Exception('File size must be less than 1MB for profile photos');
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPEG and PNG images are allowed');
        }
        
        // Read file and convert to Base64
        $imageData = file_get_contents($file['tmp_name']);
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }
    
    /**
     * Upload document
     */
    public function uploadDocument($file, $studentId, $documentType) {
        if (!$this->isValidUpload($file)) {
            throw new Exception('Invalid file upload');
        }
        
        $this->validateDocument($file);
        
        $fileName = $this->generateFileName($file, $studentId, $documentType);
        $uploadPath = $this->uploadDir . 'documents/' . $fileName;
        
        $this->ensureDirectory(dirname($uploadPath));
        
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to upload document');
        }
        
        return 'documents/' . $fileName;
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File size exceeds maximum limit of 5MB');
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            throw new Exception('Invalid file type. Only JPEG and PNG images are allowed');
        }
        
        // Check if it's actually an image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new Exception('File is not a valid image');
        }
        
        // Check image dimensions (optional)
        $maxWidth = 2000;
        $maxHeight = 2000;
        if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
            throw new Exception("Image dimensions exceed maximum size of {$maxWidth}x{$maxHeight} pixels");
        }
    }
    
    /**
     * Validate document file
     */
    private function validateDocument($file) {
        $allowedDocTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/jpg'
        ];
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File size exceeds maximum limit of 5MB');
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedDocTypes)) {
            throw new Exception('Invalid file type. Only PDF, JPEG and PNG files are allowed');
        }
    }
    
    /**
     * Check if upload is valid
     */
    private function isValidUpload($file) {
        return isset($file['error']) && $file['error'] === UPLOAD_ERR_OK;
    }
    
    /**
     * Generate unique filename
     */
    private function generateFileName($file, $studentId, $type) {
        $extension = $this->getFileExtension($file['name']);
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        
        return "{$studentId}_{$type}_{$timestamp}_{$random}.{$extension}";
    }
    
    /**
     * Get file extension
     */
    private function getFileExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    
    /**
     * Ensure upload directory exists
     */
    private function ensureUploadDirectory() {
        $directories = [
            $this->uploadDir,
            $this->uploadDir . 'profiles/',
            $this->uploadDir . 'documents/',
            $this->uploadDir . 'thumbnails/'
        ];
        
        foreach ($directories as $dir) {
            $this->ensureDirectory($dir);
        }
    }
    
    /**
     * Ensure directory exists
     */
    private function ensureDirectory($dir) {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new Exception("Failed to create directory: {$dir}");
            }
        }
        
        // Create .htaccess for security
        $htaccessFile = $dir . '.htaccess';
        if (!file_exists($htaccessFile)) {
            $htaccessContent = "Options -Indexes\n";
            if (strpos($dir, 'profiles') !== false || strpos($dir, 'thumbnails') !== false) {
                $htaccessContent .= "SetEnvIf Request_URI \"\.(gif|jpe?g|png)$\" image\n";
                $htaccessContent .= "Order deny,allow\n";
                $htaccessContent .= "Deny from all\n";
                $htaccessContent .= "Allow from env=image\n";
            }
            file_put_contents($htaccessFile, $htaccessContent);
        }
    }
    
    /**
     * Create thumbnail for profile photo
     */
    private function createThumbnail($originalPath, $fileName) {
        $thumbnailDir = $this->uploadDir . 'thumbnails/';
        $thumbnailPath = $thumbnailDir . 'thumb_' . $fileName;
        
        $imageInfo = getimagesize($originalPath);
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        // Calculate thumbnail dimensions (150x150 max, maintain aspect ratio)
        $thumbWidth = 150;
        $thumbHeight = 150;
        
        $ratio = min($thumbWidth / $originalWidth, $thumbHeight / $originalHeight);
        $newWidth = intval($originalWidth * $ratio);
        $newHeight = intval($originalHeight * $ratio);
        
        // Create image resource based on type
        switch ($mimeType) {
            case 'image/jpeg':
                $originalImage = imagecreatefromjpeg($originalPath);
                break;
            case 'image/png':
                $originalImage = imagecreatefrompng($originalPath);
                break;
            default:
                return false;
        }
        
        // Create thumbnail
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG
        if ($mimeType === 'image/png') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }
        
        imagecopyresampled(
            $thumbnail, $originalImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $originalWidth, $originalHeight
        );
        
        // Save thumbnail
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($thumbnail, $thumbnailPath, 85);
                break;
            case 'image/png':
                imagepng($thumbnail, $thumbnailPath);
                break;
        }
        
        // Clean up memory
        imagedestroy($originalImage);
        imagedestroy($thumbnail);
        
        return true;
    }
    
    /**
     * Delete uploaded file
     */
    public function deleteFile($filePath) {
        $fullPath = $this->uploadDir . $filePath;
        
        if (file_exists($fullPath)) {
            unlink($fullPath);
            
            // Also delete thumbnail if it exists
            $fileName = basename($filePath);
            $thumbnailPath = $this->uploadDir . 'thumbnails/thumb_' . $fileName;
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get file URL
     */
    public function getFileUrl($filePath) {
        $baseUrl = $this->getBaseUrl();
        return $baseUrl . '/uploads/' . $filePath;
    }
    
    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrl($filePath) {
        $fileName = basename($filePath);
        $baseUrl = $this->getBaseUrl();
        return $baseUrl . '/uploads/thumbnails/thumb_' . $fileName;
    }
    
    /**
     * Get base URL
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        
        return $protocol . '://' . $host . $path;
    }
    
    /**
     * Get file info
     */
    public function getFileInfo($filePath) {
        $fullPath = $this->uploadDir . $filePath;
        
        if (!file_exists($fullPath)) {
            return null;
        }
        
        return [
            'path' => $filePath,
            'size' => filesize($fullPath),
            'modified' => filemtime($fullPath),
            'url' => $this->getFileUrl($filePath),
            'thumbnail_url' => $this->getThumbnailUrl($filePath)
        ];
    }
}
?>