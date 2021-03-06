<?php
/**
 * PHP FORMS API
 * PHP Version 5.5
 *
 * @category Utils
 * @package  Degami\PHPFormsApi
 * @author   Mirko De Grandis <degami@github.com>
 * @license  MIT https://opensource.org/licenses/mit-license.php
 * @link     https://github.com/degami/php-forms-api
 */
/* #########################################################
   ####                     TRAITS                      ####
   ######################################################### */

namespace Degami\PHPFormsApi\Traits;

/**
 * Processor functions
 */
trait Processors
{
    /**
     * applies trim to text
     *
     * @param string $text text to trim
     * @return string       trimmed version of $text
     */
    public static function processTrim(string $text): string
    {
        return trim($text);
    }

    /**
     * applies ltrim to text
     *
     * @param string $text text to ltrim
     * @return string       ltrimmed version of $text
     */
    public static function processLtrim(string $text): string
    {
        return ltrim($text);
    }

    /**
     * applies rtrim to text
     *
     * @param string $text text to rtrim
     * @return string       rtrimmed version of $text
     */
    public static function processRtrim(string $text): string
    {
        return rtrim($text);
    }

    /**
     * applies xss checks on string (weak version)
     *
     * @param string $string text to check
     * @return string         safe value
     */
    public static function processXssWeak(string $string): string
    {
        return call_user_func_array(
            [__CLASS__, 'processXss'],
            [
                $string,
                implode(
                    "|",
                    [
                        'a',
                        'abbr',
                        'acronym',
                        'address',
                        'b',
                        'bdo',
                        'big',
                        'blockquote',
                        'br',
                        'caption',
                        'cite',
                        'code',
                        'col',
                        'colgroup',
                        'dd',
                        'del',
                        'dfn',
                        'div',
                        'dl',
                        'dt',
                        'em',
                        'h1',
                        'h2',
                        'h3',
                        'h4',
                        'h5',
                        'h6',
                        'hr',
                        'i',
                        'img',
                        'ins',
                        'kbd',
                        'li',
                        'ol',
                        'p',
                        'pre',
                        'q',
                        'samp',
                        'small',
                        'span',
                        'strong',
                        'sub',
                        'sup',
                        'table',
                        'tbody',
                        'td',
                        'tfoot',
                        'th',
                        'thead',
                        'tr',
                        'tt',
                        'ul',
                        'var',
                    ]
                )
            ]
        );
    }

    /**
     * Check if $text's character encoding is utf8
     *
     * @param string $text text to check
     * @return boolean       is utf8
     */
    private static function _validateUtf8(string $text): bool
    {
        if (strlen($text) == 0) {
            return true;
        }
        return (preg_match('/^./us', $text) == 1);
    }

    /**
     * applies xss checks on string
     *
     * @param string $string text to check
     * @param string $allowed_tags allowed tags
     * @return string         safe value
     */
    public static function processXss(string $string, $allowed_tags = FORMS_XSS_ALLOWED_TAGS): string
    {
        // Only operate on valid UTF-8 strings. This is necessary to prevent cross
        // site scripting issues on Internet Explorer 6.
        if (!call_user_func_array([__CLASS__, '_validateUtf8'], [ $string ])) {
            return '';
        }
        // Store the input format
        call_user_func_array([__CLASS__, '_filterXssSplit'], [ $allowed_tags, true ]);
        // Remove NUL characters (ignored by some browsers)
        $string = str_replace(chr(0), '', $string);
        // Remove Netscape 4 JS entities
        $string = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);

        // Defuse all HTML entities
        $string = str_replace('&', '&amp;', $string);
        // Change back only well-formed entities in our whitelist
        // Decimal numeric entities
        $string = preg_replace('/&amp;#([0-9]+;)/', '&#\1', $string);
        // Hexadecimal numeric entities
        $string = preg_replace('/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string);
        // Named entities
        $string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string);

        return preg_replace_callback(
            '%
            (
            <(?=[^a-zA-Z!/])  # a lone <
            |                 # or
            <[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
            |                 # or
            >                 # just a >
            )%x',
            [__CLASS__, '_filterXssSplit'],
            $string
        );
    }

