<?php
/**
 * Definition of floating buttons short code.
 *
 * @package Community_Calendar
 */

/**
 * Implementation of shortcode 'floatingButtons'
 *
 * [floatingButtons target="targetDivId"]btn-class-1 btn-class-2[/floatingButtons]
 *
 * @param array  $atts Attributes.
 * @param string $content Tag content.
 */
function comcal_floating_buttons_func( $atts, $content = null ) {
    $a = shortcode_atts(
        array(
            'addEvent'       => true,
            'editCategories' => false,
            'scrollToToday'  => true,
        ),
        $atts
    );

    $out            = "<div class='comcal-floating-button-container'>";
    $button_classes = array();
    if ( $a['addEvent'] ) {
        $button_classes[] = 'addEvent';
    }
    if ( $a['scrollToToday'] ) {
        $button_classes[] = 'scrollToToday';
    }
    if ( Comcal_User_Capabilities::edit_categories() && $a['editCategories'] ) {
        $button_classes[] = 'editCategories';
    }
    $index = 0;
    foreach ( $button_classes as $class ) {
        if ( 0 !== $index ) {
            $order_class = 'other';
            $bottom_pos  = 16 + 64 + 8 + ( $index - 1 ) * 56;
        } else {
            $order_class = 'first';
            $bottom_pos  = 16;
        }
        $add_style = "bottom: {$bottom_pos}px;";
        $out      .= "<button class='comcal-floating-button btn $class $order_class' style='$add_style'>";
        if ( 'addEvent' === $class ) {
            $img_src = EVTCAL__PLUGIN_URL . 'public/images/plus.png';
            $out    .= "<img class='$class' src='$img_src'></img>";
        } elseif ( 'scrollToToday' === $class ) {
            $img_src = EVTCAL__PLUGIN_URL . 'public/images/arrow_up.png';
            $out    .= "<img class='$class' src='$img_src'></img>";
        } else {
            $out .= "<span class='$class'></span>";
        }
        $out .= '</button>';
        $index++;
    }
    return $out . '</div>';

}
add_shortcode( 'community-calendar-buttons', 'comcal_floating_buttons_func' );
