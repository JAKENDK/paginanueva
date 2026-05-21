<?php

/**
 * Función auxiliar para renderizar el contenido de las noticias (Grid + Paginación)
 */
function lourdes_render_noticias_content( $atts, $paged = 1, $container_id = '' ) {
    $cols = intval($atts['columns']) > 0 ? intval($atts['columns']) : 3;
    $rows = intval($atts['rows']) > 0 ? intval($atts['rows']) : 2;
    $posts_per_page = ( isset( $atts['posts'] ) && intval( $atts['posts'] ) > 0 ) ? intval( $atts['posts'] ) : ($cols * $rows);
    
    $query_args = array(
        'post_type'      => 'post', // Cambiado a entradas generales
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'post_status'    => 'publish',
    );

    $categories_slugs = array();
    if ( ! empty( $atts['category'] ) ) {
        $categories_slugs = explode(',', $atts['category']);
    } elseif ( ! empty( $atts['profile_categories'] ) ) {
        $categories_slugs = (array) $atts['profile_categories'];
    }

    if ( ! empty( $categories_slugs ) ) {
        $query_args['tax_query'] = array(
            array( 'taxonomy' => 'category', 'field' => 'slug', 'terms' => $categories_slugs, 'operator' => 'IN' ),
        );
    }

    $query = new WP_Query( $query_args );
    $output = '';

    if ( ! $query->have_posts() ) {
        return '<p style="text-align:center; color:#64748b; padding: 40px 0;">No hay publicaciones actualmente.</p>';
    }

    // Grid principal forzado
    $output .= '<div class="lourdes-main-grid" style="display: grid !important; grid-template-columns: repeat(' . $cols . ', 1fr) !important; gap: 20px !important; align-items: stretch !important; width: 100% !important;">';

    while ( $query->have_posts() ) {
        $query->the_post();
        $thumbnail = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' ) ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80';
        
        // Obtener categorías estándar de WordPress
        $categories = get_the_category( get_the_ID() );
        $badge_text = ($categories) ? $categories[0]->name : 'General';
        
        // Lógica de color basada en el nombre de la categoría o un valor por defecto
        $badge_bg = ( stripos($badge_text, 'urgente') !== false ) ? '#ef4444' : '#2563eb';

        $output .= '<div class="comunicado-card" style="display: flex !important; flex-direction: column !important; height: 100% !important; background: #fff !important; border-radius: 16px !important; border: 1px solid #f1f5f9 !important; overflow: hidden !important; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05) !important; box-sizing: border-box !important; min-width: 0 !important;">';
        
        // Imagen
        $img_height = ($atts['layout'] === 'board') ? '170px' : '140px';
        $output .= '<div style="height: ' . $img_height . '; min-height: ' . $img_height . '; overflow: hidden; position: relative; width: 100%; box-sizing: border-box !important;">';
        $output .= '<img src="' . esc_url($thumbnail) . '" style="width: 100%; height: 100%; object-fit: cover; display: block;">';
        $output .= '</div>';

        // Cuerpo compacto
        $output .= '<div class="card-content-body" style="padding: 10px 12px !important; flex-grow: 1 !important; display: flex !important; flex-direction: column !important; box-sizing: border-box !important; min-width: 0 !important; width: 100% !important;">';
        $output .= '<div class="card-text-area" style="flex-grow: 1 !important; margin-bottom: 8px !important; min-width: 0 !important; width: 100% !important; box-sizing: border-box !important;">';
        
        if ( $atts['show_category'] === 'yes' || $atts['show_date'] === 'yes' ) {
            $output .= '<div class="card-meta-wrapper" style="display: flex !important; align-items: center !important; gap: 8px !important; margin-bottom: 8px !important; flex-wrap: nowrap !important; overflow: hidden !important;">';
            
            if ( $atts['show_category'] === 'yes' ) {
                $output .= '<span style="background: ' . $badge_bg . '; color: #fff; font-size: 9px; font-weight: 800; padding: 3px 8px; border-radius: 5px; text-transform: uppercase; font-family: ' . esc_attr($atts['font_family']) . ', sans-serif; line-height: 1 !important; display: inline-block !important; white-space: nowrap !important; flex-shrink: 0 !important;">' . esc_html($badge_text) . '</span>';
            }

            if ( $atts['show_date'] === 'yes' ) {
                $icon = ($atts['layout'] === 'board') ? 'far fa-clock' : 'far fa-calendar-alt';
                $date_text = get_the_time(get_option('date_format'));
                $output .= '<span class="card-meta" style="color: #94a3b8 !important; font-size: ' . esc_attr($atts['meta_size']) . 'px !important; text-transform: uppercase !important; font-weight: 600; font-family: ' . esc_attr($atts['font_family']) . ', sans-serif; display: inline-flex !important; align-items: center !important; line-height: 1 !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important;"><i class="' . $icon . '" style="color: #2563eb !important; margin-right: 4px !important;"></i> ' . $date_text . '</span>';
            }
            $output .= '</div>';
        }

        $title_tag = ($atts['layout'] === 'board') ? 'h4' : 'h3';
        $output .= '<' . $title_tag . ' class="card-title" style="font-size: ' . esc_attr($atts['title_size']) . 'px !important; font-weight: 700 !important; color: #0f172a !important; line-height: 1.25 !important; margin: 0 0 4px 0 !important; font-family: ' . esc_attr($atts['font_family']) . ', sans-serif !important; overflow-wrap: break-word !important; word-wrap: break-word !important; width: 100% !important;">' . get_the_title() . '</' . $title_tag . '>';

        if ( $atts['show_excerpt'] === 'yes' ) {
            $excerpt_length = ($atts['layout'] === 'board') ? 25 : 20;
            $output .= '<p class="card-excerpt" style="color: #475569 !important; font-size: ' . esc_attr($atts['excerpt_size']) . 'px !important; line-height: 1.5 !important; margin: 0 !important; overflow: hidden !important; display: -webkit-box !important; -webkit-line-clamp: 2 !important; -webkit-box-orient: vertical !important; overflow-wrap: break-word !important; word-wrap: break-word !important; width: 100% !important; font-family: ' . esc_attr($atts['font_family']) . ', sans-serif !important;">' . wp_trim_words(get_the_excerpt(), $excerpt_length) . '</p>';
        }
        $output .= '</div>';

        // Botón
        if ( $atts['layout'] === 'board' ) {
            $output .= '<a href="' . get_permalink() . '" class="card-btn" style="background: ' . esc_attr($atts['button_bg']) . ' !important; color: #fff !important; padding: 7px 0 !important; border-radius: 8px !important; font-size: ' . esc_attr($atts['button_size']) . 'px !important; font-weight: 700 !important; text-decoration: none !important; display: block !important; text-align: center !important; font-family: ' . esc_attr($atts['font_family']) . ', sans-serif;">' . esc_html($atts['button_text']) . '</a>';
        } else {
            $output .= '<a href="' . get_permalink() . '" class="card-btn" style="color: ' . esc_attr($atts['button_bg']) . ' !important; font-size: ' . esc_attr($atts['button_size']) . 'px !important; font-weight: 800 !important; text-decoration: none !important; text-transform: uppercase !important; text-align: left !important; display: block !important; font-family: ' . esc_attr($atts['font_family']) . ', sans-serif;">' . esc_html($atts['button_text']) . ' <i class="fas fa-chevron-right" style="font-size: 8px !important; margin-left: 2px !important;"></i></a>';
        }

        $output .= '</div>'; 
        $output .= '</div>'; 
    }
    $output .= '</div>'; 

    $total_pages = $query->max_num_pages;
    if ( $total_pages > 1 ) {
        $output .= '<div class="lourdes-pagination" style="margin-top: 25px !important; display: flex !important; justify-content: center !important; gap: 8px !important; align-items: center !important;">';
        
        $links = paginate_links( array( 
            'base'      => '#page/%#%', 
            'format'    => '%#%', 
            'current'   => $paged, 
            'total'     => $total_pages, 
            'type'      => 'plain',
            'prev_text' => '<i class="fas fa-chevron-left"></i>',
            'next_text' => '<i class="fas fa-chevron-right"></i>',
        ));
        
        // Limpiar enlaces para AJAX y aplicar estilos inline a cada link
        $links = preg_replace('/href="[^"]*page\/([0-9]+)[^"]*"/', 'href="#" data-page="$1"', $links);
        
        // Inyectar estilos para los números y flechas
        $pagination_style = 'display: inline-flex !important; align-items: center !important; justify-content: center !important; min-width: 32px !important; height: 32px !important; padding: 0 6px !important; background: #fff !important; border: 1px solid #e2e8f0 !important; border-radius: 8px !important; color: #475569 !important; text-decoration: none !important; font-size: ' . esc_attr($atts['pagination_size']) . 'px !important; font-weight: 600 !important; transition: all 0.2s ease !important; font-family: ' . esc_attr($atts['font_family']) . ', sans-serif !important;';
        $active_style = 'background: ' . esc_attr($atts['pagination_bg']) . ' !important; color: #fff !important; border-color: ' . esc_attr($atts['pagination_bg']) . ' !important;';
        
        $links = str_replace('page-numbers', 'page-numbers" style="' . $pagination_style . '" onmouseover="this.style.borderColor=\'' . $atts['pagination_bg'] . '\';this.style.color=\'' . $atts['pagination_bg'] . '\'" onmouseout="this.style.borderColor=\'#e2e8f0\';this.style.color=\'#475569\'"', $links);
        $links = str_replace('current', 'current" style="' . $pagination_style . $active_style . '"', $links);
        
        $output .= $links;
        $output .= '</div>';
    }
    wp_reset_postdata();
    return $output;
}

