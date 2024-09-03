<?php
require 'config/databaseconnect.php'; // Asegúrate de que este archivo establezca la conexión a la base de datos correctamente.

$id_anime = isset($_GET['id']) ? intval($_GET['id']) : 0; // Obtener el ID del anime desde la URL

// Hacer la consulta a la base de datos para un anime específico
$sql_anime = "SELECT a.ID_Anime, a.nombre AS AnimeName, a.sinopsis AS Synopsis, ca.url AS CoverURL
              FROM Animes a
              JOIN CoverAnime ca ON a.ID_CoverAnime = ca.ID_CoverAnime
              WHERE a.ID_Anime = ?";

$stmt_anime = $mysqli->prepare($sql_anime);
$stmt_anime->bind_param("i", $id_anime);
$stmt_anime->execute();
$result_anime = $stmt_anime->get_result();

// Hacer la consulta a la base de datos para los episodios de un anime específico
$sql_episodes = "SELECT e.ID_Episodio, e.NumEpisodios, el.url AS EpisodeURL, ce.url AS CoverURL
                  FROM Episodios e
                  JOIN EpisodioLinks el ON e.ID_EpisodioLink = el.ID_EpisodioLink
                  JOIN CoverEpisodios ce ON e.ID_CoverEpisodio = ce.ID_CoverEpisodio
                  WHERE e.ID_Anime = ?";

$stmt_episodes = $mysqli->prepare($sql_episodes);
$stmt_episodes->bind_param("i", $id_anime);
$stmt_episodes->execute();
$result_episodes = $stmt_episodes->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maicflv2</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header class="nav-bar">
        <div id="logo-nav">
            <a><img id="logo" src="https://64.media.tumblr.com/786badcba9a9df080c43584b26a95f14/tumblr_naq49m3kkQ1tahrlzo1_500.png" alt="Logo"></a>
        </div>
        <ul>
            <a href="index.php">
                <li>Inicio</li>
            </a>
            <a href="episodios.php">
                <li>Directorio</li>
            </a>
        </ul>
    </header>

    <div class="main-asidebar-wrapper">
        <section class="asidebar2"></section>

        <section class="main">
            <?php
            if ($result_anime->num_rows > 0) {
                $row_anime = $result_anime->fetch_assoc();
            ?>
                <div id="anime-info">
                    <div class="cover-largo">
                        <img class="cover" src="<?php echo htmlspecialchars($row_anime['CoverURL']); ?>" alt="Cover del anime">
                        <div class="ani-info">
                            <h1 id="Anime-name"><?php echo htmlspecialchars($row_anime['AnimeName']); ?></h1>
                            <div id="Anime-Desc">
                                <p><?php echo htmlspecialchars($row_anime['Synopsis']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            } else {
                echo 'No se encontró información del anime.';
            }

            if ($result_episodes->num_rows > 0) {
            ?>
                <h1>Episodios</h1>
                <div class="box episodes">
                    <?php
                    while ($row_episode = $result_episodes->fetch_assoc()) {
                        echo '<div class="box-episode">
                <a href="reproductor.php?id=' . htmlspecialchars($row_episode["ID_Episodio"]) . '">
                    <img class="imagen-escalada" src="' . htmlspecialchars($row_episode["CoverURL"]) . '" alt="Cover del episodio">
                    <p>Episodio ' . htmlspecialchars($row_episode["NumEpisodios"]) . '</p>
                </a>
            </div>';
                    }
                    ?>
                </div>
            <?php
            } else {
                echo 'No se encontraron episodios.';
            }

            // Cerrar la conexión
            $stmt_anime->close();
            $stmt_episodes->close();
            $mysqli->close();
            ?>
        </section>

        <section class="asidebar"></section>
    </div>

    <footer class="foot"></footer>
</body>

</html>