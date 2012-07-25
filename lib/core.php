<?php

/* Begin QTI Element classes
 *
* These are directly related to a QTI element type
*
*/

class qti_orderInteraction extends qti_element {

    /* TODO: We'd really like to tell the simpleChoice elements what type of
     * input control they're to display in the constructor, but we don't have access to the
    * variable declarations.
    */

    public $simpleChoice = array();
    public $fixed = array(); // indices of simpleChoices with fixed set to true
    public $prompt;

    public function __invoke($controller) {
        $variableName = $this->attrs['responseIdentifier'];
        $result = "<form method=\"post\" id=\"orderInteraction_{$variableName}\" class=\"qti_blockInteraction\">";

        // Work out what kind of HTML tag will be used for simpleChoices
        if (!isset($controller->response[$variableName])) {
            throw new Exception("Declaration for $variableName not found");
        }

        $responseVariable = $controller->response[$variableName];
        $simpleChoiceType = 'input';
        $brackets = ''; // we need brackets for multiple responses
        
        $this->simpleChoice = array();
        $this->fixed = array();
        
        // Count simple choices
        $numberOfChoices = 0;
        foreach($this->children as $child) {
            if ($child instanceof qti_simpleChoice) {
                $numberOfChoices++;
            }
        }
        // Process child nodes
        foreach($this->children as $child) {
            if ($child instanceof qti_prompt) {
                $this->prompt = $child;
            } else if ($child instanceof qti_simpleChoice) {
                $child->inputType = 'input';
                $child->interactionType = 'orderInteraction';
                $child->name = $variableName.$brackets;
                $child->numberOfChoices = $numberOfChoices;
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

        $result .= "<input type=\"submit\"  value=\"Submit response\"/>";
        $result .= "</form>";
        return $result;
    }

}

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
        $result = "<div id=\"choiceInteraction_{$variableName}\" class=\"qti_blockInteraction\">";

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
        
        if (!is_null($this->prompt)) {
            $result .= $this->prompt->__invoke($controller);
        }

        $shuffle = $this->attrs['shuffle'] === 'true';
        $choiceIterator = new qti_choiceIterator($this->simpleChoice, $shuffle);
        foreach($choiceIterator as $choice) {
            $result .= $choice->__invoke($controller);
        }

        $result .= "</div>";
        return $result;
    }

}

class qti_associateInteraction extends qti_element {

    public $simpleAssociableChoice = array();
    public $fixed = array(); // indices of simpleAssociableChoices with fixed set to true
    public $prompt;

    public function __invoke($controller) {
        $variableName = $this->attrs['responseIdentifier'];
        $result = "<div id=\"associateInteraction_{$variableName}\" class=\"qti_blockInteraction\">";

        // Work out what kind of HTML tag will be used for simpleAssociableChoices
        if (!isset($controller->response[$variableName])) {
            throw new Exception("Declaration for $variableName not found");
        }

        $responseVariable = $controller->response[$variableName];

        $this->simpleAssociableChoice = array();
        $this->fixed = array();
        // Process child nodes
        foreach($this->children as $child) {
            if ($child instanceof qti_prompt) {
                $this->prompt = $child;
            } else if ($child instanceof qti_simpleAssociableChoice) {
                $child->name = $variableName;
                $this->simpleAssociableChoice[] = $child;
                if($child->attrs['fixed'] === 'true') {
                    $this->fixed[] = count($this->simpleAssociableChoice) - 1;
                }
            }
        }

        if (!is_null($this->prompt)) {
            $result .= $this->prompt->__invoke($controller);
        }

        $result .= "<ol>";
        // Work out an order to display them in
        // TODO: Worst implementation ever!
        $identifiers = array(); 
        $order = range(0, count($this->simpleAssociableChoice) - 1);
        if ($this->attrs['shuffle'] === 'true') {
            $notfixed = array_diff($order, $this->fixed);
            shuffle($notfixed);
            $shuffledused = 0;
            for($i = 0; $i < count($this->simpleAssociableChoice); $i++) {
                if(in_array($i, $this->fixed)) {
                    $identifiers[] = $this->simpleAssociableChoice[$i]->attrs['identifier'];
                    $result .= "<li>" . $this->simpleAssociableChoice[$i]->__invoke($controller) . "</li>";
                } else {
                    $identifiers[] = $this->simpleAssociableChoice[$notfixed[$shuffledused]]->attrs['identifier'];
                    $result .= "<li>" . $this->simpleAssociableChoice[$notfixed[$shuffledused++]]->__invoke($controller) . "</li>";
                }
            }
        } else {
            foreach($order as $i) {
                $identifiers[] = $this->simpleAssociableChoice[$i]->attrs['identifier'];
                $result .= $this->simpleAssociableChoice[$i]->__invoke($controller);
            }
        }
        
        $result .= "</ol>";
        
        // Now create however many empty associations are required
        $maxAssociations = $this->attrs['maxAssociations'];
        
        // This is horrible but what else can we do without Javascript?
        if ($maxAssociations == 0) {
            $maxAssociations = count($this->simpleAssociableChoice) *
            count($this->simpleAssociableChoice);
        }
        
        for($i = 0; $i < $maxAssociations; $i++) {
            
            $inputs = "<div>";
            
            $inputs .= "<select name=\"{$variableName}[]\"><option></option>";
            $leftnumber = 1;
            foreach($identifiers as $left) {
                $rightnumber = 1;
                foreach($identifiers as $right) {
                    if (isset($responseVariable->value[$i]) && $responseVariable->value[$i] == "{$left} {$right}") {
                        $selected = " selected=\"selected\" ";
                    } else {
                        $selected = '';
                    }
                    $inputs .= "<option value=\"{$left} {$right}\" $selected>" . $leftnumber . ", " . $rightnumber++ . "</option>";
                }
                $leftnumber++;
            }
            $inputs .= "</select>";
            
            $result .= $inputs;
        }

        $result .= "</div>";
        return $result;
    }

}

// TODO: Implement associableChoice (in particular matchGroup)
class qti_simpleAssociableChoice extends qti_element {
    
    public function __invoke($controller) {
        $result = "";
        foreach($this->children as $child) {
            $result .= $child->__invoke($controller);
        }
        return $result;
    }
    
}

class qti_matchInteraction extends qti_element {
    
    public $simpleMatchSet = array();
    public $prompt;
    
