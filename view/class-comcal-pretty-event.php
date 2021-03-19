<?php
/**
 * Helpers for converting Comcal_Event fields into pretty HTML.
 *
 * @package Community_Calendar
 */

/**
 * Converts a snake_cased string to a camelCased string.
 *
 * @param string $string String containing snake_case.
 */
function comcal_snake_to_camel_case( $string ) {
    $camel    = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $string ) ) );
    $camel[0] = $string[0];
    return $camel;
}

/**
 * Extracts public fields from an event and generates some
 * prettified fields.
 */
class Comcal_Pretty_Event extends stdClass {
    /**
     * A copy of the data fields of the Comcal_Event object.
     *
     * @var array
     */
    protected $fields;

    /**
     * A callback map that maps custom field names to functions that generate
     * pretty names.
     *
     * @var array( string => function )
     */
    protected $prettier_map;

    /**
     * Comcal_Date_Time instance, initialized from the event object.
     *
     * @var Comcal_Date_time
     */
    protected $datetime;

    /**
     * Override and/or extend to define custom pretty fields.
     *
     * @return array( string => function ) Map of custom field functions.
     */
    protected function initialize_prettier_map() {
        return array(
            'prettyDate' => function() {
                return $this->datetime->get_pretty_date();
            },
            'prettyTime' => function() {
                return $this->datetime->get_pretty_time();
            },
            'weekday'    => function() {
                return $this->datetime->get_weekday();
            },
        );
    }

    public function __construct( Comcal_Event $event ) {
        $this->fields       = $event->get_public_fields();
        $this->datetime     = Comcal_Date_Time::from_date_str_time_str(
            $this->fields['date'],
            $this->fields['time']
        );
        $this->prettier_map = $this->initialize_prettier_map();
    }

    /**
     * Magic method that returns Comcal_Event-field and custom field values.
     *
     * @param string $name Field name.
     * @return string|int|object Field value.
     * @throws RuntimeException If the field name is not valid.
     */
    public function __get( $name ) {

        // Value is found directly in the database.
        if ( isset( $this->fields[ $name ] ) ) {
            $value = $this->fields[ $name ];
            return $this->format_value( $name, $value );
        }

        // Value is calculated as defined in the prettier map.
        if ( isset( $this->prettier_map[ $name ] ) ) {
            return $this->prettier_map[ $name ]();
        }

        // Be tolerant: check if the requested field exists if converted to camelCase.
        $camel_cased_name = comcal_snake_to_camel_case( $name );
        if ( isset( $this->fields[ $camel_cased_name ] ) ) {
            $value = $this->fields[ $camel_cased_name ];
            return $this->format_value( $camel_cased_name, $value );
        }

        throw new RuntimeException( "Unknown event field '$name'!" );
    }

    protected function format_value( $name, $value ) {
        if ( $this->is_url( $name ) ) {
            $value = esc_url( $value );
        } elseif ( is_string( $value ) ) {
            $value = nl2br( $value );
        }
        return $value;
    }

    protected function is_url( $name ) {
        return 'url' === $name || 'imageUrl' === $name;
    }
}
