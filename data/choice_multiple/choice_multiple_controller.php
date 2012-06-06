<?php
class choice_multiple_controller extends qti_item_controller {

    public function __construct() {
        $p = new qti_item_body($this);
        $p->itemBody(array(),
        $p->choiceInteraction(array('responseIdentifier' => 'RESPONSE', 'shuffle' => 'true', 'maxChoices' => '0'),
        $p->prompt(array(),
        $p->__text('Which of the following elements are used to form water?')),
        $p->simpleChoice(array('identifier' => 'H', 'fixed' => 'false'),
        $p->__text('Hydrogen')),
        $p->simpleChoice(array('identifier' => 'He', 'fixed' => 'false'),
        $p->__text('Helium')),
        $p->simpleChoice(array('identifier' => 'C', 'fixed' => 'false'),
        $p->__text('Carbon')),
        $p->simpleChoice(array('identifier' => 'O', 'fixed' => 'false'),
        $p->__text('Oxygen')),
        $p->simpleChoice(array('identifier' => 'N', 'fixed' => 'false'),
        $p->__text('Nitrogen')),
        $p->simpleChoice(array('identifier' => 'Cl', 'fixed' => 'false'),
        $p->__text('Chlorine'))));
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
        $this->response['RESPONSE'] = new qti_variable('multiple', 'identifier', array('correctResponse' => array('H','O'),'mapping' => array('lowerBound' => '0','upperBound' => '2','defaultValue' => '-2','mapEntry' => array('H' => '1','O' => '1','Cl' => '-1'))));$this->outcome['SCORE'] = new qti_variable('single', 'float', array());
       
    }
}