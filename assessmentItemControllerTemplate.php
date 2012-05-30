<?php
    /* Assume here that:
     *   a. $assessmentItem is a simple_xml object containing a valid QTI assessmentItem node
     */
    // Testing stuff
    $assessmentItem = simplexml_load_file('ref/qtiv2p1pd2/examples/items/choice.xml');
    
?>
class <?php echo $assessmentItem['identifier']; ?>_controller extends qti_controller {
    
    public $response_source; // provides new values for variables
    public $persistence; // provides existing values of variables
    
    public $identifier = "<?php echo $assessmentItem['identifier']; ?>";
    public $title = "<?php echo $assessmentItem['title']; ?>";
    public $adaptive = <?php echo $assessmentItem['adaptive']; ?>;
    public $timeDependent = <?php echo $assessmentItem['timeDependent']; ?>;
    
    public $response = array();
    public $outcome = array();
    
    public function init() {
        
    }
    
    public function start_session() {
        super::start_session();
        
<?php // TODO: fix the default value in these - add support for correct ?>
<?php foreach($assessmentItem->responseDeclaration as $responseDeclaration) { ?>
        $this->response['<?php echo $responseDeclaration['identifier']; ?>'] = new qti_variable('<?php echo $responseDeclaration['cardinality']; ?>', '<?php echo $responseDeclaration['type']; ?>', 'choiceA');
<?php } ?>
<?php foreach($assessmentItem->outcomeDeclaration as $outcomeDeclaration) { ?>
        $this->response['<?php echo $outcomeDeclaration['identifier']; ?>'] = new qti_variable('<?php echo $outcomeDeclaration['cardinality']; ?>', '<?php echo $outcomeDeclaration['type']; ?>', 'choiceA');
<?php } ?>
        
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