<?php 
class order_controller extends qti_item_controller {

    		public function __construct() {
$this->identifier = 'order';
$this->title = 'Grand Prix of Bahrain';
$this->adaptive = 'false';
$this->timeDependent = 'false';
$p = new qti_item_body($this);
$p->itemBody(array(),
$p->orderInteraction(array('responseIdentifier' => 'RESPONSE', 'shuffle' => 'true'),
$p->prompt(array(),
$p->__text('The following F1 drivers finished on the podium in the first ever Grand Prix of
				Bahrain. Can you rearrange them into the correct finishing order?')),
$p->simpleChoice(array('identifier' => 'DriverA'),
$p->__text('Rubens Barrichello')),
$p->simpleChoice(array('identifier' => 'DriverB'),
$p->__text('Jenson Button')),
$p->simpleChoice(array('identifier' => 'DriverC', 'fixed' => 'true'),
$p->__text('Michael Schumacher'))));
$this->item_body = $p;

$r = new qti_response_processing($this);
$r->responseProcessing(array('schemaLocation' => 'http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/imsqti_v2p1.xsd'),
$r->responseCondition(array(),
$r->responseIf(array(),
$r->match(array(),
$r->variable(array('identifier' => 'RESPONSE')),
$r->correct(array('identifier' => 'RESPONSE'))),
$r->setOutcomeValue(array('identifier' => 'SCORE'),
$r->baseValue(array('baseType' => 'integer'),
$r->__text('1')))),
$r->responseElse(array(),
$r->setOutcomeValue(array('identifier' => 'SCORE'),
$r->baseValue(array('baseType' => 'integer'),
$r->__text('0'))))));
$this->response_processing = $r;
}    public function beginAttempt() {
        parent::beginAttempt();
$this->response['RESPONSE'] = new qti_variable('ordered', 'identifier', array('correctResponse' => array('DriverC','DriverA','DriverB')));$this->outcome['SCORE'] = new qti_variable('single', 'integer', array());}}