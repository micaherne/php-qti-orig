<?php

class choice_controller extends qti_item_controller {
    
    public function __construct() {
        $this->rootdir = dirname(__FILE__);
    }
    
    public function beginAttempt() {
        parent::beginAttempt();
        
        $this->response['RESPONSE'] = new qti_variable('single', 'identifier', array(
            'correct' => 'ChoiceA'
        ));
        $this->outcome['SCORE'] = new qti_variable('single', 'integer', array(
            'default' => 0
        ));
        
    }
    
    public function processResponse() {
        $p = new qti_response_processing($this);
        $p->responseProcessing(
            $p->responseCondition(
                $p->responseIf(
                    $p->match(
                        $p->variable(array('identifier' => 'RESPONSE')),
                        $p->correct(array('identifier' => 'RESPONSE'))
                    ),
                    $p->setOutcomeValue(array('identifier' => 'SCORE'),
                        $p->baseValue(array('baseType' => 'integer'), 1)
                    )
                ),
                $p->responseElse(
                    $p->setOutcomeValue(array('identifier' => 'SCORE'),
                        $p->baseValue(array('baseType' => 'integer'), 0)
                    )
                )
            )
        );
        $p->execute();
    }
    
}