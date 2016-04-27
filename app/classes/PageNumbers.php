<?php
/**
*
* Pagenumbers Class
*
* @author       Jonathan Maltezo (Kameloh)
* @copyright    (c) 2016, Jonathan Maltezo (Kameloh)
* @lastupdated  2016-04-27
*
*/
namespace SketchbookCafe\PageNumbers;

use SketchbookCafe\SBC\SBC as SBC;

class PageNumbers {
    public $name = '';
    private $data; // array

    // Generated
    private $pagenumbers = '';
    public $pages_min = 0;
    public $pages_max = 0;
    public $pages_total = 0;

    // Construct
    // $input:  'first', 'current', 'total', 'display', 'link',
    //          'css_overlay','css_inactive','css_active',
    //          'posts', 'ppage'
    public function __construct($input)
    {
        $method = 'PageNumbers->__construct()';

        // Let's set a name at least
        $this->name = isset($input['name']) ? $input['name'] : '';
        if (empty($this->name))
        {
            SBC::devError('$name is not set',$method);
        }

        // Set Data
        $this->data['testmanhero']      = 1;
        $this->data['first']            = isset($input['first']) ? (int) $input['first'] : 0;
        $this->data['current']          = isset($input['current']) ? (int) $input['current'] : 0;
        $this->data['display']          = isset($input['display']) ? (int) $input['display'] : 0;
        $this->data['link']             = isset($input['link']) ? $input['link'] : '';
        $this->data['css_overlay']      = isset($input['css_overlay']) ? $input['css_overlay'] : '';
        $this->data['css_inactive']     = isset($input['css_inactive']) ? $input['css_inactive'] : '';
        $this->data['css_active']       = isset($input['css_active']) ? $input['css_active'] : '';

        // Total Calculations
        $this->data['posts']            = isset($input['posts']) ? (int) $input['posts'] : 0;
        $this->data['ppage']            = isset($input['ppage']) ? (int) $input['ppage'] : 0;

        // Min, Max, and Total Values
        $pageno         = $this->data['current'];
        $ppage          = $this->data['ppage'];
        $offset         = $pageno * $ppage;
        $total          = $this->data['posts'];
        $current_off    = $offset + $ppage;
        $showmin        = $offset + 1;
        $showmax        = $current_off;
        if ($showmax > $total)
        {
            $showmax = $total;
        }
        if ($showmin > $total)
        {
            $showmin = 0;
            $showmax = 0;
        }

        // Set vars
        $this->pages_min    = $showmin;
        $this->pages_max    = $showmax;
        $this->pages_total  = $total;

        // Generate Pagenumbers
        $this->pagenumbers = $this->generatePageNumbers();
    }

    // Get Page Numbers
    final public function getPageNumbers()
    {
        $method = 'PageNumbers->getPageNumbers()';

        return $this->pagenumbers;
    }

    // Offset Replace
    final public function offset_replace($offset,$number,$string)
    {
        $method = 'PageNumbers->offset_replace()';

        $old    = array('{page_link}','{page}');
        $new    = array($number + $offset, $number);
        return str_replace($old,$new,$string);
    }

    // Generate Page Numbers
    final public function generatePageNumbers()
    {
        $method = 'PageNumbers->generatePageNumbers()';

        // Initialize Vars
        $first          = $this->data['first'];
        $current        = $this->data['current'];
        $display        = $this->data['display'];
        $link           = $this->data['link'];
        $offset         = 0;

        // Calculate new total
        $posts          = $this->data['posts'];
        $ppage          = $this->data['ppage'];
        $total          = ceil($posts/$ppage);

        // CSS Vars
        $css_overlay    = $this->data['css_overlay'];
        $css_inactive   = $this->data['css_inactive'];
        $css_active     = $this->data['css_active'];

        // $in Array: used for replacing many things!
        $in = array
        (
            'prev_enabled'      => '<a href="{link}"><div class="'.$css_inactive.'">Prev</div></a>', 
            'prev_disabled'     => '<div class="'.$css_inactive.'">Prev</div>', 
            'next_enabled'      => '<a href="{link}"><div class="'.$css_inactive.'">Next</div></a>', 
            'next_disabled'     => '<div class="'.$css_inactive.'">Next</div>', 
            'button_sep'        => '<div class="'.$css_inactive.'">&middot;</div>', 
            'link_format'       => '<a href="{link}"><div class="'.$css_inactive.'">{page}</div></a>', 
            'active_format'     => '<div class="'.$css_active.'">{page}</div>', 
            'elipses'           => '<div class="'.$css_inactive.'">...</div>', 
            'page_sep'          => ' ', 
        );

        // Set vars and settings
        $output = '';
        if ($display < 1)
        {
            $display = 4;
        }
        if ($first == 0)
        {
            $offset     -= 1;
            $first      += 1;
            $current    += 1;
        }
        else
        {
            $offset     = 0;
        }

        // Page Link Replace
        $page_link          = str_replace('{link}',$link,$in['link_format']);
        $in['prev_enabled'] = str_replace('{link}',$link,$in['prev_enabled']);
        $in['next_enabled'] = str_replace('{link}',$link,$in['next_enabled']);

        // Calculate the list
        $list_start = 1;
        $list_end   = $total;
        if ($current > $display + 1)
        {
            $list_start = $current - $display;
        }
        if ($total - $current > $display)
        {
            $list_end = $current + $display;
        }

        // Previous Button
        if ($current > 1)
        {
            $output .= $this->offset_replace($offset,$current - 1,$in['prev_enabled']);
        }
        else
        {
            $output .= $in['prev_disabled'];
        }

        // Add seperator
        $output .= $in['button_sep'];
        if ($list_start > 1)
        {
            $output .= $this->offset_replace($offset,1,$page_link);
            $output .= $in['elipses'];
        }

        // Loop Pages
        $i  = $list_start;
        while ($i <= $list_end)
        {
            // Current Page
            if ($i == $current)
            {
                $output .= $this->offset_replace($offset,$i,$in['active_format']);
            }
            else
            {
                $output .= $this->offset_replace($offset,$i,$page_link);
            }

            // Page Seperator
            if ($i != $list_end)
            {
                $output .= $in['page_sep'];
            }

            $i++;
        }

        // Ellipses separator between page numbers
        if ($list_end < $total)
        {
            $output .= $in['elipses'];
            $output .= $this->offset_replace($offset,$total,$page_link);
        }

        // More seperators
        $output .= $in['button_sep'];

        // Next Button
        if ($current < $total)
        {
            $output .= $this->offset_replace($offset,$current + 1,$in['next_enabled']);
        }
        else
        {
            $output .= $in['next_disabled'];
        }

        // Do we have an overlay class?
        if (!empty($css_overlay))
        {
            $output = '<div class="'.$css_overlay.'">'.$output.'</div>';
        }

        // Return 
        return $output;
    }
}