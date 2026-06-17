<?php

function uploadFileStrict($fileInputName, $targetDir, $allowedExtensions = ['pdf','jpg','jpeg','png'], $allowedMimeTypes = ['application/pdf','image/jpeg','image/png']) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload failed for {$fileInputName}.");
    }

    $file = $_FILES[$fileInputName];
    $originalName = $file['name'];
    $tmpName = $file['tmp_name'];
    $size = $file['size'];

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        throw new Exception("Invalid file format for {$fileInputName}. Only PDF, JPG, JPEG and PNG are allowed.");
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMimeTypes)) {
        throw new Exception("Invalid MIME type for {$fileInputName}.");
    }

    if ($size > 5 * 1024 * 1024) {
        throw new Exception("File size for {$fileInputName} exceeds 5MB.");
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $newName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = rtrim($targetDir, '/') . '/' . $newName;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        throw new Exception("Failed to move uploaded file for {$fileInputName}.");
    }

    return $newName;
}