    /**
     * _filter_xss_split private method
     *
     * @param string $m string to split
     * @param boolean $store store elements into static $allowed html
     * @return string         string
     */
    private static function _filterXssSplit(string $m, $store = false): string
    {
        static $allowed_html;

        if ($store) {
            $m = explode("|", $m);
            $allowed_html = array_flip($m);
            return '';
        }

        $string = $m[1];

        if (substr($string, 0, 1) != '<') {
            // We matched a lone ">" character
            return '&gt;';
        } elseif (strlen($string) == 1) {
            // We matched a lone "<" character
            return '&lt;';
        }

        if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9]+)([^>]*)>?$%', $string, $matches)) {
            // Seriously malformed
            return '';
        }

        $slash = trim($matches[1]);
        $elem = &$matches[2];
        $attrlist = &$matches[3];

        if (!isset($allowed_html[strtolower($elem)])) {
            // Disallowed HTML element
            return '';
        }

        if ($slash != '') {
            return "</$elem>";
        }

        // Is there a closing XHTML slash at the end of the attributes?
        // In PHP 5.1.0+ we could count the changes, currently we need a separate match
        $xhtml_slash = preg_match('%\s?/\s*$%', $attrlist) ? ' /' : '';
        $attrlist = preg_replace('%(\s?)/\s*$%', '\1', $attrlist);

        // Clean up attributes
        $attr2 = implode(
            ' ',
            call_user_func_array(
                [__CLASS__,
                    '_filterXssAttributes'
                ],
                [$attrlist]
            )
        );
        $attr2 = preg_replace('/[<>]/', '', $attr2);
        $attr2 = strlen($attr2) ? ' ' . $attr2 : '';

        return "<$elem$attr2$xhtml_slash>";
    }

    /**
     * _filter_xss_attributes private method
     *
     * @param string $attr attributes string
     * @return array        filtered attributes array
     */
    private static function _filterXssAttributes(string $attr): array
    {
        $attrarr = [];
        $mode = 0;
        $attrname = '';
        $skip = false;

        while (strlen($attr) != 0) {
            // Was the last operation successful?
            $working = 0;

            switch ($mode) {
                case 0:
                    // Attribute name, href for instance.
                    if (preg_match('/^([-a-zA-Z]+)/', $attr, $match)) {
                        $attrname = strtolower($match[1]);
                        $skip = ($attrname == 'style' || substr($attrname, 0, 2) == 'on');
                        $working = $mode = 1;
                        $attr = preg_replace('/^[-a-zA-Z]+/', '', $attr);
                    }
                    break;

                case 1:
                    // Equals sign or valueless ("selected").
                    if (preg_match('/^\s*=\s*/', $attr)) {
                        $working = 1;
                        $mode = 2;
                        $attr = preg_replace('/^\s*=\s*/', '', $attr);
                        break;
                    }

                    if (preg_match('/^\s+/', $attr)) {
                        $working = 1;
                        $mode = 0;
                        if (!$skip) {
                            $attrarr[] = $attrname;
                        }
                        $attr = preg_replace('/^\s+/', '', $attr);
                    }
                    break;

                case 2:
                    // Attribute value, a URL after href= for instance.
                    if (preg_match('/^"([^"]*)"(\s+|$)/', $attr, $match)) {
                        $thisval = call_user_func_array(
                            [__CLASS__,
                                '_filterXssBadProtocol'
                            ],
                            [ $match[1] ]
                        );

                        if (!$skip) {
                              $attrarr[] = "$attrname=\"$thisval\"";
                        }
                        $working = 1;
                        $mode = 0;
                        $attr = preg_replace('/^"[^"]*"(\s+|$)/', '', $attr);
                        break;
                    }

                    if (preg_match("/^'([^']*)'(\s+|$)/", $attr, $match)) {
                        $thisval = call_user_func_array(
                            [__CLASS__,
                                '_filterXssBadProtocol'
                            ],
                            [ $match[1] ]
                        );

                        if (!$skip) {
                            $attrarr[] = "$attrname='$thisval'";
                        }
                        $working = 1;
                        $mode = 0;
                        $attr = preg_replace("/^'[^']*'(\s+|$)/", '', $attr);
                        break;
                    }

                    if (preg_match("%^([^\s\"']+)(\s+|$)%", $attr, $match)) {
                        $thisval = call_user_func_array(
                            [__CLASS__,'_filterXssBadProtocol'],
                            [ $match[1] ]
                        );

                        if (!$skip) {
                            $attrarr[] = "$attrname=\"$thisval\"";
                        }
                        $working = 1;
                        $mode = 0;
                        $attr = preg_replace("%^[^\s\"']+(\s+|$)%", '', $attr);
                    }
                    break;
            }

            if ($working == 0) {
                // Not well formed; remove and try again.

                /*
                 *
                 * '/
                 * ^
                 * (
                 * "[^"]*("|$)     # - a string that starts with a double quote,
                 *                 #   up until the next double quote or the end of the string
                 * |               # or
                 * \'[^\']*(\'|$)| # - a string that starts with a quote,
                 *                 #   up until the next quote or the end of the string
                 * |               # or
                 * \S              # - a non-whitespace character
                 * )*              # any number of the above three
                 * \s*             # any number of whitespaces
                 * /x',
                 * */

                $attr = preg_replace(
                    '/^("[^"]*("|$)|\'[^\']*(\'|$)||\S)*\s*/x',
                    '',
                    $attr
                );
                $mode = 0;
            }
        }

        // The attribute list ends with a valueless attribute like "selected".
        if ($mode == 1 && !$skip) {
            $attrarr[] = $attrname;
        }
        return $attrarr;
    }

    /**
     * [_filter_xss_bad_protocol private method
     *
     * @param string $string string
     * @param boolean $decode process entity decode on string
     * @return string          safe value
     */
    private static function _filterXssBadProtocol(string $string, $decode = true): string
    {
        if ($decode) {
            $string = call_user_func_array([__CLASS__, 'processEntityDecode'], [ $string ]);
        }

        return call_user_func_array(
            [__CLASS__, 'processPlain'],
            [
                call_user_func_array([__CLASS__, '_stripDangerousProtocols'], [ $string ])
            ]
        );
    }

    /**
     * _strip_dangerous_protocols private method
     *
     * @param string $uri uri
     * @return string      safe value
     */
    private static function _stripDangerousProtocols(string $uri): string
    {
        static $allowed_protocols;

        if (!isset($allowed_protocols)) {
            $allowed_protocols = array_flip(
                [
                    'ftp',
                    'http',
                    'https',
                    'irc',
                    'mailto',
                    'news',
                    'nntp',
                    'rtsp',
                    'sftp',
                    'ssh',
                    'tel',
                    'telnet',
                    'webcal'
                ]
            );
        }

        // Iteratively remove any invalid protocol found.
        do {
            $before = $uri;
            $colonpos = strpos($uri, ':');
            if ($colonpos > 0) {
                // We found a colon, possibly a protocol. Verify.
                $protocol = substr($uri, 0, $colonpos);
                // If a colon is preceded by a slash, question mark or hash, it cannot
                // possibly be part of the URL scheme. This must be a relative URL, which
                // inherits the (safe) protocol of the base document.
                if (preg_match('![/?#]!', $protocol)) {
                    break;
                }
                // Check if this is a disallowed protocol. Per RFC2616, section 3.2.3
                // (URI Comparison) scheme comparison must be case-insensitive.
                if (!isset($allowed_protocols[strtolower($protocol)])) {
                    $uri = substr($uri, $colonpos + 1);
                }
            }
        } while ($before != $uri);

        return $uri;
    }

    /**
     * applies entity_decode to text
     *
     * @param string $text text to decode
     * @return string       decoded version of $text
     */
    public static function processEntityDecode(string $text): string
    {
        return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * applies addslashes to text
     *
     * @param string $text text to addslash
     * @return string       addslashed version of $text
     */
    public static function processAddslashes(string $text): string
    {
        if (!get_magic_quotes_gpc() && !preg_match("/\\/i", $text)) {
            return addslashes($text);
        } else {
            return $text;
        }
    }
}