    public function __invoke($controller) {
        $variableName = $this->attrs['responseIdentifier'];
        $result = "<div id=\"matchInteraction_{$variableName}\" class=\"qti_blockInteraction\">";
    
        // Work out what kind of HTML tag will be used for simpleMatchSets
        if (!isset($controller->response[$variableName])) {
            throw new Exception("Declaration for $variableName not found");
        }
    
        $responseVariable = $controller->response[$variableName];
    
        $this->simpleMatchSet = array();
        $this->fixed = array();
        // Process child nodes
        foreach($this->children as $child) {
            if ($child instanceof qti_prompt) {
                $this->prompt = $child;
            } else if ($child instanceof qti_simpleMatchSet) {
                $child->name = $variableName;
                $this->simpleMatchSet[] = $child;
            }
        }
    
        if (!is_null($this->prompt)) {
            $result .= $this->prompt->__invoke($controller);
        }
    
        $shuffle = ($this->attrs['shuffle'] == 'true');
        $sourceChoicesIterator = $this->simpleMatchSet[0]->iterator($shuffle);
        $targetChoicesIterator = $this->simpleMatchSet[1]->iterator($shuffle);
        
        $result .= "<table>";
        
        // Create headers and extract target identifiers
        $result .= "<tr><td></td>";
        $targetIdentifiers = array();
        foreach($targetChoicesIterator as $targetChoice) {
            $targetIdentifiers[] = $targetChoice->attrs['identifier'];
            $result .= "<td>" . $targetChoice->__invoke($controller) . "</td>";
        }
        $result .= "</tr>";
        
        foreach($sourceChoicesIterator as $sourceChoice) {
            $result .= "<tr><td>";
            $result .= $sourceChoice->__invoke($controller);
            $result .= "</td>";
            
            $sourceIdentifier = $sourceChoice->attrs['identifier'];
            foreach($targetIdentifiers as $targetIdentifier) {
                $result .= "<td>";
                // Tick values from variable
                if (isset($responseVariable->value) && in_array("{$sourceIdentifier} {$targetIdentifier}", $responseVariable->value)) {
                    $checked = " checked=\"checked\" ";
                } else {
                    $checked = "";
                }
                $result .= "<input type=\"checkbox\" name=\"{$variableName}[{$targetIdentifier}][]\" value=\"{$sourceIdentifier}\" $checked/>";
                $result .= "</td>";
            }
            
            $result .= "</tr>";
        }
    
        $result .= "</table>";
        $result .= "</div>";
        return $result;
    }
    
}

class qti_simpleMatchSet extends qti_element {
    
    public function iterator($shuffle = false) {
        return new qti_choiceIterator($this->children, $shuffle);
    }
    
}

class qti_gapMatchInteraction extends qti_element {

    /* TODO: gapMatchInteraction should support shuffle (for the choices, not gaps!)
    */

    public $gapChoice = array();
    public $fixed = array(); // indices of gapChoices with fixed set to true
    public $prompt;

    public function __invoke($controller) {
        $variableName = $this->attrs['responseIdentifier'];
        $result = "<div id=\"gapMatchInteraction_{$variableName}\" class=\"qti_blockInteraction\">";

        // Find variable
        if (!isset($controller->response[$variableName])) {
            throw new Exception("Declaration for $variableName not found");
        }

        $responseVariable = $controller->response[$variableName];
        
        $this->gapChoice = array();
        // TODO: Implement gapImg
        $this->fixed = array();
        $this->displayNodes = array(); // Nodes which will be processed for display as normal
        // Process child nodes
        foreach($this->children as $child) {
            if ($child instanceof qti_prompt) {
                $this->prompt = $child;
            } else if ($child instanceof qti_gapChoice) {
                $this->gapChoice[] = $child;
                if($child->attrs['fixed'] === 'true') {
                    $this->fixed[] = count($this->gapChoice) - 1;
                }
            } else {
                $this->displayNodes[] = $child;
            }
        }
        
        $controller->context['gapMatchInteraction'] = $this;
        
        if (!is_null($this->prompt)) {
            $result .= $this->prompt->__invoke($controller);
        }
        
        // Work out an order to display them in
        // TODO: Worst implementation ever!
        /* $order = range(0, count($this->gapChoice) - 1);
        if ($this->attrs['shuffle'] === 'true') {
            $notfixed = array_diff($order, $this->fixed);
            shuffle($notfixed);
            $shuffledused = 0;
            for($i = 0; $i < count($this->gapChoice); $i++) {
                if(in_array($i, $this->fixed)) {
                    $result .= $this->gapChoice[$i]->__invoke($controller);
                } else {
                    $result .= $this->gapChoice[$notfixed[$shuffledused++]]->__invoke($controller);
                }
            }
        } else {
            foreach($order as $i) {
                $result .= $this->gapChoice[$i]->__invoke($controller);
            }
        } */

        foreach($this->displayNodes as $node) {
            $result .= $node->__invoke($controller);
        }
        
        $result .= "</div>";
        return $result;
    }

}

// TODO: Implement "required" attribute
class qti_inlineChoiceInteraction extends qti_element {
    
    public $inlineChoice = array();
    public $fixed = array(); // indices of inlineChoices with fixed set to true
    
    public function __invoke($controller) {
        $variableName = $this->attrs['responseIdentifier'];
        $result = "<select name=\"{$variableName}\" id=\"inlineChoiceInteraction_{$variableName}\" class=\"qti_inlineChoiceInteraction\">";
        // Empty choice
        $result .= "<option></option>";
        
        // Find variable
        if (!isset($controller->response[$variableName])) {
            throw new Exception("Declaration for $variableName not found");
        }
        
        $responseVariable = $controller->response[$variableName];
        
        $this->inlineChoice = array();
        $this->fixed = array();
        // Process child nodes
        foreach($this->children as $child) {
            if ($child instanceof qti_inlineChoice) {
                $this->inlineChoice[] = $child;
                $child->name = $variableName;
                if($child->attrs['fixed'] === 'true') {
                    $this->fixed[] = count($this->inlineChoice) - 1;
                }
            } else {
                throw new Exception("Unknown child element in inlineChoice");
            }
        }
        
         // Work out an order to display them in
        // TODO: Worst implementation ever!
        $order = range(0, count($this->inlineChoice) - 1);
        if ($this->attrs['shuffle'] === 'true') {
            $notfixed = array_diff($order, $this->fixed);
            shuffle($notfixed);
            $shuffledused = 0;
            for($i = 0; $i < count($this->inlineChoice); $i++) {
                if(in_array($i, $this->fixed)) {
                    $result .= $this->inlineChoice[$i]->__invoke($controller);
                } else {
                    $result .= $this->inlineChoice[$notfixed[$shuffledused++]]->__invoke($controller);
                }
            }
        } else {
            foreach($order as $i) {
                $result .= $this->inlineChoice[$i]->__invoke($controller);
            }
        }

        $result .= "</select>";
        return $result;
    }
    
}

class qti_inlineChoice extends qti_element {
    
    public function __invoke($controller) {
        $identifier = $this->attrs['identifier'];
        
        // See if this response was selected already
        
        if ($controller->response[$this->name]->value == $identifier) {
            $selected = ' selected="selected" ';
        } else {
            $selected = '';
        }
        $result = '<option value="' . $identifier . "\" $selected>";
        foreach($this->children as $child) { // should be only one
            $result .= $child->__invoke($controller);
        }
        $result .= '</option>';
        return $result;
    }
    
}

// TODO: Implement stringInteraction features such as base, stringIdentifier,
// expectedLength, patternMask, placeholderText
class qti_stringInteraction extends qti_element {

}

class qti_textEntryInteraction extends qti_stringInteraction {
    
    public function __invoke($controller) {
        $variableName = $this->attrs['responseIdentifier'];
        $result = "<input type=\"text\" name=\"{$variableName}\"></input>";
        return $result;
    }
    
}

class qti_extendedTextInteraction extends qti_stringInteraction {

    public function __invoke($controller) {
        $variableName = $this->attrs['responseIdentifier'];
        $variable = $controller->response[$variableName];
        $result = '';
        
        // Process child nodes
        foreach($this->children as $child) {
            if ($child instanceof qti_prompt) {
                $result .= $child->__invoke($controller);
            } 
        }
        
        if ($variable->cardinality == 'single') {
            $brackets = '';
            $values = array($variable->value);
            $count = 1;
        } else {
            $brackets = '[]';
            $values = $variable->value;
            $count = $this->attrs['maxStrings'];
        }
        
        
        for($i = 0; $i < $count; $i++) {
            if(isset($values[$i])) {
                $value = $values[$i];
            } else {
                $value = '';
            }
            $result .= "<textarea name=\"{$variableName}{$brackets}\">" . htmlentities($value) . "</textarea>";
        }
        return $result;
    }

}

