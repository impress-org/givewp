<?php

namespace Give\Tests\Unit\License\DataTransferObjects;

use Give\License\DataTransferObjects\Download;
use Give\Tests\TestCase;

/**
 * @since 4.3.0
 */
class TestDownload extends TestCase
{
    /**
     * @since 4.3.0
     */
    public function testFromDataReturnsDownloadObject(): void
    {
        $data = [
            "index" => "0",
            "attachment_id" => "0",
            "thumbnail_size" => "",
            "name" => "Manual Donations",
            "file" => "https://givewp.com",
            "condition" => "all",
            "array_index" => 1,
            "plugin_slug" => "give-manual-donations",
            "readme" => "https://givewp.com/downloads/plugins/give-manual-donations/readme.txt",
            "current_version" => "1.8.0",
        ];

        $download = Download::fromData($data);

        $this->assertSame(0, $download->index);
        $this->assertSame(0, $download->attachmentId);
        $this->assertSame('', $download->thumbnailSize);
        $this->assertSame('Manual Donations', $download->name);
        $this->assertSame('https://givewp.com', $download->file);
        $this->assertSame('all', $download->condition);
        $this->assertSame(1, $download->arrayIndex);
        $this->assertSame('give-manual-donations', $download->pluginSlug);
        $this->assertSame('https://givewp.com/downloads/plugins/give-manual-donations/readme.txt', $download->readme);
        $this->assertSame('1.8.0', $download->currentVersion);
    }
}
