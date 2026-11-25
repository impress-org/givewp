<?php

namespace Give\DonorDashboards\Tabs\EditProfileTab;

use Give\DonorDashboards\Tabs\Contracts\Route as RouteAbstract;
use Give\Framework\Support\Actions\CreateAttachmentFromUpload;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 3.14.2 added security measure avatarBelongsToCurrentUser to handleRequest
 * @since 2.10.3
 */
class AvatarRoute extends RouteAbstract
{

    /**
     * @inheritdoc
     */
    public function endpoint()
    {
        return 'avatar';
    }

    /**
     * @inheritdoc
     */
    public function args()
    {
        return [];
    }

    /**
     * @since TBD Pass the uploaded file path, not its URL, to the attachment functions.
     *
     * @inheritDoc
     *
     * @return array|WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        if (!(is_array($_POST) && is_array($_FILES))) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- REST route; WordPress verifies the wp_rest nonce and the route checks the donor permission.
            return new WP_REST_Response(
                [
                    'status' => 400,
                    'response' => 'missing_files',
                    'body_response' => [
                        'message' => __('No files were included in request for upload.', 'give'),
                    ],
                ]
            );
        }

        // Delete existing Donor profile avatar attachment
        if (give()->donorDashboard->getAvatarId()) {
            if (!give()->donorDashboard->avatarBelongsToCurrentUser()) {
                return new WP_REST_Response(
                    [
                        'status' => 401,
                        'response' => 'unauthorized',
                        'body_response' => [
                            'message' => __('Permission denied.', 'give'),
                        ],
                    ]
                );
            }

            wp_delete_attachment(give()->donorDashboard->getAvatarId(), true);
        }


        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        foreach ($_FILES as $file) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- REST route; WordPress verifies the wp_rest nonce and the route checks the donor permission.
            $upload = wp_handle_upload(
                $file,
                [
                    'test_form' => false,
                ]
            );

            if (isset($upload['url'])) {
                $attachmentId = (new CreateAttachmentFromUpload())($upload);

                return [
                    'id' => $attachmentId,
                ];
            }
        }

        return new WP_REST_Response(
            [
                'status' => 404,
                'response' => 'not_found',
                'body_response' => [
                    'message' => __('No file with an upload URL was found', 'give'),
                ],
            ]
        );
    }
}
