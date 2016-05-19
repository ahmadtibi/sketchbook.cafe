<?php
// @author          Kameloh
// @lastUpdated     2016-05-11
namespace SketchbookCafe\PollDisplay;

use SketchbookCafe\SBC\SBC as SBC;

class PollDisplay
{
    private $html = '';
    private $thread_row = [];
    private $poll_row = [];

    // CSS
    private $css_poll = ' sbc_font ';

    private $obj_array = [];
    public function __construct(&$obj_array)
    {
        $method = 'PollDisplay->__construct()';

        // Objects
        $this->obj_array = &$obj_array;
        if (empty($this->obj_array))
        {
            SBC::devError('obj_array is empty',$method);
        }
    }

    // Process
    final private function process()
    {
        $method     = 'PollObject->process()';
        $thread_row = &$this->obj_array['thread_row'];
        $poll_row   = &$this->obj_array['poll_row'];
        $PollForm   = &$this->obj_array['PollForm'];
        $User       = &$this->obj_array['User'];

        // Does the poll exist?
        if ($thread_row['poll_id'] < 1)
        {
            return null;
        }

        // Create Poll Loop
        $poll_html = '';
        $i = 1;
        while ($i < 11)
        {
            if (!empty($poll_row['message'.$i]))
            {
                // Create Calculation
                $innerWidth = 0;
                if ($poll_row['total_votes'] > 0)
                {
                    $innerWidth = number_format(($poll_row['vote'.$i] / $poll_row['total_votes']) * 100, 2);
                }
                $poll_html .= '
        <div class="pollInnerLeft">
            <input id="radio-'.$i.'" class="radio-custom" name="poll_option" type="radio" value="'.$i.'">
            <label for="radio-'.$i.'" class="radio-custom-label"></label>
        </div>
        <div class="pollInnerRight">

            <div class="pollMessageWrap">
                <div class="pollMessage '.$this->css_poll.'">
                    '.$poll_row['message'.$i].'
                </div>
                <div class="pollBarOuter">
                    <div class="pollBarInner '.$this->css_poll.'" style="width: '.$innerWidth.'%;">
                        <div class="pollBarInnerText">
                            '.$innerWidth.'%
                            &nbsp;
                            <script>sbc_number_display('.$poll_row['vote'.$i].',\'vote\',\'votes\');</script>
                        </div>
                    </div>
                </div>
            </div>

        </div>
                    ';

            }

            $i++;
        }

        // User Only
        if ($User->loggedIn())
        {
            // Form Start
            $this->html .= $PollForm->start();
            $this->html .= $PollForm->field['poll_id'];
        }

        // Main HTML
        $this->html .= '
<div class="pollWrap">
    <div class="pollTitle sbc_font sbc_font_size">
        <div class="pollTitleRight">
            <script>sbc_number_display('.$poll_row['total_votes'].',\'vote\',\'votes\');</script>
        </div>
        '.$thread_row['title'].'
    </div>

    <div class="pollInnerWrap sbc_font">
';
        // Poll HTML
        $this->html .= $poll_html;

        // User Only
        if ($User->loggedIn())
        {
            $this->html .= '<div>'.$PollForm->field['submit'].'</div>';
            $this->html .= $PollForm->end();
        }

        $this->html .= '
    </div>
</div>
';
    }

    // Get Poll
    final public function getPoll()
    {
        // Process
        $this->process();

        return $this->html;
    }
}