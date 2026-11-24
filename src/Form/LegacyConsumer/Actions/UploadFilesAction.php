<?php

namespace Give\Form\LegacyConsumer\Actions;

use Give\Form\LegacyConsumer\Traits\HasFilesArray;
use Give\Framework\FieldsAPI\File;
use Give\Framework\Support\Actions\CreateAttachmentFromUpload;

use function wp_handle_upload;

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
     * @since TBD Pass the uploaded file path, not its URL, to the attachment functions.
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
                $attachmentId = (new CreateAttachmentFromUpload())($upload);

                $fileIds[] = $attachmentId;
            }
        }

        return $fileIds;
    }
}
