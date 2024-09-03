<?php
require 'config/databaseconnect.php'; // Asegúrate de que este archivo establezca la conexión a la base de datos correctamente.

$id_episodio = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Hacer la consulta a la base de datos para obtener la URL del episodio y del subtítulo
$sql_episodio = "SELECT el.url AS EpisodeURL, s.archivo_subtitulo AS SubtitleURL
                 FROM Episodios e
                 JOIN EpisodioLinks el ON e.ID_EpisodioLink = el.ID_EpisodioLink
                 LEFT JOIN subtitulos s ON e.ID_Episodio = s.ID_Episodio
                 WHERE e.ID_Episodio = ?";

$stmt_episodio = $mysqli->prepare($sql_episodio);
$stmt_episodio->bind_param("i", $id_episodio);
$stmt_episodio->execute();
$result_episodio = $stmt_episodio->get_result();

if ($result_episodio->num_rows > 0) {
    $row_episodio = $result_episodio->fetch_assoc();
    $video_url = htmlspecialchars($row_episodio['EpisodeURL']);
    $subtitle_url = htmlspecialchars($row_episodio['SubtitleURL']);
} else {
    echo 'No se encontró el episodio.';
    exit();
}

$stmt_episodio->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Videojs ASS/SSA subtitles</title>

    <!-- npm -->
    <link href="node_modules/video.js/dist/video-js.min.css" rel="stylesheet">
    <link href="node_modules/libjass/libjass.css" rel="stylesheet">
    <script src="node_modules/video.js/dist/video.min.js"></script>
    <script src="node_modules/libjass/libjass.js"></script>

    <!-- src -->
    <link href="src/videojs.ass.css" rel="stylesheet">
    <script src="src/videojs.ass.js"></script>

    <style>
      body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh; /* Altura completa de la ventana */
        margin: 0;
        background-color:  rgb(46, 46, 46); /* Fondo negro para mejor contraste con el video */
      }
      .video-container {
        text-align: center;
      }
    </style>
  </head>

  <body>
    <div class="video-container">
      <video id="player" class="video-js vjs-default-skin vjs-big-play-centered">
        <source src="<?php echo $video_url; ?>" type="video/mp4">
      </video>
    </div>
    <script>
      videojs('player', {
        controls: true,
        nativeControlsForTouch: false,
        width: 1024,
        height: 600,
        playbackRates: [0.5, 1, 1.5, 2]
      });

      var vjs = videojs('player');
      // initialize the plugin this way to access internal methods
      var vjs_ass = vjs.ass({
        'src': ["<?php echo $subtitle_url; ?>"], // URL de los subtítulos desde la base de datos
        label: "esp",
        videoWidth: 1024,
        videoHeight: 600,
        // enableSvg: false
      });

      // you will then be able to use the following js to switch subtitle
      // vjs_ass.loadNewSubtitle('URL HERE')
    </script>
  </body>
</html>
