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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            padding: 10px;
            margin: 0;
        }
        .container-fluid {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 0;
        }
        .row {
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .card {
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.2);
            background: rgba(255,255,255,0.95);
            display: flex;
            flex-direction: column;
            height: 90vh;
            max-height: 90vh;
            justify-content: flex-end;
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
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
            background: transparent;
            padding: 0.5rem 0;
        }
        .eliminar-link {
            color: #e74c3c;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.85rem;
        }
        .eliminar-link:hover {
            text-decoration: underline;
        }
        .editar-link {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
            margin-right: 10px;
            font-size: 0.85rem;
        }
        .editar-link:hover {
            text-decoration: underline;
        }
        .titulo {
            font-weight: 700;
            color: #1e3c72;
            letter-spacing: 1px;
            font-size: 1.5rem;
        }
        .bubble-left {
            background: #e5e5ea;
            color: #222;
            border-radius: 1.2em 1.2em 1.2em 0.3em;
            max-width: 85%;
            align-self: flex-start;
            margin-bottom: 8px;
            box-shadow: 0 2px 8px rgba(30,60,114,0.07);
        }
        .bubble-right {
            background: #1e3c72;
            color: #fff;
            border-radius: 1.2em 1.2em 0.3em 1.2em;
            max-width: 85%;
            align-self: flex-end;
            margin-bottom: 8px;
            box-shadow: 0 2px 8px rgba(30,60,114,0.07);
            position: relative;
            padding: 12px 16px;
            word-wrap: break-word;
        }
        .chat-date {
            font-size: 0.75rem;
            color: #b3c0d1;
            position: absolute;
            right: 12px;
            bottom: 6px;
            background: rgba(30, 60, 114, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
            white-space: nowrap;
        }
        .indice-msg {
            position: absolute;
            top: 6px;
            right: 12px;
            font-size: 0.75rem;
            color: #b3c0d1;
            font-weight: bold;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
            padding: 2px 6px;
            z-index: 2;
        }
        .editado-label {
            color: #fff;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 4px;
        }
        .avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 1px 4px rgba(30,60,114,0.12);
            background: #fff;
        }
        .user-info {
            min-width: 40px;
            text-align: center;
        }
        .user-name {
            font-size: 0.7rem;
            color: #1e3c72;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .message-content {
            flex: 1;
            max-width: 85%;
            padding-right: 8px;
        }
        .message-text {
            margin-bottom: 20px;
            line-height: 1.4;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
            margin-top: 4px;
        }
        
        /* Media queries para responsividad */
        @media (max-width: 768px) {
            body {
                padding: 5px;
            }
            .container-fluid {
                padding: 5px;
            }
            .card {
                height: 95vh;
                max-height: 95vh;
                border-radius: 1rem;
                padding: 1rem !important;
                max-width: 450px;
                margin: 0 auto;
            }
            .titulo {
                font-size: 1.3rem;
            }
            .bubble-right, .bubble-left {
                max-width: 90%;
                padding: 10px 14px;
            }
            .message-content {
                max-width: 90%;
            }
            .chat-date {
                font-size: 0.7rem;
                padding: 1px 4px;
            }
            .indice-msg {
                font-size: 0.7rem;
                padding: 1px 4px;
            }
            .avatar {
                width: 24px;
                height: 24px;
            }
            .user-info {
                min-width: 32px;
            }
            .user-name {
                font-size: 0.65rem;
            }
            .editar-link, .eliminar-link {
                font-size: 0.8rem;
            }
            .chat-messages-area {
                max-height: 55vh;
            }
        }
        
        @media (max-width: 576px) {
            .card {
                height: 98vh;
                max-height: 98vh;
                border-radius: 0.8rem;
                padding: 0.8rem !important;
                max-width: 400px;
                margin: 0 auto;
            }
            .titulo {
                font-size: 1.2rem;
                margin-bottom: 0.8rem !important;
            }
            .bubble-right, .bubble-left {
                max-width: 95%;
                padding: 8px 12px;
            }
            .message-content {
                max-width: 95%;
            }
            .chat-date {
                font-size: 0.65rem;
                right: 8px;
                bottom: 4px;
            }
            .indice-msg {
                font-size: 0.65rem;
                top: 4px;
                right: 8px;
            }
            .avatar {
                width: 20px;
                height: 20px;
            }
            .user-info {
                min-width: 28px;
            }
            .user-name {
                font-size: 0.6rem;
            }
            .editar-link, .eliminar-link {
                font-size: 0.75rem;
            }
            .chat-messages-area {
                max-height: 50vh;
            }
            .sticky-header {
                padding-bottom: 6px;
            }
            .form-control {
                font-size: 0.9rem;
            }
            .btn {
                font-size: 0.9rem;
                padding: 0.375rem 0.75rem;
            }
        }
        
        @media (max-width: 480px) {
            .card {
                padding: 0.6rem !important;
                max-width: 350px;
                margin: 0 auto;
            }
            .titulo {
                font-size: 1.1rem;
            }
            .bubble-right, .bubble-left {
                max-width: 98%;
                padding: 6px 10px;
            }
            .message-content {
                max-width: 98%;
            }
            .chat-date {
                font-size: 0.6rem;
            }
            .indice-msg {
                font-size: 0.6rem;
            }
            .avatar {
                width: 18px;
                height: 18px;
            }
            .user-info {
                min-width: 24px;
            }
            .user-name {
                font-size: 0.55rem;
            }
            .editar-link, .eliminar-link {
                font-size: 0.7rem;
            }
        }
        
        /* Para pantallas muy grandes, mantener un ancho máximo cómodo */
        @media (min-width: 1200px) {
            .card {
                max-width: 600px;
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 d-flex justify-content-center">
                <div class="card p-4 d-flex flex-column">
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
                            echo '<div class="message-content">';
                            if ($edit_id === intval($fila['id'])) {
                                // Formulario de edición inline tipo chat
                                echo '<form action="editar.php" method="post" class="w-100 d-flex">';
                                echo '<input type="hidden" name="id" value="' . $fila['id'] . '">';
                                echo '<input type="text" name="contenido" class="form-control me-2" value="' . htmlspecialchars($edit_texto) . '" required autofocus>';
                                echo '<button type="submit" class="btn btn-primary btn-sm me-2">Guardar</button>';
                                echo '<a href="index.php" class="btn btn-secondary btn-sm">Cancelar</a>';
                                echo '</form>';
                            } else {
                                echo '<div class="' . $bubble . ' position-relative">';
                                // Número de índice
                                echo '<span class="indice-msg">' . $linea . '</span>';
                                echo '<div class="message-text">';
                                echo '<span class="fw-semibold">' . htmlspecialchars($fila['contenido']) . '</span>';
                                echo '</div>';
                                echo '<span class="chat-date">' . $fecha;
                                if ($es_editado) {
                                    echo ' <span class="editado-label">(editado 🖉)</span>';
                                }
                                echo '</span>';
                                echo '</div>';
                                // Botones debajo de la burbuja
                                echo '<div class="action-buttons">';
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