class qti_hottextInteraction extends qti_element {

    /* hottextInteraction doesn't implement the shuffle attribute, even though
     * the hottext elements are choices (and therefore theoretically support fixed)
     */
    public $hottext = array();
    public $fixed = array(); // indices of hottexts with fixed set to true
    public $prompt;

    public function __invoke($controller) {
        $variableName = $this->attrs['responseIdentifier'];
        $result = "<div id=\"hottextInteraction_{$variableName}\" class=\"qti_blockInteraction\">";

        // Work out what kind of HTML tag will be used for hottexts
        if (!isset($controller->response[$variableName])) {
            throw new Exception("Declaration for $variableName not found");
        }

        $responseVariable = $controller->response[$variableName];
        $this->hottextType = 'radio';
        $this->brackets = ''; // we need brackets for multiple responses
        if ($responseVariable->cardinality == 'multiple') {
            $this->hottextType = 'checkbox';
            $this->brackets = '[]';
        }

        $this->variableName = $variableName; // to be used by embedded hottext elements
        
        $controller->context['hottextInteraction'] = $this;
        $this->displayNodes = array();
        // Process child nodes just to find hottexts
        foreach($this->children as $child) {
            if ($child instanceof qti_prompt) {
                $this->prompt = $child;
            } else {
                $this->displayNodes[] = $child;
            }
        }

        if (!is_null($this->prompt)) {
            $result .= $this->prompt->__invoke($controller);
        }

        foreach($this->displayNodes as $node) {
            $result .= $node->__invoke($controller);
        }
        
        $result .= "</div>";
        return $result;
    }

}
class qti_endAttemptInteraction extends qti_element {

    public function __invoke($controller) {
        $variableName = $this->attrs['responseIdentifier'];
        $result = "<div id=\"endAttemptInteraction_{$variableName}\" method=\"post\">";
        $result .= "<input type=\"hidden\" name=\"{$variableName}\" value=\"true\" />";
        $result .= "<input type=\"submit\" value=\"{$this->attrs['title']}\" >";
        $result .= "</div>";
        return $result;
    }

}

class qti_uploadInteraction extends qti_element {

    public function __invoke($controller) {
        $variableName = $this->attrs['responseIdentifier'];
        $result = "<div id=\"uploadInteraction_{$variableName}\" method=\"post\">";
        $result .= "<input type=\"file\" name=\"{$variableName}\" >";
        foreach($this->children as $child) {
            if ($child instanceof qti_prompt) {
                $result .= "<div class=\"qti_prompt\">" . $child->__invoke($controller) . "</div>";
            }
        }
        $result .= "</div>";
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
    
    public function __invoke($controller) {
        $result .= '<span class="' . get_class($this) . '">';
        foreach($this->children as $child) {
            $result .= $child->__invoke($controller);
        }
        $result .= "</span>";
        return $result;
    }

}

class qti_prompt extends qti_element {

}

class qti_gapChoice extends qti_element {
    
}

class qti_feedbackInline extends qti_feedbackElement {
    
}

class qti_feedbackBlock extends qti_feedbackElement {
    
}

class qti_feedbackElement extends qti_element {
    
    public function __invoke($controller) {
        $outcomeIdentifier = $this->attrs['outcomeIdentifier'];
        $showHide = $this->attrs['showHide'];
        $identifier = $this->attrs['identifier'];
        
        $class = get_class($this); // for CSS class
        
        if (!$variable = $controller->outcome[$outcomeIdentifier]) {
            return '';
        }
        
        // Create new variable for comparison
        /* 
         * TODO: It looks from the examples as if it should be possible to have
         * a single "identifier" attribute representing multiple items (space delimited), but
         * the spec doesn't seem to mention this that I can find. 
         * 
        */
        $testvar = new qti_variable('single', $variable->type, array('value' => $identifier));
        if ($variable->cardinality == 'multiple') {
            $comparisonresult = $variable->contains($testvar);
        } else {
            $comparisonresult = $variable->match($testvar);
        }
        
        if ($comparisonresult->value && $showHide == 'show') {
            $result = "<span class=\"{$class}\">"; 
            foreach ($this->children as $child) {
                $result .= $child->__invoke($controller);
            }
            $result .= '</span>';
            return $result;
        } else if (!$comparisonresult->value && $showHide == 'hide') {
            $result = "<span class=\"{$class}\">"; 
            foreach ($this->children as $child) {
                $result .= $child->__invoke($controller);
            }
            $result .= '</span>';
            return $result;
        }
        return '';
    }
    
}

class qti_gapText extends qti_gapChoice {
    
    public function __invoke($controller) {
        // No-op. Only used at function generation time
    }
    
}

class qti_gap extends qti_element {
    
    public function __invoke($controller) {
        $gapMatchInteraction = $controller->context['gapMatchInteraction'];
        $identifier = $this->attrs['identifier'];
        $result = "<span class=\"qti_gap\"><select name=\"{$gapMatchInteraction->attrs['responseIdentifier']}[{$identifier}]\">";
        $result .= "<option></option>";
        foreach($gapMatchInteraction->gapChoice as $choice) {
            $variable = $controller->response[$gapMatchInteraction->attrs['responseIdentifier']];
            $directedPairString = $choice->attrs['identifier'] . ' ' . $identifier;
            
            // Select correct options if we already have a value (i.e. after end attempt)
            if (!empty($variable->value)) {
                if ($variable->cardinality == 'single') {
                    $selected = ($variable->value ==  $directedPairString ? ' selected="selected"' : '');
                } else if ($variable->cardinality == 'multiple') {
                    $selected = (in_array($directedPairString, $variable->value) ? ' selected="selected"' : '');
                }
            } else {
                $selected = '';
            }
            
            $result .= "<option value=\"{$choice->attrs['identifier']}\" $selected>";
            foreach($choice->children as $child) {
                $result .= $child->__invoke($controller);
            }
            $result .= "</option>";
        }
        $result .= '</select></span>';
        return $result;
    }
    
}

class qti_hottext extends qti_element {

    public $interactionType = 'choiceInteraction';

    public function __invoke($controller) {
        $result = "<span class=\"qti_hottext\">\n";
        
        $identifier = $this->attrs['identifier'];
        $hottextInteraction = $controller->context['hottextInteraction'];
        
        $variable = $controller->response[$hottextInteraction->variableName];
        $testvar = new qti_variable('single', $variable->type, array('value' => $identifier));
        if ($variable->cardinality == 'multiple') {
            $comparisonresult = $variable->contains($testvar);
        } else {
            $comparisonresult = $variable->match($testvar);
        }
        $checked = $comparisonresult->value ? " checked=\"checked\" " : "";
        $result .= "<input type=\"{$hottextInteraction->hottextType}\" name=\"{$hottextInteraction->variableName}{$hottextInteraction->brackets}\" value=\"{$identifier}\" {$checked}/> ";
        
        foreach($this->children as $child) {
            $result .= $child($controller);
        }
        $result .= "</span>";
        return $result;
    }

}

class qti_simpleChoice extends qti_element {
    
