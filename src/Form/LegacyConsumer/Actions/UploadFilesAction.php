<?php

namespace Give\Form\LegacyConsumer\Actions;

use Give\Form\LegacyConsumer\Traits\HasFilesArray;
use Give\Framework\FieldsAPI\File;

use function wp_check_filetype;
use function wp_generate_attachment_metadata;
use function wp_handle_upload;
use function wp_insert_attachment;
use function wp_update_attachment_metadata;

/**
 * @package Give\Form\LegacyConsumer\Commands
 *
 * @since 2.14.0
 */
class UploadFilesAction
{
    use HasFilesArray;

    /**
     * @var array
     */
    private $files;

    /**
     * @var File
     */
    private $field;

    /**
     * @since 2.14.0
     */
    public function __construct(File $field)
    {
        $this->field = $field;
        $this->files = $this->getFiles();
    }

    /**
     * @since 2.14.0
     */
    public function __invoke()
    {
        if ( ! function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $fileIds = [];
        foreach ($this->files as $file) {
            $upload = wp_handle_upload($file, ['test_form' => false,]);

            if (isset($upload['url'])) {
                $path = $upload['url'];

                // Check the type of file. We'll use this as the 'post_mime_type'.
                $filetype = wp_check_filetype(basename($path), null);

                // Prepare an array of post data for the attachment.
                $attachment = [
                    'guid' => $path,
                    'post_mime_type' => $filetype['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($path)),
                    'post_content' => '',
                    'post_status' => 'inherit',
                ];

                // Insert the attachment.
                $attachmentId = wp_insert_attachment($attachment, $path);

                // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                require_once(ABSPATH . 'wp-admin/includes/image.php');

                // Generate the metadata for the attachment, and update the database record.
                $attachmentData = wp_generate_attachment_metadata($attachmentId, $path);
                wp_update_attachment_metadata($attachmentId, $attachmentData);

                $fileIds[] = $attachmentId;
            }
        }

        return $fileIds;
    }
}
