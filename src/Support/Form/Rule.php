<?php
namespace Marion\Support\Form;
use Closure;

class Rule {

    
    public Closure $validation_function;

    /**
     * Undocumented function
     *
     * @param Closure $function
     * @return self
     */
    public function validate(Closure $function): self{
        $this->validation_function = $function;
        return $this;
    }

}