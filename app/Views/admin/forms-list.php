<?php

if (!defined('ABSPATH')) {
    exit;
}

?>


<div class="wrap">

    <h1 class="wp-heading-inline">
        Forms
    </h1>

    <a href="<?php echo esc_url(
        admin_url('admin.php?page=formnova-add')
    ); ?>" class="page-title-action">

        Add New

    </a>

    <hr class="wp-header-end">

    <form method="post">

        <input type="hidden" name="page" value="formnova">

        <?php
        wp_nonce_field(
            'formnova_bulk_action',
            'formnova_bulk_nonce'
        );

        $table->search_box(
            'Search Forms',
            'formnova-search'
        );

        $table->display();
        ?>

    </form>

</div>