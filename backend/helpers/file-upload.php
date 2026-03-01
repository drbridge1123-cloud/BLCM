<?php
/**
 * File Upload Helper
 *
 * Secure file upload handling with validation, sanitization, and storage management.
 */

function validateUploadedFile($file, $options = []) {
    $maxSize = $options['max_size'] ?? 10485760; // 10MB default
    $allowedMimeTypes = $options['allowed_mime_types'] ?? [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/tiff',
        'image/tif'
    ];
    $allowedExtensions = $options['allowed_extensions'] ?? [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'tif', 'tiff'
    ];

    if (!isset($file['error']) || is_array($file['error'])) {
        return ['valid' => false, 'error' => 'Invalid file upload'];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['valid' => false, 'error' => 'File exceeds maximum size'];
        case UPLOAD_ERR_NO_FILE:
            return ['valid' => false, 'error' => 'No file uploaded'];
        default:
            return ['valid' => false, 'error' => 'File upload failed'];
    }

    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'File exceeds maximum size of ' . formatBytes($maxSize)];
    }

    if ($file['size'] === 0) {
        return ['valid' => false, 'error' => 'File is empty'];
    }

    $pathInfo = pathinfo($file['name']);
    $extension = strtolower($pathInfo['extension'] ?? '');

    if (!in_array($extension, $allowedExtensions)) {
        return ['valid' => false, 'error' => 'File type not allowed. Allowed: ' . implode(', ', $allowedExtensions)];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedMimeTypes)) {
        return ['valid' => false, 'error' => 'Invalid file type detected'];
    }

    if (in_array($extension, ['jpg', 'jpeg', 'png', 'tif', 'tiff'])) {
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['valid' => false, 'error' => 'Invalid image file'];
        }
    }

    return ['valid' => true, 'error' => null];
}

function sanitizeFilename($filename) {
    $pathInfo = pathinfo($filename);
    $extension = strtolower($pathInfo['extension'] ?? '');
    $basename = $pathInfo['filename'] ?? 'file';
    $basename = basename($basename);
    $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
    $basename = substr($basename, 0, 100);
    return $basename . '.' . $extension;
}

function generateUniqueFilename($caseId, $originalFilename) {
    $pathInfo = pathinfo($originalFilename);
    $extension = strtolower($pathInfo['extension'] ?? 'bin');
    $timestamp = date('YmdHis');
    $hash = substr(md5(uniqid(rand(), true)), 0, 8);
    return "{$caseId}_{$timestamp}_{$hash}.{$extension}";
}

function storeUploadedFile($file, $subdir, $caseId) {
    $validation = validateUploadedFile($file);
    if (!$validation['valid']) {
        return ['success' => false, 'file_path' => null, 'file_name' => null, 'error' => $validation['error']];
    }

    $subdir = basename($subdir);
    $allowedSubdirs = ['hipaa', 'releases', 'other'];
    if (!in_array($subdir, $allowedSubdirs)) {
        $subdir = 'other';
    }

    $storageBase = STORAGE_PATH . '/documents';
    $storageDir = $storageBase . '/' . $subdir;

    if (!is_dir($storageDir)) {
        if (!mkdir($storageDir, 0755, true)) {
            return ['success' => false, 'file_path' => null, 'file_name' => null, 'error' => 'Failed to create storage directory'];
        }
    }

    $uniqueFilename = generateUniqueFilename($caseId, $file['name']);
    $fullPath = $storageDir . '/' . $uniqueFilename;

    if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
        return ['success' => false, 'file_path' => null, 'file_name' => null, 'error' => 'Failed to move uploaded file'];
    }

    chmod($fullPath, 0644);

    $relativePath = 'documents/' . $subdir . '/' . $uniqueFilename;

    return ['success' => true, 'file_path' => $relativePath, 'file_name' => $uniqueFilename, 'error' => null];
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}

function getMimeTypeFromExtension($extension) {
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff'
    ];
    return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
}

function getStoredFilePath($filePath) {
    $storageBase = realpath(STORAGE_PATH);
    $fullPath = $storageBase . '/' . $filePath;
    $realPath = realpath($fullPath);

    if ($realPath === false || strpos($realPath, $storageBase) !== 0) {
        return null;
    }

    if (!file_exists($realPath)) {
        return null;
    }

    return $realPath;
}

function deleteStoredFile($filePath) {
    $storageBase = realpath(STORAGE_PATH);
    $filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);
    $fullPath = $storageBase . DIRECTORY_SEPARATOR . $filePath;

    if (!file_exists($fullPath)) {
        return ['success' => true, 'error' => null];
    }

    $realPath = realpath($fullPath);
    if ($realPath === false || strpos($realPath, $storageBase) !== 0) {
        return ['success' => false, 'error' => 'Invalid file path'];
    }

    if (!unlink($realPath)) {
        return ['success' => false, 'error' => 'Failed to delete file'];
    }

    return ['success' => true, 'error' => null];
}
