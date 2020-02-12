<?php
const PAR = [
    'id' => [
        'def' => '',
        'tipo' => TIPO_CADENA,
        'etiqueta' => 'ID',
    ],
    'contenido' => [
        'def' => '',
        'tipo' => TIPO_CADENA,
        'etiqueta' => 'Contenido',
    ],
    'usuario_id' => [
        
        'tipo' => TIPO_ENTERO,
        'def' => '',
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