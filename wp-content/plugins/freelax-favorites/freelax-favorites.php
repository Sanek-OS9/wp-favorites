<?php
/*
Plugin Name: Добавление в избранное
Plugin URI: http://reklama.ua
Description: Самый ахуенный плагин в мире
Version: 1.0
Author: Sanek_OS9
Author URI: http://test.com
*/

# подключаем файл функций
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/freelax_favorites_widget_class.php';

# добавляем фильтр к content
add_filter('the_content', 'freelax_favorites');
# подключаем javascript|css (пользовательская часть)
add_action('wp_enqueue_scripts', 'freelax_favorites_scripts');

# ajax запрос для пользователей
add_action('wp_ajax_freelax_add', 'wp_ajax_freelax_add');
add_action('wp_ajax_freelax_del_all', 'wp_ajax_freelax_del_all');
# добавляем виджет в консоль
add_action('wp_dashboard_setup', 'freelax_favorites_dashboard_widget');

add_action('admin_enqueue_scripts', 'freelax_favorites_admin_scripts');

# регистрируем свой виджет
add_action('widgets_init', 'freelax_favorites_widget');
