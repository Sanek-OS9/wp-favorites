<?php
/*
* регистрируем виджет для пользователей
*/
function freelax_favorites_widget()
{
    register_widget('freelax_favorites_widget_class');
}
/*
* виджет в консоле
*/
function freelax_favorites_dashboard_widget()
{
    wp_add_dashboard_widget('freelax_favorites_dashboard', 'Список Избранного', 'freelax_show_dashboard_widget');
}
function freelax_show_dashboard_widget()
{
    $user = wp_get_current_user();
    $favorites = get_user_meta($user->ID, 'freelax_favorites');
    $favorites = array_reverse($favorites);
    $img_src = plugins_url('/img/loader.gif', __FILE__);
    if (!$favorites) {
        echo 'Список Избранного пуст';
        return;
    }
    // $str = implode(',', $favorites);
    // $posts = get_posts(['include' => $str]);
    // printr($posts);
    echo '<ul>';
    foreach ($favorites AS $favorite) {
        echo '<li id="favorites' . $favorite . '">
            <a href="' . get_permalink($favorite) . '" target="_blank">' . get_the_title($favorite) . '</a>
            <span><a class="freelax-favorites-del" href="#" data-post="' . $favorite . '">&#10008;</a></span>
            <span class="freelax-favorites-hidden"><img src="' . $img_src . '" alt="loader" width="16" /></span>
        </li>';
        // $data[$favorite] = [
        //     'title' => get_the_title($favorite),
        //     'url' => get_permalink($favorite)
        // ];
    }
    echo '</ul>';
    echo '<div class="freelax-favorites-del-all">
        <button id="freelax-favorites-del-all" class="button">Очистить список</button>
        <span class="freelax-favorites-hidden"><img src="' . $img_src . '" alt="loader" width="16" /></span>
    </div>';
}
/*
* плагин
*/
# добавление ссылки добавить/удалить с избранного
function freelax_favorites(string $content): string
{
    # если не на странице просмотра записи
    # или пользователь не авторизован, возвращаем только $content
    if (!is_single() || !is_user_logged_in()) {
        return $content;
    }
    global $post;
    $param = freelax_is_favorites($post->ID) ? ['action' => 'del', 'title' => 'Удалить из Избранного'] : ['action' => 'add', 'title' => 'Добавить в Избранное'];

    $new_content = [];
    $img_src = plugins_url('/img/loader.gif', __FILE__);
    $new_content[] = '<p class="freelax-favorites"><a href="#" data-action="' . $param['action'] . '">' . $param['title'] . '</a><span class="freelax-favorites-hidden"><img src="' . $img_src . '" alt="loader" width="32" /></span></p>';
    $new_content[] = $content;
    return implode('', $new_content);
}
# подключаем js|css в консоль
function freelax_favorites_admin_scripts($hook)
{
    if ($hook != 'index.php') {
        return;
    }
    wp_enqueue_script('freelax-favorites-admin-script', plugins_url('/js/freelax-favorites-admin-script.js', __FILE__), ['jquery'], null, true);
    wp_enqueue_style('freelax-favorites-admin-style', plugins_url('/css/freelax-favorites-admin-style.css', __FILE__));

    wp_localize_script('freelax-favorites-admin-script', 'freelaxFavorites', ['nonce' => wp_create_nonce('string')]);
}
# подключаем js|css в пользовательскую часть
function freelax_favorites_scripts()
{
    # если не на странице просмотра записи
    # или пользователь не авторизован, ничего не подключаем
    // if (!is_single() || !is_user_logged_in()) {
    if (!is_user_logged_in()) {
        return;
    }
    /*
    * подключаем javascript
    @handle string - название скрипта в системе
    @src string - путь к скрипту
    @debs string - зависимости
    @ver number - версия скрипта
    @in_footer bool - подключать в футере или хеадере
    */
    // wp_deregister_script('jquery');
    // wp_register_script('jquery', "https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js", false, null);
    // wp_enqueue_script('jquery');
    global $post;

    wp_enqueue_script('freelax-favorites', plugins_url('/js/freelax-favorites-script.js', __FILE__), ['jquery'], null, true);
    wp_enqueue_style('freelax-favorites', plugins_url('/css/freelax-favorites-style.css', __FILE__));

    wp_localize_script('freelax-favorites', 'freelaxFavorites', ['url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('string'), 'id_post' => $post->ID]);
}
# очистка списка избранного
function wp_ajax_freelax_del_all()
{
    if (!wp_verify_nonce($_POST['security'], 'string')) {
        wp_die('Ошибка безопасности');
    }
    # проверяем авторизацию
    if (!is_user_logged_in()) {
        wp_die('Ошибка');
    }
    $user = wp_get_current_user();
    # удаляем все записи
    if (delete_metadata('user', $user->ID, 'freelax_favorites')) {
        wp_die('Список Избранного очищен');
    }
    // if (delete_user_meta($user->ID, 'freelax_favorites')) {
    //     wp_die('Список Избранного чист');
    // }
}
# удаление/добавление записи в избранное
function wp_ajax_freelax_add()
{
    if (!wp_verify_nonce($_POST['security'], 'string')) {
        wp_die('Ошибка безопасности');
    }
    $id_post = (int) $_POST['id_post'];
    $user = wp_get_current_user();
    # проверяем авторизацию
    if (!is_user_logged_in()) {
        wp_die('Ошибка');
    }
    # если запись была добавлена ранее то удаляем
    if (freelax_is_favorites($id_post) && delete_user_meta($user->ID, 'freelax_favorites', $id_post)) {
        wp_die(json_encode(['action' => 'delete', 'title' => 'Удалено из Избранного']));
    }
    # добавляем запись в закладки
    if (add_user_meta($user->ID, 'freelax_favorites', $id_post)) {
        wp_die(json_encode(['action' => 'add', 'title' => 'Добавлено в Избранное', 'post_title' => get_the_title($id_post), 'post_url' => get_permalink($id_post)]));
    }
}
# проверяем находится ли запись в закладках
function freelax_is_favorites(int $id_post): bool
{
    static $favorites;
    if (!$favorites) {
        $favorites = get_user_meta(wp_get_current_user()->ID, 'freelax_favorites');
    }
    return in_array($id_post, $favorites) ? true : false;
}
function printr(array $array)
{
    echo '<pre>';
    print_r($array);
    echo '<pre>';
}
