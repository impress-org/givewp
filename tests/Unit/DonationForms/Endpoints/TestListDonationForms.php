<?php

namespace Give\Tests\Unit\DonationForms\Endpoints;

use Exception;
use Give\DonationForms\Repositories\DonationFormDataRepository;
use Give\DonationForms\V2\Endpoints\ListDonationForms;
use Give\DonationForms\V2\ListTable\DonationFormsListTable;
use Give\DonationForms\V2\Models\DonationForm;
use Give\Helpers\Language;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use WP_REST_Request;
use WP_REST_Server;

class TestListDonationForms extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    private $donationForms = [];

    public function setUp(): void
    {
        parent::setUp();

        $formLevelTypes = ['multi', 'simple'];
        for ( $count = 1; $count <= 5; $count++ ) {
            $formLevelType = $formLevelTypes[round(rand(0, 1))];
            $this->donationForms[] = $formLevelType === 'multi' ? $this->createMultiLevelDonationForm() : $this->createSimpleDonationForm();
        }
    }

    /**
     * @since 2.25.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnListWithSameSize()
    {
        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('status', 'any');

        $listDonationForms = new ListDonationForms();
        $response = $listDonationForms->handleRequest($mockRequest);

        $this->assertSameSize($this->donationForms, $response->data['items']);
    }

    /**
     * @since 2.25.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnListWithSameData()
    {
        $sortDirection = ['asc', 'desc'][round(rand(0, 1))];
        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('sortColumn', 'id');
        $mockRequest->set_param('sortDirection', $sortDirection);
        $mockRequest->set_param('status', 'any');

        $expectedItems = $this->getMockColumns($this->donationForms, $sortDirection);

        $listDonationForms = new ListDonationForms();
        $response = $listDonationForms->handleRequest($mockRequest);

        $this->assertSame($expectedItems, $response->data['items']);
    }

    /**
     * @since 2.25.0
     *
     * @return WP_REST_Request
     */
    public function getMockRequest(): WP_REST_Request
    {
        return new WP_REST_Request(
            WP_REST_Server::READABLE,
            '/wp/v2/admin/forms'
        );
    }

    /**
     * @since 4.0.0 Add support to isDefaultCampaignForm key
     * @since 2.25.0
     *
     * @param DonationForm[]  $donationForms
     * @param string $sortDirection
     *
     * @return array
     */
    public function getMockColumns(array $donationForms, string $sortDirection = 'desc'): array
    {
        $formsData = DonationFormDataRepository::forms($donationForms);

        $columns = (new DonationFormsListTable())
            ->setData($formsData)
            ->getColumns();

        $expectedItems = [];
        foreach ( $donationForms as $donationForm ) {
            $expectedItem = [];
            foreach ( $columns as $column ) {
                $expectedItem[$column::getId()] = $column->getCellValue($donationForm, 'en-US');
            }
            $expectedItem['name'] = $donationForm->title;
            $expectedItem['edit'] = add_query_arg(['locale' => Language::getLocale()],
                get_edit_post_link($donationForm->id, 'edit'));
            $expectedItem['permalink'] = get_permalink($donationForm->id);

            $expectedItem['v3form'] = false;
            $expectedItem['status_raw'] = $donationForm->status->getValue();

            $expectedItem['isDefaultCampaignForm'] = false;

            $expectedItems[] = $expectedItem;
        }

        if ($sortDirection === 'desc') {
            $expectedItems = array_reverse($expectedItems);
        }

        return $expectedItems;
    }
}

