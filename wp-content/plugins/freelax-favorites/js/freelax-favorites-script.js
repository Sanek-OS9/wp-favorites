(function($) {
    $('.freelax-favorites a').click(function(e){
        var action = $(this).data('action');
        $.ajax({
            type: 'POST',
            url: freelaxFavorites.url,
            data: {
                security: freelaxFavorites.nonce,
                action: 'freelax_add',
                id_post: freelaxFavorites.id_post
            },
            beforeSend: function(){
                //$('p.freelax-favorites a').fadeOut(300, function(){
                    $('.freelax-favorites .freelax-favorites-hidden').fadeIn();
                //});
            },
            success: function(data){
                var data = JSON.parse(data);
                $('.freelax-favorites .freelax-favorites-hidden').fadeOut(300, function(){
                    if (data.action == 'delete') {
                        $('#favorites' + freelaxFavorites.id_post).remove();
                        $('p.freelax-favorites a').text('Добавить в Избранное');
                    } else if (data.action == 'add') {
                        $(".widget_freelax_favorites_widget ul").prepend('<li id="favorites' + freelaxFavorites.id_post + '" class="freelax-favorites-hidden new_post"><a href="' + data.post_url + '" target="_blank">' + data.post_title + '</a></li>');
                        $('.freelax-favorites-hidden.new_post').fadeIn(400);
                        $('p.freelax-favorites a').text('Удалить из Избранного');
                        // $(".widget_freelax_favorites_widget ul").append("тест");
                    }
                    //$('p.freelax-favorites').fadeOut();
                    // $('.freelax-favorites').html(data.title);
                });
            },
            error: function(){
                console.log('Ошибка!');
            }
        });
        return false;
    });
})(jQuery);
