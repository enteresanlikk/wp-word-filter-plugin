<?php
/*
 * Plugin Name: Word Filter
 * Version: 1.0.0
 * Author: Bilal Demir
 * Author URI: https://bilaldemir.dev
 */

if(!defined('ABSPATH')) exit;

class WordFilter {
    public function __construct() {
        add_action('admin_menu', [$this, 'MenuSetup']);
        add_action('admin_init', [$this, 'AdminInit']);
        add_filter('the_content', [$this, 'ContentFilter']);
    }

    public function MenuSetup() {
        $mainPageHook = add_menu_page(
            __('Word Filter', 'word-filter'),
            __('Word Filter', 'word-filter'),
            'manage_options',
            'word-filter',
            [$this, 'FilterPage'],
            'dashicons-filter',
            100
        );

        add_submenu_page(
            'word-filter',
            __('Word Filter List', 'word-filter'),
            __('Words List', 'word-filter'),
            'manage_options',
            'word-filter',
            [$this, 'FilterPage']
        );

        add_submenu_page(
            'word-filter',
            __('Word Filter Options', 'word-filter'),
            __('Options', 'word-filter'),
            'manage_options',
            'word-filter-options',
            [$this, 'OptionsPage']
        );

        add_action("load-{$mainPageHook}", [$this, 'MainPageLoad']);
    }

    public function MainPageLoad() {
        wp_enqueue_style('word-filter', plugin_dir_url(__FILE__). 'assets/css/style.css');
    }

    public function HandleForm() {
        if(isset($_POST['submit'])) {
            if(wp_verify_nonce($_POST['word-filter-nonce'], 'word-filter') && current_user_can('manage_options')) {
                $words = $_POST['words_list'];

                update_option('word_filter_words', $words);

                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>'.__('Words list updated successfully.', 'word-filter').'</p>';
                echo '</div>';
            } else {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>'.__('An error occured while updating the words list.', 'word-filter').'</p>';
                echo '</div>';
            }
        }
    }

    public function FilterPage() {
        echo '<div class="wrap">';
        echo '<h1>'.__('Words List', 'word-filter').'</h1>';

        $this->HandleForm();

        echo '<form method="post">';
        wp_nonce_field('word-filter', 'word-filter-nonce');
        echo '<label id="words_list">'.__('Enter a comma seperated list of words to filter from your site\'s content.', 'word-filter').'</label>';
        echo '<div class="word-filter__flex-container">';
        echo '<textarea name="words_list" placeholder="back, front, etc.">'.esc_textarea(get_option('word_filter_words')).'</textarea>';
        echo '</div>';

                    submit_button();

        echo '</form>';
        echo '</div>';
    }

    public function ContentFilter($content) {
        $wordList = get_option('word_filter_words');
        if (empty($wordList)) {
            return $content;
        }

        $words = explode(',', $wordList);
        $words = array_map('trim', $words);
        $replacementCharacter = get_option('word_filter_replacement_character', '*');

        return str_ireplace($words, esc_html($replacementCharacter), $content);
    }

    public function AdminInit() {
        add_settings_section(
            'default',
            null,
            null,
            'word-filter-options'
        );

        add_settings_field(
            'word_filter_replacement_character',
            __('Replacement Character', 'word-filter'),
            [$this, 'ReplacementCharacterField'],
            'word-filter-options',
            'default'
        );
        register_setting(
            'word-filter-options',
            'word_filter_replacement_character'
        );
    }

    public function ReplacementCharacterField() {
        $replacementCharacter = get_option('word_filter_replacement_character', '*');
        echo '<input type="text" name="word_filter_replacement_character" value="'.esc_attr($replacementCharacter).'" />';
        echo '<p class="description">'.__('The character to replace filtered words with.', 'word-filter').'</p>';
    }

    public function OptionsPage() {
        echo "<div class='wrap'>";
        echo "<h1>".__('Options', 'word-filter')."</h1>";

        echo "<form action='options.php' method='post'>";

        settings_errors();
        settings_fields('word-filter-options');
        do_settings_sections('word-filter-options');
        submit_button();

        echo "</form>";
        echo "</div>";
    }
}

new WordFilter();