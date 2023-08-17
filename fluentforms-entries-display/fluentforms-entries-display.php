<?php
/**
 * Plugin Name: Fluent Forms Profile Entries
 * Description: Affiche les soumissions de formulaire Fluent Forms de l'utilisateur actuellement connecté sur sa page de profil.
 * Version: 1.15
 * Author: Thomas Germain, Hungry Nuggets
 * Author URI: https://thomasgermain.be
 */

include_once plugin_dir_path(__FILE__) . 'admin-settings.php';

function fluentforms_enqueue_styles() {
    // Enregistrez et chargez le fichier CSS de votre plugin
    wp_enqueue_style('fluentforms-entries-display-style', plugin_dir_url(__FILE__) . 'css/style.css');
}

add_action('wp_enqueue_scripts', 'fluentforms_enqueue_styles');

function fluentforms_logged_out_message() {
    return '<hr class="wp-block-separator has-text-color has-border-alt-color has-alpha-channel-opacity has-border-alt-background-color has-background is-style-wide lemmony-animation animation-inited animate lemmonyFadeIn">
    <div class="entry-content wp-block-post-content is-layout-flow wp-block-post-content-is-layout-flow">
        <div class="is-layout-flex wp-block-buttons-is-layout-flex">
            <p>Please <a href="/">log in</a> to view your entries.</p>
        </div>
    </div>';
}

function fluentforms_no_entries_message() {
    return '<div class="entry-content wp-block-post-content is-layout-flow wp-block-post-content-is-layout-flow">
        <div class="is-layout-flex wp-block-buttons-is-layout-flex">
            <p>No application found yet.</p>
        </div>
    </div>';
}

function fluentforms_display_entry($entry) {
    // Décodage du champ 'response'
    $responses = json_decode($entry->response, true);

    // Formatage de la date created_at
    $dateFormatted = date('d/m/Y', strtotime($entry->created_at));

    // Si 'image-upload' est un tableau et a au moins une valeur, affichez le premier lien comme une image
    $placeholderImage = plugins_url('fluentforms-entries-display/images/carPlaceholder.png');
    $imageLink = (isset($responses['image-upload']) && is_array($responses['image-upload']) && !empty($responses['image-upload'][0])) ? $responses['image-upload'][0] : $placeholderImage;

    // Si le status est approved on affiche la couleur verte
    $buttonClass = ($responses['dropdown'] == 'Approved') ? 'status-green' : (($responses['dropdown'] == 'Not approved') ? 'status-red' : '');

    return '<hr class="wp-block-separator has-text-color has-border-alt-color has-alpha-channel-opacity has-border-alt-background-color has-background is-style-wide lemmony-animation animation-inited animate lemmonyFadeIn">
    <div class="wp-block-columns are-vertically-aligned-center is-layout-flex wp-container-45">
        <div class="wp-block-column is-vertically-aligned-center is-layout-flow" style="flex-basis:12%">
            <figure class="wp-block-image size-full has-custom-border lemmony-animation animation-inited animate" style="--lemmony-animation-name:lemmonyFadeInLeft;animation-duration:var(--lemmony-speed-fast)">
                <img decoding="async" src="' . esc_url($imageLink) . '" alt="Car Image" class="wp-image-555 carImage">
            </figure>
        </div>
        <div class="wp-block-column is-vertically-aligned-center is-layout-flow wp-container-44">
            <div class="wp-block-columns are-vertically-aligned-center is-layout-flex wp-container-43">
                <div class="wp-block-column is-vertically-aligned-center is-layout-flow wp-container-40">
                    <h2 class="wp-block-heading has-large-font-size">' . esc_html($responses['input_text'] ?? '') . '</h2>
                    <p class="has-small-font-size entryContent">' . esc_html($responses['plate_number'] ?? '') . ' ajouté le ' . $dateFormatted . '</p>
                </div>
                <div class="wp-block-column is-vertically-aligned-center lemmony-mobile-left is-layout-flow" style="flex-basis: max-content;">
                    <div class="wp-block-buttons is-content-justification-right is-layout-flex wp-container-41">
                        <div class="wp-block-button has-custom-font-size has-tiny-font-size">
                            <a class="wp-block-button__link wp-element-button ' . $buttonClass . '">' . esc_html($responses['dropdown'] ?? '') . '</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>';
}

function fluentforms_profile_entries_shortcode() {
    global $wpdb;
    $user_id = get_current_user_id();

    // Vérifiez si l'utilisateur est connecté
    if (!$user_id) {
        return fluentforms_logged_out_message();
    }

    $table_name = $wpdb->prefix . 'fluentform_submissions';

    // Interrogez la base de données pour obtenir les entrées de cet utilisateur
    $entries = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id));

    if (!$entries) {
        return fluentforms_no_entries_message();
    }

    // Débutez le code HTML de sortie
    $output = '';

    // Affichez les données de réponse selon la nouvelle structure
    foreach ($entries as $entry) {
        $output .= fluentforms_display_entry($entry);
    }

    return $output;
}

add_shortcode('fluentforms_profile_entries', 'fluentforms_profile_entries_shortcode');
