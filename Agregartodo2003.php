<?php
session_start();
require 'config/databaseconnect.php';

// Verificar si el usuario está autenticado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Procesar el formulario de agregar anime
if (isset($_POST['agregar_anime'])) {
    $anime_nombre = $_POST['anime_nombre'];
    $anime_sinopsis = $_POST['anime_sinopsis'];
    $anime_cover = $_POST['anime_cover'];

    // Iniciar transacción
    $mysqli->begin_transaction();

    try {
        $stmt = $mysqli->prepare("INSERT INTO CoverAnime (url) VALUES (?)");
        $stmt->bind_param("s", $anime_cover);
        $stmt->execute();
        $ID_CoverAnime = $mysqli->insert_id;
        $stmt->close();

        $stmt = $mysqli->prepare("INSERT INTO Animes (nombre, sinopsis, ID_CoverAnime) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $anime_nombre, $anime_sinopsis, $ID_CoverAnime);
        $stmt->execute();
        $ID_Anime = $mysqli->insert_id;
        $stmt->close();

        $mysqli->commit();
        echo "Anime agregado exitosamente.";

    } catch (Exception $e) {
        $mysqli->rollback();
        echo "Error al agregar anime: " . $e->getMessage();
    }
}

// Procesar el formulario de agregar episodios
if (isset($_POST['agregar_episodios'])) {
    $id_anime = $_POST['anime_id'];
    $episodio_cover = $_POST['episodio_cover'];
    $episodio_url = $_POST['episodio_url'];
    $subtitulo_archivos = $_FILES['subtitulo_archivo'];

    // Iniciar transacción
    $mysqli->begin_transaction();

    try {
        foreach ($episodio_cover as $index => $cover) {
            $stmt = $mysqli->prepare("INSERT INTO CoverEpisodios (url) VALUES (?)");
            $stmt->bind_param("s", $cover);
            $stmt->execute();
            $CoverEpisodioID = $mysqli->insert_id;
            $stmt->close();

            $stmt = $mysqli->prepare("INSERT INTO EpisodioLinks (url) VALUES (?)");
            $stmt->bind_param("s", $episodio_url[$index]);
            $stmt->execute();
            $EpisodioLinkID = $mysqli->insert_id;
            $stmt->close();

            // Obtener el siguiente número de episodio
            $stmt = $mysqli->prepare("SELECT COALESCE(MAX(NumEpisodios), 0) + 1 AS next_num FROM Episodios WHERE ID_Anime = ?");
            $stmt->bind_param("i", $id_anime);
            $stmt->execute();
            $result = $stmt->get_result();
            $next_num = $result->fetch_assoc()['next_num'];
            $stmt->close();

            // Insertar el episodio
            $stmt = $mysqli->prepare("INSERT INTO Episodios (NumEpisodios, ID_Anime, ID_EpisodioLink, ID_CoverEpisodio) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiii", $next_num, $id_anime, $EpisodioLinkID, $CoverEpisodioID);
            $stmt->execute();
            $ID_Episodio = $mysqli->insert_id;
            $stmt->close();

            // Manejar el archivo de subtítulos
            if ($subtitulo_archivos['error'][$index] == UPLOAD_ERR_OK) {
                $tmp_name = $subtitulo_archivos['tmp_name'][$index];
                $nombre_archivo = basename($subtitulo_archivos['name'][$index]);
                $directorio_subtitulos = 'subtitulo/';
                $ruta_archivo = $directorio_subtitulos . $nombre_archivo;

                if (move_uploaded_file($tmp_name, $ruta_archivo)) {
                    $stmt = $mysqli->prepare("INSERT INTO subtitulos (ID_Episodio, archivo_subtitulo) VALUES (?, ?)");
                    $stmt->bind_param("is", $ID_Episodio, $ruta_archivo);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    throw new Exception("Error al mover el archivo de subtítulos.");
                }
            }
        }

        $mysqli->commit();
        echo "Episodios agregados exitosamente.";

    } catch (Exception $e) {
        $mysqli->rollback();
        echo "Error al agregar episodios: " . $e->getMessage();
    }
}

// Obtener la lista de animes para el formulario
$animes = $mysqli->query("SELECT ID_Anime, nombre FROM Animes");

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Agregar Anime y Episodios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
        }
        h1 {
            color: #333;
        }
        form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"], input[type="number"], textarea {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"], button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover, button:hover {
            background-color: #0056b3;
        }
        .episodio {
            margin-bottom: 20px;
        }
        select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>Agregar Anime</h1>
    <form action="Agregartodo2003.php" method="POST">
        <label for="anime_nombre">Nombre del Anime:</label>
        <input type="text" id="anime_nombre" name="anime_nombre" required>

        <label for="anime_sinopsis">Sinopsis:</label>
        <textarea id="anime_sinopsis" name="anime_sinopsis" rows="4" required></textarea>

        <label for="anime_cover">URL de la Portada del Anime:</label>
        <input type="text" id="anime_cover" name="anime_cover" required>

        <input type="submit" name="agregar_anime" value="Guardar Anime">
    </form>

    <h1>Agregar Episodios</h1>
    <form id="episodio_form" action="Agregartodo2003.php" method="POST" enctype="multipart/form-data">
        <label for="anime_id">Selecciona el Anime:</label>
        <select id="anime_id" name="anime_id" required>
            <option value="">Selecciona un anime</option>
            <?php while ($anime = $animes->fetch_assoc()): ?>
                <option value="<?php echo $anime['ID_Anime']; ?>"><?php echo $anime['nombre']; ?></option>
            <?php endwhile; ?>
        </select>

        <div id="episodios">
            <div class="episodio">
                <label for="episodio1_cover">URL de la Portada del Episodio 1:</label>
                <input type="text" id="episodio1_cover" name="episodio_cover[]" required>

                <label for="episodio1_url">URL del Episodio 1:</label>
                <input type="text" id="episodio1_url" name="episodio_url[]" required>

                <label for="subtitulo1">Archivo de Subtítulo del Episodio 1:</label>
                <input type="file" id="subtitulo1" name="subtitulo_archivo[]" accept=".ass">
            </div>
        </div>

        <button type="button" onclick="agregarEpisodio()">Agregar Otro Episodio</button>
        <input type="submit" name="agregar_episodios" value="Guardar Episodios">
    </form>

    <script>
        let episodioCount = 1;

        function agregarEpisodio() {
            episodioCount++;
            const div = document.createElement('div');
            div.className = 'episodio';
            div.innerHTML = `
                <label for="episodio${episodioCount}_cover">URL de la Portada del Episodio ${episodioCount}:</label>
                <input type="text" id="episodio${episodioCount}_cover" name="episodio_cover[]" required>

                <label for="episodio${episodioCount}_url">URL del Episodio ${episodioCount}:</label>
                <input type="text" id="episodio${episodioCount}_url" name="episodio_url[]" required>

                <label for="subtitulo${episodioCount}">Archivo de Subtítulo del Episodio ${episodioCount}:</label>
                <input type="file" id="subtitulo${episodioCount}" name="subtitulo_archivo[]" accept=".ass">
            `;
            document.getElementById('episodios').appendChild(div);
        }
    </script>
</body>
</html>
