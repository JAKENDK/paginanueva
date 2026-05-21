<?php
/*
Plugin Name: Lourdes-Core
Plugin URI: http://localhost/plugins/
Description: Funcionalidades básicas para el portal de la EESPP Nuestra Señora de Lourdes.
Version: 1.0
Author: Lourdes Dev Team
Author URI: http://localhost/
License: GPL2
Text Domain: lourdes-core
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Salir si se accede directamente
}

// Cargar tipos de post personalizados
require_once plugin_dir_path( __FILE__ ) . 'includes/cpts.php';

// Cargar ajustes del plugin
require_once plugin_dir_path( __FILE__ ) . 'includes/settings.php';

// Cargar shortcodes personalizados
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes.php';

// Registrar ubicaciones de menú adicionales
add_action( 'init', 'lourdes_core_register_menus' );
function lourdes_core_register_menus() {
    register_nav_menus( array(
        'lourdes-top-menu' => __( 'Menú de Cabecera Superior', 'lourdes-core' ),
    ) );
}

/**
 * Forzar la activación del Top Header en Divi
 * Esto asegura que la barra superior se muestre siempre.
 */
add_filter( 'et_divi_show_top_header', '__return_true' );

/**
 * Crear automáticamente el menú superior institucional
 */
function lourdes_core_setup_default_menus() {
    $menu_name = 'Menu Superior Institucional';
    $menu_exists = wp_get_nav_menu_object( $menu_name );

    if ( ! $menu_exists ) {
        $menu_id = wp_create_nav_menu( $menu_name );

        // Enlaces por defecto
        wp_update_nav_menu_item( $menu_id, 0, array(
            'menu-item-title'   =>  __( 'Portal de Transparencia', 'lourdes-core' ),
            'menu-item-url'     => home_url( '/transparencia/' ),
            'menu-item-status'  => 'publish',
        ) );

        wp_update_nav_menu_item( $menu_id, 0, array(
            'menu-item-title'   =>  __( 'SIGAL', 'lourdes-core' ),
            'menu-item-url'     => 'https://sigal.eesppnsl.edu.pe',
            'menu-item-status'  => 'publish',
        ) );

        wp_update_nav_menu_item( $menu_id, 0, array(
            'menu-item-title'   =>  __( 'Correo Institucional', 'lourdes-core' ),
            'menu-item-url'     => 'https://mail.google.com/a/eesppnsl.edu.pe',
            'menu-item-status'  => 'publish',
        ) );

        // Asignar el menú a la ubicación de Divi (secondary-menu)
        $locations = get_theme_mod( 'nav_menu_locations' );
        $locations['secondary-menu'] = $menu_id;
        $locations['lourdes-top-menu'] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    }
}
add_action( 'admin_init', 'lourdes_core_setup_default_menus' );

// Encolar estilos y scripts globales
add_action( 'wp_enqueue_scripts', 'lourdes_core_enqueue_assets' );
function lourdes_core_enqueue_assets() {
    wp_enqueue_style( 'lourdes-styles', plugin_dir_url( __FILE__ ) . 'assets/css/lourdes-styles.css', array( 'divi-style' ), '1.0' );
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0' );

    wp_enqueue_script( 'lourdes-ajax-pagination', plugin_dir_url( __FILE__ ) . 'assets/js/lourdes-ajax-pagination.js', array( 'jquery' ), '1.0', true );
    wp_localize_script( 'lourdes-ajax-pagination', 'lourdes_ajax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ) );
}

/**
 * Inyectar CSS Crítico directamente en el Head
 * Esto sobresale por encima de la caché de Divi.
 */
add_action( 'wp_head', 'lourdes_core_inject_critical_css', 100 );
function lourdes_core_inject_critical_css() {
    ?>
    <style id="lourdes-critical-styles">
        /* OCULTAR PIE DE PÁGINA PREDETERMINADO DE DIVI */
        #main-footer { display: none !important; }

        /* Forzar Colores de Cabecera Superior */
        #top-header { 
            background-color: #0f172a !important; 
            display: block !important;
        }
        #top-header a, #top-header #et-info span { 
            color: #ffffff !important; 
            font-family: 'Inter', sans-serif !important; 
            font-weight: 500 !important;
        }
        
        /* Ajustes de Menú Principal Estilo Tailwind */
        #main-header { 
            background-color: #ffffff !important; 
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05) !important;
        }
        
        #top-menu li a { 
            font-family: 'Inter', sans-serif !important; 
            font-weight: 600 !important; 
            color: #1e293b !important;
            font-size: 15px !important;
            transition: all 0.3s ease !important;
        }
        
        #top-menu li a:hover, #top-menu li.current-menu-item > a { 
            color: #2563eb !important; 
        }

        /* Corregir Logo para que no se deforme */
        #logo {
            transition: all 0.3s ease !important;
            max-height: 80% !important;
        }
    </style>
    <?php
}

// Activar el plugin y vaciar reglas de reescritura
register_activation_hook( __FILE__, 'lourdes_core_activate' );
function lourdes_core_activate() {
    lourdes_core_register_cpts();
    flush_rewrite_rules();
}
