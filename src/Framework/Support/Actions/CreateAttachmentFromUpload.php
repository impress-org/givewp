<?php

namespace Give\Framework\Support\Actions;

/**
 * @since TBD
 */
class CreateAttachmentFromUpload
{
    /**
     * Creates a media library attachment from the result of wp_handle_upload().
     *
     * @since TBD
     *
     * @param array{file: string, url: string} $upload Successful result of wp_handle_upload().
     *
     * @return int The attachment ID, or 0 if the attachment could not be inserted.
     */
    public function __invoke(array $upload): int
    {
        $path = $upload['file'];

        // Check the type of file. We'll use this as the 'post_mime_type'.
        $filetype = wp_check_filetype(basename($path), null);

        $attachmentId = wp_insert_attachment([
            'guid' => $upload['url'],
            'post_mime_type' => $filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($path)),
            'post_content' => '',
            'post_status' => 'inherit',
        ], $path);

        // wp_generate_attachment_metadata() depends on this file.
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        wp_update_attachment_metadata($attachmentId, wp_generate_attachment_metadata($attachmentId, $path));

        return $attachmentId;
    }
}
