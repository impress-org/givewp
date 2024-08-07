<?php

namespace Give\DonationSpam\Akismet\DataTransferObjects;

use Give\DonationForms\DataTransferObjects\DonateControllerData;

/**
 * @unreleased
 */
class CommentCheckArgs
{
    public $blog;
    public $blog_lang;
    public $blog_charset;
    public $user_ip;
    public $user_agent;
    public $referrer;
    public $comment_type;
    public $comment_content;
    public $comment_author;
    public $comment_author_email;

    /**
     * @unreleased
     */
    public static function make(DonateControllerData $data): CommentCheckArgs
    {
        $self = new self();

        $self->comment_type = 'contact-form';
        $self->comment_content = $data->comment;
        $self->comment_author = $data->firstName;
        $self->comment_author_email = $data->email;

        $self->blog = get_option('home');
        $self->blog_lang = get_locale();
        $self->blog_charset = get_option('blog_charset');

        $self->user_ip = @$_SERVER['REMOTE_ADDR'];
        $self->user_agent = @$_SERVER['HTTP_USER_AGENT'];
        $self->referrer = @$_SERVER['HTTP_REFERER'];

        // Append additional server variables.
        foreach ( $_SERVER as $key => $value ) {
            if ( ! in_array( $key, [ 'HTTP_COOKIE', 'HTTP_COOKIE2', 'PHP_AUTH_PW' ], true ) ) {
                $self->$key = $value;
            }
        }

        return $self;
    }

    /**
     * @unreleased
     */
    public function toHttpQuery(): string
    {
        return http_build_query(get_object_vars($this));
    }
}
