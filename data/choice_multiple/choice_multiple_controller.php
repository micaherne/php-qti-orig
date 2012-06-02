<?php

class choice_multiple_controller extends qti_item_controller {
    
    public function __construct() {
        $this->rootdir = dirname(__FILE__);
    }
    
    public function beginAttempt() {
        parent::beginAttempt();
        
        $this->response['RESPONSE'] = new qti_variable('multiple', 'identifier', array(
            'correct' => array('H', 'O')
        ));
        $this->response['RESPONSE']->mapping = new qti_mapping(array(
        	'lowerBound' => 0, 'upperBound' => 2, 'defaultValue' => -2,
            'mapEntry' => array(
                'H' => 1,
                'O' => 1,
                'Cl' => -1 
            )
        ));
        $this->outcome['SCORE'] = new qti_variable('single', 'float');
        
    }
    
    public function processResponse() {
        $p = new qti_response_processing($this);
        $p->responseProcessing(
            $p->responseCondition(
                $p->responseIf(
                    $p->isNull(
                        $p->variable(array('identifier' => 'RESPONSE'))
                    ),
                    $p->setOutcomeValue(array('identifier' => 'SCORE'),
                        $p->baseValue(array('baseType' => 'float'), 0.0)
                    )
                ),
                $p->responseElse(
                    $p->setOutcomeValue(array('identifier' => 'SCORE'),
                        $p->mapResponse(array('identifier' => 'RESPONSE'))
                    )
                )
            )
        );
        $p->execute();
    }
    
}