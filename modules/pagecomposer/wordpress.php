<?php
function get_shortcode_atts_regex() {
    return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
}

function do_shortcodes_in_html_tags( $content, $ignore_html, $tagnames ) {
    // Normalize entities in unfiltered HTML before adding placeholders.
    $trans   = array(
        '&#91;' => '&#091;',
        '&#93;' => '&#093;',
    );
    $content = strtr( $content, $trans );
    $trans   = array(
        '[' => '&#91;',
        ']' => '&#93;',
    );
 
    $pattern = get_shortcode_regex( $tagnames );
	debugga($pattern);
	return; 
    $textarr = wp_html_split( $content );
 
    foreach ( $textarr as &$element ) {
        if ( '' == $element || '<' !== $element[0] ) {
            continue;
        }
 
        $noopen  = false === strpos( $element, '[' );
        $noclose = false === strpos( $element, ']' );
        if ( $noopen || $noclose ) {
            // This element does not contain shortcodes.
            if ( $noopen xor $noclose ) {
                // Need to encode stray '[' or ']' chars.
                $element = strtr( $element, $trans );
            }
            continue;
        }
 
        if ( $ignore_html || '<!--' === substr( $element, 0, 4 ) || '<![CDATA[' === substr( $element, 0, 9 ) ) {
            // Encode all '[' and ']' chars.
            $element = strtr( $element, $trans );
            continue;
        }
 
        $attributes = wp_kses_attr_parse( $element );
        if ( false === $attributes ) {
            // Some plugins are doing things like [name] <[email]>.
            if ( 1 === preg_match( '%^<\s*\[\[?[^\[\]]+\]%', $element ) ) {
                $element = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $element );
            }
 
            // Looks like we found some crazy unfiltered HTML. Skipping it for sanity.
            $element = strtr( $element, $trans );
            continue;
        }
 
        // Get element name.
        $front   = array_shift( $attributes );
        $back    = array_pop( $attributes );
        $matches = array();
        preg_match( '%[a-zA-Z0-9]+%', $front, $matches );
        $elname = $matches[0];
 
        // Look for shortcodes in each attribute separately.
        foreach ( $attributes as &$attr ) {
            $open  = strpos( $attr, '[' );
            $close = strpos( $attr, ']' );
            if ( false === $open || false === $close ) {
                continue; // Go to next attribute. Square braces will be escaped at end of loop.
            }
            $double = strpos( $attr, '"' );
            $single = strpos( $attr, "'" );
            if ( ( false === $single || $open < $single ) && ( false === $double || $open < $double ) ) {
                /*
                 * $attr like '[shortcode]' or 'name = [shortcode]' implies unfiltered_html.
                 * In this specific situation we assume KSES did not run because the input
                 * was written by an administrator, so we should avoid changing the output
                 * and we do not need to run KSES here.
                 */
                $attr = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $attr );
            } else {
                // $attr like 'name = "[shortcode]"' or "name = '[shortcode]'".
                // We do not know if $content was unfiltered. Assume KSES ran before shortcodes.
                $count    = 0;
                $new_attr = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $attr, -1, $count );
                if ( $count > 0 ) {
                    // Sanitize the shortcode output using KSES.
                    $new_attr = wp_kses_one_attr( $new_attr, $elname );
                    if ( '' !== trim( $new_attr ) ) {
                        // The shortcode is safe to use now.
                        $attr = $new_attr;
                    }
                }
            }
        }
        $element = $front . implode( '', $attributes ) . $back;
 
        // Now encode any remaining '[' or ']' chars.
        $element = strtr( $element, $trans );
    }
 
    $content = implode( '', $textarr );
 
    return $content;
}


