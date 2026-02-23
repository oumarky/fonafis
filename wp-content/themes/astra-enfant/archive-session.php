<?php
get_header();
?>

<main class="ast-container">
    <h1>Programme du Forum</h1>

    <?php if ( have_posts() ) : ?>
        <ul class="programme-list">
            <?php while ( have_posts() ) : the_post(); ?>
                <li class="programme-item">
                    <h2>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_title(); ?>
                        </a>
                    </h2>

                    <div class="meta">
                        <?php
                        the_terms( get_the_ID(), 'jour', 'Jour : ' );
                        echo ' | ';
                        the_terms( get_the_ID(), 'type_session', 'Type : ' );
                        ?>
                    </div>

                    <div class="excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else : ?>
        <p>Aucune session programmée.</p>
    <?php endif; ?>
</main>

<?php
get_footer();
