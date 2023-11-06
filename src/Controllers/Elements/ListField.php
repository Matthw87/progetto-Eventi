<?php
namespace Marion\Controllers\Elements;
class ListField{
	private $name;
	private $fieldValue;
	private $functionType;
	private $function;
	private $searchable;
	private $sortable;
	private $sortId;
	private $searchName;
	private $searchName1;
	private $searchName2;
	private $searchValue;
	private $searchValue1;
	private $searchValue2;
	private $searchTypeValue;
	private $searchTypeValue1;
	private $searchTypeValue2;
	private $searchOptions;
	private $searchType;





	/**
	 * Get the value of name
	 */ 
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the value of name
	 *
	 * @return  self
	 */ 
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Get the value of fieldValue
	 */ 
	public function getFieldValue()
	{
		return $this->fieldValue;
	}

	/**
	 * Set the value of fieldValue
	 *
	 * @return  self
	 */ 
	public function setFieldValue($fieldValue)
	{
		$this->fieldValue = $fieldValue;

		return $this;
	}

	/**
	 * Get the value of functionType
	 */ 
	public function getFunctionType()
	{
		return $this->functionType;
	}

	/**
	 * Set the value of functionType
	 *
	 * @return  self
	 */ 
	public function setFunctionType($functionType)
	{
		$this->functionType = $functionType;

		return $this;
	}

	/**
	 * Get the value of function
	 */ 
	public function getFunction()
	{
		return $this->function;
	}

	/**
	 * Set the value of function
	 *
	 * @return  self
	 */ 
	public function setFunction($function)
	{
		$this->function = $function;

		return $this;
	}

	/**
	 * Get the value of searchable
	 */ 
	public function getSearchable()
	{
		return $this->searchable;
	}

	/**
	 * Set the value of searchable
	 *
	 * @return  self
	 */ 
	public function setSearchable($searchable)
	{
		$this->searchable = $searchable;

		return $this;
	}

	/**
	 * Get the value of sortable
	 */ 
	public function getSortable()
	{
		return $this->sortable;
	}

	/**
	 * Set the value of sortable
	 *
	 * @return  self
	 */ 
	public function setSortable($sortable)
	{
		$this->sortable = $sortable;

		return $this;
	}

	/**
	 * Get the value of sortId
	 */ 
	public function getSortId()
	{
		return $this->sortId;
	}

	/**
	 * Set the value of sortId
	 *
	 * @return  self
	 */ 
	public function setSortId($sortId)
	{
		$this->sortId = $sortId;

		return $this;
	}

	/**
	 * Get the value of searchName
	 */ 
	public function getSearchName()
	{
		return $this->searchName;
	}

	/**
	 * Set the value of searchName
	 *
	 * @return  self
	 */ 
	public function setSearchName($searchName)
	{
		$this->searchName = $searchName;

		return $this;
	}

	/**
	 * Get the value of searchName1
	 */ 
	public function getSearchName1()
	{
		return $this->searchName1;
	}

	/**
	 * Set the value of searchName1
	 *
	 * @return  self
	 */ 
	public function setSearchName1($searchName1)
	{
		$this->searchName1 = $searchName1;

		return $this;
	}

	/**
	 * Get the value of searchName2
	 */ 
	public function getSearchName2()
	{
		return $this->searchName2;
	}

	/**
	 * Set the value of searchName2
	 *
	 * @return  self
	 */ 
	public function setSearchName2($searchName2)
	{
		$this->searchName2 = $searchName2;

		return $this;
	}

	/**
	 * Get the value of searchValue
	 */ 
	public function getSearchValue()
	{
		return $this->searchValue;
	}

	/**
	 * Set the value of searchValue
	 *
	 * @return  self
	 */ 
	public function setSearchValue($searchValue)
	{
		$this->searchValue = $searchValue;

		return $this;
	}

	/**
	 * Get the value of searchValue1
	 */ 
	public function getSearchValue1()
	{
		return $this->searchValue1;
	}

	/**
	 * Set the value of searchValue1
	 *
	 * @return  self
	 */ 
	public function setSearchValue1($searchValue1)
	{
		$this->searchValue1 = $searchValue1;

		return $this;
	}

	/**
	 * Get the value of searchValue2
	 */ 
	public function getSearchValue2()
	{
		return $this->searchValue2;
	}

	/**
	 * Set the value of searchValue2
	 *
	 * @return  self
	 */ 
	public function setSearchValue2($searchValue2)
	{
		$this->searchValue2 = $searchValue2;

		return $this;
	}

	/**
	 * Get the value of searchTypeValue
	 */ 
	public function getSearchTypeValue()
	{
		return $this->searchTypeValue;
	}

	/**
	 * Set the value of searchTypeValue
	 *
	 * @return  self
	 */ 
	public function setSearchTypeValue($searchTypeValue)
	{
		$this->searchTypeValue = $searchTypeValue;

		return $this;
	}

	/**
	 * Get the value of searchTypeValue1
	 */ 
	public function getSearchTypeValue1()
	{
		return $this->searchTypeValue1;
	}

	/**
	 * Set the value of searchTypeValue1
	 *
	 * @return  self
	 */ 
	public function setSearchTypeValue1($searchTypeValue1)
	{
		$this->searchTypeValue1 = $searchTypeValue1;

		return $this;
	}

	/**
	 * Get the value of searchTypeValue2
	 */ 
	public function getSearchTypeValue2()
	{
		return $this->searchTypeValue2;
	}

	/**
	 * Set the value of searchTypeValue2
	 *
	 * @return  self
	 */ 
	public function setSearchTypeValue2($searchTypeValue2)
	{
		$this->searchTypeValue2 = $searchTypeValue2;

		return $this;
	}

	/**
	 * Get the value of searchOptions
	 */ 
	public function getSearchOptions()
	{
		return $this->searchOptions;
	}

	/**
	 * Set the value of searchOptions
	 *
	 * @return  self
	 */ 
	public function setSearchOptions($searchOptions)
	{
		$this->searchOptions = $searchOptions;

		return $this;
	}

	/**
	 * Get the value of searchType
	 */ 
	public function getSearchType()
	{
		return $this->searchType;
	}

	/**
	 * Set the value of searchType
	 *
	 * @return  self
	 */ 
	public function setSearchType($searchType)
	{
		$this->searchType = $searchType;

		return $this;
	}

	public static function create(){
		return new ListField();
	}
	
}
?>