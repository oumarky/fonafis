<?php
get_header();
?>

<main class="ast-container">

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <article class="session-detail">
        <h1><?php the_title(); ?></h1>

        <div class="session-meta">
            <p><?php the_terms( get_the_ID(), 'jour', 'Jour : ' ); ?></p>
            <p><?php the_terms( get_the_ID(), 'type_session', 'Type : ' ); ?></p>
            <p><?php the_terms( get_the_ID(), 'thematique_liee', 'Thématique : ' ); ?></p>
        </div>

        <div class="session-content">
            <?php the_content(); ?>
        </div>
    </article>

<?php endwhile; endif; ?>

</main>


<?php
$intervenants = get_post_meta( get_the_ID(), 'intervenants', true );

if ( ! empty( $intervenants ) ) :
?>
    <section class="session-intervenants">
        <h2>Intervenants</h2>
        <ul>
            <?php foreach ( $intervenants as $id ) : ?>
                <li>
                    <a href="<?php echo get_permalink( $id ); ?>">
                        <?php echo get_the_title( $id ); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php
get_footer();

