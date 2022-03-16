<?php

use Give\DonationForms\Repositories\DonationFormsRepository as DonationFormsRepository;

final class DonationFormsTest extends Give_Unit_Test_Case {

    public $testingForms = [];

    public function setUp()
    {
        parent::setUp();
        $this->testingForms[] = Give_Helper_Form::create_simple_form();
        $this->testingForms[] = Give_Helper_Form::create_simple_form();
    }

    public function tearDown()
    {
        parent::tearDown();
        foreach ( $this->testingForms as $form )
        {
            Give_Helper_Form::delete_form( $form->id );
        }
        $this->testingForms = [];
    }

    public function testListDonationForms()
    {
        $request = new WP_REST_Request();
        $request->set_param('page', 1);
        $request->set_param('search', '');
        $request->set_param('perPage', 30);
        $request->set_param('status', 'any');

        $class = new ReflectionClass(DonationFormsRepository::class);
        $getFormsForRequest = $class->getMethod('getFormsForRequest');
        $getFormsForRequest->setAccessible(true);
        $forms = $getFormsForRequest->invokeArgs(new DonationFormsRepository, [$request]);
        $this->assertEquals(2, count($forms), 'Repository retrieves correct number of total forms');
    }

    public function testSearchRetrievesCorrectDonationForm(){
        $this->testingForms[] = Give_Helper_Form::create_simple_form(
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

        $class = new ReflectionClass(DonationFormsRepository::class);
        $getFormsForRequest = $class->getMethod('getFormsForRequest');
        $getFormsForRequest->setAccessible(true);
        $forms = $getFormsForRequest->invokeArgs(new DonationFormsRepository, [$request]);
        $this->assertEquals(1, count($forms), 'Search retrieves a single form');
        $this->assertEquals('My Simple Form', $forms[0]->title, 'Search retrieves correct form');
    }

}
