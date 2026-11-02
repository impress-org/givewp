<?php

namespace Give\Tests\Unit\Framework\ListTable\FilterOptions;

use Give\Campaigns\Models\Campaign;
use Give\Donors\Models\Donor;
use Give\Framework\ListTable\FilterOptions\FilterOptionSource;
use Give\Framework\ListTable\FilterOptions\FilterOptionSources;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
class FilterOptionQueriesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->refreshDatabase();
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
    }

    /**
     * @since TBD
     *
     * @return array<string, array{0: string}>
     */
    public function sources(): array
    {
        return ['campaign' => ['campaign'], 'form' => ['form'], 'donor' => ['donor']];
    }

    /**
     * @dataProvider sources
     *
     * @since TBD
     */
    public function testReturnsTwentyOptionsAndHasMore(string $source): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->seed($source, sprintf('Zed %02d', $i));
        }

        $result = $this->source($source)->search('Zed', 1);

        $this->assertCount(FilterOptionSource::PER_PAGE, $result['options']);
        $this->assertTrue($result['hasMore']);
    }

    /**
     * @dataProvider sources
     *
     * @since TBD
     */
    public function testSecondPageReturnsTheRest(string $source): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->seed($source, sprintf('Zed %02d', $i));
        }

        $result = $this->source($source)->search('Zed', 2);

        $this->assertSame(
            ['Zed 21', 'Zed 22', 'Zed 23', 'Zed 24', 'Zed 25'],
            array_column($result['options'], 'label')
        );
        $this->assertFalse($result['hasMore']);
    }

    /**
     * @dataProvider sources
     *
     * @since TBD
     */
    public function testOrderedByLabelThenId(string $source): void
    {
        $second = $this->seed($source, 'Zed B');
        $firstOfTwins = $this->seed($source, 'Zed A');
        $secondOfTwins = $this->seed($source, 'Zed A');

        $result = $this->source($source)->search('Zed', 1);

        $this->assertSame(
            [(string)$firstOfTwins, (string)$secondOfTwins, (string)$second],
            array_column($result['options'], 'value')
        );
    }

    /**
     * @dataProvider sources
     *
     * @since TBD
     */
    public function testSearchMatchesTheStartOfAnyWord(string $source): void
    {
        $this->seed($source, 'Spring appeal');
        $this->seed($source, 'Appeal for all');
        $this->seed($source, 'Happy days');

        $result = $this->source($source)->search('app', 1);

        $this->assertSame(['Appeal for all', 'Spring appeal'], array_column($result['options'], 'label'));
    }

    /**
     * @dataProvider sources
     *
     * @since TBD
     */
    public function testSearchEscapesLikeWildcards(string $source): void
    {
        $this->seed($source, '100% match');
        $this->seed($source, '1000 matches');
        $this->seed($source, 'a_b');
        $this->seed($source, 'axb');

        $this->assertSame(['100% match'], array_column($this->source($source)->search('100%', 1)['options'], 'label'));
        $this->assertSame(['a_b'], array_column($this->source($source)->search('a_', 1)['options'], 'label'));
    }

    /**
     * @dataProvider sources
     *
     * @since TBD
     */
    public function testLabelIsDecodedPlainText(string $source): void
    {
        $this->seed($source, 'Kids &amp; Co');

        $result = $this->source($source)->search('Kids', 1);

        $this->assertSame(['Kids & Co'], array_column($result['options'], 'label'));
    }

    /**
     * @dataProvider sources
     *
     * @since TBD
     */
    public function testIncludeReturnsKnownIdsOnly(string $source): void
    {
        $first = $this->seed($source, 'Zed One');
        $this->seed($source, 'Zed Two');
        $third = $this->seed($source, 'Zed Three');

        $options = $this->source($source)->labels([$first, $third, 999999]);

        $this->assertSame(
            [
                ['value' => (string)$first, 'label' => 'Zed One'],
                ['value' => (string)$third, 'label' => 'Zed Three'],
            ],
            $options
        );
        $this->assertSame([], $this->source($source)->labels([]));
    }

    /**
     * @dataProvider sources
     *
     * @since TBD
     */
    public function testValuesAreStringIds(string $source): void
    {
        $id = $this->seed($source, 'Zed One');

        $options = $this->source($source)->search('Zed', 1)['options'];

        $this->assertSame([['value' => (string)$id, 'label' => 'Zed One']], $options);
    }

    /**
     * @since TBD
     */
    public function testDonorOptionsNeverContainEmail(): void
    {
        Donor::factory()->create(['name' => 'Jane Doe', 'email' => 'jane.doe@example.com']);
        $source = $this->source('donor');

        $found = $source->search('Jane', 1);

        $this->assertSame(['Jane Doe'], array_column($found['options'], 'label'));
        $this->assertStringNotContainsString('@', (string)wp_json_encode($found));
        $this->assertSame([], $source->search('jane.doe', 1)['options']);
        $this->assertSame([], $source->search('example.com', 1)['options']);
        $this->assertStringNotContainsString('@', (string)wp_json_encode($source->labels([1, 2, 3])));
    }

    /**
     * @since TBD
     */
    public function testDraftFormLabelHasTheDraftSuffix(): void
    {
        $this->seed('form', 'Zed Live');
        $draft = $this->seedForm('Zed Draft', 'draft');
        $this->seedForm('Zed Trashed', 'trash');

        $result = $this->source('form')->search('Zed', 1);

        $this->assertSame(
            [
                ['value' => (string)$draft, 'label' => 'Zed Draft (Draft)'],
                ['value' => (string)get_posts(['post_type' => 'give_forms', 'title' => 'Zed Live', 'fields' => 'ids'])[0], 'label' => 'Zed Live'],
            ],
            $result['options']
        );
    }

    /**
     * @since TBD
     */
    private function source(string $source): FilterOptionSource
    {
        return give(FilterOptionSources::class)->get($source);
    }

    /**
     * @since TBD
     */
    private function seed(string $source, string $label): int
    {
        if ($source === 'campaign') {
            return Campaign::factory()->create(['title' => $label])->id;
        }

        if ($source === 'form') {
            return $this->seedForm($label, 'publish');
        }

        return Donor::factory()->create(['name' => $label])->id;
    }

    /**
     * @since TBD
     */
    private function seedForm(string $title, string $status): int
    {
        return (int)wp_insert_post([
            'post_type' => 'give_forms',
            'post_status' => $status,
            'post_title' => $title,
        ]);
    }
}
