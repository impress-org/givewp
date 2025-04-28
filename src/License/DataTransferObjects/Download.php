<?php

namespace Give\License\DataTransferObjects;

/**
 * @unreleased
 */
class Download
{
    public int $index;
    public int $attachmentId;
    public string $thumbnailSize;
    public string $name;
    public string $file;
    public string $condition;
    public int $arrayIndex;
    public string $pluginSlug;
    public string $readme;
    public string $currentVersion;

    /**
     * @unreleased
     */
    public static function fromData(array $data): self
    {
        $self = new self();
        $self->index = (int)$data['index'];
        $self->attachmentId = (int)$data['attachment_id'];
        $self->thumbnailSize = (string)$data['thumbnail_size'];
        $self->name = (string)$data['name'];
        $self->file = (string)$data['file'];
        $self->condition = (string)$data['condition'];
        $self->arrayIndex = (int)$data['array_index'];
        $self->pluginSlug = (string)$data['plugin_slug'];
        $self->readme = (string)$data['readme'];
        $self->currentVersion = (string)$data['current_version'];

        return $self;
    }
}

