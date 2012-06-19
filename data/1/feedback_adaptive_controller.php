<?php 
class feedback_adaptive_controller extends qti_item_controller {

    		public function __construct() {
$this->identifier = 'feedbackAdaptive';
$this->title = 'Mexican President with adaptive feedback';
$this->adaptive = 'true';
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
					Prime Minister of Israel.')))),
$p->feedbackBlock(array('outcomeIdentifier' => 'FEEDBACK', 'showHide' => 'show', 'identifier' => 'again'),
$p->p(array(),
$p->__text('You already tried that option!'))));
$this->item_body = $p;

$r = new qti_response_processing($this);
$r->responseProcessing(array(),
$r->setOutcomeValue(array('identifier' => 'completion_status'),
$r->baseValue(array('baseType' => 'identifier'),
$r->__text('incomplete'))),
$r->responseCondition(array(),
$r->responseIf(array(),
$r->lt(array(),
$r->variable(array('identifier' => 'numAttempts')),
$r->baseValue(array('baseType' => 'integer'),
$r->__text('3'))),
$r->setOutcomeValue(array('identifier' => 'FEEDBACK'),
$r->multiple(array(),
$r->baseValue(array('baseType' => 'identifier'),
$r->__text('tryAgain'))))),
$r->responseElseIf(array(),
$r->lt(array(),
$r->variable(array('identifier' => 'numAttempts')),
$r->baseValue(array('baseType' => 'integer'),
$r->__text('4'))),
$r->setOutcomeValue(array('identifier' => 'FEEDBACK'),
$r->multiple(array(),
$r->baseValue(array('baseType' => 'identifier'),
$r->__text('oneMore'))))),
$r->responseElse(array(),
$r->setOutcomeValue(array('identifier' => 'FEEDBACK'),
$r->multiple(array(),
$r->baseValue(array('baseType' => 'identifier'),
$r->__text('giveUp')))),
$r->setOutcomeValue(array('identifier' => 'completion_status'),
$r->baseValue(array('baseType' => 'identifier'),
$r->__text('completed'))))),
$r->responseCondition(array(),
$r->responseIf(array(),
$r->match(array(),
$r->variable(array('identifier' => 'RESPONSE')),
$r->correct(array('identifier' => 'RESPONSE'))),
$r->setOutcomeValue(array('identifier' => 'SCORE'),
$r->baseValue(array('baseType' => 'float'),
$r->__text('1'))),
$r->setOutcomeValue(array('identifier' => 'completion_status'),
$r->baseValue(array('baseType' => 'identifier'),
$r->__text('completed'))),
$r->setOutcomeValue(array('identifier' => 'FEEDBACK'),
$r->variable(array('identifier' => 'RESPONSE')))),
$r->responseElse(array(),
$r->setOutcomeValue(array('identifier' => 'SCORE'),
$r->baseValue(array('baseType' => 'float'),
$r->__text('0'))),
$r->setOutcomeValue(array('identifier' => 'FEEDBACK'),
$r->multiple(array(),
$r->variable(array('identifier' => 'FEEDBACK')),
$r->variable(array('identifier' => 'RESPONSE')))))),
$r->responseCondition(array(),
$r->responseIf(array(),
$r->member(array(),
$r->variable(array('identifier' => 'PREVIOUSRESPONSES')),
$r->variable(array('identifier' => 'RESPONSE'))),
$r->setOutcomeValue(array('identifier' => 'FEEDBACK'),
$r->multiple(array(),
$r->variable(array('identifier' => 'FEEDBACK')),
$r->baseValue(array('baseType' => 'identifier'),
$r->__text('again'))))),
$r->responseElse(array(),
$r->setOutcomeValue(array('identifier' => 'PREVIOUSRESPONSES'),
$r->multiple(array(),
$r->variable(array('identifier' => 'PREVIOUSRESPONSES')),
$r->variable(array('identifier' => 'RESPONSE')))),
$r->setOutcomeValue(array('identifier' => 'FEEDBACK'),
$r->delete(array(),
$r->variable(array('identifier' => 'FEEDBACK')),
$r->baseValue(array('baseType' => 'identifier'),
$r->__text('again')))))));
$this->response_processing = $r;
$m = new qti_modal_feedback_processing($this);
$m->modalFeedback(array('outcomeIdentifier' => 'FEEDBACK', 'identifier' => 'MGH001C', 'showHide' => 'show'),
$m->__text('Yes, that is
		correct.'));
$m->modalFeedback(array('outcomeIdentifier' => 'FEEDBACK', 'identifier' => 'tryAgain', 'showHide' => 'show'),
$m->__text('No, that is
		not correct.'));
$m->modalFeedback(array('outcomeIdentifier' => 'FEEDBACK', 'identifier' => 'oneMore', 'showHide' => 'show'),
$m->__text('No, that is not
		correct, try one more time.'));
$m->modalFeedback(array('outcomeIdentifier' => 'FEEDBACK', 'identifier' => 'giveUp', 'showHide' => 'show'),
$m->__text('No, the correct
		answer is Vicente Fox.'));
$this->modal_feedback_processing = $m;
}    public function beginAttempt() {
        parent::beginAttempt();
$this->response['RESPONSE'] = new qti_variable('single', 'identifier', array('correctResponse' => 'MGH001C'));$this->outcome['PREVIOUSRESPONSES'] = new qti_variable('multiple', 'identifier', array());$this->outcome['SCORE'] = new qti_variable('single', 'float', array());$this->outcome['FEEDBACK'] = new qti_variable('multiple', 'identifier', array());}}