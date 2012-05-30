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
        $this->response['numAttempts'] = new qti_variable('single', 'integer', 0);
        $this->response['duration'] = new qti_variable('single', 'float', 0);
        $this->outcome['completionStatus'] = new qti_variable('single', 'identifier', 'not_attempted');
    }
    
    public function showItemBody() {
        include $rootdir . '/gen_choice_view.php'; // TODO: fix - needs to be correct view file
    }
    
    public function run() {
        if ($this->state == qti_item_controller::STATE_NONE) {
            $this->beginItemSession();
        }
        
        if ($this->state == qti_item_controller::STATE_INTERACTING) {
            if($this->response_source->isEndAttempt()) { // TODO: fix (the person has submitted the item)
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
        $this->processResponse();
        $this->state = qti_item_controller::STATE_CLOSED; // TODO: What should this be? Does it depend on response processing?
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
        
        //throw new Exception("qti_response_processing method _$name not found");
        
        echo "$name, ";
    }
    
    public function _baseValue($attrs, $children) {
        return new qti_variable('single', $attrs['baseType'], $children[0]);
    }
    
    public function _variable($attrs, $children) {
        $varname = $attrs['identifier'];
        if(isset($this->controller->response[$varname])) {
            return $this->controller->response[$varname];
        } else if (isset($this->controller->outcome[$varname])) {
            return $this->controller->outcome[$varname];
        } else {
            throw new qti_response_processing_exception("Variable $varname not found");
        }
    }
    
    public function _default($attrs, $children) {
        // TODO: Implement
    }
    
    public function _correct($attrs, $children) {
        
    }
    
}
