<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Actions\GetExternalEmbedScriptData;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * The skeletons map is the contract between the script URL's form id and what the embed draws
 * before the form page answers, so which forms get in and how ids are read are pinned here.
 *
 * @since TBD
 */
class GetExternalEmbedScriptDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testNoFormIdGivesAnEmptySkeletonMap()
    {
        $data = (new GetExternalEmbedScriptData())([]);

        $this->assertEquals((object)[], $data['skeletons']);
        $this->assertSame('{}', wp_json_encode($data['skeletons']));
    }

    /**
     * @since TBD
     */
    public function testPublishedFormsNamedByQueryOrPathGetTheirSkeleton()
    {
        $byQuery = DonationForm::factory()->create();
        $byPath = DonationForm::factory()->create();

        $skeletons = (array)(new GetExternalEmbedScriptData())([
            'form-id' => (string)$byQuery->id,
            'id' => $byPath->id,
        ])['skeletons'];

        $this->assertSame([$byQuery->id, $byPath->id], array_keys($skeletons));
        $this->assertStringContainsString('givewp-embed-skeleton--classic', $skeletons[$byQuery->id]);
    }

    /**
     * @since TBD
     */
    public function testDraftMissingAndMalformedIdsAreLeftOut()
    {
        $draft = DonationForm::factory()->create(['status' => DonationFormStatus::DRAFT()]);
        $published = DonationForm::factory()->create();

        $skeletons = (array)(new GetExternalEmbedScriptData())([
            'form-id' => "{$draft->id},999999,abc,-1,{$published->id}",
        ])['skeletons'];

        $this->assertSame([$published->id], array_keys($skeletons));
    }

    /**
     * @since TBD
     */
    public function testAtMostTenIdsAreHonored()
    {
        $ids = array_map(static function () {
            return DonationForm::factory()->create()->id;
        }, range(1, 11));

        $skeletons = (array)(new GetExternalEmbedScriptData())(['form-id' => implode(',', $ids)])['skeletons'];

        $this->assertCount(10, $skeletons);
        $this->assertArrayNotHasKey(end($ids), $skeletons);
    }
}