    public $interactionType = 'choiceInteraction';

    public function __invoke($controller) {
        $result = "<span class=\"qti_simpleChoice\">\n";
        if ($this->interactionType == 'choiceInteraction') {
            
            // str_replace is for checkboxes where the element name always has [] at the end
            $responseValue = $controller->response[str_replace('[]', '', $this->name)]->value;
            
            // See if this response was selected already
            // TODO: Do this checking in qti_variable so it can be reused
            if (is_array($responseValue)) {
                $checked = in_array($this->attrs['identifier'], $responseValue) ? ' checked="checked"' : '';
            } else {
                $checked = $responseValue == $this->attrs['identifier'] ? ' checked="checked"' : '';
            }
            $result .= "<input type=\"{$this->inputType}\" name=\"{$this->name}\" value=\"{$this->attrs['identifier']}\" $checked></input>\n";
        } else if ($this->interactionType = 'orderInteraction') {
            $result .= "<select name=\"{$this->name}[{$this->attrs['identifier']}]\">\n";
            $result .= "<option></option>";
            for($i = 1; $i <= $this->numberOfChoices; $i++) {
                $selected = $controller->response[$this->name]->value[$i - 1] == $this->attrs['identifier'] ? ' selected="selected"' : '';
                $result .= "<option value=\"$i\" $selected>$i</option>";
            }
            $result .= "</select>";
        }
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
    
    // A unique identifier for the controller.
    public $identifier;

    public $response = array();
    public $outcome = array();

    public $response_source; // provides response values for variables
    public $persistence; // provides existing values of variables
    public $resource_provider; // provides URLs for images etc.

    public $response_processing; // closure which processes responses
    public $item_body; // closure which displays item body
    public $modal_feedback_processing; // closure which displays modal feedback

    public $stylesheets; // a simple array of stylesheets
    
    public $show_debugging = false; // do we show memory usage etc.?
    
    public $context = array(); // for passing contextual info (e.g. ancestor nodes) 

    public function __construct() {

    }

    public function setUpDefaultVars() {
        // Built-in variables (section 5.1.1 & 5.2.1 of info model)
        $this->response['numAttempts'] = new qti_variable('single', 'integer', array('value' => 0));
        $this->response['duration'] = new qti_variable('single', 'float', array('value' => 0));
        $this->outcome['completionStatus'] = new qti_variable('single', 'identifier', array('value' => 'not_attempted'));
        
        // TODO: We have this to get around mistakes (?) in the example QTI - should we?
        $this->outcome['completion_status'] = $this->outcome['completionStatus'];
    }

    public function showItemBody() {
        echo "<form method=\"post\" enctype=\"multipart/form-data\">";
        $resource_provider = $this->resource_provider;
        echo $this->item_body->execute();
        echo "<input type=\"submit\" value=\"Submit response\"/>";
        echo "</form>";
    }

    // TODO: Should this be moved out of the item controller into
    // an engine class?
    public function run() {
        $this->persistence->restore($this);
        
        if ($this->state == qti_item_controller::STATE_NONE) {
            $this->beginItemSession();
        }

        if ($this->state == qti_item_controller::STATE_INTERACTING) {
            if($this->response_source->isEndAttempt()) {
                // TODO: fix (the person has submitted the item)
                $this->endAttempt();
            }
        }

        // TODO: How do we know when to show the body / results?
        $this->showItemBody();
        $this->displayResults();
        
        $this->persistence->persist($this);
        
        // TODO: This is resetting all the variables somehow. More work needed
        // on the item lifecycle!! Having it commented out doesn't update
        // numAttempts properly
        $this->beginAttempt();

        if ($this->show_debugging) {
            echo "<hr />Memory: " . memory_get_peak_usage() / (1024 * 1024) . "Mb"; // TODO: Remove this debugging
        }

    }

    public function beginItemSession() {
        $this->state = qti_item_controller::STATE_INITIAL;
        $this->setUpDefaultVars();
        $this->beginAttempt();
    }
    
    public function endItemSession() {
        $this->state = qti_item_controller::STATE_CLOSED;
    }

    public function beginAttempt() {
        $this->state = qti_item_controller::STATE_INTERACTING;
        // 5.2.1 completionStatus set to unknown at start of first attempt
        if ($this->outcome['completionStatus']->value == 'not_attempted') {
            $this->outcome['completionStatus']->value = 'unknown';
        }
        // 5.1.1 numAttempts increases at the start of the attempt
        $this->response['numAttempts']->value++;
    }

    public function endAttempt() {
        $this->bindVariables();
        $this->processResponse();
        // TODO: Shouldn't change state to closed here, but when should we??
        // $this->state = qti_item_controller::STATE_CLOSED; 
    }

    // Bind the responses to the controller variables
    public function bindVariables() {
        foreach($this->response as $key => $val) {
            $this->response_source->bindVariable($key, $val);
        }
    }

    public function processResponse() {
        $this->response_processing->execute();

        if ($this->modal_feedback_processing) {
            echo $this->modal_feedback_processing->execute();
        }
    }
    
    public function displayResults() {
        echo "<div class=\"well\">";
        foreach($this->outcome as $key => $outcome) {
            echo "$key: " . $outcome . "<br />";
        }
        echo "<hr />";
        foreach($this->response as $key => $response) {
            echo "$key: " . $response . "<br />";
        }
                
        echo "</div>";
    }

    public function getCSS() {
        $result = '';
        if (count($this->stylesheets) == 0) {
            return $result;
        }
        foreach($this->stylesheets as $sheet) {
            $url = $this->resource_provider->urlFor($sheet);
            $result .= '<link rel="stylesheet" href="' . $url . "\"></link>\n";
        }
        return $result;
    }
}

class qti_variable {

    public $cardinality;
    public $type;
    // For response vars, QTI has a candidateResponse wrapper for the value - any reason to implement?
    public $value;
    public $correctResponse;
    public $defaultValue;
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
        if(isset($params['correctResponse'])) {
            $this->correctResponse = $params['correctResponse'];
        }

        $this->defaultValue = null;
        if(isset($params['defaultValue'])) {
            $this->defaultValue = $params['defaultValue'];
            $this->value = $this->defaultValue;
        }

        $this->mapping = null;
        if(isset($params['mapping'])) {
            $this->mapping = $params['mapping'];
        }
    }

    // Implement mapResponse processing here because it's sensible!
    public function mapResponse() {
        // TODO: Check mapping is defined here?
        if ($this->cardinality == 'single') {
            if (array_key_exists($this->value, $this->mapping['mapEntry'])) {
                $value = $this->mapping['mapEntry'][$this->value];
            } else {
                $value = $this->mapping['defaultValue'];
            }
        } else {
            $value = 0;
            // array_unique used because values should only be counted once - see mapResponse documentation
            foreach(array_unique($this->value) as $response) {
                if (array_key_exists($response, $this->mapping['mapEntry'])) {
                    $value += $this->mapping['mapEntry'][$response];
                } else if ($this->type == 'pair') {  // Check pair opposite way round
                    $responseReversed = implode(' ', array_reverse(explode(' ', $response)));
                    if (array_key_exists($responseReversed, $this->mapping['mapEntry'])) {
                        $value += $this->mapping['mapEntry'][$responseReversed];
                    } else {
                        $value += $this->mapping['defaultValue'];
                    }
                } else {
                    $value += $this->mapping['defaultValue'];
                }
                
            }
        }

        return new qti_variable('single', 'float', array('value' => $value));
    }

