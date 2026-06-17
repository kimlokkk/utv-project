<?php

include 'db_connect/db_connect.php'; // Betulkan nama fail sambungan ke database

// Kata laluan baru yang ingin ditukar
$newPassword = "Ntahla_97";

// Hash kata laluan menggunakan bcrypt
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Semak sambungan
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Sediakan dan jalankan query dengan prepared statement
/*$stmt = $db->prepare("UPDATE uitm_staff SET password=? WHERE id=?");
if ($stmt) {
    $id = 4; // ID admin yang ingin diubah kata laluannya
    $stmt->bind_param("si", $hashedPassword, $id); // "si" bermaksud string dan integer
    if ($stmt->execute()) {
        echo "Kata laluan berjaya ditukar!";
    } else {
        echo "Ralat: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Ralat: " . $db->error;
}*/

$stmt = $db->prepare("UPDATE research_assistant SET password=? WHERE id=?");
if ($stmt) {
    $id = 2; // ID admin yang ingin diubah kata laluannya
    $stmt->bind_param("si", $hashedPassword, $id); // "si" bermaksud string dan integer
    if ($stmt->execute()) {
        echo "Kata laluan berjaya ditukar!";
    } else {
        echo "Ralat: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Ralat: " . $db->error;
}

// Tutup sambungan
$db->close();

?>
