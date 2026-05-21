<?php
/**
 * Administrador de Perfiles con Shortcodes Dinámicos y Botones Personalizables - Lourdes Core
 */

add_action( 'admin_menu', 'lourdes_core_add_settings_page' );
function lourdes_core_add_settings_page() {
    add_menu_page('Lourdes Core', 'Lourdes Core', 'manage_options', 'lourdes-core-settings', 'lourdes_core_render_settings_page', 'dashicons-admin-generic', 100);
}

/**
 * Lógica de Guardado Personalizada
 */
add_action( 'admin_init', 'lourdes_core_handle_profile_save' );
function lourdes_core_handle_profile_save() {
    if ( ! isset( $_POST['lourdes_save_profile_nonce'] ) || ! wp_verify_nonce( $_POST['lourdes_save_profile_nonce'], 'lourdes_save_profile' ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) return;

    $profiles = get_option('lourdes_noticias_profiles', array());
    $slug = sanitize_text_field( $_POST['profile_slug'] );

    // Guardar si vienen datos del perfil, sin importar qué botón se presionó
    if ( isset( $_POST['profile_data'] ) ) {
        $raw_data = $_POST['profile_data'];
        $selected_cats = isset($raw_data['categories']) ? array_map('sanitize_text_field', $raw_data['categories']) : array();

        $profiles[$slug] = array(
            'label'         => sanitize_text_field( $raw_data['label'] ),
            'columns'       => intval( $raw_data['columns'] ),
            'rows'          => intval( $raw_data['rows'] ),
            'layout'        => sanitize_text_field( $raw_data['layout'] ),
            'show_date'     => sanitize_text_field( $raw_data['show_date'] ?? 'no' ),
            'show_excerpt'  => sanitize_text_field( $raw_data['show_excerpt'] ?? 'no' ),
            'show_category' => sanitize_text_field( $raw_data['show_category'] ?? 'no' ),
            'font_family'   => sanitize_text_field( $raw_data['font_family'] ?? 'Inter' ),
            'title_size'    => intval( $raw_data['title_size'] ?? 16 ),
            'excerpt_size'  => intval( $raw_data['excerpt_size'] ?? 13 ),
            'meta_size'     => intval( $raw_data['meta_size'] ?? 10 ),
            'button_size'   => intval( $raw_data['button_size'] ?? 11 ),
            'pagination_size' => intval( $raw_data['pagination_size'] ?? 12 ),
            'button_text'   => sanitize_text_field( $raw_data['button_text'] ),
            'button_bg'     => sanitize_hex_color( $raw_data['button_bg'] ),
            'pagination_bg' => sanitize_hex_color( $raw_data['pagination_bg'] ?? '#2563eb' ),
            'categories'    => $selected_cats,
        );
        update_option('lourdes_noticias_profiles', $profiles);
        add_settings_error( 'lourdes_messages', 'lourdes_message', 'Perfil "' . $profiles[$slug]['label'] . '" actualizado correctamente.', 'updated' );
    }
}

function lourdes_core_render_settings_page() {
    $profiles = get_option('lourdes_noticias_profiles', array());
    $all_categories = get_terms( array('taxonomy' => 'category', 'hide_empty' => false) );

    // Manejar eliminación
    if (isset($_GET['delete_profile']) && check_admin_referer('delete_profile_nonce')) {
        $to_delete = sanitize_text_field($_GET['delete_profile']);
        if ($to_delete !== 'grid' && $to_delete !== 'board') {
            unset($profiles[$to_delete]);
            update_option('lourdes_noticias_profiles', $profiles);
            echo '<div class="updated"><p>Perfil eliminado.</p></div>';
        }
    }

    // Manejar creación
    if (isset($_POST['new_profile_name']) && !empty($_POST['new_profile_name'])) {
        $new_label = sanitize_text_field($_POST['new_profile_name']);
        $slug = sanitize_title($new_label);
        if (!isset($profiles[$slug])) {
            $profiles[$slug] = array(
                'label' => $new_label, 'columns' => 3, 'rows' => 1, 'layout' => 'grid', 'show_date' => 'yes', 'show_excerpt' => 'no', 'button_text' => 'Leer más', 'button_bg' => '#0f172a', 'categories' => array()
            );
            update_option('lourdes_noticias_profiles', $profiles);
            echo '<div class="updated"><p>Nuevo perfil "' . esc_html($new_label) . '" creado.</p></div>';
        }
    }

    settings_errors( 'lourdes_messages' );
    ?>
    <div class="wrap">
        <h1>Administrador de Secciones Institucionales</h1>
        <p>Configura el diseño, categorías y botones para cada sección de tu portal.</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(450px, 1fr)); gap: 25px; margin-top: 20px;">
            
            <?php foreach ($profiles as $slug => $data) : ?>
            <div style="background: #fff; padding: 25px; border-radius: 15px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); position: relative; display: flex; flex-direction: column;">
                
                <form method="post" action="">
                    <?php wp_nonce_field( 'lourdes_save_profile', 'lourdes_save_profile_nonce' ); ?>
                    <input type="hidden" name="profile_slug" value="<?php echo esc_attr($slug); ?>" />
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #f8fafc; padding-bottom: 10px;">
                        <h2 style="margin:0; font-size: 18px; color: #1e293b;"><i class="fas fa-tag" style="color:#2563eb; margin-right:8px;"></i> <?php echo esc_html($data['label']); ?></h2>
                        <?php if ($slug !== 'grid' && $slug !== 'board') : ?>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=lourdes-core-settings&delete_profile=' . $slug), 'delete_profile_nonce'); ?>" style="color: #ef4444; font-size: 11px; text-decoration: none;" onclick="return confirm('¿Borrar perfil?')"><i class="fas fa-trash"></i> Borrar</a>
                        <?php endif; ?>
                    </div>

                    <table class="form-table" style="margin: 0; width: 100%;">
                        <tr>
                            <th style="width: 140px; padding: 8px 0;">Nombre</th>
                            <td><input type="text" name="profile_data[label]" value="<?php echo esc_attr($data['label']); ?>" style="width: 100%; font-weight: 600;" /></td>
                        </tr>
                        <tr>
                            <th style="padding: 8px 0;">Configuración</th>
                            <td>
                                <select name="profile_data[layout]" style="width: 90px;">
                                    <option value="grid" <?php selected($data['layout'], 'grid'); ?>>Grid</option>
                                    <option value="board" <?php selected($data['layout'], 'board'); ?>>Board</option>
                                </select>
                                <input type="number" name="profile_data[columns]" value="<?php echo esc_attr($data['columns']); ?>" min="1" max="6" style="width: 45px;" /> col / 
                                <input type="number" name="profile_data[rows]" value="<?php echo esc_attr($data['rows']); ?>" min="1" max="10" style="width: 45px;" /> filas
                            </td>
                        </tr>
                        <tr>
                            <th style="padding: 8px 0; vertical-align: top;">Filtrar Categorías</th>
                            <td>
                                <div style="max-height: 100px; overflow-y: auto; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <?php if ( ! empty( $all_categories ) ) : foreach ( $all_categories as $term ) : ?>
                                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">
                                            <input type="checkbox" name="profile_data[categories][]" value="<?php echo esc_attr($term->slug); ?>" <?php checked( in_array($term->slug, (array)($data['categories'] ?? array())) ); ?> />
                                            <?php echo esc_html($term->name); ?>
                                        </label>
                                    <?php endforeach; else : ?>
                                        <span style="color:#94a3b8; font-size: 11px;">Crea categorías en Comunicados.</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th style="padding: 8px 0;">Opciones</th>
                            <td>
                                <label style="font-size:12px; margin-right:10px;"><input type="checkbox" name="profile_data[show_date]" value="yes" <?php checked($data['show_date'] ?? 'no', 'yes'); ?> /> Fecha</label>
                                <label style="font-size:12px; margin-right:10px;"><input type="checkbox" name="profile_data[show_excerpt]" value="yes" <?php checked($data['show_excerpt'] ?? 'no', 'yes'); ?> /> Extracto</label>
                                <label style="font-size:12px;"><input type="checkbox" name="profile_data[show_category]" value="yes" <?php checked($data['show_category'] ?? 'no', 'yes'); ?> /> Categoría</label>
                            </td>
                        </tr>
                        <tr>
                            <th style="padding: 8px 0;">Botón Leer más</th>
                            <td>
                                <input type="text" name="profile_data[button_text]" value="<?php echo esc_attr($data['button_text'] ?? 'Leer más'); ?>" style="width: 65%;" placeholder="Texto..." />
                                <input type="color" name="profile_data[button_bg]" value="<?php echo esc_attr($data['button_bg'] ?? '#0f172a'); ?>" style="width: 28%; height: 28px; vertical-align: middle; padding: 0; border: none; cursor: pointer;" />
                            </td>
                        </tr>
                        <tr>
                            <th style="padding: 8px 0; border-top: 1px solid #f1f5f9; vertical-align: top;">Tipografía</th>
                            <td style="border-top: 1px solid #f1f5f9; padding-top: 10px;">
                                <select name="profile_data[font_family]" style="width: 100%; margin-bottom: 10px;">
                                    <option value="Inter" <?php selected($data['font_family'] ?? 'Inter', 'Inter'); ?>>Inter (Institucional)</option>
                                    <option value="Montserrat" <?php selected($data['font_family'] ?? 'Inter', 'Montserrat'); ?>>Montserrat (Títulos)</option>
                                    <option value="Roboto" <?php selected($data['font_family'] ?? 'Inter', 'Roboto'); ?>>Roboto</option>
                                    <option value="Open Sans" <?php selected($data['font_family'] ?? 'Inter', 'Open Sans'); ?>>Open Sans</option>
                                    <option value="Playfair Display" <?php selected($data['font_family'] ?? 'Inter', 'Playfair Display'); ?>>Playfair Display (Serif)</option>
                                </select>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px;">
                                    <label style="font-size: 11px;">Título: <input type="number" name="profile_data[title_size]" value="<?php echo esc_attr($data['title_size'] ?? 16); ?>" style="width: 45px;" />px</label>
                                    <label style="font-size: 11px;">Extracto: <input type="number" name="profile_data[excerpt_size]" value="<?php echo esc_attr($data['excerpt_size'] ?? 13); ?>" style="width: 45px;" />px</label>
                                    <label style="font-size: 11px;">Meta: <input type="number" name="profile_data[meta_size]" value="<?php echo esc_attr($data['meta_size'] ?? 10); ?>" style="width: 45px;" />px</label>
                                    <label style="font-size: 11px;">Botón: <input type="number" name="profile_data[button_size]" value="<?php echo esc_attr($data['button_size'] ?? 11); ?>" style="width: 45px;" />px</label>
                                    <label style="font-size: 11px;">Paginación: <input type="number" name="profile_data[pagination_size]" value="<?php echo esc_attr($data['pagination_size'] ?? 12); ?>" style="width: 45px;" />px</label>
                                </div>
                                <div style="margin-top: 10px;">
                                    <label style="font-size: 11px; display: block; margin-bottom: 3px;">Color Paginación:</label>
                                    <input type="color" name="profile_data[pagination_bg]" value="<?php echo esc_attr($data['pagination_bg'] ?? '#2563eb'); ?>" style="width: 100%; height: 25px; padding: 0; border: none; cursor: pointer;" />
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top: 20px; padding: 15px; border-radius: 12px; background: #f0f9ff; border: 1px solid #bae6fd;">
                        <span style="display: block; font-size: 11px; color: #0369a1; font-weight: 700; text-transform: uppercase; margin-bottom: 5px;">Shortcode Único:</span>
                        <code style="font-size: 14px; color: #0284c7; font-weight: 800; background: transparent; padding: 0;">[lourdes_<?php echo $slug; ?>]</code>
                    </div>

                    <div style="margin-top: 15px; display: flex; justify-content: flex-end;">
                        <button type="submit" name="lourdes_update_profile" class="button button-primary button-large" style="padding: 0 30px; height: 40px;">Guardar Perfil</button>
                    </div>
                </form>
            </div>
            <?php endforeach; ?>

        </div>

        <div style="margin-top: 40px; background: #fff; padding: 30px; border-radius: 15px; border: 2px dashed #cbd5e1; max-width: 600px;">
            <h3 style="margin-top:0;"><i class="fas fa-plus-circle" style="color:#10b981;"></i> Crear Nueva Sección</h3>
            <form method="post" action="">
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="new_profile_name" placeholder="Nombre (ej: Actualidad Lourdes)..." style="flex: 1; padding: 10px;" required />
                    <button type="submit" class="button button-secondary" style="height: 44px;">Añadir Sección</button>
                </div>
            </form>
        </div>
    </div>
    <?php
}