    // TODO: This should be deprecated by the more specific methods
    // TODO: Make this work for things other than strings and arrays
    public static function compare($variable1, $variable2) {
        if (!is_array($variable1->value) && !(is_array($variable2->value))) {
            return strcmp($variable1->value, $variable2->value);
        }
        if (count($variable1->value) != count($variable2->value)) {
            // This doesn't mean anything
            return count($variable1->value) - count($variable2->value);
        }
        // If it's multiple just do a diff
        if ($variable1->cardinality == 'multiple') {
            return count(array_diff($variable1->value, $variable2->value));
        } else if ($variable1->cardinality == 'ordered') {
            // check them pairwise
            for($i = 0; $i < count($variable1->value); $i++) {
                if ($variable1->value[$i] != $variable2->value[$i]) {
                    // This doesn't mean too much either
                    return strcmp($variable1->value[$i], $variable2->value[$i]);
                }
            }
            return 0;
        }
        
        // default to not equal
        return -1;
    }
    
    /* 
     * Response processing functions.
     * 
     * There is a distinction between the notion of a variable and an expression.
     * In theory, most of these functions apply to expressions in the spec. However, 
     * in this implementation expressions are translated into closures / classes which, 
     * when invoked, produce a variable as a result, so it makes a certain amount
     * of sense to implement these functions in the qti_variable class.
     * 
     * In other words, these functions should not be thought of as directly related to the 
     * expressions with the same name in the spec. The closures and classes produced by 
     * qti_response_processing are the implementation of expressions, which just happen to
     * use these functions to do their work.
     * 
     * Update: today I'm thinking that the closures and classes used in response processors
     * should really be thought of as "expression processing functions" rather than expressions
     * per se. So the following methods are "operator helper methods" and will be used when 
     * creating the expression processors. As I understand it, an expression always evaluates to a variable
     * (i.e. when the processing function is executed)
     */
    public static function multiple() {
        $params = func_get_args();

        // Null if no arguments passed
        if (count($params) == 0) {
            return new qti_variable('multiple', 'identifier');
        } else {
            $result = new qti_variable('multiple', 'identifier', array('value' => array()));
        }
        
        // Allow a single array as well as a parameter list
        if (count($params) == 1 && is_array($params[0])) {
            $params = $params[0];
        }
        
        $allnull = true;
        foreach ($params as $param) {
            if (is_null($param->value)) {
                continue;
            } else {
                $allnull = false;
                $result->type = $param->type;
                if (is_array($param->value)) {
                    $result->value = array_merge($result->value, $param->value);
                } else {
                    $result->value[] = $param->value;
                }
            }
        }
        if ($allnull) {
            $result->value = null;
        }
        
        return $result;
    }
    
    /*
     * It looks from the documentation as if there is no difference
     * in the functionality of ordered and multiple, it is just the return
     * type that is different.
     */
    public static function ordered() {
        $params = func_get_args();
        $result = forward_static_call_array('qti_variable::multiple', $params);
        $result->cardinality = 'ordered';
        return $result;
    }
    
    public function containerSize() {
        $result = new qti_variable('single', 'integer', array('value' => 0));
        if (is_null($this->value)){
            return $result;
        }
        if (is_array($this->value)) {
            $result->value = count($this->value);
        } else {
            $result->value = 1;
        }
        
        return $result;
    }
    
    // This is an internal isNull function that returns a PHP boolean, not a QTI one
    private function _isNull() {
        if (is_null($this->value)) {
            return true;
        }
        if (empty($this->value) && (in_array($this->type, array('multiple', 'ordered', 'string')))) {
            return true;
        }
        return false;
    }
    
    public function isNull() {
        $result = new qti_variable('single', 'boolean', array('value' => $this->_isNull()));
        return $result;
    }
    
    public function index($i) {
        $result = new qti_variable('single', $this->type);
        if (is_array($this->value) && $i <= count($this->value) && $i > 0) {
            $result->value = $this->value[$i - 1]; // 1 based indexing
        }
        return $result;
    }
    
    public function fieldValue($fieldidentifier) {
        throw new Exception("Not implemented");
    }
    
    public function random() {
        $result = clone($this);
        $result->cardinality = 'single';
        if ($this->_isNull() || count($this->value) == 0) {
            $result->value = null;
        } else {
            $result->value = $this->value[rand(0, count($this->value))];
        }
        return $result;
    }
    
    public function member($container) {
        $result = new qti_variable('single', 'boolean', array('value' => false));
        if (!$this->_isNull() && !$container->_isNull()) {
            $result->value = in_array($this->value, $container->value);
        }
        return $result;
    }
    
    public function delete($container) {
        $result = clone($container);
        if ($this->_isNull() || $container->_isNull()) {
            $result->value = null;
        } else {
            $thisvaluearray = is_array($this->value) ? $this->value : array($this->value);
            $result->value = array_diff($container->value, $thisvaluearray);
        }
        return $result;
    }
    
    public function contains($subsequence) {
        $result = new qti_variable('single', 'boolean');
        if ($this->_isNull() || $subsequence->_isNull()) {
            $result->value = null;
        } else {
            $result->value = false;
            
            $testarr = is_array($subsequence->value) ? $subsequence->value : array($subsequence->value);
            $testcontainer = $this->value; // copy of array, not ref
            
            if ($this->cardinality == 'multiple') {
                // just check all values exist including duplicates
                foreach($testarr as $val) {
                    if (false === $key = array_search($val, $testcontainer)) {
                        $result->value = false;
                        return $result;
                    }
                    unset($testcontainer[$key]);
                }
                $result->value = true;
                return $result;
            } else if ($this->cardinality == 'ordered') {
                // check that subsequence is strict
                $possiblestarts = array_keys($testcontainer, $testarr[0]);
                if (empty($possiblestarts)) {
                    $result->value = false;
                    return $result;
                }
                foreach($possiblestarts as $start) {
                    for($i = 0; $i < count($testarr); $i++) {
                        // We've reached the end of the container array
                        if ($start + $i >= count($testcontainer)) {
                            $result->value = false;
                            return $result;
                        }
                        if ($testarr[$i] != $testcontainer[$start + $i]) {
                            continue 2; // try next start
                        }
                    }
                    $result->value = true;
                    return $result;
                }
                $result->value = false;
                return $result;
            }
            
        }
    }
    
    public function substring($biggerstring, $casesensitive = true) {
        $result = new qti_variable('single', 'boolean');
        if ($casesensitive) {
            $result->value = (strpos($biggerstring->value, $this->value) !== false);
        } else {
            $result->value = (stripos($biggerstring->value, $this->value) !== false);
        }
        return $result;
    }
    
    public function not() {
        $result = clone($this);
        if ($this->_isNull()) {
            $result->value = null;
        } else {
            $result->value = !($this->value);
        }
        return $result;
    }
    
    // Underscore at end because "and" is a reserved word
    public static function and_() {
        $result = new qti_variable('single', 'boolean', array('value' => true));
        $params = func_get_args();
        // Allow a single array as well as a parameter list
        if (count($params) == 1 && is_array($params[0])) {
            $params = $params[0];
        }
        foreach($params as $param) {
            if (!$param->value) {
                $result->value = false;
                return $result;
            }
        }
        return $result;
    }
    
