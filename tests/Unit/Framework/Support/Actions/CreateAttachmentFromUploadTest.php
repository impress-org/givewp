<?php

namespace Give\Tests\Unit\Framework\Support\Actions;

use Give\Framework\Support\Actions\CreateAttachmentFromUpload;
use Give\Tests\TestCase;

final class CreateAttachmentFromUploadTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testItStoresTheRelativePathAndUsesTheUrlAsGuid()
    {
        $uploadDir = wp_upload_dir();
        $filename = wp_unique_filename($uploadDir['path'], 'attachment.txt');
        $file = $uploadDir['path'] . '/' . $filename;
        file_put_contents($file, 'contents');

        $upload = [
            'file' => $file,
            'url' => $uploadDir['url'] . '/' . $filename,
        ];

        $attachmentId = (new CreateAttachmentFromUpload())($upload);

        $expected = ltrim($uploadDir['subdir'] . '/' . $filename, '/');

        $this->assertSame($expected, get_post_meta($attachmentId, '_wp_attached_file', true));
        $this->assertSame($upload['url'], get_post($attachmentId)->guid);

        wp_delete_attachment($attachmentId, true);
    }
}
