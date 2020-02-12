<?php session_start() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="/../css/style.css">
    <title>Modificar un empleado</title>
</head>
<body>
    <div class="container">       
        <?php
        require __DIR__ . '/../comunes/auxiliar.php';
        require __DIR__ . '/auxiliar.php';

        
        if (logueoObligatorio()) {
            return;
        }
        $pdo = conectar();
        if (!isset($_GET['id']) || (!in_array($_GET['id'], obtenerIdsNoticiasUsuario($_SESSION['login'],$pdo)) && !esAdmin($_SESSION['login'], $pdo)) || !in_array($_GET['id'], obtenerIdsNoticias($pdo))) {
            header('Location: /noticias/mis-noticias.php');
            return;
        }
       
        $pdo = conectar();
        dibujarNav($pdo);

        
        $errores = [];
     
        if (es_POST() && isset($_SESSION['token'])) {
            $token_sesion = $_SESSION['token'];
            if (isset($_POST['_csrf'])) {
                $token_form = $_POST['_csrf'];
                unset($_POST['_csrf']);
                if ($token_sesion !== $token_form) {
                    alert('Ha ocurrido un error interno en el servidor1.', 'danger');
                } else {
                    
                }
            } else {
                alert('Ha ocurrido un error interno en el servidor2.', 'danger');
            }
        }
        $args = comprobarParametros(PAR, REQ_POST, $errores);
        
        $id = trim($_GET['id']);
        $pdo = conectar();

        comprobarValores($args, $id, $pdo, $errores);

        if (es_POST() && empty($errores)) {
            var_dump($errores);
            $sent = $pdo->prepare('UPDATE noticias
                                      SET titulo = :titulo
                                        , contenido = :contenido
                                        , categoria_id = :categoria_id
                                    WHERE id = :id');
            $args['id'] = $id;
            $sent->execute($args);
            aviso('Fila modificada correctamente.');
            header('Location: mis-noticias.php');
            return;
        }
        if (es_GET()) {
            $sent = $pdo->prepare('SELECT *
                                     FROM noticias
                                    WHERE id = :id');
            $sent->execute(['id' => $id]);
            if (($args = $sent->fetch(PDO::FETCH_ASSOC)) === false) {
                aviso('Ha habido un error al modificar la fila.', 'danger');
                return;
            }
        }
        dibujarFormulario($args, PAR, 'Modificar', $pdo, $errores);
        ?>
        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    </div>
</body>
</html>