    // Underscore at end because "or" is a reserved word
    public static function or_() {
        $result = new qti_variable('single', 'boolean', array('value' => false));
        $params = func_get_args();
        // Allow a single array as well as a parameter list
        if (count($params) == 1 && is_array($params[0])) {
            $params = $params[0];
        }
        foreach($params as $param) {
            if ($param->value) {
                $result->value = true;
                return $result;
            }
        }
        return $result;
    }
    
    /**
     * anyN(min, max, [boolean1], [boolean2]...)
     */
    public static function anyN() {
        $result = new qti_variable('single', 'boolean');
        $params = func_get_args();
        $min = array_shift($params);
        $max = array_shift($params);
        
        // Allow a single array as well as a parameter list
        if (count($params) == 1 && is_array($params[0])) {
            $params = $params[0];
        }
        $false = $true = $null = 0;
        foreach($params as $param) {
            if ($param->_isNull()) {
                $null++;
            } else if ($param->value == true) {
                $true++;
            } else if ($param->value == false) {
                $false++;
            } 
        }
        
        if ($false > (count($params) - $min)) {
            $result->value = false;
        } else if ($true > $max) {
            $result->value = false;
        } else if (($min <= $true) && ($true <= $max)) {
            $result->value = true;
        }
        
        return $result;
    }
    
    public function match($othervariable) {
        $result = new qti_variable('single', 'boolean', array('value' => false));
        
        // TODO: Is it OK just to let PHP decide if two values are equal?
        if (!is_array($this->value) && !(is_array($othervariable->value))) {
            $result->value = ($this->value == $othervariable->value);
            return $result;
        }
        if (count($this->value) != count($othervariable->value)) {
            $result->value = false;
            return $result;
        }
        // If it's multiple just do a diff
        if ($this->cardinality == 'multiple') {
            $result->value = (count(array_diff($this->value, $othervariable->value)) == 0);
        } else if ($this->cardinality == 'ordered') {
            // check them pairwise
            for($i = 0; $i < count($this->value); $i++) {
                if ($this->value[$i] != $othervariable->value[$i]) {
                    $result->value = false;
                    return $result;
                }
            }
            $result->value = true;
        }
        
        // default to false
        return $result;
    }
    
    public function stringMatch($othervariable, $caseSensitive, $substring = false) {
        $result = new qti_variable('single', 'boolean', array('value' => false));
        
        if ($this->_isNull() || $othervariable->_isNull()) {
            $result->value = null;
            return result;
        }
        
        $string1 = $this->value;
        $string2 = $othervariable->value;
                
        if (!$caseSensitive) {
            $string1 = strtolower($string1);
            $string2 = strtolower($string2);
        }
        
        if ($substring) {
            $result->value = (strpos($string1, $string2) !== false);
        } else {
            $result->value =  ($string1 == $string2);
        }
        
        return $result;
    }
    
    // TODO: Is PCRE compatible with the XML Schema regexes used in the spec?
    public function patternMatch($pattern) {
        $result = new qti_variable('single', 'boolean', array('value' => false));
        
        if ($this->_isNull()) {
            $result->value = null;
            return result;
        }
        
        // TODO: What if the pattern contains a percent? Should be escaped
        $result->value = (preg_match('%' . $pattern . '%', $this->value) > 0);
        return $result;
    }
    
    // TODO: Implement these methods
    public function equal() {
        throw new Exception("Not implemented");
    }
    
    public function equalRounded() {
        throw new Exception("Not implemented");
    }
    
    public function inside($shape, $coords) {
        throw new Exception("Not implemented");
    }
    
    public function lt($othervariable) {
        $result = new qti_variable('single', 'boolean', array('value' => false));
        
        if ($this->_isNull() || $othervariable->_isNull()) {
            $result->value = null;
            return result;
        }
        
        $result->value = ($this->value < $othervariable->value);
        return $result;
    }
    
    public function gt() {
         $result = new qti_variable('single', 'boolean', array('value' => false));
        
        if ($this->_isNull() || $othervariable->_isNull()) {
            $result->value = null;
            return result;
        }
        
        $result->value = ($this->value > $othervariable->value);
        return $result;
    }
    
    public function lte() {
        $result = new qti_variable('single', 'boolean', array('value' => false));
        
        if ($this->_isNull() || $othervariable->_isNull()) {
            $result->value = null;
            return result;
        }
        
        $result->value = ($this->value <= $othervariable->value);
        return $result;
    }
    
    public function gte() {
        $result = new qti_variable('single', 'boolean', array('value' => false));
        
        if ($this->_isNull() || $othervariable->_isNull()) {
            $result->value = null;
            return result;
        }
        
        $result->value = ($this->value >= $othervariable->value);
        return $result;
    }
    
    // TODO: Implement these functions
    public function durationLT() {
        throw new Exception("Not implemented");
    }
    
    public function durationGTE() {
        throw new Exception("Not implemented");
    }
    
    public static function sum() {
        $params = func_get_args();
        // Allow a single array as well as a parameter list
        if (count($params) == 1 && is_array($params[0])) {
            $params = $params[0];
        }
        $result = clone($params[0]); // There should always be one
        $result->value = 0;
        
        foreach($params as $param) {
            if($param->_isNull()) {
                $result->value = null;
                return $result;
            }
            
            $result->value += $param->value;
        }
        
        return $result;
    }
    
    public static function product() {
        $params = func_get_args();
        // Allow a single array as well as a parameter list
        if (count($params) == 1 && is_array($params[0])) {
            $params = $params[0];
        }
        $result = clone($params[0]); // There should always be one
        $result->value = 0;
        
        foreach($params as $param) {
            if($param->_isNull()) {
                $result->value = null;
                return $result;
            }
            
            $result->value *= $param->value;
        }
        
        return $result;
    }
    
    public function subtract($othervariable) {
        $result = clone($this);
        
        if ($this->_isNull() || $othervariable->_isNull()) {
            $result->value = null;
            return $result;
        }
        
        $result->value = $this->value - $othervariable->value;
        return $result;
    }
    
    public function divide($othervariable) {
        $result = clone($this);
        
        if ($this->_isNull() || $othervariable->_isNull() || $othervariable->value == 0) {
            $result->value = null;
            return $result;
        }
        
        $result->value = $this->value / $othervariable->value;
        return $result;
    }
    
    public function power($othervariable) {
        $result = clone($this);
        
        if ($this->_isNull() || $othervariable->_isNull() || $othervariable->value == 0) {
            $result->value = null;
            return $result;
        }
        
        $result->value = pow($this->value, $othervariable->value);
        return $result;
    }
    
    public function integerDivide($othervariable) {
        $result = $this->divide($othervariable);
        $result->value = round($result->value);
        return $result;
    }
    
    public function integerModulus($othervariable) {
        $result = clone($this);
        
        if ($this->_isNull() || $othervariable->_isNull() || $othervariable->value == 0) {
            $result->value = null;
            return $result;
        }
        
        $result->value = $this->value % $othervariable->value;
        return $result;
    }
    
    public function truncate() {
        $result = new qti_variable('single', 'integer');
        
        if ($this->_isNull()) {
            return $result;
        }
        
        if ($this->value > 0) {
            $result->value = floor($this->value);
        } else {
            $result->value = ceil($this->value);
        }
        return $result;
    }
    
