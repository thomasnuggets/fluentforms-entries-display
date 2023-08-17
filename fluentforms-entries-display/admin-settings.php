<?php
/**
 * Administration Settings for FluentForms Entries Display Plugin.
 * 
 * This file contains functions and hooks for the admin settings page 
 * where the user can select from which form entries should be displayed.
 * 
 * @package FluentFormsEntriesDisplay
 * @author Votre nom ici
 */

add_action('admin_menu', 'fluentforms_entries_display_admin_page');

function fluentforms_entries_display_admin_page() {
    add_options_page(
        'Fluent Forms Entries Display', 
        'Fluent Forms Entries', 
        'manage_options', 
        'fluentforms-entries-display', 
        'fluentforms_entries_display_admin_page_content'
    );
}


function fluentforms_entries_display_admin_page_content() {
    ?>
    <div class="wrap">
        <h2>Fluent Forms Entries Display</h2>
        <form method="post" action="options.php">
            <?php 
            settings_fields('fluentforms_entries_display_options');
            do_settings_sections('fluentforms-entries-display'); 
            submit_button();
            ?>
        </form>
    </div>
    <?php
}


add_action('admin_init', 'fluentforms_entries_display_settings');

function fluentforms_entries_display_settings() {
    register_setting(
        'fluentforms_entries_display_options',
        'fluentforms_selected_form'
    );

    add_settings_section(
        'fluentforms_entries_display_main',
        'Main Settings',
        'fluentforms_entries_display_main_callback',
        'fluentforms-entries-display'
    );

    add_settings_field(
        'fluentforms_selected_form', 
        'Select Form', 
        'fluentforms_selected_form_callback', 
        'fluentforms-entries-display', 
        'fluentforms_entries_display_main'
    );
}

function fluentforms_entries_display_main_callback() {
    echo 'Select the Fluent Forms form to display entries from.';
}

function fluentforms_selected_form_callback() {
    // Obtenez une liste de tous les formulaires Fluent Forms
    $forms = wpFluent()->table('fluentform_forms')->get();
    
    $selected_form = get_option('fluentforms_selected_form');
    
    echo '<select name="fluentforms_selected_form">';
    foreach ($forms as $form) {
        echo '<option value="' . esc_attr($form->id) . '"' . selected($selected_form, $form->id, false) . '>' . esc_html($form->title) . '</option>';
    }
    echo '</select>';
}

$selected_form_id = get_option('fluentforms_selected_form');