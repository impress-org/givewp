<?php

get_header();

echo render_block(
    parse_blocks(
        '<!-- wp:givewp/next-gen-donation-form-block {"formId":"' . get_the_ID() . '","blockId":"' . get_the_ID(
        ) . '"} /-->'
    )[0]
);

get_footer();