    public function round() {
        $result = new qti_variable('single', 'integer');
        
        if ($this->_isNull()) {
            return $result;
        }
        
        $result->value = round($this->value, 0, PHP_ROUND_HALF_DOWN);

        return $result;
    }
    
    public function integerToFloat() {
        $result = clone($this);
        $result->type = 'float';
        return $result;
    }
    
    public function customOperator() {
        throw new Exception("Not implemented");
    }
    
    

    // Return a qti_variable representing the default
    public function getDefaultValue() {
        return new qti_variable($this->cardinality, $this->type, array('value' => $this->defaultValue));
    }

    // Return a qti_variable representing the correct value
    public function getCorrectResponse() {
        return new qti_variable($this->cardinality, $this->type, array('value' => $this->correctResponse));
    }

    /**
     * Set the value of the variable
     * @param qti_variable $value The value either as a qti_variable
     */
    public function setValue($value) {
        $this->value = $value->value;
    }
    
    public function getValue() {
        return $this->value;
    }

    public function __toString(){
        return $this->cardinality . ' ' . $this->type . ' [' . (is_array($this->value) ? implode(',', $this->value) : $this->value) . ']';
    }

}

/**
 * An iterator which will iterate over an array of choices, taking into
 * account the shuffle and fixed attributes.
 */
class qti_choiceIterator implements Iterator {

    protected $choices;
    protected $position = 0;

    public function __construct($choiceArray, $shuffle = false) {
        $this->position = 0;

        $identifiers = array();
        $fixed = array();
        for($i = 0; $i < count($choiceArray); $i++) {
            if (isset($choiceArray[$i]->attrs['fixed']) && $choiceArray[$i]->attrs['fixed'] == 'true') {
                $fixed[] = $i;
            }
        }
        $order = range(0, count($choiceArray) - 1);
        if ($shuffle) {
            $notfixed = array_diff($order, $fixed);
            shuffle($notfixed);
            $shuffledused = 0;
            for($i = 0; $i < count($choiceArray); $i++) {
                if(in_array($i, $fixed)) {
                    $this->choices[] = $choiceArray[$i];
                } else {
                    $this->choices[] = $choiceArray[$notfixed[$shuffledused]];
                    $shuffledused++;
                }
            }
        } else {
            $this->choices = $choiceArray;
        }

    }

    public function rewind() {
        $this->position = 0;
    }

    function current() {
        return $this->choices[$this->position];
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }

    function valid() {
        return isset($this->choices[$this->position]);
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
        session_start();
        if (!isset($_SESSION[$controller->identifier])) {
            $_SESSION[$controller->identifier] = array();
        }
        $_SESSION[$controller->identifier]['response'] = $controller->response;
        $_SESSION[$controller->identifier]['outcome'] = $controller->outcome;
        $_SESSION[$controller->identifier]['state'] = $controller->state;
    }

    public function restore($controller) {
        session_start();
        if (!isset($_SESSION[$controller->identifier])) {
            return;
        }
        $sessionvariable = $_SESSION[$controller->identifier];
        if (!isset($sessionvariable['state'])) {
            return;
        }
        $controller->response = $sessionvariable['response'];
        $controller->outcome = $sessionvariable['outcome'];
        $controller->state = $sessionvariable['state'];
    }

}

class qti_http_response_source {

    /**
     * Update a variable with values from $_POST
     * @param string $name
     * @param qti_variable $variable
     */
    
    public function bindVariable($name, qti_variable &$variable) {
        switch ($variable->cardinality) {
            case 'single':
                if( $submittedvalue = $this->get($name)) {
                    $variable->value = $submittedvalue;
                    if ($variable->type == 'directedPair') {
                    // Gap is target, value is source
                        foreach($submittedvalue as $target => $source) {
                            $variable->value = "$source $target";
                            break; // There should be only one
                        }
                    } else if ($variable->type == 'boolean') {
                        $variable->value = ($submittedvalue == 'true');
                    }
                }
                break;
            case 'multiple':
                if($submittedvalue = $this->get($name)) {
                    if (is_array($submittedvalue)) {
                        $variable->value = $submittedvalue;
                    } else {
                        $variable->value = array($submittedvalue);
                    }
                    if ($variable->type == 'directedPair') {
                        $variable->value = array();
                        // Gap is target, value is source
                        // This is a bit over-complicated to deal with matchInteraction
                        foreach($submittedvalue as $target => $source) {
                            if (!is_array($source)) {
                                $source = array($source);
                            }
                            foreach($source as $s) {
                                $variable->value[] = "$s $target";
                            }
                        }
                    }
                }
                break;
            case 'ordered':
                /* Ordered variables use inputs with names like:
                 * RESPONSE[choiceA] which have integer values giving
                 * the order
                 * 
                 * TODO: Deal with unset options
                 */
                $values = $this->get($name);
                $values = array_flip($values);
                ksort($values);
                $variable->value = array_values($values);
                break;
            default:
                throw new Exception('qti_http_response_source does not support variable cardinality ' . $variable->cardinality);
        }
         
    }
    
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
        
