jQuery(document).ready(function($) {
    $(document).on('click', '.lourdes-pagination a.page-numbers', function(e) {
        e.preventDefault();
        
        var $link = $(this);
        var page = $link.data('page');
        
        // Si no tiene data-page (por ejemplo, página actual o error de regex), intentar obtenerlo del texto
        if (!page) {
            page = parseInt($link.text());
        }

        if (!page) return;

        var $wrapper = $link.closest('.lourdes-ajax-wrapper');
        var container_id = $wrapper.attr('id');
        var atts = $wrapper.data('atts');
        
        $wrapper.addClass('is-loading');
        
        $.ajax({
            url: lourdes_ajax.ajax_url,
            type: 'post',
            data: {
                action: 'lourdes_noticias_pagination',
                page: page,
                atts: atts,
                container_id: container_id
            },
            success: function(response) {
                // Si la respuesta es vacía o un error de PHP
                if (!response || response === '0' || response === '-1') {
                    console.error('Lourdes Core: Error en la respuesta AJAX');
                    $wrapper.removeClass('is-loading');
                    return;
                }

                // En el layout con Facebook, el wrapper tiene una estructura interna
                // Debemos detectar si debemos reemplazar todo o solo la columna de noticias
                var $news_column = $wrapper.find('.lourdes-news-column');
                
                if ($news_column.length > 0) {
                    $news_column.html(response);
                } else {
                    $wrapper.html(response);
                }
                
                $wrapper.removeClass('is-loading');
                
                // Scroll suave al inicio del contenedor
                $('html, body').animate({
                    scrollTop: $wrapper.offset().top - 100
                }, 500);
            },
            error: function(xhr, status, error) {
                console.error('Lourdes Core AJAX Error:', status, error);
                $wrapper.removeClass('is-loading');
            }
        });
    });
});
