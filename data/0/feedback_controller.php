<?php 
class feedback_controller extends qti_item_controller {

    		public function __construct() {
$this->identifier = 'feedback';
$this->title = 'Mexican President';
$this->adaptive = 'false';
$this->timeDependent = 'false';
$p = new qti_item_body($this);
$p->itemBody(array(),
$p->choiceInteraction(array('responseIdentifier' => 'RESPONSE', 'shuffle' => 'false', 'maxChoices' => '1'),
$p->prompt(array(),
$p->__text('Who is the President of Mexico?')),
$p->simpleChoice(array('identifier' => 'MGH001A'),
$p->__text(' George W Bush '),
$p->feedbackInline(array('outcomeIdentifier' => 'FEEDBACK', 'identifier' => 'MGH001A', 'showHide' => 'show'),
$p->__text('No, he is the
					President of the USA.'))),
$p->simpleChoice(array('identifier' => 'MGH001B'),
$p->__text(' Tony Blair '),
$p->feedbackInline(array('outcomeIdentifier' => 'FEEDBACK', 'identifier' => 'MGH001B', 'showHide' => 'show'),
$p->__text('No, he is the
					Prime Minister of England.'))),
$p->simpleChoice(array('identifier' => 'MGH001C'),
$p->__text(' Vicente Fox '),
$p->feedbackInline(array('outcomeIdentifier' => 'FEEDBACK', 'identifier' => 'MGH001C', 'showHide' => 'show'),
$p->__text('Yes.'))),
$p->simpleChoice(array('identifier' => 'MGH001D'),
$p->__text(' Ariel Sharon '),
$p->feedbackInline(array('outcomeIdentifier' => 'FEEDBACK', 'identifier' => 'MGH001D', 'showHide' => 'show'),
$p->__text('No, he is the
					Prime Minister of Israel.')))));
$this->item_body = $p;

$r = new qti_response_processing($this);
$r->responseProcessing(array(),
$r->responseCondition(array(),
$r->responseIf(array(),
$r->match(array(),
$r->variable(array('identifier' => 'RESPONSE')),
$r->correct(array('identifier' => 'RESPONSE'))),
$r->setOutcomeValue(array('identifier' => 'SCORE'),
$r->baseValue(array('baseType' => 'float'),
$r->__text('1')))),
$r->responseElse(array(),
$r->setOutcomeValue(array('identifier' => 'SCORE'),
$r->baseValue(array('baseType' => 'float'),
$r->__text('0'))))),
$r->setOutcomeValue(array('identifier' => 'FEEDBACK'),
$r->variable(array('identifier' => 'RESPONSE'))));
$this->response_processing = $r;
$m = new qti_modal_feedback_processing($this);
$m->modalFeedback(array('outcomeIdentifier' => 'FEEDBACK', 'identifier' => 'MGH001C', 'showHide' => 'show'),
$m->__text('Yes, that is
		correct.'));
$m->modalFeedback(array('outcomeIdentifier' => 'FEEDBACK', 'identifier' => 'MGH001C', 'showHide' => 'hide'),
$m->__text('No, the correct
		answer is Vicente Fox.'));
$this->modal_feedback_processing = $m;
}    public function beginAttempt() {
        parent::beginAttempt();
$this->response['RESPONSE'] = new qti_variable('single', 'identifier', array('correctResponse' => 'MGH001C'));$this->outcome['SCORE'] = new qti_variable('single', 'float', array());$this->outcome['FEEDBACK'] = new qti_variable('single', 'identifier', array());}}