        // Support MathML functions. (___mathml_math function 
        // exists below to create container with correct NS)
        // TODO: It would be good if this was pluggable to support other namespaces if required.
        if (strpos($name, '__mathml_') === 0) {
            $name = substr($name, 9);
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
    
    public static function __basicElement($name, $attrs, $args) {
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
    }

    public static function __text($text) {
        return function($controller) use ($text) {
            return $text;
        };
    }

    // TODO: These next 2 exist just to wire in the resource provider - simplify
    
    public function _img($attrs, $args) {
        return function($controller) use ($attrs) {
            if(isset($attrs['src'])) {
                $attrs['src'] = $controller->resource_provider->urlFor($attrs['src']);
            }
            return qti_item_body::__basicElement('img', $attrs, $args);
        };
    }
    
    public function _object($attrs, $args) {
        return function($controller) use ($attrs) {
            if(isset($attrs['data'])) {
                $attrs['data'] = $controller->resource_provider->urlFor($attrs['data']);
            }
            return qti_item_body::__basicElement('object', $attrs, $args);
        };
    }
    
    public function execute() {
        return ($this->displayFunction->__invoke($this->controller));
    }

    public function _itemBody($attrs, $children) {
        $this->displayFunction = function($controller) use($children) {
            $result = "<div";
            if(!empty($attrs)) { // add stuff like "class" attribute
                foreach($attrs as $key => $value) {
                    $result .= " $key=\"$value\"";
                }
            }
            $result .= ">";
            foreach($children as $child) {
                $result .= $child->__invoke($controller);
            }
            $result .= "</div>";
            return $result;
        };
    }
    
    /* Create MathML container. Note the three underscores are required
     * as the method name generated is __mathml_math (with 2 underscores)
     */
    public function ___mathml_math($attrs, $children) {
        return function($controller) use($attrs, $children) {
            $result = "<math xmlns=\"http://www.w3.org/1998/Math/MathML\">";
            foreach($children as $child) {
                $result .= $child->__invoke($controller);
            }
            $result .= "</math>";
            return $result;
        };
    }

}

/* 
 * TODO: The creation of the expression closures and classes should probably be refactored out into
 * a qti_expression_factory class, or something like that.
 */
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
        if ($this->processingFunction) { // there may be no processing (e.g. extended_text.xml)
            $this->processingFunction->__invoke($this->controller);
        }
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
                    return;
                }
            }
        };
    }

    public function _responseIf($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $result = $children[0]->__invoke($controller);
            if ($result->value === true) {
                for($i = 1; $i < count($children); $i++) {
                    $children[$i]->__invoke($controller);
                }
            }
            return $result;
        };
    }

    public function _responseElseIf($attrs, $children) {
        // Identical to responseIf
        return function($controller) use ($attrs, $children) {
            $result = $children[0]->__invoke($controller);
            if ($result->value === true) {
                for($i = 1; $i < count($children); $i++) {
                    $children[$i]->__invoke($controller);
                }
            }
            return $result;
        };
    }

    public function _responseElse($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            for($i = 0; $i < count($children); $i++) {
                $children[$i]->__invoke($controller);
            }
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
                return $controller->response[$varname]->getDefaultValue();
            } else if (isset($controller->outcome[$varname])) {
                return $controller->outcome[$varname]->getDefaultValue();
            } else {
                throw new qti_response_processing_exception("Variable $varname not found");
            }
        };
    }

    public function _correct($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $varname = $attrs['identifier'];
            if(isset($controller->response[$varname])) {
                return $controller->response[$varname]->getCorrectResponse();
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
        return function($controller) use ($attrs, $children) {
            $vars = array();
            foreach($children as $child) {
                $vars[] = $child->__invoke($controller);
            }
            return qti_variable::multiple($vars);
        };
    }

    public function _ordered($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $vars = array();
            foreach($children as $child) {
                $vars[] = $child->__invoke($controller);
            }
            return qti_variable::ordered($vars);
        };
    }

    public function _containerSize($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $container = $child->__invoke($controller);
            return $container->containerSize();
        };
    }

    public function _isNull($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $what = $children[0]->__invoke($controller);
            return $what->isNull();
        };
    }

    public function _index($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $what = $children[0]->__invoke($controller);
            return $what->index($attrs['n']);
        };
    }

    public function _fieldValue($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $what = $children[0]->__invoke($controller);
            return $what->fieldValue($attrs['fieldIdentifier']);
        };
    }

    public function _random($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $what = $children[0]->__invoke($controller);
            return $what->random();
        };
    }

    public function _member($attrs, $children) {
         return function($controller) use ($attrs, $children) {
            $var1 = $children[0]->__invoke($controller);
            $var2 = $children[1]->__invoke($controller);
            return $var1->member($var2);
        };
    }

    public function _delete($attrs, $children) {
          return function($controller) use ($attrs, $children) {
            $var1 = $children[0]->__invoke($controller);
            $var2 = $children[1]->__invoke($controller);
            return $var1->delete($var2);
        };
    }

    public function _contains($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $var1 = $children[0]->__invoke($controller);
            $var2 = $children[1]->__invoke($controller);
            return $var1->contains($var2);
        };
    }

    public function _substring($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $var1 = $children[0]->__invoke($controller);
            $var2 = $children[1]->__invoke($controller);
            return $var1->substring($var2, $attrs['caseSensitive']);
        };
    }

    public function _not($attrs, $children) {
         return function($controller) use ($attrs, $children) {
            $var1 = $children[0]->__invoke($controller);
            return $var1->not();
        };
    }

    public function _and($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $vars = array();
            foreach($children as $child) {
                $vars[] = $child->__invoke($controller);
            }
            return qti_variable::and_($vars);
        };
    }

    public function _or($attrs, $children) {
         return function($controller) use ($attrs, $children) {
            $vars = array();
            foreach($children as $child) {
                $vars[] = $child->__invoke($controller);
            }
            return qti_variable::or_($vars);
        };
    }

    public function _anyN($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $vars = array();
            foreach($children as $child) {
                $vars[] = $child->__invoke($controller);
            }
            return qti_variable::anyN($attrs['min'], $attrs['max'], $vars);
        };
    }

    public function _match($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            $val2 = $children[1]->__invoke($controller);
            
            return $val1->match($val2);
        };
    }

    public function _stringMatch($attrs, $children) {
         return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            $val2 = $children[1]->__invoke($controller);
            
            // TODO: Missing substring attribute will probably break helper function
            return $val1->stringMatch($val2, $attrs['caseSensitive'], $attrs['substring']);
        };
    }

    public function _patternMatch($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            
            return $val1->patternMatch($attrs['pattern']);
        };
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
        return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            $val2 = $children[1]->__invoke($controller);
            
            return $val1->lt($val2);
        };
    }

    public function _gt($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            $val2 = $children[1]->__invoke($controller);
            
            return $val1->gt($val2);
        };
    }

    public function _lte($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            $val2 = $children[1]->__invoke($controller);
            
            return $val1->lte($val2);
        };
    }

    public function _gte($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            $val2 = $children[1]->__invoke($controller);
            
            return $val1->gte($val2);
        };
    }

    public function _durationLT($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _durationGTE($attrs, $children) {
        throw new Exception("Not implemented");
    }

    public function _sum($attrs, $children) {
         return function($controller) use ($attrs, $children) {
            $vars = array();
            foreach($children as $child) {
                $vars[] = $child->__invoke($controller);
            }
            return qti_variable::sum($vars);
        };
    }

    public function _product($attrs, $children) {
         return function($controller) use ($attrs, $children) {
            $vars = array();
            foreach($children as $child) {
                $vars[] = $child->__invoke($controller);
            }
            return qti_variable::product($vars);
        };
    }

    public function _subtract($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            $val2 = $children[1]->__invoke($controller);
            
            return $val1->subtract($val2);
        };
    }

    public function _divide($attrs, $children) {
         return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            $val2 = $children[1]->__invoke($controller);
            
            return $val1->divide($val2);
        };
    }

    public function _power($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            $val2 = $children[1]->__invoke($controller);
            
            return $val1->power($val2);
        };
    }

    public function _integerDivide($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            $val2 = $children[1]->__invoke($controller);
            
            return $val1->integerDivide($val2);
        };
    }

    public function _integerModulus($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            $val2 = $children[1]->__invoke($controller);
            
            return $val1->integerModulus($val2);
        };
    }

    public function _truncate($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            
            return $val1->truncate();
        };
    }

    public function _round($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            
            return $val1->round();
        };
    }

    public function _integerToFloat($attrs, $children) {
        return function($controller) use ($attrs, $children) {
            $val1 = $children[0]->__invoke($controller);
            
            return $val1->integerToFloat();
        };
    }

    public function _customOperator($attrs, $children) {
        throw new Exception("Not implemented");
    }

}

// Modal feedback can contain any flowStatic elements, so this class extends qti_item_body
// TODO: Remove the Bootstrap framework specific code if possible
class qti_modal_feedback_processing extends qti_item_body {
    
    protected $controller;
    
    protected $processingFunction = array(); // There can be multiple modalFeedback nodes
    
    public function execute() {
        foreach($this->processingFunction as $processingFunction) {
            $result .= $processingFunction->__invoke($this->controller);
        }
        if (!empty($result)) {
            $result = '<div class="qti_modalFeedback alert"><button class="close" data-dismiss="alert">×</button>' . $result;
            $result .= '</div>';
        }
        return $result;
    }

    // Modal feedback isn't strictly a subclass of feedbackElement, but it behaves similarly
    public function _modalFeedback($attrs, $children) {
        $this->processingFunction[] = new qti_feedbackElement($attrs, $children);
    }
}
