<?php


class qti_item_controller {

    const STATE_NONE = 0;
    const STATE_INITIAL = 10;
    const STATE_INTERACTING = 20;
    const STATE_SUSPENDED = 30;
    const STATE_CLOSED = 40;
    const STATE_REVIEW = 50;
    const STATE_MODALFEEDBACK = 60;
    const STATE_SOLUTION = 70;

    public $state = qti_item_controller::STATE_NONE;

    public $response = array();
    public $outcome = array();

    public $response_source; // provides response values for variables
    public $persistence; // provides existing values of variables

    public $rootdir;

    public function __construct() {
        $this->rootdir = dirname(__FILE__);
    }

    public function setUpDefaultVars() {
        // Built-in variables (section 5.1.1 & 5.2.1 of info model)
        $this->response['numAttempts'] = new qti_variable('single', 'integer', array('value' => 0));
        $this->response['duration'] = new qti_variable('single', 'float', array('value' => 0));
        $this->outcome['completionStatus'] = new qti_variable('single', 'identifier', array('value' => 'not_attempted'));
    }

    public function showItemBody() {
        include $rootdir . '/gen_choice_view.php'; // TODO: fix - needs to be correct view file
    }

    public function run() {
        if ($this->state == qti_item_controller::STATE_NONE) {
            $this->beginItemSession();
        }

        if ($this->state == qti_item_controller::STATE_INTERACTING) {
            if($this->response_source->isEndAttempt()) {
                // TODO: fix (the person has submitted the item)
                $this->endAttempt();
            } else {
                $this->showItemBody();
            }
        }

        $this->persistence->persist($this);
    }

    public function beginItemSession() {
        $this->state = qti_item_controller::STATE_INITIAL;
        $this->setUpDefaultVars();
        $this->beginAttempt();
    }

    public function beginAttempt() {
        $this->state = qti_item_controller::STATE_INTERACTING;
    }

    public function endAttempt() {
        $this->bindVariables();
        $this->processResponse();
        $this->state = qti_item_controller::STATE_CLOSED; // TODO: What should this be? Does it depend on response processing?
    }
    
    // Bind the responses to the controller variables
    public function bindVariables() {
        foreach($this->response as $key => $val) {
            // TODO: Make this work for multiple valued interactions
            if($submittedvalue = $this->response_source->get($key)) {
                $this->response[$key]->value = $submittedvalue;
            }
        }
    }

    public function processResponse() {
        // TODO: This should be overriden for all controllers - what's a sensible default here?
        // TODO: How do we know what state to put it into?
        echo "DEBUG: You chose: " . $this->response_source->get('RESPONSE');
    }
}

class qti_variable {

    public $cardinality;
    public $type;
    // For response vars, QTI has a candidateResponse wrapper for the value - any reason to implement?
    public $value;
    public $correct;
    public $default;

    /**
     * Create a qti variable
     * @param string $cardinality
     * @param string $type
     * @param array $params
     */
    public function __construct($cardinality, $type, $params) {
        $this->cardinality = $cardinality;
        $this->type = $type;

        $this->value = null;
        if (isset($params['value'])) {
            $this->value = $params['value'];
        }

        $this->correct = null;
        if(isset($params['correct'])) {
            $this->correct = $params['correct'];
        }

        $this->default = null;
        if(isset($params['default'])) {
            $this->default = $params['default'];
            $this->value = $this->default;
        }
    }
    
    // TODO: Make this work for things other than strings
    public static function compare($variable1, $variable2) {
        return strcmp($variable1->value, $variable2->value);
    }

    // Return a qti_variable representing the default
    public function getDefault() {
        return new qti_variable($this->cardinality, $this->type, array('value' => $this->default));
    }

    // Return a qti_variable representing the correct value
    public function getCorrect() {
        return new qti_variable($this->cardinality, $this->type, array('value' => $this->correct));
    }

    /**
     * Set the value of the variable
     * @param qti_variable $value The value either as a qti_variable
     */
    public function setValue($value) {
        $this->value = $value->value;
    }
    
}

class qti_persistence {

    public function persist($controller) {
        // TODO: Implement properly
        session_start();
        $_SESSION['response'] = $controller->response;
        $_SESSION['outcome'] = $controller->outcome;
        $_SESSION['state'] = $controller->state;
        session_write_close();
    }

    public function restore($controller) {
        // TODO: Implement properly
        session_start();
        if (!isset($_SESSION['state'])) {
            return;
        }
        echo "DEBUG: Restoring session";
        $controller->response = $_SESSION['response'];
        $controller->outcome = $_SESSION['outcome'];
        $controller->state = $_SESSION['state'];
        session_write_close();
    }

}

class qti_http_response_source {

    public function get($name) {
        return $_GET[$name];
    }

    public function isEndAttempt() {
        return count($_GET) > 0; // TODO: Finish - how do we really check if they've ended the attempt
    }

}

class qti_response_processing_exception extends Exception {

}

class qti_response_processing {

    protected $controller;

    protected $processingFunction;

    public function __construct(qti_item_controller $controller) {
        $this->controller = $controller;
    }

