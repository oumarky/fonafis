<?php
/**
 * Template pour la page Forum
 */

get_header();
?>

<main class="ast-container">
    <h1>PAGE FORUM (template personnalisé)</h1>

    <?php
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
    endif;
    ?>
</main>

<?php
add_action( 'wp_footer', 'message_page_forum' );

function message_page_forum() {
    if ( is_page('forum') ) {
        echo '<p style="color:green;text-align:center;">Forum actif</p>';
    }
}
get_footer();
