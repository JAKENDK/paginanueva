<?php

/**
 * Registra los tipos de post personalizados para EESPP Lourdes.
 */
function lourdes_core_register_cpts() {

    // 1. Programas de Estudio (Carreras)
    $labels_programas = array(
        'name'                  => _x( 'Programas de Estudio', 'Post Type General Name', 'lourdes-core' ),
        'singular_name'         => _x( 'Programa de Estudio', 'Post Type Singular Name', 'lourdes-core' ),
        'menu_name'             => __( 'Programas Acad.', 'lourdes-core' ),
        'name_admin_bar'        => __( 'Programa de Estudio', 'lourdes-core' ),
        'archives'              => __( 'Archivo de Programas', 'lourdes-core' ),
        'attributes'            => __( 'Atributos de Programa', 'lourdes-core' ),
        'all_items'             => __( 'Todos los Programas', 'lourdes-core' ),
        'add_new_item'          => __( 'Añadir Nuevo Programa', 'lourdes-core' ),
        'add_new'               => __( 'Añadir Nuevo', 'lourdes-core' ),
        'new_item'              => __( 'Nuevo Programa', 'lourdes-core' ),
        'edit_item'             => __( 'Editar Programa', 'lourdes-core' ),
        'update_item'           => __( 'Actualizar Programa', 'lourdes-core' ),
        'view_item'             => __( 'Ver Programa', 'lourdes-core' ),
        'search_items'          => __( 'Buscar Programa', 'lourdes-core' ),
    );
    $args_programas = array(
        'label'                 => __( 'Programa de Estudio', 'lourdes-core' ),
        'description'           => __( 'Carreras profesionales de la institución', 'lourdes-core' ),
        'labels'                => $labels_programas,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'taxonomies'            => array( 'category' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-welcome-learn-more',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
        'show_in_rest'          => true, // Importante para compatibilidad con Divi y Gutenberg
    );
    register_post_type( 'programas', $args_programas );

}
add_action( 'init', 'lourdes_core_register_cpts', 0 );
