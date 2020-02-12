<?php

const TIPO_ENTERO = 0;
const TIPO_CADENA = 1;
const TIPO_PASSWORD = 2;
const TIPO_TIMESTAMP = 3;
const TIPO_BOOLEAN = 4;
const REQ_GET = 'GET';   // PARA USAR EN LA FUNCIÓN ES_GET() y PARA COMPROBAR_PARÁMETROS()
const REQ_POST = 'POST'; // PARA USAR EN LA FUNCIÓN ES_POST() y PARA COMPROBAR_PARÁMETROS()
const FPP = 4;


// COMPRUEBA QUE LOS PARÁMETROS RECIBIDOS SEAN LOS MISMOS QUE LOS DE LA CONSTANTE PAR.
function comprobarParametros($par, $req, &$errores)
{
    $res = [];
    foreach ($par as $k => $v) {
        if (isset($v['def'])) {
            $res[$k] = $v['def'];
        }
    }
    $peticion = peticion($req);


    if ((es_GET($req) && !empty($peticion)) || es_POST($req)) {
        if ((es_GET($req) || es_POST($req) && !empty($peticion))
            && empty(array_diff_key($res, $peticion))
            && empty(array_diff_key($peticion, $res))) {
            $res = array_map('trim', $peticion);
        } else {
            $errores[] = 'Los parámetros recibidos no son los correctos.';
        }
    }
    return $res;
}


//PARA MOSTRAR ERRORES EN EL DIBUJAR ELEMENTO FORMULARIO
function mensajeError($campo, $errores)
{
    if (isset($errores[$campo])) {
        return <<<EOT
        <div class="invalid-feedback">
            {$errores[$campo]}
        </div>
        EOT;
    } else {
        return '';
    }
}



//PARA INDICAR QUE ELEMENTO ES EL SELECCIONADO POR DEFECTO.
function selected($op, $o)
{
    return $op == $o ? 'selected' : '';
}

 //PARA AÑADIR AL INPUT DEL FORMULARIO SI EL CAMPO ES VALIDO O NO.
function valido($campo, $errores)
{
    $peticion = peticion();
    if (isset($errores[$campo])) {
        return 'is-invalid';
    } elseif (!empty($peticion)) {
        return 'is-valid';
    } else {
        return '';
    }
}

//DIBUJAR EL FORMULARIO PARA EL FORMULARIO DE BUSQUEDA.
function dibujarFormularioIndex($args, $par, $pdo, $errores)
{ ?>
    <div class="row mt-3">
        <div class="col-4 offset-4">
            <form action="" method="get">
                <?php dibujarElementoFormulario($args, $par, $pdo, $errores) ?>
                <button type="submit" class="btn btn-primary">
                    Buscar
                </button>
                <button type="reset" class="btn btn-secondary">
                    Limpiar
                </button>
            </form>
        </div>
    </div>
    <?php
}

//DIBUJAR EL FORMULARIO PARA MÓDIFICAR
function dibujarFormulario($args, $par, $accion, $pdo, $errores)
{ ?>
        
    <div class="row mt-3">
        <div class="col">
            <form action="" method="post">
                <?php dibujarElementoFormulario($args, $par, $pdo, $errores) ?>
                <?= token_csrf() ?>
                <button type="submit" class="btn btn-primary">
                    <?= $accion ?>
                </button>
                <a href="index.php" class="btn btn-info" role="button">
                 Volver
                </a>

            </form>
        </div>
    </div>
    <?php
}

//CREA UN TOKEN PARA EL CAMPO DEL FORMULARIO POST.
function token_csrf()
{
    if (isset($_SESSION['token'])) {
        $token = $_SESSION['token'];
        return <<<EOT
            <input type="hidden" name="_csrf" value="$token">
        EOT;
    }
}


// COMPRUEBA QUE EL TOKEN NO ES NULO Y ES VALIDO
function tokenValido($_csrf)
{
    if ($_csrf !== null) {
        return $_csrf === $_SESSION['token'];
    }
    return false;
}


