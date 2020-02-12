<?php session_start() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="/../css/style.css">
    <title>Insertar una nueva noticia</title>
</head>
<body>
    <div class="container">       
        <?php
        require __DIR__ . '/../comunes/auxiliar.php';
        require __DIR__ . '/auxiliar.php';

        if (logueoObligatorio()) {
            return;
        }
        $login = $_SESSION['login'];

        

        $pdo = conectar();

        dibujarNav($pdo);
    
        $errores = [];
        $_csrf = (isset($_POST['_csrf'])) ? $_POST['_csrf'] : null;
        unset($_POST['_csrf']);
        $args = comprobarParametros(PAR, REQ_POST, $errores);


        $id = $pdo->query("SELECT * FROM usuarios WHERE login = '$login'")->fetchColumn(0);

 


        comprobarValores($args, null, $pdo, $errores);
        if (es_POST() && empty($errores)) {
            if (!tokenValido($_csrf)) {
                alert('El token de CSRF no es válido.', 'danger');
            } else {
                $sent = $pdo->prepare('INSERT
                                         INTO noticias (titulo, contenido, usuario_id, categoria_id)
                                       VALUES (:titulo, :contenido, :usuario_id, :categoria_id)');
                if(!$sent->execute([
                    'titulo' => $args['titulo'],
                    'contenido' => $args['contenido'],
                    'usuario_id' => $id,
                    'categoria_id' => $args['categoria_id']
                ])){
                    aviso('Ha ocurrido algún problema.', 'danger');
                }else{
                    aviso('Fila insertada correctamente.');
                    header('Location: mis-noticias.php');
                    return;
                }

            }
        }
        dibujarFormulario($args, PAR, 'Insertar', $pdo, $errores);
        ?>
        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    </div>
</body>
</html>
