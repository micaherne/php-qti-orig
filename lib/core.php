<?php

/* Begin QTI Element classes
 *
* These are directly related to a QTI element type
*
*/

class qti_choiceInteraction extends qti_element{

        /* TODO: We'd really like to tell the simpleChoice elements what type of
         * input control they're to display in the constructor, but we don't have access to the
        * variable declarations.
        */
    
    public $simpleChoice = array();
    public $fixed = array(); // indices of simpleChoices with fixed set to true
    public $prompt;

    public function __invoke($controller) {
        $variableName = $this->attrs['responseIdentifier'];
        $result = "<form method=\"post\" id=\"choiceInteraction_{$variableName}\" class=\"qti_blockInteraction\">";

        // Work out what kind of HTML tag will be used for simpleChoices
        if (!isset($controller->response[$variableName])) {
            throw new Exception("Declaration for $variableName not found");
        }

        $responseVariable = $controller->response[$variableName];
        $simpleChoiceType = 'radio';
        $brackets = ''; // we need brackets for multiple responses
        if ($responseVariable->cardinality == 'multiple') {
            $simpleChoiceType = 'checkbox';
            $brackets = '[]';
        }

        $this->simpleChoice = array();
        $this->fixed = array();
        // Process child nodes
        foreach($this->children as $child) {
            if ($child instanceof qti_prompt) {
                $this->prompt = $child;
            } else if ($child instanceof qti_simpleChoice) {
                $child->inputType = $simpleChoiceType;
                $child->name = $variableName.$brackets;
                $this->simpleChoice[] = $child;
                if($child->attrs['fixed'] === 'true') {
                    $this->fixed[] = count($this->simpleChoice) - 1;
                }
            }
        }
        $result .= $this->prompt->__invoke($controller);
        
        // Work out an order to display them in
        // TODO: Worst implementation ever!
        $order = range(0, count($this->simpleChoice) - 1);
        if ($this->attrs['shuffle'] === 'true') {
            $notfixed = array_diff($order, $this->fixed);
            shuffle($notfixed);
            $shuffledused = 0;
            for($i = 0; $i < count($this->simpleChoice); $i++) {
                if(in_array($i, $this->fixed)) {
                    $result .= $this->simpleChoice[$i]->__invoke($controller);
                } else {
                    $result .= $this->simpleChoice[$notfixed[$shuffledused++]]->__invoke($controller);
                }
            }
        } else {
            foreach($order as $i) {
                $result .= $this->simpleChoice[$i]->__invoke($controller);
            }
        }
        
        $result .= "<input type=\"submit\" />";
        $result .= "</form>";
        return $result;
    }

}

class qti_element {

    public $attrs;
    public $children;

    public function __construct($attrs, $children) {
        $this->attrs = $attrs;
        $this->children = $children;
    }
    
}

class qti_prompt extends qti_element{

    public function __invoke($controller) {
        $result .= '<span class="qti_prompt">';
        foreach($this->children as $child) {
            $result .= $child->__invoke($controller);
        }
        $result .= "</span>";
        return $result;
    }

}

class qti_simpleChoice extends qti_element{

    public function __invoke($controller) {
        $result = "<span class=\"qti_simpleChoice\">\n";
        $result .= "<input type=\"{$this->inputType}\" name=\"{$this->name}\" value=\"{$this->attrs['identifier']}\"></input>\n";
        foreach($this->children as $child) {
            $result .= $child($controller);
        }
        $result .= "</span>";
        return $result;
    }

}

/* End QTI Element classes */

/* Begin PHP-QTI operational classes */


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
    public $resource_provider; // provides URLs for images etc.

    public $response_processing; // closure which processes responses
    public $item_body; // closure which displays item body

    public $rootdir;
    public $view;

    public function __construct() {

    }

    public function setUpDefaultVars() {
        // Built-in variables (section 5.1.1 & 5.2.1 of info model)
        $this->response['numAttempts'] = new qti_variable('single', 'integer', array('value' => 0));
        $this->response['duration'] = new qti_variable('single', 'float', array('value' => 0));
        $this->outcome['completionStatus'] = new qti_variable('single', 'identifier', array('value' => 'not_attempted'));
    }

    public function showItemBody() {
        // TODO: Does this resource provider thing work with the new item_body function?
        $resource_provider = $this->resource_provider;
        echo $this->item_body->execute();
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

        echo "<hr />Memory: " . memory_get_peak_usage() / (1024 * 1024) . "Mb"; // TODO: Remove this debugging

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
            if($submittedvalue = $this->response_source->get($key)) {
                $this->response[$key]->value = $submittedvalue;
            }
        }
    }

    public function processResponse() {
        $this->response_processing->execute();
    }

}

