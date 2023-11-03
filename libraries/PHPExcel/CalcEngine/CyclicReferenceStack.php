<?php




class PHPExcel_CalcEngine_CyclicReferenceStack {

	
	private $_stack = array();


	
	public function count() {
		return safe_count($this->_stack);
	}

	
	public function push($value) {
		$this->_stack[$value] = $value;
	}

	
	public function pop() {
		return array_pop($this->_stack);
	}

	
	public function onStack($value) {
		return isset($this->_stack[$value]);
	}

	
	public function clear() {
		$this->_stack = array();
	}

	
	public function showStack() {
		return $this->_stack;
	}

}