//DIBUJA LOS ELEMENTOS DE CUALQUIER FORMULARIO, UTILIZA LA VARIABLE CONSTANTE PAR.
function dibujarElementoFormulario($args, $par, $pdo, $errores)
{
    foreach ($par as $k => $v): ?>
        <?php if (isset($par[$k]['def'])): ?>
            <div class="form-group">
                <label for="<?= $k ?>"><?= $par[$k]['etiqueta'] ?></label>
                <?php if (isset($par[$k]['relacion'])): ?>
                    <?php
                    $tabla = $par[$k]['relacion']['tabla'];
                    $visualizar = $par[$k]['relacion']['visualizar'];
                    $ajena = $par[$k]['relacion']['ajena'];
                    $sent = $pdo->query("SELECT $ajena, $visualizar
                                           FROM $tabla");
                    ?>
                    <select id="<?= $k ?>" name="<?= $k ?>" class="form-control">
                        <?php foreach ($sent as $fila): ?>
                            <option value="<?= h($fila[0]) ?>"
                                    <?= selected($fila[0], $args['categoria_id']) ?>>
                                <?= h($fila[1]) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                <?php elseif ($par[$k]['tipo'] === TIPO_PASSWORD): ?>
                    <input type="password"
                           class="form-control <?= valido($k, $errores) ?>"
                           id="<?= $k ?>" name="<?= $k ?>"
                           value="">
                <?php else: ?>
                    <input type="text"
                           class="form-control <?= valido($k, $errores) ?>"
                           id="<?= $k ?>" name="<?= $k ?>"
                           value="<?= h($args[$k]) ?>">
                <?php endif ?>
                <?= mensajeError($k, $errores) ?>
            </div>
        <?php endif ?><?php
    endforeach;
}

// INSERTA UN FILTRO EN EL SQL ANTES DE PASARSELO A EJECUTARCONSULTA()
function insertarFiltro(&$sql, &$execute, $campo, $args, $par, $errores)
{
    if (isset($par[$campo]['def']) && $args[$campo] !== '' && !isset($errores[$campo])) {
        if ($par[$campo]['tipo'] === TIPO_ENTERO) {
            $sql .= " AND $campo = :$campo";
            $execute[$campo] = $args[$campo];
        } 
        else {
            $sql .= " AND $campo ILIKE :$campo";
            $execute[$campo] = '%' . $args[$campo] . '%';
        }
    }
}

//EJECUTA LA CONSULTA.
function ejecutarConsulta($sql, $execute, $pdo)
{
    $sent = $pdo->prepare("SELECT * $sql");
    $sent->execute($execute);
    return $sent;
}

//CUENTA LAS FILAS DE LAS CONSULTAS.
function contarConsulta($sql, $execute, $pdo)
{
    $sent = $pdo->prepare("SELECT COUNT(*) $sql");

    $sent->execute($execute);
    $count = $sent->fetchColumn();
    return $count;
}


// DIBUJA LA TABLA
function dibujarTabla($sent, $count, $par, $orden,$direccion, $errores)
{
    $filtro = paramsFiltro();
    ?>
    <?php if ($count == 0): ?>
        <?php alert('No se ha encontrado ninguna fila que coincida.', 'danger') ?>        <div class="row mt-3">
    <?php elseif (isset($errores[0])): ?>
        <?php alert($errores[0], 'danger') ?>
    <?php else: ?>
        <div class="row mt-4">
            <div class="col-14 offset-1">
                <table class="table">
                    <thead>
                        <?php foreach ($par as $k => $v): ?>
                            <th class="columna" scope="col">
                                    <?php if($direccion == 'asc'): ?>
                                        <a href="<?= "?$filtro&orden=$k&direccion=desc" ?>">
                                            <?= $par[$k]['etiqueta'] ?>
                                        </a>
                                        <?= ($k === $orden) ? '⬆' : '' ?>
                                    <?php else : ?>
                                        <a href="<?= "?$filtro&orden=$k&direccion=asc" ?>">
                                            <?= $par[$k]['etiqueta'] ?>
                                        </a>
                                        <?= ($k === $orden) ? '⬇' : '' ?>
                                    <?php endif; ?>
                            </th>
                        <?php endforeach ?>
                        <th scope="col">Acciones</th>
                    </thead>
                    <tbody>
                        <?php foreach ($sent as $fila): ?>
                            <tr scope="row">
                                <?php foreach ($par as $k => $v): ?>
                                    <?php if (isset($par[$k]['relacion'])): ?>
                                        <?php $visualizar = $par[$k]['relacion']['visualizar'] ?>
                                        <td><?= $fila[$visualizar] ?></td>
                                    <?php else: ?>
                                        <td><?= h($fila[$k]) ?></td>
                                    <?php endif ?>
                                <?php endforeach ?>
                                <td>
                                    <form action="" method="post">
                                        <input type="hidden" name="id" value="<?= $fila['id'] ?>">
                                        <?= token_csrf() ?>
                                        <button type="submit" class="btn btn-sm btn-danger">Borrar</button>
                                        <a href="modificar.php?id=<?= $fila['id'] ?>" class="btn btn-sm btn-info" role="button">
                                            Modificar
                                        </a>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif;
}


//PARA MOSTRAR ALERTAS.
function alert($mensaje = null, $severidad = null)
{
    if ($mensaje === null) {
        if (hayAvisos()) {
            $aviso = getAviso();
            $mensaje = $aviso['mensaje'];
            $severidad = $aviso['severidad'];
            quitarAvisos();
        } else {
            return;
        }
    }
    ?>
    <div class="row mt-3">
        <div class="col-8 offset-2">
            <div class="alert alert-<?= $severidad ?> alert-dismissible fade show" role="alert">
                <?= $mensaje ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div><?php
}


//BORRA LA FILA :')
function borrarFila($pdo, $tabla, $id)
{
    $sent = $pdo->prepare("DELETE
                             FROM $tabla
                            WHERE id = :id");
    $sent->execute(['id' => $id]);
    if ($sent->rowCount() === 1) {
        aviso('Fila borrada correctamente.');
        $retorno = $_SERVER['REQUEST_URI'];
        header("Location:  $retorno");
    } else {
        alert('Ha ocurrido un error inesperado.', 'danger');
    }
}

//CONECTA CON LA BD.
function conectar()
{
    return new PDO('pgsql:host=localhost;dbname=meneame', 'usuario', 'usuario');
}

//COMPRUEBA SI SE HA LLEGADO POR GET.
function es_GET($req = null)
{
    return ($req === null) ? metodo() === 'GET' : $req === REQ_GET;
}

//COMPRUEBA SI SE HA LLEGADO POR POST.
function es_POST($req = null)
{
    return ($req === null) ? metodo() === 'POST' : $req === REQ_POST;
}


//DEVUELVE EL METHOD DEL FORMULARIO QUE NOS HA TRAIDO.
function metodo()
{
    return $_SERVER['REQUEST_METHOD'];
}

function peticion($req = null)
{
    return es_GET($req) ? $_GET : $_POST;
}

//COMPRUEBA SI EL USUARIO ESTA LOGUEADO.
function logueado()
{
    return isset($_SESSION['login']) ? $_SESSION['login'] : false;
}

//ALMACENA EL AVISO.
function aviso($mensaje, $severidad = 'success')
{
    $_SESSION['aviso'] = [
        'mensaje' => $mensaje,
        'severidad' => $severidad,
    ];
}

//COMPRUEBA SI HAY AVISOS.
function hayAvisos()
{
    return isset($_SESSION['aviso']);
}

//NOS MUESTRA LOS AVISOS.
function getAviso()
{
    return hayAvisos() ? $_SESSION['aviso'] : [];
}

//ELIMINA LOS AVISOS.
function quitarAvisos()
{
    unset($_SESSION['aviso']);
}

//PARA EVITAR EL ATAQUE XSS (CROSS SITE SCRIPTING).
function h($cadena)
{
    return htmlspecialchars($cadena, ENT_QUOTES | ENT_SUBSTITUTE);
}


//LOGUEO OBLIGATORIO.
function logueoObligatorio()
{
    if (!logueado()) {
        aviso('Tiene que estar logueado para entrar en esa parte del programa.', 'danger');
        $_SESSION['retorno'] = $_SERVER['REQUEST_URI'];
        header('Location: /usuarios/login.php');
        return true;
    }
    return false;
}

//LOGUEO NO OBLIGATORIO.
function noLogueoObligatorio()
{
    if (logueado()) {
        aviso('Tiene que estar no logueado para entrar en esa parte del programa.', 'danger');
        $_SESSION['retorno'] = $_SERVER['REQUEST_URI'];
        header('Location: /');
        return true;
    }
    return false;
}


//CREA EL PAGINADOR.
function paginador($pag, $npags, $orden)
{
    $filtro = paramsFiltro();

    $ant = $pag - 1;
    $sig = $pag + 1; 
    $orden = "orden=$orden";
    ?>
    <div class="row">
        <div class="col-6 offset-3 mt-3">
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($pag <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= "?pag=$ant&$filtro&$orden" ?>">Anterior</a>
                    </li>
                    <?php for ($i = 1; $i <= $npags; $i++): ?>
                        <li class="page-item <?= ($i == $pag) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= "?pag=$i&$filtro&$orden" ?>"><?= $i ?></a>
                        </li>
                    <?php endfor ?>
                    <li class="page-item <?= ($pag >= $npags) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= "?pag=$sig&$filtro&$orden" ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div><?php
}

//RECOGE EL NÚMERO PAG.
function recogerNumPag()
{
    if (isset($_GET['pag']) && ctype_digit($_GET['pag'])) {
        $pag = trim($_GET['pag']);
        unset($_GET['pag']);
    } else {
        $pag = 1;
    }
    
    return $pag;
}

//RECOGE EL ORDEN.

function recogerOrden()
{
    if (isset($_GET['orden'])) {
        $orden = trim($_GET['orden']);
        unset($_GET['orden']);
    } else {
        $orden = 'created_at';
    }
    return $orden;
}

//RECOGE LA DIRECCION.

function recogerDireccion()
{
    if (isset($_GET['direccion'])) {
        $direccion = trim($_GET['direccion']);
        unset($_GET['direccion']);
    } else {
        $direccion = 'asc';
    }
    return $direccion;
}

//CREA UN FILTRO GET PARA INTRODUCIRLO EN LA URL.
function paramsFiltro()
{
    $filtro = [];

    foreach ($_GET as $k => $v) {
        $filtro[] = "$k=$v";
    }

    return implode('&', $filtro);
}

//OBTIENE LA FECHA
function obtenerFecha($timestamp)
{
    $fecha = new DateTime($timestamp);
    return $fecha->format('Y-m-d');
}

//OBTIENE LA HORA
function obtenerHora($timestamp)
{
    $hora = new DateTime($timestamp);
    $hora->setTimeZone(new DateTimeZone('CET'));
    return $hora->format('H:i:s');;
}

//DIBUJA LA BARRA DE NAVEGACIÓN.
function dibujarNav($pdo){
    ?>
    
    <nav class="navbar navbar-expand-lg">
      <a class="navbar-brand" href="../index.php">muéveme</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item active">
            <a class="nav-link subrayado" href="#">EDICIÓN GENERAL <span class="sr-only">(current)</span></a>
          </li>
        </ul>
        <form class="form-inline my-2 my-lg-0" action="" method="get">
            <input class="form-control mr-sm-2" type="search" placeholder="Buscar" name='buscar' aria-label="Search">
        </form>
        <ul class="navbar-nav">
                <?php if (logueado()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= logueado() ?>
                        </a>
                        <?php if(esAdmin($_SESSION['login'],$pdo)): ?>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <form class="dropdown-item" action="/usuarios/logout.php" method="post"><button class="" type="submit">Logout</button></form>
                                <a class="dropdown-item" href="/noticias/mis-noticias.php">Mis Noticias</a>
                                <form action="" method="post">
                                    <?= token_csrf() ?>
                                    <a href="/usuarios/modificar.php?id=<?= obtenerIdUsuario($_SESSION['login'],$pdo) ?>" class="dropdown-item" role="button">
                                        Mi perfil
                                    </a>
                                </form>

                                <a class="dropdown-item" href="/noticias/noticias-admin.php">Administrar Noticias</a>
                                <a class="dropdown-item" href="/usuarios/usuarios-admin.php">Administrar Usuarios</a>

                            </div>
                        <?php else: ?>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <form class="dropdown-item" action="/usuarios/logout.php" method="post"><button class="" type="submit">Logout</button></form>
                                <a class="dropdown-item" href="/noticias/mis-noticias.php">Mis Noticias</a>
                                <a class="dropdown-item" href="/usuarios/modificar.php?id=<?= obtenerIdUsuario($_SESSION['login'],$pdo) ?>">Mi Perfil</a>
                            </div>
                        <?php endif ?>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Usuario
                        </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="/../usuarios/login.php">Login</a>
                        <a class="dropdown-item" href="/../usuarios/registrar.php"">Registrarse</a>
                    </li>
                <?php endif ?>
        </ul>
      </div>
    </nav>
    <nav class="publicar">
      <a href="/noticias/insertar.php"><button class="botonpublicar">+ Publicar</button></a>
    </nav>
    <?php
    
}


//COMPRUEBA SI EL USUARIO ES ADMIN.
function esAdmin($login,$pdo){
    $sql = "SELECT * FROM usuarios WHERE login = '$login'";
    $resultado = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC); 
    if($resultado['admin']!== true){
        return false;
    }else{
        return true;
    }
}

//ES OBLIGATORIO SER ADMIN.
function adminObligatorio($pdo)
{
    if (!esAdmin($_SESSION['login'],$pdo)) {
        aviso('No tienes permisos.', 'danger');
        $_SESSION['retorno'] = $_SERVER['REQUEST_URI'];
        header('Location: /noticias/mis-noticias.php');
        return true;
    }
    return false;
}

//OBTIENE LAS IDS DE TODAS LAS NOTICIAS DEL USUARIO.
function obtenerIdsNoticiasUsuario($login,$pdo){

    $sql = "SELECT id FROM noticias WHERE usuario_id in (select id from usuarios where login = '$login')";
    $resultado = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $res = [];

    foreach ($resultado as $k => $v) {
        $res[$k] = $v['id'];
    }
    return $res;

}

//OBTIENE EL ID DEL USUARIO.
function obtenerIdUsuario($login,$pdo){

    $sql = "SELECT id FROM usuarios where login = '$login'";
    $resultado = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $res = [];
    
    foreach ($resultado as $k => $v) {
        $res = $v['id'];
    }
    return $res;

}

//OBTIENE TODOS LOS IDS DE LAS NOTICIAS.
function obtenerIdsNoticias($pdo){

    $sql = "SELECT id FROM noticias ORDER BY id";
    $resultado = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $res = [];
    
    foreach ($resultado as $k => $v) {
        $res[$k] = $v['id'];
    }
    return $res;

}

//OBTIENE TODOS LOS IDS DE LOS USUARIOS
function obtenerIdsUsuarios($pdo){

    $sql = "SELECT id FROM usuarios ORDER BY id";
    $resultado = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $res = [];
    
    foreach ($resultado as $k => $v) {
        $res[$k] = $v['id'];
    }
    return $res;

}




