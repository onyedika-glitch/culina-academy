<?php

namespace Tests\Unit\Services;

use Tests\Support\ExposedLegacyAcfSyncService as Sync;
use Tests\TestCase;

class LegacyAcfSyncParsersTest extends TestCase
{
    public function test_parse_faq_questions_splits_bold_paragraphs_into_question_answer_pairs(): void
    {
        $html = '<p><strong>How long is the course?</strong></p><p>Twelve weeks, full time.</p>'
            .'<p><strong>Do I need experience?</strong></p><p>No, beginners are welcome.</p><p>Bring an apron.</p>';

        $questions = Sync::call('parseFaqQuestions', $html);

        $this->assertCount(2, $questions);
        $this->assertSame('How long is the course?', $questions[0]['question']);
        $this->assertSame('<p>Twelve weeks, full time.</p>', $questions[0]['answer']);
        $this->assertSame('Do I need experience?', $questions[1]['question']);
        $this->assertStringContainsString('Bring an apron.', $questions[1]['answer']);
    }

    public function test_parse_faq_questions_decodes_entities_and_skips_empty_answers(): void
    {
        $html = '<p><strong>Fees &amp; payment?</strong></p><p>Paid in instalments.</p><p><strong>Orphan?</strong></p>';

        $questions = Sync::call('parseFaqQuestions', $html);

        $this->assertSame([['question' => 'Fees & payment?', 'answer' => '<p>Paid in instalments.</p>']], $questions);
    }

    public function test_parse_faq_questions_returns_empty_array_without_bold_headings(): void
    {
        $this->assertSame([], Sync::call('parseFaqQuestions', '<p>Just a paragraph.</p>'));
    }

    public function test_parse_careers_cards_reads_image_title_and_text_triplets(): void
    {
        $html = '<p><img src="a.jpg" alt="Chef"></p><p>Head Chef,</p><p>Lead our kitchen team.</p>'
            .'<p><img src="b.jpg" alt="Tutor"></p><p>Pastry Tutor</p><p>Teach patisserie classes.</p>';

        $cards = Sync::call('parseCareersCards', $html);

        $this->assertSame([
            ['image' => '', 'title' => 'Head Chef', 'text' => 'Lead our kitchen team.'],
            ['image' => '', 'title' => 'Pastry Tutor', 'text' => 'Teach patisserie classes.'],
        ], $cards);
    }

    public function test_parse_careers_cards_ignores_contact_footer_blocks(): void
    {
        $html = '<p><img src="x.jpg" alt=""></p><p>Registered Office</p><p>1 High Street</p>'
            .'<p><img src="y.jpg" alt=""></p><p>Phone: 0100</p><p>Call us</p>';

        $this->assertSame([], Sync::call('parseCareersCards', $html));
    }

    public function test_extract_paragraphs_strips_tags_and_drops_print_email_links(): void
    {
        $html = '<p>First <em>line</em>.</p><p>Print</p><p> </p><p>Email</p><p>Caf&eacute; culture.</p>';

        $this->assertSame(['First line.', 'Café culture.'], Sync::call('extractParagraphs', $html));
    }

    public function test_extract_entry_content_prefers_the_entry_content_section(): void
    {
        $html = '<html><body><section class="post entry-content clearfix"> <p>Body</p> </section></body></html>';

        $this->assertSame('<p>Body</p>', Sync::call('extractEntryContent', $html));
    }

    public function test_extract_entry_content_returns_empty_string_for_unrecognised_markup(): void
    {
        $this->assertSame('', Sync::call('extractEntryContent', '<div>No article here</div>'));
    }

    public function test_repeaters_equal_compares_restaurant_fields_and_star_counts_as_integers(): void
    {
        $row = ['name' => 'Bistro', 'address' => '1 Road', 'michelin' => 1, 'aa' => '3',
            'map_url' => '', 'website_url' => 'https://bistro.test', 'website_label' => 'bistro.test'];

        $this->assertTrue(Sync::call('repeatersEqual', [$row], [array_merge($row, ['aa' => 3, 'name' => ' Bistro '])]));
        $this->assertFalse(Sync::call('repeatersEqual', [$row], [array_merge($row, ['michelin' => 2])]));
        $this->assertFalse(Sync::call('repeatersEqual', [$row], []));
    }

    public function test_extract_restaurant_address_keeps_address_lines_only(): void
    {
        $content = '12 Market Lane<br>Your City<br>Michelin Stars: <img src="michelin_rose.gif"><br>'
            .'<a href="https://maps.test">View map</a><br><a href="https://r.test">www.r.test</a>';

        $this->assertSame("12 Market Lane\nYour City", Sync::call('extractRestaurantAddress', $content, 'Fallback'));
    }

    public function test_extract_restaurant_address_falls_back_to_the_name(): void
    {
        $this->assertSame('The Grill', Sync::call('extractRestaurantAddress', '<a href="x">View map</a>', 'The Grill'));
    }

    public function test_parse_restaurant_articles_reads_name_awards_and_links(): void
    {
        $html = '<article><h2 class="entry-title"><a href="/r">The Grill</a></h2>'
            .'<section class="entry-content">5 Quay St<br>'
            .'<img src="/img/michelin_rose.gif"><img src="/img/aa_rose.gif"><img src="/img/aa_rose.gif"><br>'
            .'<a href="https://www.thegrill.test/">www.thegrill.test</a></section></article>'
            .'<article><p>No title, skipped</p></article>';

        $restaurants = Sync::call('parseRestaurantArticles', $html);

        $this->assertCount(1, $restaurants);
        $this->assertSame('The Grill', $restaurants[0]['name']);
        $this->assertSame(1, $restaurants[0]['michelin']);
        $this->assertSame(2, $restaurants[0]['aa']);
        $this->assertSame('https://www.thegrill.test/', $restaurants[0]['website_url']);
        $this->assertSame('www.thegrill.test', $restaurants[0]['website_label']);
        $this->assertSame('5 Quay St', $restaurants[0]['address']);
    }

    public function test_clean_legacy_wysiwyg_removes_scripts_and_email_cloaking(): void
    {
        $html = '<p>Write to <span id="cloak1234">obfuscated</span>.</p><script>alert(1)</script>'
            .'</article><div>sidebar</div>';

        $clean = Sync::call('cleanLegacyWysiwyg', $html);

        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('sidebar', $clean);
        $this->assertStringContainsString('enrolment@example.com', $clean);
    }
}
