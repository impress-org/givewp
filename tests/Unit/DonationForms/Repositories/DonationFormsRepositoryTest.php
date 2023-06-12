<?php

namespace Give\Tests\Unit\DonationForms\Repositories;

use Give\DonationForms\V2\Endpoints\ListDonationForms;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give_Helper_Form;
use WP_REST_Request;

final class DonationFormsRepositoryTest extends TestCase
{

    public $testingForms = [];

    public function setUp()
    {
        parent::setUp();

        // Delete previous donation forms
        $posts = DB::prefix('posts');
        DB::query("TRUNCATE TABLE $posts");

        Give_Helper_Form::create_simple_form();
        Give_Helper_Form::create_simple_form();
    }

    public function tearDown()
    {
        parent::tearDown();

        $posts = DB::prefix('posts');
        DB::query("TRUNCATE TABLE $posts");
    }

    public function testListDonationForms()
    {
        $request = new WP_REST_Request();
        $request->set_param('page', 1);
        $request->set_param('search', '');
        $request->set_param('perPage', 30);
        $request->set_param('status', 'any');
        $request->set_param('return', 'model');

        $listDonationForms = new ListDonationForms();
        $response = $listDonationForms->handleRequest($request);
        $forms = $response->data['items'];

        $this->assertEquals(2, count($forms), 'Repository retrieves correct number of total forms');
    }

    public function testSearchRetrievesCorrectDonationForm()
    {
        Give_Helper_Form::create_simple_form(
            [
                'form' =>
                    [
                        'post_title' => 'My Simple Form'
                    ]
            ]
        );

        $request = new WP_REST_Request();
        $request->set_param('page', 1);
        $request->set_param('search', 'simple my');
        $request->set_param('perPage', 30);
        $request->set_param('status', 'any');
        $request->set_param('return', 'model');

        $listDonationForms = new ListDonationForms();
        $response = $listDonationForms->handleRequest($request);
        $forms = $response->data['items'];

        $this->assertEquals(1, count($forms), 'Search retrieves a single form');
        $this->assertEquals('My Simple Form', $forms[0]->title, 'Search retrieves correct form');
    }

}
