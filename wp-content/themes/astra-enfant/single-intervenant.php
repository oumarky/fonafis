<?php
get_header();
?>

<main class="ast-container">

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <!-- FICHE INTERVENANT -->
    <article class="intervenant-detail">

        <h1><?php the_title(); ?></h1>

        <div class="intervenant-content">
            <?php the_content(); ?>
        </div>

    </article>

    <!-- SESSIONS DE CET INTERVENANT -->
    <section class="intervenant-sessions">

        <h2>Sessions auxquelles cet intervenant participe</h2>

        <?php
        // ID de l’intervenant courant
        $intervenant_id = get_the_ID();

        // Requête : sessions qui contiennent cet intervenant
        $sessions = new WP_Query( array(
            'post_type'  => 'session',
            'meta_query' => array(
                array(
                    'key'     => 'intervenants',
                    'value'   => '"' . $intervenant_id . '"',
                    'compare' => 'LIKE'
                )
            )
        ) );

        if ( $sessions->have_posts() ) :
            echo '<ul class="session-list">';
            while ( $sessions->have_posts() ) :
                $sessions->the_post();
                ?>
                <li>
                    <a href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                </li>
                <?php
            endwhile;
            echo '</ul>';
            wp_reset_postdata();
        else :
            echo '<p>Aucune session associée.</p>';
        endif;
        ?>

    </section>

<?php endwhile; endif; ?>

</main>

<?php
get_footer();
