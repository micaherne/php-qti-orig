<?php 
class gap_match_controller extends qti_item_controller {

    		public function __construct() {
$this->identifier = 'gapMatch';
$this->title = 'Richard III (Take 1)';
$this->adaptive = 'false';
$this->timeDependent = 'false';
$p = new qti_item_body($this);
$p->itemBody(array(),
$p->gapMatchInteraction(array('responseIdentifier' => 'RESPONSE', 'shuffle' => 'false'),
$p->prompt(array(),
$p->__text('Identify the missing words in this famous quote from Shakespeare\'s Richard III.')),
$p->gapText(array('identifier' => 'W', 'matchMax' => '1'),
$p->__text('winter')),
$p->gapText(array('identifier' => 'Sp', 'matchMax' => '1'),
$p->__text('spring')),
$p->gapText(array('identifier' => 'Su', 'matchMax' => '1'),
$p->__text('summer')),
$p->gapText(array('identifier' => 'A', 'matchMax' => '1'),
$p->__text('autumn')),
$p->blockquote(array(),
$p->p(array(),
$p->__text('Now is the '),
$p->gap(array('identifier' => 'G1')),
$p->__text(' of our discontent'),
$p->br(array()),
$p->__text(' Made glorious '),
$p->gap(array('identifier' => 'G2')),
$p->__text(' by this sun of York;'),
$p->br(array()),
$p->__text(' And all the clouds that lour\'d
					upon our house'),
$p->br(array()),
$p->__text(' In the deep bosom of the ocean buried.')))));
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
$this->response['RESPONSE'] = new qti_variable('multiple', 'directedPair', array('correctResponse' => array('W G1','Su G2'),'mapping' => array('defaultValue' => '-1','lowerBound' => '0','mapEntry' => array('W G1' => '1','Su G2' => '2'))));$this->outcome['SCORE'] = new qti_variable('single', 'float', array());}}