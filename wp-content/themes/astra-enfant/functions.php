<?php
/**
 * Theme enfant Astra - Functions
 */

/**
 * Chargement correct des styles Astra + thème enfant
 */
add_action( 'wp_enqueue_scripts', 'astra_enfant_charger_styles', 20 );

function astra_enfant_charger_styles() {

    // Style du thème parent (Astra)
    wp_enqueue_style(
        'astra-parent',
        get_template_directory_uri() . '/style.css'
    );

    // Style du thème enfant
    wp_enqueue_style(
        'astra-enfant',
        get_stylesheet_directory_uri() . '/style.css',
        array('astra-parent'),
        wp_get_theme()->get('Version')
    );
}

/* ======================================================
   CPT FONAFIS
   ====================================================== */

/**
 * CPT : Thématiques
 */
function fonafis_cpt_thematique() {

    $labels = array(
        'name'          => 'Thématiques',
        'singular_name' => 'Thématique',
        'menu_name'     => 'Thématiques',
    );

    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'menu_icon'     => 'dashicons-lightbulb',
        'supports'      => array( 'title', 'editor', 'thumbnail' ),
        'has_archive'   => true,
        'rewrite'       => array( 'slug' => 'thematiques' ),
        'show_in_rest'  => true,
    );

    register_post_type( 'thematique', $args );
}
add_action( 'init', 'fonafis_cpt_thematique' );


/**
 * CPT : Sessions (Programme)
 */
function fonafis_cpt_session() {

    $labels = array(
        'name'          => 'Programme',
        'singular_name' => 'Session',
        'menu_name'     => 'Programme',
    );

    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'menu_icon'     => 'dashicons-calendar-alt',
        'supports'      => array( 'title', 'editor' ),
        'has_archive'   => true,
        'rewrite'       => array( 'slug' => 'programme' ),
        'show_in_rest'  => true,
    );

    register_post_type( 'session', $args );
}
add_action( 'init', 'fonafis_cpt_session' );


/**
 * CPT : Intervenants
 */
function fonafis_cpt_intervenant() {

    $labels = array(
        'name'          => 'Intervenants',
        'singular_name' => 'Intervenant',
        'menu_name'     => 'Intervenants',
    );

    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'menu_icon'     => 'dashicons-businessperson',
        'supports'      => array( 'title', 'editor', 'thumbnail' ),
        'has_archive'   => true,
        'rewrite'       => array( 'slug' => 'intervenants' ),
        'show_in_rest'  => true,
    );

    register_post_type( 'intervenant', $args );
}
add_action( 'init', 'fonafis_cpt_intervenant' );


/**
 * CPT : Ressources
 */
function fonafis_cpt_ressource() {

    $labels = array(
        'name'          => 'Ressources',
        'singular_name' => 'Ressource',
        'menu_name'     => 'Ressources',
    );

    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'menu_icon'     => 'dashicons-media-document',
        'supports'      => array( 'title', 'editor' ),
        'has_archive'   => true,
        'rewrite'       => array( 'slug' => 'ressources' ),
        'show_in_rest'  => true,
    );

    register_post_type( 'ressource', $args );
}
add_action( 'init', 'fonafis_cpt_ressource' );

/* ======================================================
   TAXONOMIES FONAFIS
   ====================================================== */

/**
 * Taxonomie : Jour du forum (pour les sessions)
 */
function fonafis_tax_jour() {

    $labels = array(
        'name'          => 'Jours',
        'singular_name' => 'Jour',
        'menu_name'     => 'Jour du forum',
    );

    register_taxonomy( 'jour', 'session', array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'jour' ),
        'show_in_rest'      => true,
    ));
}
add_action( 'init', 'fonafis_tax_jour' );


/**
 * Taxonomie : Type de session
 */
function fonafis_tax_type_session() {

    $labels = array(
        'name'          => 'Types de session',
        'singular_name' => 'Type de session',
        'menu_name'     => 'Type de session',
    );

    register_taxonomy( 'type_session', 'session', array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'type-session' ),
        'show_in_rest'      => true,
    ));
}
add_action( 'init', 'fonafis_tax_type_session' );


/**
 * Taxonomie : Thématiques (liée aux sessions et ressources)
 */
function fonafis_tax_thematique() {

    $labels = array(
        'name'          => 'Thématiques',
        'singular_name' => 'Thématique',
        'menu_name'     => 'Thématique',
    );

    register_taxonomy( 'thematique_liee', array( 'session', 'ressource' ), array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'thematique' ),
        'show_in_rest'      => true,
    ));
}
add_action( 'init', 'fonafis_tax_thematique' );

/**
 * Meta box : Intervenants liés à une session
 */
function fonafis_session_intervenants_metabox() {
    add_meta_box(
        'session_intervenants',
        'Intervenants',
        'fonafis_session_intervenants_callback',
        'session',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'fonafis_session_intervenants_metabox' );


function fonafis_session_intervenants_callback( $post ) {

    $selected = get_post_meta( $post->ID, 'intervenants', true );
    if ( ! is_array( $selected ) ) {
        $selected = array();
    }

    $intervenants = get_posts( array(
        'post_type' => 'intervenant',
        'numberposts' => -1
    ) );

    echo '<ul>';

    foreach ( $intervenants as $intervenant ) {
        $checked = in_array( $intervenant->ID, $selected ) ? 'checked' : '';
        echo '<li>';
        echo '<label>';
        echo '<input type="checkbox" name="intervenants[]" value="' . $intervenant->ID . '" ' . $checked . '> ';
        echo esc_html( $intervenant->post_title );
        echo '</label>';
        echo '</li>';
    }

    echo '</ul>';
}

/**
 * Sauvegarde des intervenants liés
 */
function fonafis_save_session_intervenants( $post_id ) {

    if ( isset( $_POST['intervenants'] ) ) {
        update_post_meta( $post_id, 'intervenants', array_map( 'intval', $_POST['intervenants'] ) );
    } else {
        delete_post_meta( $post_id, 'intervenants' );
    }
}
add_action( 'save_post_session', 'fonafis_save_session_intervenants' );
