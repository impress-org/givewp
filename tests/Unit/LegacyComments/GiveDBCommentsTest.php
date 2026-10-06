<?php

namespace Give\Tests\Unit\LegacyComments;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Comment;
use Give_DB_Comments;

/**
 * Covers the queries of Give_DB_Comments and Give_Comment.
 *
 * The tests for a bad order value and a comment type with a quote were added after the SQL fix,
 * because they check the new hardening of get_sql(). Every other test was written first and passed
 * on the unchanged code. This includes the unknown fields test, which pins the allowlist that
 * validate_params() already had.
 *
 * @since TBD
 */
class GiveDBCommentsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    private function db(): Give_DB_Comments
    {
        return Give()->comment->db;
    }

    /**
     * @since TBD
     */
    private function addNote(int $parent, string $content, string $type): int
    {
        $id = Give_Comment::add(
            [
                'comment_parent' => $parent,
                'comment_content' => $content,
                'comment_type' => $type,
            ]
        );

        $this->assertGreaterThan(0, $id);

        return (int)$id;
    }

    /**
     * @since TBD
     */
    public function testGetCommentsReturnsOnlyTheRequestedType()
    {
        $donationNoteId = $this->addNote(900001, 'Donation note', 'donation');
        $this->addNote(900001, 'Donor note', 'donor');

        $comments = $this->db()->get_comments(['comment_parent' => 900001, 'comment_type' => 'donation']);

        $this->assertCount(1, $comments);
        $this->assertSame($donationNoteId, (int)$comments[0]->comment_ID);
    }

    /**
     * @since TBD
     */
    public function testGetCommentsAcceptsACommaSeparatedListOfTypes()
    {
        $this->addNote(900002, 'Donation note', 'donation');
        $this->addNote(900002, 'Donor note', 'donor');
        $this->addNote(900002, 'General note', 'general');

        $comments = $this->db()->get_comments(['comment_parent' => 900002, 'comment_type' => 'donation, donor']);

        $this->assertCount(2, $comments);
    }

    /**
     * @since TBD
     */
    public function testCountReturnsTheNumberOfMatchingComments()
    {
        $this->addNote(900003, 'One', 'donation');
        $this->addNote(900003, 'Two', 'donation');
        $this->addNote(900003, 'Three', 'donor');

        $this->assertSame(2, $this->db()->count(['comment_parent' => 900003, 'comment_type' => 'donation']));
        $this->assertSame(3, $this->db()->count(['comment_parent' => 900003]));
    }

    /**
     * @since TBD
     */
    public function testGetCommentByIdReturnsTheRow()
    {
        $id = $this->addNote(900004, 'Find me', 'donation');

        $comment = $this->db()->get_comment_by($id);

        $this->assertSame('Find me', $comment['comment_content']);
        $this->assertNull($this->db()->get_comment_by(0));
    }

    /**
     * @since TBD
     */
    public function testGiveCommentGetReturnsTheNotesOfAParent()
    {
        $this->addNote(900005, 'Donation note', 'donation');
        $this->addNote(900005, 'Donor note', 'donor');

        $comments = Give_Comment::get(['comment_parent' => 900005, 'comment_type' => 'donor']);

        $this->assertCount(1, $comments);
        $this->assertSame('Donor note', $comments[0]->comment_content);
    }

    /**
     * @since TBD
     */
    public function testWordPressCommentCountsRunWithoutErrors()
    {
        global $wpdb;

        $this->addNote(900009, 'Not counted', 'donation');

        $stats = Give()->comment->remove_comments_from_comment_counts([], 0);

        $this->assertSame('', $wpdb->last_error);
        $this->assertIsObject($stats);
    }

    /**
     * @since TBD
     */
    public function testOrderCanBeAscendingOrDescending()
    {
        $first = $this->addNote(900006, 'First', 'donation');
        $second = $this->addNote(900006, 'Second', 'donation');
        $args = ['comment_parent' => 900006, 'orderby' => 'comment_ID'];

        $ascending = $this->db()->get_comments($args + ['order' => 'asc']);
        $descending = $this->db()->get_comments($args + ['order' => 'DESC']);

        $this->assertSame([$first, $second], array_map('intval', wp_list_pluck($ascending, 'comment_ID')));
        $this->assertSame([$second, $first], array_map('intval', wp_list_pluck($descending, 'comment_ID')));
    }

    /**
     * @since TBD
     */
    public function testGetSqlReturnsTheExpectedQueryForNormalInput()
    {
        global $wpdb;

        $sql = $this->db()->get_sql(
            ['comment_parent' => 5, 'comment_type' => 'donation', 'number' => 10, 'offset' => 20]
        );

        $this->assertSame(
            "SELECT {$wpdb->give_comments}.* FROM {$wpdb->give_comments}  WHERE 1=1  AND {$wpdb->give_comments}.comment_parent IN( 5 )  AND {$wpdb->give_comments}.comment_type IN( 'donation' )  ORDER BY {$wpdb->give_comments}.comment_date DESC LIMIT 20,10;",
            $wpdb->remove_placeholder_escape($sql)
        );
    }

    /**
     * Added after the SQL fix.
     *
     * @since TBD
     */
    public function testUnknownFieldsFallBackToAllColumns()
    {
        global $wpdb;

        $sql = $this->db()->get_sql(['fields' => 'comment_ID FROM wp_users; --']);

        $this->assertStringStartsWith("SELECT {$wpdb->give_comments}.* FROM", $sql);
    }

    /**
     * Added after the SQL fix.
     *
     * @since TBD
     */
    public function testBadOrderFallsBackToTheDefault()
    {
        global $wpdb;

        $this->addNote(900007, 'Note', 'donation');

        $sql = $this->db()->get_sql(['comment_parent' => 900007, 'order' => 'ASC; DROP TABLE x']);

        $this->assertStringContainsString('comment_date DESC LIMIT', $sql);
        $this->assertNotEmpty($this->db()->get_comments(['comment_parent' => 900007, 'order' => 'ASC; DROP TABLE x']));
        $this->assertSame('', $wpdb->last_error);
    }

    /**
     * Added after the SQL fix.
     *
     * @since TBD
     */
    public function testCommentTypeWithAQuoteDoesNotBreakTheQuery()
    {
        global $wpdb;

        $this->addNote(900008, 'Note', 'donation');

        $comments = $this->db()->get_comments(['comment_parent' => 900008, 'comment_type' => "don'ation"]);

        $this->assertSame([], $comments);
        $this->assertSame('', $wpdb->last_error);
    }
}
