<?php

get_header();

echo render_block(
    parse_blocks(
        '<!-- wp:givewp/donation-form {"formId":"' . get_the_ID() . '","blockId":"' . get_the_ID() . '"} /-->'
    )[0]
);

get_footer();
