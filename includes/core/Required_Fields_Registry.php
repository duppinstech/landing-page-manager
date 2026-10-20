<?php
namespace LPManager\core;

/**
 * Required landing-page fields per template.
 *
 * Templates register their required Carbon field names in lib/fields/landing-page-fields.php:
 *
 *     Required_Fields_Registry::register( 'my-template', [ 'hero_heading', 'hero_image' ] );
 *
 * or, to control the label shown in the "Missing required fields" notice:
 *
 *     Required_Fields_Registry::register( 'my-template', [ 'hero_heading' => __( 'Headline (Hero tab)', 'my-domain' ) ] );
 *
 * Without a label, the notice uses the field's own label and tab.
 */
class Required_Fields_Registry {
    /** @var array<string, array<string, string>> Template slug => [ field name => label ('' = use the field's label) ]. */
    private static $fields = [];

    public static function register( $template, array $fields ) {
        if ( ! isset( self::$fields[ $template ] ) ) {
            self::$fields[ $template ] = [];
        }
        foreach ( $fields as $key => $value ) {
            $name  = is_int( $key ) ? (string) $value : (string) $key;
            $label = is_int( $key ) ? '' : (string) $value;
            if ( '' === $name ) {
                continue;
            }
            if ( '' !== $label || ! isset( self::$fields[ $template ][ $name ] ) ) {
                self::$fields[ $template ][ $name ] = $label;
            }
        }
    }

    /**
     * Required field names for a template.
     *
     * @param string $template Template slug.
     * @return string[]
     */
    public static function get( $template ) {
        return isset( self::$fields[ $template ] ) ? array_keys( self::$fields[ $template ] ) : [];
    }

    /**
     * The label a template registered for a required field, or '' when it gave none.
     *
     * @param string $template Template slug.
     * @param string $field    Field name.
     * @return string
     */
    public static function get_label( $template, $field ) {
        return isset( self::$fields[ $template ][ $field ] ) ? self::$fields[ $template ][ $field ] : '';
    }
}
