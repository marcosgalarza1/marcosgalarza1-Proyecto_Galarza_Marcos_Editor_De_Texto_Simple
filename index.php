<?php
require 'conexion.php';

// Buscador
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$where = '';
if ($busqueda !== '') {
    $where = "WHERE contenido LIKE '%" . $conn->real_escape_string($busqueda) . "%'";
}
// Obtener todas las líneas de texto de la base de datos (filtradas si hay búsqueda)
$resultado = $conn->query("SELECT * FROM texto $where ORDER BY id ASC");

// Para edición inline
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : null;
$edit_texto = '';
if ($edit_id !== null) {
    $res_edit = $conn->query("SELECT contenido FROM texto WHERE id = $edit_id LIMIT 1");
    if ($fila_edit = $res_edit->fetch_assoc()) {
        $edit_texto = $fila_edit['contenido'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="img/logo.png">
    <title>Editor de Texto</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.2);
            background: rgba(255,255,255,0.95);
            display: flex;
            flex-direction: column;
            height: 80vh;
            justify-content: flex-end;
            position: relative;
        }
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 10;
            background: rgba(255,255,255,0.95);
            padding-bottom: 8px;
        }
        .chat-messages-area {
            flex-grow: 1;
            overflow-y: auto;
            max-height: 60vh;
            margin-bottom: 10px;
            padding-right: 4px;
        }
        .form-control, .btn {
            border-radius: 0.75rem;
        }
        .btn-primary {
            background: #1e3c72;
            border: none;
        }
        .btn-primary:hover {
            background: #16325c;
        }
        .list-group-item {
            border: none;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            background: #f4f8fb;
        }
        .eliminar-link {
            color: #e74c3c;
            text-decoration: none;
            font-weight: bold;
        }
        .eliminar-link:hover {
            text-decoration: underline;
        }
        .editar-link {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
            margin-right: 10px;
        }
        .editar-link:hover {
            text-decoration: underline;
        }
        .titulo {
            font-weight: 700;
            color: #1e3c72;
            letter-spacing: 1px;
        }
        .bubble-left {
            background: #e5e5ea;
            color: #222;
            border-radius: 1.2em 1.2em 1.2em 0.3em;
            max-width: 75%;
            align-self: flex-start;
            margin-bottom: 8px;
            box-shadow: 0 2px 8px rgba(30,60,114,0.07);
        }
        .bubble-right {
            background: #1e3c72;
            color: #fff;
            border-radius: 1.2em 1.2em 0.3em 1.2em;
            max-width: 75%;
            align-self: flex-end;
            margin-bottom: 8px;
            box-shadow: 0 2px 8px rgba(30,60,114,0.07);
        }
        .chat-date {
            font-size: 0.85em;
            color: #888;
            position: absolute;
            right: 12px;
            bottom: 2px;
        }
        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 1px 4px rgba(30,60,114,0.12);
            background: #fff;
        }
        .user-info {
            min-width: 48px;
        }
        .user-name {
            font-size: 0.8em;
            color: #1e3c72;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .bubble-right {
            background: #1e3c72;
            color: #fff;
            border-radius: 1.2em 1.2em 0.3em 1.2em;
            max-width: 75%;
            align-self: flex-end;
            margin-bottom: 8px;
            box-shadow: 0 2px 8px rgba(30,60,114,0.07);
            position: relative;
        }
        .chat-date {
            font-size: 0.85em;
            color: #b3c0d1;
            position: absolute;
            right: 12px;
            bottom: 2px;
        }
        .indice-msg {
            position: absolute;
            top: 6px;
            right: 12px;
            font-size: 0.85em;
            color: #b3c0d1;
            font-weight: bold;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
            padding: 0 7px;
            z-index: 2;
        }
        .editado-label {
            color: #fff;
            font-size: 0.85em;
            font-weight: bold;
            margin-left: 6px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card p-4 my-5 d-flex flex-column" style="min-height: 80vh;">
                    <div class="sticky-header">
                        <h2 class="mb-4 text-center titulo">Editor de Texto</h2>
                        <!-- Buscador y PDF -->
                        <form method="get" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="busqueda" class="form-control" placeholder="Buscar texto..." value="<?php echo htmlspecialchars($busqueda); ?>">
                                <button type="submit" class="btn btn-secondary ms-2">Buscar</button>
                            </div>
                        </form>
                        <div class="mb-3 text-end">
                            <a href="ver_pdf.php" target="_blank" class="btn btn-success">HISTORIAL EN PDF</a>
                        </div>
                    </div>
                    <div class="chat-messages-area flex-grow-1 d-flex flex-column">
                        <!-- Lista de mensajes -->
                        <h5 class="mb-3">Contenido actual:</h5>
                        <ul class="list-group border-0" style="background:transparent;">
                        <?php
                        $linea = 0;
                        $avatar_url = 'https://ui-avatars.com/api/?name=MG&background=1e3c72&color=fff&rounded=true&size=32';
                        while ($fila = $resultado->fetch_assoc()) {
                            $es_editado = !empty($fila['fecha_edicion']) && $fila['fecha_edicion'] !== $fila['fecha_creacion'];
                            $fecha = $es_editado ? $fila['fecha_edicion'] : $fila['fecha_creacion'];
                            $fecha_label = $es_editado ? 'editado' : 'enviado';
                            $align = 'justify-content-end';
                            $bubble = 'bubble-right';
                            echo '<li class="list-group-item border-0 d-flex align-items-end ' . $align . '" style="background:transparent;">';
                            // Avatar y nombre
                            echo '<div class="user-info me-2 text-end">';
                            echo '<img src="' . $avatar_url . '" alt="User" class="avatar mb-1"><br>';
                            echo '<span class="user-name">Marcos</span>';
                            echo '</div>';
                            echo '<div style="flex:1; max-width:80%;">';
                            if ($edit_id === intval($fila['id'])) {
                                // Formulario de edición inline tipo chat
                                echo '<form action="editar.php" method="post" class="w-100 d-flex">';
                                echo '<input type="hidden" name="id" value="' . $fila['id'] . '">';
                                echo '<input type="text" name="contenido" class="form-control me-2" value="' . htmlspecialchars($edit_texto) . '" required autofocus>';
                                echo '<button type="submit" class="btn btn-primary btn-sm me-2">Guardar</button>';
                                echo '<a href="index.php" class="btn btn-secondary btn-sm">Cancelar</a>';
                                echo '</form>';
                            } else {
                                echo '<div class="' . $bubble . ' p-2 px-3 position-relative">';
                                // Número de índice
                                echo '<span class="indice-msg">' . $linea . '</span>';
                                echo '<span class="fw-semibold">' . htmlspecialchars($fila['contenido']) . '</span><br>';
                                echo '<span class="chat-date">' . $fecha;
                                if ($es_editado) {
                                    echo ' <span class="editado-label">(editado 🖉)</span>';
                                }
                                echo '</span>';
                                echo '</div>';
                                // Botones debajo de la burbuja
                                echo '<div class="d-flex mt-1" style="gap:8px;">';
                                echo '<a href="index.php?edit_id=' . $fila['id'] . ( $busqueda ? '&busqueda=' . urlencode($busqueda) : '' ) . '" class="editar-link">Editar</a>';
                                echo "<a href='eliminar.php?id=" . $fila['id'] . "' class='eliminar-link' onclick='return confirm(\"¿Eliminar esta línea?\")'>Eliminar</a>";
                                echo '</div>';
                            }
                            echo '</div>';
                            echo '</li>';
                            $linea++;
                        }
                        ?>
                        </ul>
                    </div>
                    <!-- Formulario para insertar texto -->
                    <form action="insertar.php" method="post" class="mt-auto mb-0">
                        <div class="input-group">
                            <input type="text" name="contenido" class="form-control" placeholder="Escribe un mensaje" required>
                            <button type="submit" class="btn btn-primary ms-2">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Scroll al final cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            const chatArea = document.querySelector('.chat-messages-area');
            if (chatArea) {
                chatArea.scrollTop = chatArea.scrollHeight;
            }
        });
    </script>
</body>
</html>
