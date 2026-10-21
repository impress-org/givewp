<?php

namespace Give\Tests\Unit\DonationForms\Controllers;

use Give\DonationForms\Controllers\DonationFormViewController;
use Give\DonationForms\DataTransferObjects\DonationFormPreviewRouteData;
use Give\DonationForms\DataTransferObjects\DonationFormViewRouteData;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use RuntimeException;
use WPDieException;

/**
 * @since TBD
 */
class DonationFormViewControllerTest extends TestCase
{
    use RefreshDatabase;

    private const DESIGN_SYSTEM_VERSION_FILE = GIVE_PLUGIN_DIR . 'build/assets/dist/css/design-system/version';

    /**
     * @var bool Whether setUp() wrote the design system version file.
     */
    private $createdVersionFile = false;

    /**
     * The form render reads the design system version from the asset build, which the PHP test
     * workflows do not run. A placeholder is written only when no build is present.
     *
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!file_exists(self::DESIGN_SYSTEM_VERSION_FILE)) {
            wp_mkdir_p(dirname(self::DESIGN_SYSTEM_VERSION_FILE));
            file_put_contents(self::DESIGN_SYSTEM_VERSION_FILE, '0.0.0');
            $this->createdVersionFile = true;
        }
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        wp_set_current_user(0);

        if ($this->createdVersionFile) {
            unlink(self::DESIGN_SYSTEM_VERSION_FILE);
        }

        parent::tearDown();
    }

    /**
     * @dataProvider unpublishedStatusProvider
     *
     * @since TBD
     */
    public function testAnonymousRequestsCannotViewUnpublishedForms(DonationFormStatus $status): void
    {
        $form = DonationForm::factory()->create(['status' => $status]);

        wp_set_current_user(0);

        $this->assertNotFound(fn() => $this->renderShow($form->id));
        $this->assertNotFound(fn() => $this->renderPreview($form->id));
    }

    /**
     * @since TBD
     */
    public function testAnonymousRequestsCanViewPublishedForms(): void
    {
        $form = DonationForm::factory()->create(['status' => DonationFormStatus::PUBLISHED()]);

        wp_set_current_user(0);

        $this->assertTrue($this->renderShow($form->id));
        $this->assertTrue($this->renderPreview($form->id));
    }

    /**
     * @since TBD
     */
    public function testUsersWhoCanEditFormsCanViewUnpublishedForms(): void
    {
        $form = DonationForm::factory()->create(['status' => DonationFormStatus::DRAFT()]);

        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));

        $this->assertTrue($this->renderShow($form->id));
        $this->assertTrue($this->renderPreview($form->id));
    }

    /**
     * @since TBD
     */
    public function unpublishedStatusProvider(): array
    {
        return [
            'draft' => [DonationFormStatus::DRAFT()],
            'pending' => [DonationFormStatus::PENDING()],
            'private' => [DonationFormStatus::PRIVATE()],
            'scheduled' => [DonationFormStatus::FUTURE()],
        ];
    }

    /**
     * @since TBD
     */
    private function assertNotFound(callable $render): void
    {
        try {
            $render();
            $this->fail('Expected the route to refuse an unpublished form.');
        } catch (WPDieException $exception) {
            $this->assertSame(404, $exception->getCode());
        }
    }

    /**
     * @since TBD
     */
    private function renderShow(int $formId): bool
    {
        $data = DonationFormViewRouteData::fromRequest(['form-id' => (string)$formId]);

        return $this->render(fn() => (new DonationFormViewController())->show($data));
    }

    /**
     * @since TBD
     */
    private function renderPreview(int $formId): bool
    {
        $data = DonationFormPreviewRouteData::fromRequest(['form-id' => (string)$formId]);

        return $this->render(fn() => (new DonationFormViewController())->preview($data));
    }

    /**
     * DonationFormViewModel::render() ends with exit(), so the render is stopped at the first
     * filter it applies. Reaching that filter means the controller accepted the form. The
     * controller calls ob_clean(), which needs an open buffer, so one is opened here and every
     * buffer still open afterwards is closed.
     *
     * @since TBD
     *
     * @return bool Whether the controller started rendering the form.
     */
    private function render(callable $callback): bool
    {
        $marker = new RuntimeException('The form view started rendering.');
        $stopRender = static function () use ($marker) {
            throw $marker;
        };

        add_filter('givewp_donation_form_view_allowed_asset_handles', $stopRender);
        $level = ob_get_level();
        ob_start();

        try {
            $callback();
        } catch (RuntimeException $exception) {
            if ($exception !== $marker) {
                throw $exception;
            }

            return true;
        } finally {
            remove_filter('givewp_donation_form_view_allowed_asset_handles', $stopRender);

            while (ob_get_level() > $level) {
                ob_end_clean();
            }
        }

        return false;
    }
}
