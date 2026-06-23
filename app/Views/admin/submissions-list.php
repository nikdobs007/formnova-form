<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        Form Submissions
    </h1>

    <hr class="wp-header-end">

    <form method="post">

        <?php
        wp_nonce_field(
            'formnova_bulk_action',
            'formnova_bulk_nonce'
        );
        ?>

        <?php
        $table->search_box(
            'Search Submissions',
            'submission_search'
        );
        ?>

        <?php
        $table->display();
        ?>

    </form>
</div>