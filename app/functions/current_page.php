<?php
// Current Page Calculator
function current_page($ppage,$total)
{
    $ppage  = isset($ppage) ? (int) $ppage : 0;
    $total  = isset($total) ? (int) $total : 0;

    // Calculate
    $pageno = floor ($total / $ppage);

    // Return
    return $pageno;
}