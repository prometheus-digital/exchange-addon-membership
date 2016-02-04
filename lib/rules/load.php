<?php
/**
 * Load the rules.
 *
 * @since   1.18
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/interface.php';
require_once dirname( __FILE__ ) . '/evaluator.php';

require_once dirname( __FILE__ ) . '/content/factory.php';
require_once dirname( __FILE__ ) . '/content/interface.php';
require_once dirname( __FILE__ ) . '/content/abstract.php';
require_once dirname( __FILE__ ) . '/content/post-type.php';
require_once dirname( __FILE__ ) . '/content/post.php';
require_once dirname( __FILE__ ) . '/content/term.php';

require_once dirname( __FILE__ ) . '/delay/interface.php';
require_once dirname( __FILE__ ) . '/delay/drip.php';