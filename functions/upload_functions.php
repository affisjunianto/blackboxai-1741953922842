<?php
/**
 * Functions for handling file uploads securely
 */

/**
 * Upload a file securely
 * @param array $file The $_FILES array element
 * @param string $targetDir The directory to upload to
 * @param array $allowedTypes Array of allowed MIME types
 * @param int $maxSize Maximum file size in bytes
 * @return string|false The path to the uploaded file or false on failure
 */
function uploadFile($file, $targetDir = '../uploads', $allowedTypes = [], $maxSize = 5242880) {
    try {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload failed with error code: ' . $file['error']);
        }

        // Check file size
        if ($file['size'] > $maxSize) {
            throw new Exception('File is too large. Maximum size is ' . formatBytes($maxSize));
        }

        // Create target directory if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Verify MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
            throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowedTypes));
        }

        // Generate safe filename
        $fileInfo = pathinfo($file['name']);
        $safeFilename = slugify($fileInfo['filename']) . '_' . time();
        
        // Determine extension based on MIME type
        $extension = getExtensionFromMimeType($mimeType);
        if (!$extension) {
            $extension = $fileInfo['extension'];
        }
        
        $targetFile = $targetDir . '/' . $safeFilename . '.' . $extension;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
            throw new Exception('Failed to move uploaded file');
        }

        return str_replace('../', '/', $targetFile);
    } catch (Exception $e) {
        error_log('File upload error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get file extension from MIME type
 * @param string $mimeType The MIME type
 * @return string|false The extension or false if not found
 */
function getExtensionFromMimeType($mimeType) {
    $mimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/svg+xml' => 'svg',
        'image/x-icon' => 'ico',
        'application/pdf' => 'pdf'
    ];
    
    return isset($mimeTypes[$mimeType]) ? $mimeTypes[$mimeType] : false;
}

/**
 * Format bytes to human readable size
 * @param int $bytes Number of bytes
 * @return string Formatted size
 */
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Create URL-friendly slug from string
 * @param string $string The string to slugify
 * @return string The slugified string
 */
function slugify($string) {
    // Replace non-alphanumeric characters with dash
    $string = preg_replace('/[^\p{L}\p{N}]+/u', '-', $string);
    // Remove duplicate dashes
    $string = preg_replace('/-+/', '-', $string);
    // Remove leading and trailing dashes
    $string = trim($string, '-');
    // Convert to lowercase
    return strtolower($string);
}