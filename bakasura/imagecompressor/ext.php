<?php
/**
 * @package bakasura\imagecompressor
 * @copyright (c) bakasura
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace bakasura\imagecompressor;

/**
 * Extension base class.
 *
 * No custom lifecycle hooks are required; this class exists to make the
 * extension discoverable by static-analysis tools and to provide an
 * explicit extension entry point for future use.
 */
class ext extends \phpbb\extension\base {}
