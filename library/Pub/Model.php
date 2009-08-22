<?php

/**
 * Abstract Pub Model class. Encapsulates generic functionality to set and
 * retrieve model specific data through accessors based on the model attributes
 * and parameters.
 * 
 * This is based on Default_Model_GuestbookMapper from the Zend Framework
 * Quickstart tutorial source as at April 30, 2009.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Wed Aug 19, 2009 01:00 AM
 */

abstract class Pub_Model
{
	protected $_mapper;
	
	/**
	 * Constructor.
	 * 
	 * @param array|null $options
	 * @return void
	 */
	public function __construct(array $options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		}
	}
	
	/**
	 * Overloading: allow property access.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$method = 'set'.$name;
		if ('mapper' == $name || !method_exists($this, $method)) {
			throw Exception('Invalid property specified!');
		}
		$this->$method($value);
	}
	
	/**
	 * Overloading: allow property access.
	 * 
	 * @param string $name
	 * @return void
	 */
	public function __get($name)
	{
		$method = 'get'.$name;
		if ('mapper' == $name || !method_exists($this, $method)) {
			throw Exception('Invalid property specified!');
		}
		return $this->$method();
	}
	
	/**
	 * Set object state.
	 * 
	 * @param array $options
	 * @return Pub_Model sub class instance
	 */
	public function setOptions(array $options)
	{
		$methods = get_class_methods($this);
		foreach ($options as $key => $value) {
			$method = 'set'.ucfirst($key);
			if (in_array($method, $methods)) {
				$this->$method($value);
			}
		}
		return $this;
	}
	
	/**
	 * Set data mapper.
	 * 
	 * @param mixed $mapper
	 * @return Pub_Model sub class instance.
	 */
	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}
	
	/**
	 * Get data mapper.
	 * 
	 * Lazy loads the default mapper instance if no mapper registered.
	 * 
	 * @return Mapper for a model.
	 */
	public function getMapper()
	{
		if (null === $this->_mapper) {
			$mapperClass = get_class($this).'Mapper';
			$this->setMapper(new $mapperClass());
		}
		return $this->_mapper;
	}
	
	/**
	 * Save the current entry.
	 * 
	 * @return void
	 */
	public function save()
	{
		$this->getMapper()->save($this);
	}
	
	/**
	 * Find an entry.
	 * 
	 * Resets entry state if matching id found.
	 * 
	 * @param mixed $id
	 * @return Pub_Model sub class.
	 */
	public function find($id)
	{
		$this->getMapper()->find($id, $this);
		return $this;
	}
	
	/**
	 * Fetch all entries.
	 * 
	 * @return array
	 */
	public function fetchAll()
	{
		return $this->getMapper()->fetchAll();
	}
}
