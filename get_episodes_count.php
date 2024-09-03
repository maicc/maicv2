<?php
require 'config/databaseconnect.php';

header('Content-Type: application/json');

if (isset($_GET['anime_id'])) {
    $anime_id = intval($_GET['anime_id']);

    $stmt = $mysqli->prepare("SELECT COUNT(*) AS count FROM Episodios WHERE ID_Anime = ?");
    $stmt->bind_param("i", $anime_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    echo json_encode($data);
}

$mysqli->close();
?>
