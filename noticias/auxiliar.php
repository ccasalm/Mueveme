<?php
const PAR = [
    'titulo' => [
        'def' => '',
        'tipo' => TIPO_CADENA,
        'etiqueta' => 'Titulo',
    ],
    'contenido' => [
        'def' => '',
        'tipo' => TIPO_CADENA,
        'etiqueta' => 'Contenido',
    ],
    'usuario_id' => [
        
        'tipo' => TIPO_ENTERO,
        'etiqueta' => 'Autor',
        'relacion' => [
            'tabla' => 'usuarios',
            'ajena' => 'id',
            'visualizar' => 'login',
        ],
    ],
    'categoria_id' => [
        'def' => '',
        'tipo' => TIPO_ENTERO,
        'etiqueta' => 'Categoria',
        'relacion' => [
            'tabla' => 'categorias',
            'ajena' => 'id',
            'visualizar' => 'nombre',
        ],
    ],
    'created_at' => [
        'tipo' => TIPO_TIMESTAMP,
        'etiqueta' => 'Fecha',
    ],   
];

function mostrarNoticias($pdo, $sent, $nfilas, $par, $errores)
{ ?>
    <?php if ($nfilas == 0): ?>
        <?php alert('No se ha encontrado ninguna fila que coincida.', 'danger') ?>  <div class="row mt-3">
    <?php elseif (isset($errores[0])): ?>
        <?php alert($errores[0], 'danger') ?>
    <?php else: ?>
        <div class="row">
                <?php foreach ($sent as $fila): ?>
                <div class="col-9 mx-auto mt-3 mb-3">                                                                  
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title"><?= h($fila['titulo']) ?></div>
                            <div class="card-subtitle mb-2 text-muted">
                                <span>Publicado por: </span>
                                <div class="autor">
                                    <?php foreach ($par as $k => $v): 
                                        if ($par[$k]['etiqueta'] == 'Autor' && isset($par[$k]['relacion'])): 
                                            $visualizar = $par[$k]['relacion']['visualizar'];
                                            echo h($fila[$visualizar]); 
                                        endif;
                                    endforeach?>
                                </div>
                                <div class="categoria">
                                    <?php foreach ($par as $k => $v): 
                                        if ($par[$k]['etiqueta'] == 'Categoria' && isset($par[$k]['relacion'])): 
                                            $visualizar = $par[$k]['relacion']['visualizar'];
                                            echo h($fila[$visualizar]); 
                                        endif;
                                    endforeach?>
                                </div>
                                <div class="fecha">
                                    <?= h(obtenerFecha($fila['created_at']))?>
                                </div>
                                <div class="hora">
                                    <?= h(obtenerHora($fila['created_at']))?>
                                </div>
                            </div>
                            <p class="card-text"><?=h($fila['contenido'])?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach ?>
            </div>
    <?php endif;
}


function comprobarValores(&$args, $id, $pdo, &$errores)
{
    if (!empty($errores) || empty($_POST)) {
        return;
    }

    extract($args);

    if (isset($args['titulo'])) {
        if ($titulo === '') {
            $errores['titulo'] = 'El titulo de la noticia es obligatoria.';
        } elseif (mb_strlen($titulo) > 255) {
            $errores['titulo'] = 'El número no puede tener más 255 caracteres.';
        }
    }

    if (isset($args['contenido'])) {
        if ($contenido === '') {
            $errores['contenido'] = 'El contenido es obligatorio.';
        } elseif (mb_strlen($contenido) > 1000) {
            $errores['contenido'] = 'El nombre del departamento no puede tener más de 1000 caracteres.';
        }
    }
}