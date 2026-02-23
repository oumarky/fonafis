<?php
get_header();
?>

<main class="ast-container">
    <h1>Thématiques du FONAFIS</h1>

    <?php if ( have_posts() ) : ?>
        <ul class="thematique-list">
            <?php while ( have_posts() ) : the_post(); ?>
                <li>
                    <h2>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_title(); ?>
                        </a>
                    </h2>

                    <?php the_excerpt(); ?>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else : ?>
        <p>Aucune thématique disponible.</p>
    <?php endif; ?>
</main>

<?php
get_footer();
