<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

function create_testdata_basic() {
    return array(
        create_event_data( 'A', '2020-01-01' ),
        create_event_data( 'B', '2020-01-01' ),
        create_event_data( 'C', '2020-01-02' ),
        create_event_data( 'D', '2020-02-02' ),
    );
}

function create_testdata_multiday() {
    return array(
        create_event_data( 'A', '2020-01-01' ),
        create_event_data( 'B4', '2020-01-01', '2020-01-05' ),
        create_event_data( 'C3', '2020-01-02', '2020-01-04', '12:00:00' ),
        create_event_data( 'C1', '2020-01-02', '2020-01-02', '13:00:00' ),
        create_event_data( 'D1', '2020-01-04' ),
        create_event_data( 'E', '2020-02-02' ),
    );
}

/**
 * Tests for event iterator.
 */
final class Comcal_Event_Iterator_Test extends TestCase {

    public function test_basic_event_iterator() {
        $iterator = new Comcal_Event_Iterator( create_testdata_basic() );

        $this->assertTrue( $iterator->valid() );
        $e = $iterator->current();
        $this->assertEquals( 'A', $e->get_field( 'title' ) );

        $iterator->next();
        $this->assertTrue( $iterator->valid() );
        $e = $iterator->current();
        $this->assertEquals( 'B', $e->get_field( 'title' ) );

        $iterator->next();
        $this->assertTrue( $iterator->valid() );
        $e = $iterator->current();
        $this->assertEquals( 'C', $e->get_field( 'title' ) );

        $iterator->next();
        $this->assertTrue( $iterator->valid() );
        $e = $iterator->current();
        $this->assertEquals( 'D', $e->get_field( 'title' ) );

        $iterator->next();
        $this->assertFalse( $iterator->valid() );
    }

    public function test_prevent_duplicates() {
        $test_data   = create_testdata_basic();
        $test_data[] = $test_data[0];
        $iterator    = new Comcal_Event_Iterator( $test_data );

        $this->assertEquals( 4, iterator_count( $iterator ) );

    }

    public function test_multiday_event_iterator() {
        $iterator = new Comcal_Multiday_Event_Iterator( new Comcal_Event_Iterator( create_testdata_multiday() ) );

        $iterator->rewind();

        // 1.1.
        $this->assertTrue( $iterator->valid() );
        list( $e, $day ) = $iterator->current();
        $this->assertEquals( 'A', $e->get_field( 'title' ) );
        $this->assertEquals( 0, $day );

        $iterator->next();
        $this->assertTrue( $iterator->valid() );
        list( $e, $day ) = $iterator->current();
        $this->assertEquals( 'B4', $e->get_field( 'title' ) );
        $this->assertEquals( 0, $day );

        // 2.1.
        $iterator->next();
        $this->assertTrue( $iterator->valid() );
        list( $e, $day ) = $iterator->current();
        $this->assertEquals( 'C3', $e->get_field( 'title' ) );
        $this->assertEquals( 0, $day );

        $iterator->next();
        $this->assertTrue( $iterator->valid() );
        list( $e, $day ) = $iterator->current();
        $this->assertEquals( 'C1', $e->get_field( 'title' ) );
        $this->assertEquals( 0, $day );

        $iterator->next();
        $this->assertTrue( $iterator->valid() );
        list( $e, $day ) = $iterator->current();
        $this->assertEquals( 'B4', $e->get_field( 'title' ) );
        $this->assertEquals( 1, $day );

        // 3.1.
        $iterator->next();
        $this->assertTrue( $iterator->valid() );
        list( $e, $day ) = $iterator->current();
        $this->assertEquals( 'C3', $e->get_field( 'title' ) );
        $this->assertEquals( 1, $day );

        $iterator->next();
        $this->assertTrue( $iterator->valid() );
        list( $e, $day ) = $iterator->current();
        $this->assertEquals( 'B4', $e->get_field( 'title' ) );
        $this->assertEquals( 2, $day );

        // 4.1.
        $iterator->next();
        $this->assertTrue( $iterator->valid() );
        list( $e, $day ) = $iterator->current();
        $this->assertEquals( 'D1', $e->get_field( 'title' ) );
        $this->assertEquals( 0, $day );

        $iterator->next();
        $this->assertTrue( $iterator->valid() );
        list( $e, $day ) = $iterator->current();
        $this->assertEquals( 'C3', $e->get_field( 'title' ) );
        $this->assertEquals( 2, $day );

        $iterator->next();
        $this->assertTrue( $iterator->valid() );
        list( $e, $day ) = $iterator->current();
        $this->assertEquals( 'B4', $e->get_field( 'title' ) );
        $this->assertEquals( 3, $day );

        // 5.1.
        $iterator->next();
        $this->assertTrue( $iterator->valid() );
        list( $e, $day ) = $iterator->current();
        $this->assertEquals( 'B4', $e->get_field( 'title' ) );
        $this->assertEquals( 4, $day );

        // 2.2.
        $iterator->next();
        $this->assertTrue( $iterator->valid() );
        list( $e, $day ) = $iterator->current();
        $this->assertEquals( 'E', $e->get_field( 'title' ) );
        $this->assertEquals( 0, $day );

        // End.
        $iterator->next();
        $this->assertFalse( $iterator->valid() );
    }
}
