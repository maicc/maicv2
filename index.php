<?php

require 'config/databaseconnect.php';


?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>maicflv2</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <header class="nav-bar">

        <div class="logo-nav">
            <a><img id="logo" src="https://64.media.tumblr.com/786badcba9a9df080c43584b26a95f14/tumblr_naq49m3kkQ1tahrlzo1_500.png"></a>
        </div>
        <ul>
            <a href="">
                <li>Inicio </li>
            </a>
            <a href="iniciosesion.php">
                <li>Login</li>
            </a>

        </ul>

    </header>

    <div class="main-asidebar-wrapper">

        <section class="asidebar2">

        </section>

        <section class="main">

            <h1>Animes</h1>

            <?php
            // Hacer la consulta a la base de datos
            $sql = "SELECT a.ID_Anime, a.nombre, ca.url AS CoverURL
            FROM Animes a 
            JOIN CoverAnime ca ON a.ID_CoverAnime = ca.ID_CoverAnime
            ORDER BY a.ID_Anime DESC"
            ;

            $result = $mysqli->query($sql);
            ?>

            <div class="box ani">
                <?php
                // Por cada fila que haya en la base de datos se repite esto y se genera una caja nueva
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="box-anime"> 
                        <a href="episodios.php?id=' . $row["ID_Anime"] . '">
                        <img class="imagen-escalada" src="' . $row["CoverURL"] . '">
                        </a>
                        <p>' . htmlspecialchars($row["nombre"]) . '</p> <!-- Mostrar nombre del anime -->
            </div>';
                    }
                } else {
                    echo 'No se encontraron animes.';
                }

                // Cerrar la conexión
                $mysqli->close();
                ?>
            </div>


        </section>

        <section class="asidebar">

        </section>


    </div>

    <footer class="foot">

<a id="aa" href="Agregartodo2003.php">a</a>
    </footer>
</body>

</html>