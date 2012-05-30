<?php

class choice_controller extends qti_controller {
    
    public $response_source; // provides new values for variables
    public $persistence; // provides existing values of variables
    
    public $identifier = "choice";
    public $title = "Unattended Luggage";
    public $adaptive = false;
    public $timeDependent = false;
    
    public $response = array();
    public $outcome = array();
    
    public function init() {
        
    }
    
    public function start_session() {
        // Built-in variables (section 5.1.1 & 5.2.1 of info model)
        $this->response['numAttempts'] = new qti_variable('single', 'integer', 0);
        $this->response['duration'] = new qti_variable('single', 'float', 0);
        $this->outcome['completionStatus'] = new qti_variable('single', 'identifier', 'not_attempted');

        
        $this->response['RESPONSE'] = new qti_variable('single', 'identifier', 'choiceA');
        $this->outcome['SCORE'] = new qti_variable('single', 'integer', 0);
        
        $this->persistence->persist($this);
    }
    
    public function render_body($content_type='text/html') {
        $result = '<div class="itemBody">';
        
        // Create a text escape function depending on return type
        $e = function($text) use ($content_type) {
            if($content_type == 'text/html') {
                return htmlentities($text);
            } else {
                return $text;
            }
        };
        
        $result .= '<p>' . $e('Look at the text in the picture.') . '</p>';
        $result .= '<p>' . $e('') . '<img src="' . $e('images/sign.png') . '" alt="' . $e('NEVER LEAVE LUGGAGE UNATTENDED') . '"/>' . '</p>';
        $result .= '<form>' . '<p>' . $e('What does it say?') . '</p>' . 
        '<input type="radio" name="RESPONSE" value="' . $e('ChoiceA') . '"/>' . $e('You must stay with your luggage at all times.') .
        '<input type="radio" name="RESPONSE" value="' . $e('ChoiceB') . '"/>' . $e('Do not let someone else look after your luggage.') .
        '<input type="radio" name="RESPONSE" value="' . $e('ChoiceC') . '"/>' . $e('Remember your luggage when you leave.') .
        '<input type="submit" />' .
        '</form>';
        $result .= '</div>';
        return $result;
    }
    
    public function end_attempt() {
        foreach($this->response as $name) {
            $this->response[$name]->value = $this->response_source->get($name);
        }
    }
    
    public function process_response() {
        $template = new qti_rp_template('http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct');
        return $template->process_response($this->response, $this->outcome);
    }
}