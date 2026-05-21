<?php
/**
 * Plugin Name: Lourdes Convocatorias
 * Plugin URI: https://eesppnsl.edu.pe
 * Description: Sistema seguro para gestionar convocatorias institucionales con estados y enlaces dinámicos, compatible con Divi.
 * Version: 1.0.0
 * Author: Gemini CLI
 * Author URI: https://eesppnsl.edu.pe
 * License: GPL2
 * Text Domain: lourdes-convocatorias
 */

// Prevenir el acceso directo al archivo por seguridad
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registro del Custom Post Type: Convocatoria
 */
function lourdes_register_convocatoria_cpt() {
    $labels = array(
        'name'                  => _x('Convocatorias', 'Post Type General Name', 'lourdes-convocatorias'),
        'singular_name'         => _x('Convocatoria', 'Post Type Singular Name', 'lourdes-convocatorias'),
        'menu_name'             => __('Convocatorias', 'lourdes-convocatorias'),
        'name_admin_bar'        => __('Convocatoria', 'lourdes-convocatorias'),
        'archives'              => __('Archivo de Convocatorias', 'lourdes-convocatorias'),
        'attributes'            => __('Atributos de Convocatoria', 'lourdes-convocatorias'),
        'parent_item_colon'     => __('Convocatoria Padre:', 'lourdes-convocatorias'),
        'all_items'             => __('Todas las Convocatorias', 'lourdes-convocatorias'),
        'add_new_item'          => __('Añadir Nueva Convocatoria', 'lourdes-convocatorias'),
        'add_new'               => __('Añadir Nueva', 'lourdes-convocatorias'),
        'new_item'              => __('Nueva Convocatoria', 'lourdes-convocatorias'),
        'edit_item'             => __('Editar Convocatoria', 'lourdes-convocatorias'),
        'update_item'           => __('Actualizar Convocatoria', 'lourdes-convocatorias'),
        'view_item'             => __('Ver Convocatoria', 'lourdes-convocatorias'),
        'view_items'            => __('Ver Convocatorias', 'lourdes-convocatorias'),
        'search_items'          => __('Buscar Convocatoria', 'lourdes-convocatorias'),
        'not_found'             => __('No se encontraron convocatorias', 'lourdes-convocatorias'),
        'not_found_in_trash'    => __('No se encontraron convocatorias en la papelera', 'lourdes-convocatorias'),
        'featured_image'        => __('Imagen Destacada', 'lourdes-convocatorias'),
        'set_featured_image'    => __('Asignar imagen destacada', 'lourdes-convocatorias'),
        'remove_featured_image' => __('Eliminar imagen destacada', 'lourdes-convocatorias'),
        'use_featured_image'    => __('Usar como imagen destacada', 'lourdes-convocatorias'),
        'insert_into_item'      => __('Insertar en convocatoria', 'lourdes-convocatorias'),
        'uploaded_to_this_item' => __('Subido a esta convocatoria', 'lourdes-convocatorias'),
        'items_list'            => __('Lista de convocatorias', 'lourdes-convocatorias'),
        'items_list_navigation' => __('Navegación de lista de convocatorias', 'lourdes-convocatorias'),
        'filter_items_list'     => __('Filtrar lista de convocatorias', 'lourdes-convocatorias'),
    );
    $args = array(
        'label'                 => __('Convocatoria', 'lourdes-convocatorias'),
        'description'           => __('Gestión de convocatorias institucionales', 'lourdes-convocatorias'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-clipboard',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true, // Habilita Gutenberg
    );
    register_post_type('convocatoria', $args);
}
add_action('init', 'lourdes_register_convocatoria_cpt', 0);

// Flush rewrite rules on activation
function lourdes_convocatorias_activation() {
    lourdes_register_convocatoria_cpt();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'lourdes_convocatorias_activation');

/**
 * Añadir Meta Boxes para Estado y Enlaces
 */
function lourdes_convocatorias_add_meta_boxes() {
    add_meta_box(
        'lourdes_convocatoria_meta',
        __('Opciones de la Convocatoria', 'lourdes-convocatorias'),
        'lourdes_convocatorias_meta_callback',
        'convocatoria',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'lourdes_convocatorias_add_meta_boxes');

/**
 * Callback para renderizar los Meta Boxes
 */
function lourdes_convocatorias_meta_callback($post) {
    // Añadir nonce para seguridad
    wp_nonce_field('lourdes_convocatoria_save_meta', 'lourdes_convocatoria_nonce');

    // Recuperar valores existentes
    $estado = get_post_meta($post->ID, '_lourdes_convocatoria_estado', true);
    $enlaces = get_post_meta($post->ID, '_lourdes_convocatoria_enlaces', true);
    if (!is_array($enlaces)) $enlaces = array();

    // Nuevos campos de Información General
    $entidad      = get_post_meta($post->ID, '_lourdes_convocatoria_entidad', true);
    $tipo_contrato = get_post_meta($post->ID, '_lourdes_convocatoria_tipo_contrato', true);
    $plazas       = get_post_meta($post->ID, '_lourdes_convocatoria_plazas', true);
    $lugar        = get_post_meta($post->ID, '_lourdes_convocatoria_lugar', true);
    $remuneracion = get_post_meta($post->ID, '_lourdes_convocatoria_remuneracion', true);

    // Campos de Resultados (Refactorizado a dinámico)
    $resultados_meta = get_post_meta($post->ID, '_lourdes_convocatoria_resultados_din', true);
    if (!is_array($resultados_meta)) $resultados_meta = array();

    // Campos de Cronograma
    $cronograma_meta = get_post_meta($post->ID, '_lourdes_convocatoria_cronograma_din', true);
    if (!is_array($cronograma_meta)) $cronograma_meta = array();

    ?>
    <style>
        .lourdes-meta-row { margin-bottom: 20px; }
        .lourdes-meta-row label { display: block; font-weight: bold; margin-bottom: 5px; }
        .lourdes-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f0f0f1; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .lourdes-dynamic-item { background: #f9f9f9; border: 1px solid #ddd; padding: 12px; margin-bottom: 10px; position: relative; border-radius: 4px; }
        .lourdes-dynamic-item .remove-item { position: absolute; top: 10px; right: 10px; color: #a00; cursor: pointer; text-decoration: none; }
        .lourdes-dynamic-item input, .lourdes-dynamic-item select { width: 100%; margin-bottom: 8px; }
        .lourdes-res-container-box { background: #eef2ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c7d2fe; }
        .lourdes-crono-container-box { background: #fff7ed; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #fed7aa; }
    </style>

    <div class="lourdes-meta-row">
        <label for="lourdes_estado"><?php _e('Estado General de la Convocatoria:', 'lourdes-convocatorias'); ?></label>
        <select name="lourdes_estado" id="lourdes_estado" class="widefat">
            <option value="vigente" <?php selected($estado, 'vigente'); ?>>🟢 <?php _e('Vigente', 'lourdes-convocatorias'); ?></option>
            <option value="proceso" <?php selected($estado, 'proceso'); ?>>🟡 <?php _e('En Proceso', 'lourdes-convocatorias'); ?></option>
            <option value="terminado" <?php selected($estado, 'terminado'); ?>>🔴 <?php _e('Terminado', 'lourdes-convocatorias'); ?></option>
        </select>
    </div>

    <div class="lourdes-meta-row">
        <label><?php _e('Resultados del Proceso (Dinámico):', 'lourdes-convocatorias'); ?></label>
        <div class="lourdes-res-container-box">
            <div id="lourdes-resultados-container">
                <?php foreach ($resultados_meta as $index => $res) : ?>
                    <div class="lourdes-dynamic-item">
                        <a href="#" class="remove-item dashicons dashicons-no-alt" title="Eliminar"></a>
                        <input type="text" name="lourdes_resultados[<?php echo $index; ?>][titulo]" value="<?php echo esc_attr($res['titulo']); ?>" placeholder="<?php _e('Título (ej: Resultados Preliminares)', 'lourdes-convocatorias'); ?>" />
                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
                            <input type="url" name="lourdes_resultados[<?php echo $index; ?>][url]" value="<?php echo esc_url($res['url']); ?>" placeholder="URL del PDF (https://...)" />
                            <select name="lourdes_resultados[<?php echo $index; ?>][estado]">
                                <option value="pendiente" <?php selected($res['estado'], 'pendiente'); ?>><?php _e('Pendiente', 'lourdes-convocatorias'); ?></option>
                                <option value="publicado" <?php selected($res['estado'], 'publicado'); ?>><?php _e('Publicado', 'lourdes-convocatorias'); ?></option>
                            </select>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="add-resultado" class="button button-primary"><?php _e('+ Añadir Etapa de Resultado', 'lourdes-convocatorias'); ?></button>
        </div>
    </div>

    <div class="lourdes-meta-row">
        <label><?php _e('Cronograma y Etapas (Dinámico):', 'lourdes-convocatorias'); ?></label>
        <div class="lourdes-crono-container-box">
            <div id="lourdes-cronograma-container">
                <?php foreach ($cronograma_meta as $index => $item) : ?>
                    <div class="lourdes-dynamic-item">
                        <a href="#" class="remove-item dashicons dashicons-no-alt" title="Eliminar"></a>
                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
                            <input type="text" name="lourdes_cronograma[<?php echo $index; ?>][etapa]" value="<?php echo esc_attr($item['etapa']); ?>" placeholder="<?php _e('Etapa (ej: Evaluación Curricular)', 'lourdes-convocatorias'); ?>" />
                            <input type="text" name="lourdes_cronograma[<?php echo $index; ?>][fecha]" value="<?php echo esc_attr($item['fecha']); ?>" placeholder="<?php _e('Fecha (ej: 25 de Mayo)', 'lourdes-convocatorias'); ?>" />
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="add-cronograma" class="button button-primary"><?php _e('+ Añadir Etapa al Cronograma', 'lourdes-convocatorias'); ?></button>
        </div>
    </div>

    <div class="lourdes-meta-row">
        <label><?php _e('Información General de la Convocatoria:', 'lourdes-convocatorias'); ?></label>
        <div class="lourdes-meta-grid">
            <div>
                <label><?php _e('Entidad Contratante:', 'lourdes-convocatorias'); ?></label>
                <input type="text" name="lourdes_entidad" value="<?php echo esc_attr($entidad); ?>" class="widefat" placeholder="Ej: IESPP Lourdes" />
            </div>
            <div>
                <label><?php _e('Tipo de Contrato:', 'lourdes-convocatorias'); ?></label>
                <input type="text" name="lourdes_tipo_contrato" value="<?php echo esc_attr($tipo_contrato); ?>" class="widefat" placeholder="Ej: CAS" />
            </div>
            <div>
                <label><?php _e('Cantidad de Plazas:', 'lourdes-convocatorias'); ?></label>
                <input type="text" name="lourdes_plazas" value="<?php echo esc_attr($plazas); ?>" class="widefat" placeholder="Ej: 04 posiciones" />
            </div>
            <div>
                <label><?php _e('Lugar de prestación:', 'lourdes-convocatorias'); ?></label>
                <input type="text" name="lourdes_lugar" value="<?php echo esc_attr($lugar); ?>" class="widefat" placeholder="Ej: Sede Central" />
            </div>
            <div style="grid-column: span 2;">
                <label><?php _e('Remuneración:', 'lourdes-convocatorias'); ?></label>
                <input type="text" name="lourdes_remuneracion" value="<?php echo esc_attr($remuneracion); ?>" class="widefat" placeholder="Ej: Según detalle en las Bases Adjuntas" />
            </div>
        </div>
    </div>

    <div class="lourdes-meta-row">
        <label><?php _e('Enlaces de Documentos:', 'lourdes-convocatorias'); ?></label>
        <div id="lourdes-enlaces-container">
            <?php foreach ($enlaces as $index => $enlace) : ?>
                <div class="lourdes-enlace-item">
                    <a href="#" class="remove-enlace dashicons dashicons-no-alt" title="Eliminar"></a>
                    <input type="text" name="lourdes_enlaces[<?php echo $index; ?>][titulo]" value="<?php echo esc_attr($enlace['titulo']); ?>" placeholder="<?php _e('Título del documento (ej: Bases)', 'lourdes-convocatorias'); ?>" />
                    <input type="url" name="lourdes_enlaces[<?php echo $index; ?>][url]" value="<?php echo esc_url($enlace['url']); ?>" placeholder="<?php _e('URL del documento', 'lourdes-convocatorias'); ?>" />
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="add-enlace" class="button button-secondary"><?php _e('+ Añadir Documento', 'lourdes-convocatorias'); ?></button>
    </div>

    <script>
    jQuery(document).ready(function($) {
        var container = $('#lourdes-enlaces-container');
        var index = <?php echo count($enlaces); ?>;

        $('#add-enlace').click(function(e) {
            e.preventDefault();
            var html = '<div class="lourdes-enlace-item">' +
                       '<a href="#" class="remove-enlace dashicons dashicons-no-alt" title="Eliminar"></a>' +
                       '<input type="text" name="lourdes_enlaces[' + index + '][titulo]" placeholder="<?php _e('Título del documento', 'lourdes-convocatorias'); ?>" />' +
                       '<input type="url" name="lourdes_enlaces[' + index + '][url]" placeholder="https://" />' +
                       '</div>';
            container.append(html);
            index++;
        });

        container.on('click', '.remove-enlace', function(e) {
            e.preventDefault();
            $(this).parent().remove();
        });

        // Script para Resultados Dinámicos
        var resContainer = $('#lourdes-resultados-container');
        var resIndex = <?php echo count($resultados_meta); ?>;

        $('#add-resultado').click(function(e) {
            e.preventDefault();
            var html = '<div class="lourdes-dynamic-item">' +
                       '<a href="#" class="remove-item dashicons dashicons-no-alt" title="Eliminar"></a>' +
                       '<input type="text" name="lourdes_resultados[' + resIndex + '][titulo]" placeholder="<?php _e('Título de la etapa', 'lourdes-convocatorias'); ?>" />' +
                       '<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">' +
                       '<input type="url" name="lourdes_resultados[' + resIndex + '][url]" placeholder="https://" />' +
                       '<select name="lourdes_resultados[' + resIndex + '][estado]">' +
                       '<option value="pendiente">Pendiente</option>' +
                       '<option value="publicado">Publicado</option>' +
                       '</select>' +
                       '</div>' +
                       '</div>';
            resContainer.append(html);
            resIndex++;
        });

        resContainer.on('click', '.remove-item', function(e) {
            e.preventDefault();
            $(this).parent().remove();
        });

        // Script para Cronograma Dinámico
        var cronoContainer = $('#lourdes-cronograma-container');
        var cronoIndex = <?php echo count($cronograma_meta); ?>;

        $('#add-cronograma').click(function(e) {
            e.preventDefault();
            var html = '<div class="lourdes-dynamic-item">' +
                       '<a href="#" class="remove-item dashicons dashicons-no-alt" title="Eliminar"></a>' +
                       '<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">' +
                       '<input type="text" name="lourdes_cronograma[' + cronoIndex + '][etapa]" placeholder="<?php _e('Etapa', 'lourdes-convocatorias'); ?>" />' +
                       '<input type="text" name="lourdes_cronograma[' + cronoIndex + '][fecha]" placeholder="<?php _e('Fecha', 'lourdes-convocatorias'); ?>" />' +
                       '</div>' +
                       '</div>';
            cronoContainer.append(html);
            cronoIndex++;
        });
    });
    </script>
    <?php
}

/**
 * Guardar los metadatos de forma segura
 */
function lourdes_convocatorias_save_meta($post_id) {
    // ... (nonce and permission checks ...)
    if (!isset($_POST['lourdes_convocatoria_nonce']) || !wp_verify_nonce($_POST['lourdes_convocatoria_nonce'], 'lourdes_convocatoria_save_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Guardar Estado
    if (isset($_POST['lourdes_estado'])) update_post_meta($post_id, '_lourdes_convocatoria_estado', sanitize_text_field($_POST['lourdes_estado']));

    // Guardar Campos CAS
    if (isset($_POST['lourdes_entidad'])) update_post_meta($post_id, '_lourdes_convocatoria_entidad', sanitize_text_field($_POST['lourdes_entidad']));
    if (isset($_POST['lourdes_tipo_contrato'])) update_post_meta($post_id, '_lourdes_convocatoria_tipo_contrato', sanitize_text_field($_POST['lourdes_tipo_contrato']));
    if (isset($_POST['lourdes_plazas'])) update_post_meta($post_id, '_lourdes_convocatoria_plazas', sanitize_text_field($_POST['lourdes_plazas']));
    if (isset($_POST['lourdes_lugar'])) update_post_meta($post_id, '_lourdes_convocatoria_lugar', sanitize_text_field($_POST['lourdes_lugar']));
    if (isset($_POST['lourdes_remuneracion'])) update_post_meta($post_id, '_lourdes_convocatoria_remuneracion', sanitize_text_field($_POST['lourdes_remuneracion']));

    // Guardar Resultados (Dinámico)
    if (isset($_POST['lourdes_resultados']) && is_array($_POST['lourdes_resultados'])) {
        $resultados_sanitizados = array();
        foreach ($_POST['lourdes_resultados'] as $res) {
            if (!empty($res['titulo'])) {
                $resultados_sanitizados[] = array(
                    'titulo' => sanitize_text_field($res['titulo']),
                    'url'    => esc_url_raw($res['url']),
                    'estado' => sanitize_text_field($res['estado'])
                );
            }
        }
        update_post_meta($post_id, '_lourdes_convocatoria_resultados_din', $resultados_sanitizados);
    } else {
        delete_post_meta($post_id, '_lourdes_convocatoria_resultados_din');
    }

    // Guardar Cronograma (Dinámico)
    if (isset($_POST['lourdes_cronograma']) && is_array($_POST['lourdes_cronograma'])) {
        $cronograma_sanitizado = array();
        foreach ($_POST['lourdes_cronograma'] as $item) {
            if (!empty($item['etapa'])) {
                $cronograma_sanitizado[] = array(
                    'etapa' => sanitize_text_field($item['etapa']),
                    'fecha' => sanitize_text_field($item['fecha'])
                );
            }
        }
        update_post_meta($post_id, '_lourdes_convocatoria_cronograma_din', $cronograma_sanitizado);
    } else {
        delete_post_meta($post_id, '_lourdes_convocatoria_cronograma_din');
    }

    // Guardar Enlaces de Documentos
    if (isset($_POST['lourdes_enlaces']) && is_array($_POST['lourdes_enlaces'])) {
        $enlaces_sanitizados = array();
        foreach ($_POST['lourdes_enlaces'] as $enlace) {
            if (!empty($enlace['titulo']) && !empty($enlace['url'])) {
                $enlaces_sanitizados[] = array(
                    'titulo' => sanitize_text_field($enlace['titulo']),
                    'url'    => esc_url_raw($enlace['url'])
                );
            }
        }
        update_post_meta($post_id, '_lourdes_convocatoria_enlaces', $enlaces_sanitizados);
    } else {
        delete_post_meta($post_id, '_lourdes_convocatoria_enlaces');
    }
}
add_action('save_post', 'lourdes_convocatorias_save_meta');

/**
 * Shortcode: [convocatoria_cronograma]
 */
function lourdes_convocatoria_cronograma_shortcode() {
    $post_id = get_the_ID();
    $cronograma = get_post_meta($post_id, '_lourdes_convocatoria_cronograma_din', true);

    if (empty($cronograma) || !is_array($cronograma)) return '';

    $output = '<div class="lourdes-ficha-card lourdes-crono-card">';
    $output .= '<h2 class="lourdes-ficha-card-title">';
    $output .= '<svg class="lourdes-ficha-icon" style="color:#dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>';
    $output .= __('Cronograma y Etapas', 'lourdes-convocatorias');
    $output .= '</h2>';
    
    $output .= '<div class="lourdes-crono-table-wrapper">';
    $output .= '<table class="lourdes-crono-table">';
    $output .= '<thead><tr><th>' . __('Etapas del Proceso', 'lourdes-convocatorias') . '</th><th>' . __('Fechas', 'lourdes-convocatorias') . '</th></tr></thead>';
    $output .= '<tbody>';
    
    $total = count($cronograma);
    foreach ($cronograma as $index => $item) {
        $is_last = ($index === $total - 1);
        $tr_class = $is_last ? 'lourdes-crono-last-row' : '';
        
        $output .= '<tr class="' . $tr_class . '">';
        $output .= '<td>' . esc_html($item['etapa']) . '</td>';
        $output .= '<td>' . esc_html($item['fecha']) . '</td>';
        $output .= '</tr>';
    }
    
    $output .= '</tbody></table></div></div>';

    return $output;
}
add_shortcode('convocatoria_cronograma', 'lourdes_convocatoria_cronograma_shortcode');

/**
 * Shortcode: [convocatorias]
 * Muestra el listado de convocatorias con estilo de Comunicados
 */
function lourdes_convocatorias_shortcode($atts) {
    $atts = shortcode_atts(array(
        'estado' => '', // vigente, proceso, terminado
        'limit'  => 5,
    ), $atts);

    $args = array(
        'post_type'      => 'convocatoria',
        'posts_per_page' => $atts['limit'],
        'post_status'    => 'publish',
    );

    if (!empty($atts['estado'])) {
        $args['meta_query'] = array(
            array(
                'key'   => '_lourdes_convocatoria_estado',
                'value' => $atts['estado'],
            ),
        );
    }

    $query = new WP_Query($args);
    $output = '<div class="lourdes-comunicados-container">';
    $output .= '<ul class="lourdes-comunicados-list">';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $estado = get_post_meta($post_id, '_lourdes_convocatoria_estado', true);
            
            // Lógica de fecha
            $dia = get_the_date('d');
            $mes = get_the_date('M');
            
            // Lógica de Estado (Etiqueta)
            $estado_label = '';
            $estado_bg = '';
            $estado_text = '';

            switch ($estado) {
                case 'vigente':
                    $estado_label = __('NUEVA CONVOCATORIA', 'lourdes-convocatorias');
                    $estado_bg = '#dcfce7'; // green-100
                    $estado_text = '#166534'; // green-800
                    break;
                case 'proceso':
                    $estado_label = __('EN PROCESO', 'lourdes-convocatorias');
                    $estado_bg = '#fef9c3'; // yellow-100
                    $estado_text = '#854d0e'; // yellow-800
                    break;
                case 'terminado':
                    $estado_label = __('CONCLUIDO', 'lourdes-convocatorias');
                    $estado_bg = '#fee2e2'; // red-100
                    $estado_text = '#991b1b'; // red-800
                    break;
            }

            $output .= '<li class="lourdes-comunicado-item">';
            
            // Bloque de fecha/tipo a la izquierda
            $output .= '<div class="lourdes-comunicado-date">';
            $output .= '<span class="lourdes-date-day">' . esc_html($dia) . '</span>';
            $output .= '<span class="lourdes-date-month">' . esc_html($mes) . '</span>';
            $output .= '</div>';

            // Contenido a la derecha
            $output .= '<div class="lourdes-comunicado-content">';
            if ($estado_label) {
                $output .= '<span class="lourdes-comunicado-tag" style="background-color:' . $estado_bg . '; color:' . $estado_text . ';">' . esc_html($estado_label) . '</span>';
            }
            $output .= '<h4 class="lourdes-comunicado-title"><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h4>';
            $output .= '<p class="lourdes-comunicado-excerpt">' . wp_trim_words(get_the_excerpt(), 15) . '</p>';
            $output .= '</div>';
            
            $output .= '</li>';
        }
        wp_reset_postdata();
    } else {
        $output .= '<p>' . __('No hay convocatorias disponibles.', 'lourdes-convocatorias') . '</p>';
    }

    $output .= '</ul>';
    
    // Botón Ver más (opcional, configurado para redirigir a la página de convocatorias)
    $output .= '<a href="' . esc_url(get_post_type_archive_link('convocatoria')) . '" class="lourdes-comunicados-more">' . __('Ver historial de convocatorias', 'lourdes-convocatorias') . '</a>';
    
    $output .= '</div>';
    return $output;
}
add_shortcode('convocatorias', 'lourdes_convocatorias_shortcode');

/**
 * Shortcode: [convocatoria_estado]
 * Muestra el estado de la convocatoria actual (Para Divi)
 */
function lourdes_convocatoria_estado_shortcode() {
    $post_id = get_the_ID();
    if (get_post_type($post_id) !== 'convocatoria') return '';

    $estado = get_post_meta($post_id, '_lourdes_convocatoria_estado', true);
    $label = '';
    $color = '';

    switch ($estado) {
        case 'vigente': $label = __('Vigente', 'lourdes-convocatorias'); $color = '#28a745'; break;
        case 'proceso': $label = __('En Proceso', 'lourdes-convocatorias'); $color = '#ffc107'; break;
        case 'terminado': $label = __('Terminado', 'lourdes-convocatorias'); $color = '#dc3545'; break;
    }

    return '<span style="display:inline-block; padding: 5px 15px; border-radius: 20px; background:'.esc_attr($color).'; color:#fff; font-weight:bold; font-size:14px;">'.esc_html($label).'</span>';
}
add_shortcode('convocatoria_estado', 'lourdes_convocatoria_estado_shortcode');

/**
 * Shortcode: [convocatoria_enlaces]
 * Muestra los enlaces de documentos con formato de panel institucional azul
 */
function lourdes_convocatoria_enlaces_shortcode() {
    $post_id = get_the_ID();
    $enlaces = get_post_meta($post_id, '_lourdes_convocatoria_enlaces', true);

    if (empty($enlaces) || !is_array($enlaces)) return '';

    $output = '<div class="lourdes-docs-panel">';
    $output .= '<h3 class="lourdes-docs-panel-title">' . __('Documentos del Proceso', 'lourdes-convocatorias') . '</h3>';
    
    $output .= '<div class="lourdes-docs-list">';
    $counter = 1;
    foreach ($enlaces as $enlace) {
        $output .= '<a href="' . esc_url($enlace['url']) . '" class="lourdes-doc-button" target="_blank" rel="noopener noreferrer">';
        $output .= '<span class="lourdes-doc-text">' . $counter . '. ' . esc_html($enlace['titulo']) . '</span>';
        $output .= '<svg class="lourdes-doc-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>';
        $output .= '</a>';
        $counter++;
    }
    $output .= '</div>';

    // Sección de postulación (Mensaje de cronograma)
    $output .= '<div class="lourdes-docs-footer">';
    $output .= '<p class="lourdes-docs-footer-text" style="color:rgba(255,255,255,0.7); font-weight:400; font-size:11px; margin:0; text-align:center;">' . __('Presente sus documentos según cronograma establecido', 'lourdes-convocatorias') . '</p>';
    $output .= '</div>';

    $output .= '</div>';

    return $output;
}
add_shortcode('convocatoria_enlaces', 'lourdes_convocatoria_enlaces_shortcode');

/**
 * Shortcode: [convocatoria_ficha]
 * Muestra la ficha de información general con formato de tarjeta
 */
function lourdes_convocatoria_ficha_shortcode() {
    $post_id = get_the_ID();
    
    $datos = array(
        'Entidad Contratante' => get_post_meta($post_id, '_lourdes_convocatoria_entidad', true),
        'Tipo de Contrato'    => get_post_meta($post_id, '_lourdes_convocatoria_tipo_contrato', true),
        'Cantidad de Plazas'  => get_post_meta($post_id, '_lourdes_convocatoria_plazas', true),
        'Lugar de prestación del servicio' => get_post_meta($post_id, '_lourdes_convocatoria_lugar', true),
        'Remuneración'        => get_post_meta($post_id, '_lourdes_convocatoria_remuneracion', true),
    );

    // Si todos están vacíos, no mostrar nada
    if (empty(array_filter($datos))) return '';

    $output = '<div class="lourdes-ficha-card">';
    $output .= '<h2 class="lourdes-ficha-card-title">';
    $output .= '<svg class="lourdes-ficha-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
    $output .= __('Información General', 'lourdes-convocatorias');
    $output .= '</h2>';
    $output .= '<ul class="lourdes-ficha-list">';
    
    foreach ($datos as $label => $valor) {
        if (!empty($valor)) {
            $output .= '<li><strong>' . esc_html($label) . ':</strong> ' . esc_html($valor) . '</li>';
        }
    }
    
    $output .= '</ul>';
    $output .= '</div>';

    return $output;
}
add_shortcode('convocatoria_ficha', 'lourdes_convocatoria_ficha_shortcode');

/**
 * Shortcode: [convocatoria_resultados]
 * Muestra la tarjeta de resultados con estados dinámicos e ilimitados
 */
function lourdes_convocatoria_resultados_shortcode() {
    $post_id = get_the_ID();
    $resultados_meta = get_post_meta($post_id, '_lourdes_convocatoria_resultados_din', true);

    if (empty($resultados_meta) || !is_array($resultados_meta)) return '';

    $output = '<div class="lourdes-ficha-card lourdes-res-card">';
    $output .= '<h3 class="lourdes-ficha-card-title">' . __('Resultados', 'lourdes-convocatorias') . '</h3>';
    $output .= '<div class="lourdes-res-list">';

    foreach ($resultados_meta as $data) {
        $is_pendiente = ($data['estado'] !== 'publicado' || empty($data['url']));
        $class = $is_pendiente ? 'lourdes-res-item-pending' : 'lourdes-res-item-published';
        $tag_text = $is_pendiente ? __('Pendiente', 'lourdes-convocatorias') : __('Descargar', 'lourdes-convocatorias');
        
        if (!$is_pendiente) {
            $output .= '<a href="' . esc_url($data['url']) . '" class="lourdes-res-item ' . $class . '" target="_blank">';
        } else {
            $output .= '<div class="lourdes-res-item ' . $class . '">';
        }

        // Icono: Candado para pendiente, Descarga para publicado
        if ($is_pendiente) {
            $output .= '<svg class="lourdes-res-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>';
        } else {
            $output .= '<svg class="lourdes-res-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>';
        }

        $output .= '<span class="lourdes-res-label">' . esc_html($data['titulo']) . '</span>';
        $output .= '<span class="lourdes-res-status-tag">' . esc_html($tag_text) . '</span>';

        if (!$is_pendiente) {
            $output .= '</a>';
        } else {
            $output .= '</div>';
        }
    }

    $output .= '</div>';
    $output .= '</div>';

    return $output;
}
add_shortcode('convocatoria_resultados', 'lourdes_convocatoria_resultados_shortcode');

/**
 * Añadir estilos básicos para el frontend
 */
function lourdes_convocatorias_styles() {
    ?>
    <style>
        /* Listado General */
        .lourdes-comunicados-container {
            background: #fff;
            border-radius: 0.75rem;
            padding: 1.5rem;
            border: 1px solid #f3f4f6;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .lourdes-comunicados-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .lourdes-comunicado-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .lourdes-comunicado-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }
        .lourdes-comunicado-date {
            background-color: #020617;
            color: #fff;
            border-radius: 0.5rem;
            text-align: center;
            padding: 0.4rem 0.6rem;
            flex-shrink: 0;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            line-height: 1;
        }
        .lourdes-date-day {
            display: block;
            font-size: 1.05rem;
            font-weight: 700;
        }
        .lourdes-date-month {
            display: block;
            font-size: 0.6rem;
            text-transform: uppercase;
            margin-top: 0.2rem;
            opacity: 0.9;
        }
        .lourdes-comunicado-item:has(.lourdes-comunicado-tag[style*="background-color:#dcfce7"]) .lourdes-comunicado-date {
            background-color: #dc2626;
        }
        .lourdes-comunicado-tag {
            display: inline-block;
            padding: 0.1rem 0.3rem;
            font-size: 8px;
            font-weight: 700;
            border-radius: 0.2rem;
            margin-bottom: 0.15rem;
            text-transform: uppercase;
            line-height: 1;
        }
        .lourdes-comunicado-title {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.1;
        }
        .lourdes-comunicado-title a {
            color: inherit;
            text-decoration: none;
        }
        .lourdes-comunicado-excerpt {
            margin: 0;
            font-size: 0.75rem;
            color: #4b5563;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.3;
        }
        .lourdes-comunicados-more {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            width: 100%;
            padding: 0.5rem 0;
            border: 2px solid #020617;
            color: #020617;
            border-radius: 0.25rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.8rem;
        }

        /* FICHA Y ENLACES (Reducción de 2px) */
        .lourdes-ficha-card {
            background: #fff;
            border-radius: 0.75rem;
            padding: 1.25rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        .lourdes-ficha-card-title {
            font-size: 0.95rem !important;
            font-weight: 700 !important;
            color: #020617 !important;
            margin-bottom: 0.75rem !important;
            display: flex !important;
            align-items: center !important;
            border-bottom: 1px solid #f3f4f6 !important;
            padding-bottom: 0.4rem !important;
            margin-top: 0 !important;
        }
        .lourdes-ficha-icon {
            width: 1.1rem;
            height: 1.1rem;
            margin-right: 0.5rem;
            color: #facc15;
        }
        .lourdes-ficha-list {
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .lourdes-ficha-list li {
            font-size: 0.75rem !important;
            color: #374151 !important;
            line-height: 1.4 !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .lourdes-ficha-list li strong {
            color: #111827;
            font-weight: 700;
        }

        .lourdes-docs-panel {
            background-color: #020617;
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        .lourdes-docs-panel-title {
            font-size: 0.95rem !important;
            font-weight: 700 !important;
            color: #facc15 !important;
            margin-bottom: 0.75rem !important;
            border-bottom: 1px solid rgba(255,255,255,0.1) !important;
            padding-bottom: 0.4rem !important;
            margin-top: 0 !important;
        }
        .lourdes-docs-list {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }
        .lourdes-doc-button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.375rem;
            color: #fff !important;
            text-decoration: none !important;
            transition: all 0.2s;
        }
        .lourdes-doc-button:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        .lourdes-doc-text {
            font-size: 0.75rem;
            font-weight: 600;
        }
        .lourdes-doc-icon {
            width: 0.9rem;
            height: 0.9rem;
            opacity: 0.8;
        }
        .lourdes-docs-footer {
            margin-top: 1.25rem;
            padding-top: 0.75rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        .lourdes-docs-footer-text {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.7);
            margin-bottom: 0.5rem;
            font-weight: 400;
        }

        /* Estilos Cronograma */
        .lourdes-crono-table-wrapper {
            overflow-x: auto;
            margin-top: 0.5rem;
        }
        .lourdes-crono-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem; /* Reducido a ~12px */
            color: #4b5563;
        }
        .lourdes-crono-table thead th {
            background-color: #f9fafb;
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.65rem;
            text-transform: uppercase;
            color: #374151;
            font-weight: 700;
            border-bottom: 1px solid #e5e7eb;
        }
        .lourdes-crono-table tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f3f4f6;
            font-weight: 400; /* Asegurar que no haya negrita */
        }
        .lourdes-crono-table tbody tr:last-child td {
            border-bottom: 0;
        }
        .lourdes-crono-last-row {
            background-color: #f9fafb;
            border-top: 2px solid #020617; /* Slate 950 */
        }
        .lourdes-crono-last-row td {
            font-weight: 700;
            color: #111827;
        }

        /* Estilos Resultados */
        .lourdes-res-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .lourdes-res-item {
            display: flex;
            align-items: center;
            padding: 0.6rem;
            border-radius: 0.375rem;
            border: 1px solid #f3f4f6;
            text-decoration: none !important;
            transition: all 0.2s;
        }
        .lourdes-res-item-pending {
            background-color: #f9fafb;
            opacity: 0.5;
            cursor: not-allowed;
        }
        .lourdes-res-item-published {
            background-color: #fff;
            border-color: #e5e7eb;
            color: #1f2937 !important;
        }
        .lourdes-res-item-published:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
        }
        .lourdes-res-icon {
            width: 1rem;
            height: 1rem;
            margin-right: 0.5rem;
            flex-shrink: 0;
            color: #94a3b8;
        }
        .lourdes-res-item-published .lourdes-res-icon {
            color: #020617;
        }
        .lourdes-res-label {
            font-size: 0.75rem;
            font-weight: 600;
            flex-grow: 1;
        }
        .lourdes-res-status-tag {
            font-size: 9px;
            background-color: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
        }
        .lourdes-res-item-published .lourdes-res-status-tag {
            background-color: #dcfce7;
            color: #166534;
        }
    </style>
    <?php
}
add_action('wp_head', 'lourdes_convocatorias_styles');
