<?php 
class choice_controller extends qti_item_controller {

    		public function __construct() {
$p = new qti_item_body($this);
$p->itemBody(array(),
$p->p(array(),
$p->__text('Look at the text in the picture.')),
$p->p(array(),
$p->img(array('src' => 'images/sign.png', 'alt' => 'NEVER LEAVE LUGGAGE UNATTENDED'))),
$p->choiceInteraction(array('responseIdentifier' => 'RESPONSE', 'shuffle' => 'false', 'maxChoices' => '1'),
$p->prompt(array(),
$p->__text('What does it say?')),
$p->simpleChoice(array('identifier' => 'ChoiceA'),
$p->__text('You must stay with your luggage at all times.')),
$p->simpleChoice(array('identifier' => 'ChoiceB'),
$p->__text('Do not let someone else look after your luggage.')),
$p->simpleChoice(array('identifier' => 'ChoiceC'),
$p->__text('Remember your luggage when you leave.'))));
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
$this->response['RESPONSE'] = new qti_variable('single', 'identifier', array('correctResponse' => 'ChoiceA'));$this->outcome['SCORE'] = new qti_variable('single', 'integer', array('defaultValue' => '0'));}}