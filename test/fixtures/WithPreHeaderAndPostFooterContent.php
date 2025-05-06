<?php

// Defaults are overwritten by actual content in "content" mode. In "contentBetweenHeaderAndFooter" mode, these vars are ignored altogether.
$me = 'me';
$you = 'you';

/* Begin Horde Config File - do not edit */
$you = 'not you the other one';
// Managed content goes here. Comments in here are lost.
$something = 'set';
/* End Horde Config File - do not edit */
$me = 'not me';
// Values after the footer overwrite managed content in 'content' mode. In 'contentBetweenHeaderAndFooter' mode, these vars are ignored altogether.
$something = 'overwritten';
$footer_only = 'footer only';

?>