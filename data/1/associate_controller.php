<?php 
class associate_controller extends qti_item_controller {

    		public function __construct() {
$this->identifier = 'associate';
$this->title = 'Shakespearian Rivals';
$this->adaptive = 'false';
$this->timeDependent = 'false';
$p = new qti_item_body($this);
$p->itemBody(array(),
$p->associateInteraction(array('responseIdentifier' => 'RESPONSE', 'shuffle' => 'true', 'maxAssociations' => '3'),
$p->prompt(array(),
$p->__text('Hidden in this list of characters from famous Shakespeare plays are three pairs
				of rivals. Can you match each character to his adversary?')),
$p->simpleAssociableChoice(array('identifier' => 'A', 'matchMax' => '1'),
$p->__text('Antonio')),
$p->simpleAssociableChoice(array('identifier' => 'C', 'matchMax' => '1'),
$p->__text('Capulet')),
$p->simpleAssociableChoice(array('identifier' => 'D', 'matchMax' => '1'),
$p->__text('Demetrius')),
$p->simpleAssociableChoice(array('identifier' => 'L', 'matchMax' => '1'),
$p->__text('Lysander')),
$p->simpleAssociableChoice(array('identifier' => 'M', 'matchMax' => '1'),
$p->__text('Montague')),
$p->simpleAssociableChoice(array('identifier' => 'P', 'matchMax' => '1'),
$p->__text('Prospero'))));
$this->item_body = $p;

$r = new qti_response_processing($this);
$r->responseProcessing(array('schemaLocation' => 'http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/imsqti_v2p1.xsd'),
$r->responseCondition(array(),
$r->responseIf(array(),
$r->isNull(array(),
$r->variable(array('identifier' => 'RESPONSE'))),
$r->setOutcomeValue(array('identifier' => 'SCORE'),
$r->baseValue(array('baseType' => 'float'),
$r->__text('0.0')))),
$r->responseElse(array(),
$r->setOutcomeValue(array('identifier' => 'SCORE'),
$r->mapResponse(array('identifier' => 'RESPONSE'))))));
$this->response_processing = $r;
}    public function beginAttempt() {
        parent::beginAttempt();
$this->response['RESPONSE'] = new qti_variable('multiple', 'pair', array('correctResponse' => array('A P','C M','D L'),'mapping' => array('defaultValue' => '0','mapEntry' => array('A P' => '2','C M' => '1','D L' => '1'))));$this->outcome['SCORE'] = new qti_variable('single', 'float', array());}}