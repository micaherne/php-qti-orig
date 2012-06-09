<?php 
class choice_multiple_chocolade_controller extends qti_item_controller {

    		public function __construct() {
$p = new qti_item_body($this);
$p->itemBody(array(),
$p->choiceInteraction(array('responseIdentifier' => 'MR01', 'shuffle' => 'true', 'maxChoices' => '10'),
$p->prompt(array(),
$p->__text('How to make chocolate milk. Select the combination of steps that lead to a nice
				glass of hot and steamy chocolate milk.')),
$p->simpleChoice(array('identifier' => 'C01', 'fixed' => 'false'),
$p->__text('Take a lighter')),
$p->simpleChoice(array('identifier' => 'C02', 'fixed' => 'false'),
$p->__text('Open the gas on the stove')),
$p->simpleChoice(array('identifier' => 'C03', 'fixed' => 'false'),
$p->__text('Light the gas')),
$p->simpleChoice(array('identifier' => 'C04', 'fixed' => 'false'),
$p->__text('Poor the milk in the pan')),
$p->simpleChoice(array('identifier' => 'C05', 'fixed' => 'false'),
$p->__text('Add 2 tea spoons of cocoa into the mug')),
$p->simpleChoice(array('identifier' => 'C06', 'fixed' => 'false'),
$p->__text('Add 2 tea spoons of sugar into the mug')),
$p->simpleChoice(array('identifier' => 'C07', 'fixed' => 'false'),
$p->__text('Add 2 spoons of water into the mug')),
$p->simpleChoice(array('identifier' => 'C08', 'fixed' => 'false'),
$p->__text('Stir the water, cocoa and sugar until the
				mixture is smooth')),
$p->simpleChoice(array('identifier' => 'C09', 'fixed' => 'false'),
$p->__text('Put the pan with milk on the stove')),
$p->simpleChoice(array('identifier' => 'C10', 'fixed' => 'false'),
$p->__text('Poor the boiling milk into the mug')),
$p->simpleChoice(array('identifier' => 'C11', 'fixed' => 'false'),
$p->__text('Put the mug with the mixture and milk into
				the microwave')),
$p->simpleChoice(array('identifier' => 'C12', 'fixed' => 'false'),
$p->__text('Add milk to the mug with the smooth mixture')),
$p->simpleChoice(array('identifier' => 'C13', 'fixed' => 'false'),
$p->__text('Add cold milk from the fridge into the mug
				with smooth mixture')),
$p->simpleChoice(array('identifier' => 'C14', 'fixed' => 'false'),
$p->__text('Set the microwave on 700 Watt and set the
				timer to 2 minutes'))));
$this->item_body = $p;

$r = new qti_response_processing($this);
$r->responseProcessing(array(),
$r->responseCondition(array(),
$r->responseIf(array(),
$r->match(array(),
$r->variable(array('identifier' => 'MR01')),
$r->baseValue(array('baseType' => 'identifier'),
$r->__text('C01 C02 C03 C04 C05 C06 C07 C08 C09
					C10'))),
$r->setOutcomeValue(array('identifier' => 'SCORE'),
$r->baseValue(array('baseType' => 'float'),
$r->__text('1')))),
$r->responseElseIf(array(),
$r->match(array(),
$r->variable(array('identifier' => 'MR01')),
$r->baseValue(array('baseType' => 'identifier'),
$r->__text('C11 C05 C06 C07 C08 C12 C13 C14'))),
$r->setOutcomeValue(array('identifier' => 'SCORE'),
$r->baseValue(array('baseType' => 'float'),
$r->__text('1'))))));
$this->response_processing = $r;
}    public function beginAttempt() {
        parent::beginAttempt();
$this->response['MR01'] = new qti_variable('multiple', 'identifier', array());$this->outcome['SCORE'] = new qti_variable('single', 'integer', array());}}