function get_shortcode_regex( $tagnames = null ) {
    global $shortcode_tags;
 
    if ( empty( $tagnames ) ) {
        $tagnames = array_keys( $shortcode_tags );
    }
    $tagregexp = join( '|', array_map( 'preg_quote', $tagnames ) );
 
    // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag().
    // Also, see shortcode_unautop() and shortcode.js.
 
    // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
    return '\\['                             // Opening bracket.
        . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]].
        . "($tagregexp)"                     // 2: Shortcode name.
        . '(?![\\w-])'                       // Not followed by word character or hyphen.
        . '('                                // 3: Unroll the loop: Inside the opening shortcode tag.
        .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash.
        .     '(?:'
        .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket.
        .         '[^\\]\\/]*'               // Not a closing bracket or forward slash.
        .     ')*?'
        . ')'
        . '(?:'
        .     '(\\/)'                        // 4: Self closing tag...
        .     '\\]'                          // ...and closing bracket.
        . '|'
        .     '\\]'                          // Closing bracket.
        .     '(?:'
        .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags.
        .             '[^\\[]*+'             // Not an opening bracket.
        .             '(?:'
        .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag.
        .                 '[^\\[]*+'         // Not an opening bracket.
        .             ')*+'
        .         ')'
        .         '\\[\\/\\2\\]'             // Closing shortcode tag.
        .     ')?'
        . ')'
        . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]].
    // phpcs:enable
}


function wp_html_split( $input ) {
    return preg_split( get_html_split_regex(), $input, -1, PREG_SPLIT_DELIM_CAPTURE );
}


function wp_kses_attr_parse( $element ) {
    $valid = preg_match( '%^(<\s*)(/\s*)?([a-zA-Z0-9]+\s*)([^>]*)(>?)$%', $element, $matches );
    if ( 1 !== $valid ) {
        return false;
    }
 
    $begin  = $matches[1];
    $slash  = $matches[2];
    $elname = $matches[3];
    $attr   = $matches[4];
    $end    = $matches[5];
 
    if ( '' !== $slash ) {
        // Closing elements do not get parsed.
        return false;
    }
 
    // Is there a closing XHTML slash at the end of the attributes?
    if ( 1 === preg_match( '%\s*/\s*$%', $attr, $matches ) ) {
        $xhtml_slash = $matches[0];
        $attr        = substr( $attr, 0, -strlen( $xhtml_slash ) );
    } else {
        $xhtml_slash = '';
    }
 
    // Split it.
    $attrarr = wp_kses_hair_parse( $attr );
    if ( false === $attrarr ) {
        return false;
    }
 
    // Make sure all input is returned by adding front and back matter.
    array_unshift( $attrarr, $begin . $slash . $elname );
    array_push( $attrarr, $xhtml_slash . $end );
 
    return $attrarr;
}

function wp_kses_hair_parse( $attr ) {
    if ( '' === $attr ) {
        return array();
    }
 
    // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
    $regex =
    '(?:'
    .     '[-a-zA-Z:]+'   // Attribute name.
    . '|'
    .     '\[\[?[^\[\]]+\]\]?' // Shortcode in the name position implies unfiltered_html.
    . ')'
    . '(?:'               // Attribute value.
    .     '\s*=\s*'       // All values begin with '='.
    .     '(?:'
    .         '"[^"]*"'   // Double-quoted.
    .     '|'
    .         "'[^']*'"   // Single-quoted.
    .     '|'
    .         '[^\s"\']+' // Non-quoted.
    .         '(?:\s|$)'  // Must have a space.
    .     ')'
    . '|'
    .     '(?:\s|$)'      // If attribute has no value, space is required.
    . ')'
    . '\s*';              // Trailing space is optional except as mentioned above.
    // phpcs:enable
 
    // Although it is possible to reduce this procedure to a single regexp,
    // we must run that regexp twice to get exactly the expected result.
 
    $validation = "%^($regex)+$%";
    $extraction = "%$regex%";
 
    if ( 1 === preg_match( $validation, $attr ) ) {
        preg_match_all( $extraction, $attr, $attrarr );
        return $attrarr[0];
    } else {
        return false;
    }
}

?>