class qti_variable {

    public $cardinality;
    public $type;
    // For response vars, QTI has a candidateResponse wrapper for the value - any reason to implement?
    public $value;
    public $correct;
    public $default;
    public $mapping;

    /**
     * Create a qti variable
     * @param string $cardinality
     * @param string $type
     * @param array $params
     */
    public function __construct($cardinality, $type, $params = array()) {
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

    // Implement mapResponse processing here because it's sensible!
    public function mapResponse() {
        // TODO: Check mapping is defined here?
        if ($this->cardinality == 'single') {
            if (in_array($this->value, $this->mapping->mapEntry)) {
                $value = $this->mapping->mapEntry[$this->value];
            } else {
                $value = $this->mapping->defaultValue;
            }
        } else {
            $value = 0;
            // array_unique used because values should only be counted once - see mapResponse documentation
            foreach(array_unique($this->value) as $response) {
                if (array_key_exists($response, $this->mapping->mapEntry)) {
                    $value += $this->mapping->mapEntry[$response];
                } else {
                    $value += $this->mapping->defaultValue;
                }
            }
        }

        return new qti_variable('single', 'float', array('value' => $value));
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

    public function __toString(){
        return $this->cardinality . ' ' . $this->type . ' [' . (is_array($this->value) ? implode(',', $this->value) : $this->value) . ']';
    }

}

class qti_mapping {

    public $lowerBound;
    public $upperBound;
    public $defaultValue;

    public $mapEntry = array();

    public function __construct($params) {
        // TODO: Check the params are OK
        foreach($params as $key => $value) {
            $this->$key = $value;
        }
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
        return $_POST[$name];
    }

    public function isEndAttempt() {
        return count($_POST) > 0; // TODO: Finish - how do we really check if they've ended the attempt
    }

}

class qti_response_processing_exception extends Exception {

}

class qti_item_body {

    protected $controller;

    protected $displayFunction;

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
        if (count($args) > 0 && is_array($args[0])) {
            $attrs = array_shift($args);
        } else {
            $attrs = array();
        }
        $realclassname = "qti_$name";
        if (class_exists($realclassname)) {
            return new $realclassname($attrs, $args);
        }
        $realmethodname = "_$name";
        if (method_exists($this, $realmethodname)) {
            return $this->$realmethodname($attrs, $args);
        }

        // default to just creating a basic HTML element
        return $this->__default($name, $attrs, $args);
    }

    // Just return a function to create a basic HTML element
    public static function __default($name, $attrs, $args) {
        return function($controller) use ($name, $attrs, $args) {
            $result = "<$name";
            if(!empty($attrs)) {
                foreach($attrs as $key => $value) {
                    $result .= " $key=\"$value\"";
                }
            }
            $result .= ">";
            if(!empty($args)) {
                foreach($args as $child) {
                    $result .= $child->__invoke($controller);
                }
            }
            $result .= "</$name>";
            return $result;
        };
    }

    /*     public static function _choiceInteraction($attrs, $children) {
     // test
    $result = new qti_choiceInteraction($attrs, $children);

    return $result;
    }

    public static function _simpleChoice($attrs, $children) {
    // test
    $result = new qti_simpleChoice($attrs, $children);

    return $result;
    } */

    public static function __text($text) {
        return function($controller) use ($text) {
            return $text;
        };
    }

    public function execute() {
        return ($this->displayFunction->__invoke($this->controller));
    }

    public function _itemBody($attrs, $children) {
        $this->displayFunction = function($controller) use($children) {
            $result = '';
            foreach($children as $child) {
                $result .= $child->__invoke($controller);
            }
            return $result;
        };
    }

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

    public function __text($text) {
        return function($controller) use ($text) {
            return $text;
        };
    }

    public function execute() {
        $this->processingFunction->__invoke($this->controller);
        echo "DEBUG: SCORE = " . $this->controller->outcome['SCORE'];
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
            	'value' => $children[0]($controller)
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
        return function($controller) use ($attrs, $children) {
            $varname = $attrs['identifier'];
            if(isset($controller->response[$varname])) {
                return $controller->response[$varname]->mapResponse();
            } else {
                throw new qti_response_processing_exception("Variable $varname not found");
            }
        };
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
        return function($controller) use ($attrs, $children) {
            $what = $children[0]->__invoke($controller);
            return (!isset($what->value) || is_null($what->value));
        };
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
            print_r($val1); print_r($val2);
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