    /**
     * Magic function to simplify creating processing methods. If the first string
     * passed to the function is an array, it will be assumed to be an associative
     * array of attribute name/value pairs, otherwise an empty attribute array will
     * be passed to the underlying method.
     *
     * e.g. __call('test', array('id' => 12), object1, object2) will cause the following
     * method call: _test(array('id' => 12), object1, object2)
     * whereas __call('test', object1, object2) will cause the following:
     * _test(array(), object1, object2)
     *
     * This is because most processing instructions don't need attributes, but it could
     * be a source of bugs if we had to remember to generate an empty array each time.
     * @param unknown_type $name
     * @param unknown_type $args
     * @throws Exception
     */
    public function __call($name, $args) {
        $realmethodname = "_$name";
        if (method_exists($this, $realmethodname)) {
            if (count($args) > 0 && is_array($args[0])) {
                $attrs = array_shift($args);
            } else {
                $attrs = array();
            }
            return $this->$realmethodname($attrs, $args);
        }

        throw new Exception("qti_response_processing method _$name not found");
    }

    public function execute() {
        ($this->processingFunction->__invoke($this->controller));
        echo "DEBUG: SCORE = " . print_r($this->controller->outcome['SCORE'], true);
    }

    /*
     * TODO: Implement
    * 8.2. Generalized Response Processing
    */

    public function _responseProcessing($attrs, $children) {
        $this->processingFunction = function($controller) use($children) {
            foreach($children as $child) {
                $child->__invoke($controller);
            }
        };
    }

    public function _responseCondition($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            foreach($children as $child) {
                $result = $child->__invoke($controller);
                if ($result->value === true) {
                    break;
                }
            }
        };
    }

    public function _responseIf($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $result = $children[0]->__invoke($controller);
            if ($result->value === true) {
                $children[1]->__invoke($controller);
            }
            return $result;
        };
    }

    public function _responseElseIf($attrs, $children) {
        // Identical to responseIf
        return function($controller) use ($attrs, $children) {
            $result = $children[0]->__invoke($controller);
            if ($result->value === true) {
                $children[1]->__invoke($controller);
            }
            return $result;
        };
    }

    public function _responseElse($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $result = $children[0]->__invoke($controller);
        };
    }

    public function _setOutcomeValue($attrs, $children) {
        return function($controller) use($attrs, $children) {
            $varname = $attrs['identifier'];
            $controller->outcome[$varname]->setValue($children[0]->__invoke($controller));
        };
    }

    public function _lookupOutcomeValue($attrs, $children) {
        throw new Exception("Not implemented");
    }



    /*
     * 15.1. Built-in General Expressions
    */

    public function _baseValue($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            return new qti_variable('single', $attrs['baseType'], array(
            	'value' => $children[0]
            ));
        };
    }

    public function _variable($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $varname = $attrs['identifier'];
            if(isset($controller->response[$varname])) {
                return $controller->response[$varname];
            } else if (isset($controller->outcome[$varname])) {
                return $controller->outcome[$varname];
            } else {
                throw new qti_response_processing_exception("Variable $varname not found");
            }
        };
    }

    public function _default($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $varname = $attrs['identifier'];
            if(isset($controller->response[$varname])) {
                return $controller->response[$varname]->getDefault();
            } else if (isset($controller->outcome[$varname])) {
                return $controller->outcome[$varname]->getDefault();
            } else {
                throw new qti_response_processing_exception("Variable $varname not found");
            }
        };
    }

    public function _correct($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $varname = $attrs['identifier'];
            if(isset($controller->response[$varname])) {
                return $controller->response[$varname]->getCorrect();
            } else {
                throw new qti_response_processing_exception("Variable $varname not found");
            }
        };
        
    }

    public function _mapResponse($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _mapResponsePoint($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _null($attrs, $children) {
        // Create as single identifier, although it can be matched against any other null
        return function($controller) use ($attrs, $children) {
            return new qti_variable('single', 'identifier', array(
                'value' => null
            ));
        };
    }

    public function _randomInteger($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _randomFloat($attrs, $children) {
        throw new Exception("Not implemented");
    }

    /*
     * TODO: Implement
    * 15.2. Expressions Used only in Outcomes Processing
    */

    /*
     * 15.3. Operators
    */
    public function _multiple($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _ordered($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _containerSize($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _isNull($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _index($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _fieldValue($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _random($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _member($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _delete($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _contains($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _substring($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _not($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _and($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _or($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _anyN($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _match($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            $val2 = $children[1]->__invoke($controller);
            // TODO: Make work for arrays, floats etc.
            return  new qti_variable('single', 'boolean', array(
                'value' => (qti_variable::compare($val1, $val2) === 0) ? true : false
            ));
        };
    }

    public function _stringMatch($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _patternMatch($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _equal($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _equalRounded($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _inside($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _lt($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _gt($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _lte($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _gte($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _durationLT($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _durationGTE($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _sum($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _product($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _subtract($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _divide($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _power($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _integerDivide($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _integerModulus($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _truncate($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _round($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _integerToFloat($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _customOperator($attrs, $children) {
        throw new Exception("Not implemented");
    }







}
