<?php
class freelax_favorites_widget_class extends WP_Widget{

    # настройка виджета в списке виджетов
    public function __construct()
    {
        $args = [
            'name' => 'Избранные записи',
            'description' => 'Выводит блок избранных записей пользователя'
        ];
        parent::__construct('freelax_favorites_widget', null, $args);
    }
    # настройка виджета в админке
    public function form(array $instance)
    {
        $title = !empty($instance['title']) ? esc_attr($instance['title']) : 'Избранные записи';
        ?>
        <p>
            <label for="<?= $this->get_field_id('title') ?>">Заголовок</label>
            <input type="text" name="<?= $this->get_field_name('title') ?>" value="<?= $title ?>" id="<?= $this->get_field_id('title') ?>" class="widefat">
        </p>
        <?php
    }

    # настройка виджета в пользовательской части
    public function widget($args, $instance)
    {
        if (!is_user_logged_in()) {
            return;
        }
        $user = wp_get_current_user();
        $favorites = get_user_meta($user->ID, 'freelax_favorites');
        $favorites = array_reverse($favorites);

        echo $args['before_widget'];
            echo $args['before_title'];
                echo $instance['title'];
            echo $args['after_title'];
        echo '<ul>';
        foreach ($favorites as $favorite) {
            echo '<li id="favorites' . $favorite . '"><a href="' . get_permalink($favorite) . '" target="_blank">' . get_the_title($favorite) . '</a></li>';
        }
        echo '</ul>';
        echo $args['after_widget'];
    }
    # обновление насройек виджета в админке
    // public function update()
    // {
    //
    // }
}
