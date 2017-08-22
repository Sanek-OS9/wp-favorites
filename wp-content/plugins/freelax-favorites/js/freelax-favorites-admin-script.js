(function($) {
    $('a.freelax-favorites-del').click(function(e){
        var id_post = $(this).data('post'),
            parent = $(this).parent(),
            loader = parent.next(),
            li = $(this).closest('li');

        if (!confirm('Подтвердите удаление')) {
            return false;
        }
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                security: freelaxFavorites.nonce,
                action: 'freelax_add',
                id_post: id_post
            },
            beforeSend: function(){
                parent.fadeOut(300, function(){
                    $(loader).fadeIn();
                });
            },
            success: function(data){
                var data = JSON.parse(data);
                $(loader).fadeOut(300, function(){
                    $(li).html(data.title);
                });
            },
            error: function(){
                console.log('Ошибка!');
            }
        });
        return false;
    });

    $('#freelax-favorites-del-all').click(function(e){
        var loader = $(this).next(),
            parent = $(this).parent(),
            list = parent.prev();
        if (!confirm('Вы действительно хотите очистить весь список Избранного?')) {
            return false;
        }
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                security: freelaxFavorites.nonce,
                action: 'freelax_del_all'
            },
            beforeSend: function(){
                $(loader).fadeIn();
            },
            success: function(res){
                $(loader).fadeOut(300, function(){
                    $(list).html(res);
                    $('.freelax-favorites-del-all').fadeOut();
                });
            },
            error: function(){
                console.log('Ошибка!');
            }
        });
        return false;
    });
})(jQuery);