/**
 * Lógica Principal
 */
function lourdes_noticias_master_handler( $atts, $content = null, $tag = '' ) {
    $profiles = get_option('lourdes_noticias_profiles', array());
    
    // Determinar el slug del perfil
    // 1. Si el tag es lourdes_noticias, intentamos obtenerlo de los atributos o usamos el primero disponible
    // 2. Si el tag es lourdes_{slug}, usamos ese slug
    $profile_slug = '';
    if ( $tag === 'lourdes_noticias' ) {
        if ( isset($atts['profile']) ) {
            $profile_slug = sanitize_title($atts['profile']);
        } else {
            // Intentar usar el primer perfil disponible en la base de datos
            $keys = array_keys($profiles);
            $profile_slug = !empty($keys) ? $keys[0] : 'grid';
        }
    } else {
        $profile_slug = str_replace('lourdes_', '', $tag);
    }

    $p = isset($profiles[$profile_slug]) ? $profiles[$profile_slug] : array();

    $defaults = array(
        'profile'            => $profile_slug,
        'posts'              => 0,
        'columns'            => isset($p['columns']) ? $p['columns'] : 3,
        'rows'               => isset($p['rows']) ? $p['rows'] : 2,
        'layout'             => isset($p['layout']) ? $p['layout'] : 'grid',
        'category'           => '',
        'profile_categories' => isset($p['categories']) ? $p['categories'] : array(),
        'show_date'          => isset($p['show_date']) ? $p['show_date'] : 'yes',
        'show_category'      => isset($p['show_category']) ? $p['show_category'] : 'no',
        'show_excerpt'       => isset($p['show_excerpt']) ? $p['show_excerpt'] : 'no',
        'font_family'        => isset($p['font_family']) ? $p['font_family'] : 'Inter',
        'title_size'         => isset($p['title_size']) ? $p['title_size'] : 16,
        'excerpt_size'       => isset($p['excerpt_size']) ? $p['excerpt_size'] : 13,
        'meta_size'          => isset($p['meta_size']) ? $p['meta_size'] : 10,
        'button_size'        => isset($p['button_size']) ? $p['button_size'] : 11,
        'pagination_size'    => isset($p['pagination_size']) ? $p['pagination_size'] : 12,
        'button_bg'          => isset($p['button_bg']) ? $p['button_bg'] : '#0f172a',
        'pagination_bg'      => isset($p['pagination_bg']) ? $p['pagination_bg'] : '#2563eb',
        'button_text'        => isset($p['button_text']) ? $p['button_text'] : 'Leer más',
    );

    $atts = shortcode_atts( $defaults, $atts, $tag );
    static $instance = 0; $instance++;
    $wrapper_id = 'lourdes-noticias-' . $instance;
    
    $output = '<div id="' . esc_attr($wrapper_id) . '" class="lourdes-ajax-wrapper" data-atts=\'' . esc_attr(json_encode($atts)) . '\' style="width: 100%;">';
    $output .= lourdes_render_noticias_content( $atts, 1, $wrapper_id );
    $output .= '</div>';
    
    return $output;
}

/**
 * Registrar Shortcodes
 */
add_action( 'init', 'lourdes_register_all_shortcodes' );
function lourdes_register_all_shortcodes() {
    add_shortcode( 'lourdes_noticias', 'lourdes_noticias_master_handler' );
    $profiles = get_option('lourdes_noticias_profiles', array());
    if ( ! empty( $profiles ) ) {
        foreach ( $profiles as $slug => $data ) {
            add_shortcode( 'lourdes_' . $slug, 'lourdes_noticias_master_handler' );
        }
    }
}

/**
 * AJAX Handler
 */
function lourdes_noticias_ajax_pagination() {
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $atts = isset($_POST['atts']) ? (array)$_POST['atts'] : array();
    echo lourdes_render_noticias_content($atts, $page);
    wp_die();
}
add_action( 'wp_ajax_lourdes_noticias_pagination', 'lourdes_noticias_ajax_pagination' );
add_action( 'wp_ajax_nopriv_lourdes_noticias_pagination', 'lourdes_noticias_ajax_pagination' );
