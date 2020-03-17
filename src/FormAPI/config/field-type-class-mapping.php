<?php

use Give\FormAPI\Form\Colorpicker;
use Give\FormAPI\Form\Media;
use Give\FormAPI\Form\Radio;
use Give\FormAPI\Form\Text;
use Give\FormAPI\Form\Textarea;
use Give\FormAPI\Form\File;
use Give\FormAPI\Form\Wysiwyg;

return [
	'text'        => Text::class,
	'textarea'    => Textarea::class,
	'file'        => File::class,
	'media'       => Media::class,
	'radio'       => Radio::class,
	'wysiwyg'     => Wysiwyg::class,
	'colorpicker' => Colorpicker::class,
];
