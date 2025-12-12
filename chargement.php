<?php
// upload.php

// Dossier de destination
$uploadDir = "chargement/";

// Vérifier si un fichier a été envoyé
if (!isset($_FILES['file'])) {
    echo json_encode(["error" => "Aucun fichier reçu."]);
    exit;
}

$file = $_FILES['file'];
$fileName = time() . "_" . basename($file["name"]); // nom unique
$filePath = $uploadDir . $fileName;

// Types autorisés
$allowedTypes = [
    "image/jpeg",
    "image/png",
    "image/gif",
    "video/mp4",
    "application/pdf"
];

if (!in_array($file["type"], $allowedTypes)) {
    echo json_encode(["error" => "Type de fichier non autorisé."]);
    exit;
}

// Taille maximale (10 Mo)
if ($file["size"] > 10 * 1024 * 1024) {
    echo json_encode(["error" => "Fichier trop volumineux."]);
    exit;
}

// Upload du fichier
if (move_uploaded_file($file["tmp_name"], $filePath)) {
    echo json_encode(["success" => true, "path" => $filePath]);
} else {
    echo json_encode(["error" => "Erreur lors de l'upload."]);
}
?>
