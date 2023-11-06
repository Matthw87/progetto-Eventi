<?php
namespace Marion\Support\ListWrapper;
use Marion\Controllers\Controller;
use JasonGrimes\Paginator;
use Marion\Controllers\Interfaces\TabAdminInterface;
use Marion\Core\Marion;

class ListCore {

	/**
     * List container layout
     *
     * @var string
     */
    public string $container_layout = '@core/layouts/base.htm';
   
	/**
	 * List id
	 *
	 * @var any
	 */
    public $id;


	/**
	 * Nama rom id
	 *
	 * @var string
	 */
    protected string $row_id = 'id';


	/**
	 * Controller context
	 *
	 * @var Controller
	 */
    protected Controller $ctrl;

	/**
	 * Datasource
	 *
	 * @var DataSource
	 */
    public DataSource $data_source;

	/**
	 * list object fields
	 *
	 * @var array
	 */
    protected array $fields = [];
    
	/**
	 * Total items
	 *
	 * @var integer
	 */
	protected int $totalItems;

	/**
	 * Items per page
	 *
	 * @var integer
	 */
	protected static int $perPage;

	/**
	 * select perPage
	 *
	 * @var integer
	 */
	protected static array $perPageSelect = [25,50,100];


	/**
	 * Max page to show
	 *
	 * @var integer
	 */
	protected int $maxPagesToShow = 4;

	/**
	 * list builded fields
	 *
	 * @var array
	 */
    protected array $_array_fields = [];

	/**
	 * Template data
	 *
	 * @var array
	 */
    protected array $template_data = [];


	/**
	 * Data list
	 *
	 * @var array
	 */
    protected array $data_list = [];

	/**
	 * List action buttons
	 *
	 * @var array
	 */
	protected array $actionRowButtons = [];


	/**
	 * Enable row actions
	 *
	 * @var boolean
	 */
	protected bool $actionRowEnabled = true;


	/**
	 * html template
	 *
	 * @var string
	 */
	protected string $html_template;

	/**
	 * action callback
	 *
	 * @var array \Closure
	 */
	public $action_functions = [];



    public function __construct(string $id, Controller $ctrl)
	{
        $this->id = $id;
		$this->ctrl = $ctrl;
	}

		
	/**
	 * Set row id
	 *
	 * @param string $id
	 * @return static
	 */
	public function setRowId(string $id): static{
		$this->row_id = $id;
		return $this;
	}

	/**
	 * Set template html
	 *
	 * @param string $template
	 * @return static
	 */
	function setTemplateHtml(string $template): static{
		$this->html_template = $template;
		return $this;
	}


	/**
	 * Set per page
	 *
	 * @param integer $num
	 * @return static
	 */
    function setPerPage(int $num): static{
		self::$perPage = $num;
		return $this;
    }

	/**
	 * Set max pages to show
	 *
	 * @param integer $num
	 * @return static
	 */
    function setMaxPagesToShow(int $num): static{
		$this->maxPagesToShow = $num;
		return $this;
    }

	/* Set per page select
	*
	* @param integer $num
	* @return static
	*/
   function setPerPageSelect(array $select): static{
	   self::$perPageSelect = $select;
	   return $this;
   }
    

	/**
	 * Set Datasource
	 *
	 * @param DataSource $source
	 * @return static
	 */
    public function setDataSource(DataSource $source): static{
		$this->data_source = $source;
        $this->data_source->setListCore($this);
        return $this;
	}

	/**
	 * Set builded fields
	 *
	 * @param array $fields
	 * @return static
	 */
	function setFieldsFromArray(array $fields): static{
		$this->_array_fields = $fields;
		return $this;
	}

	/**
	 * Set fields
	 *
	 * @param array $fields
	 * @return static
	 */
	function setFields(array $fields): static{
		$this->fields = $fields;
		return $this;
	}

	/**
	 * Set Data list
	 *
	 * @param array|null $list
	 * @return static
	 */
	public function setDataList(array $list = null): static{
		$this->data_list = $list;
		//$this->_list_options['data_list'] = $list;
		return $this;
	}

	/**
	 * Set total items
	 *
	 * @param integer $totalItems
	 * @return static
	 */
	function setTotalItems(int $totalItems): static{
		$this->totalItems = $totalItems;
		return $this;
	}


	/**
	 * Add action row button
	 *
	 * @param ListActionRowButton $button
	 * @return static
	 */
	function addActionRowButton(ListActionRowButton $button): static{
		$this->actionRowButtons[] = $button;
		return $this;
	}

	/**
	 * Add multiple action row buttons
	 *
	 * @param Array $buttons
	 * @return static
	 */
	function addActionRowButtons(Array $buttons): static{
		foreach($buttons as $btn){
			$this->addActionRowButton($btn);
		}
		return $this;
	}

	/**
	 * Enable row actions
	 *
	 * @param boolean $bool
	 * @return static
	 */
	function enableRowActions(bool $bool): static{
		$this->actionRowEnabled = $bool;
		return $this;
	}


	/**
	 * Return ction row button from tag
	 *
	 * @param string $action
	 * @return ListActionRowButton|null
	 */
	function getActionRowButton(string $action): ?ListActionRowButton{
		foreach($this->actionRowButtons as $b){
			if( $b->getAction() == $action ){
				$btn = $b;
				break;
			}
		}
		return isset($btn)?$btn:null;
	}

	/**
	 * Remove action row button from tag
	 *
	 * @param string $action
	 * @return static
	 */
	function removeActionRowButton(string $action): static{
		$this->actionRowButtons = array_filter($this->actionRowButtons,function($item) use ($action){
			return $item->getAction() != $action;
		});

		return $this;
	}

	/**
	 * get per page
	 *
	 * @return integer
	 */
	function getPerPage(): int{
		return self::$perPage;
	}

    function getFields(){
		return $this->fields;
	}

	/**
	 * return current limit pager
	 *
	 * @return integer
	 */
	public static function limit(): int{
		$per_page = _var('perPage');
		if($per_page ){
			self::$perPage = $per_page;
			$limit = $per_page;
		}else{
			if( isset(static::$perPage) ){
				$limit = static::$perPage;
			}else{
				$limit = static::$perPageSelect[0];
			}
		}
		return $limit;
	}

	/**
	 * return urrent offser
	 *
	 * @return integer
	 */
	public static function offset(): int{
		$page_id = _var('pageID');
		
		$limit = self::limit();
		$offset = $page_id?($page_id-1)*$limit:0;
		return $offset;
	}

    
    function getFieldList(){
        return $this->_array_fields;
    }



	function buildRowActions(): void{
		
		$row_actions = [];
		if( $this->actionRowEnabled ){
			foreach($this->actionRowButtons as $btn){
				$row_actions[$btn->getAction()] = $btn->getData();
			}
		}
		$this->template_data['row_actions'] = $row_actions;
	}
	
    
   
	/**
	 * build list
	 *
	 * @return void
	 */
    function build(): void{
		

		Marion::do_action('action_extend_list_'.$this->id,array($this));

		$this->runActions();

		if( isset($this->data_source) ){
			$this->data_source->build();

			$this->setDataList($this->data_source->data);
			$this->setTotalItems($this->data_source->count);
		}
        
        $this->template_data = [
			"enable_row_actions" => $this->actionRowEnabled,
            "total_items" => isset($this->totalItems)?$this->totalItems:0,
            "per_page" => isset(self::$perPage)?self::$perPage: self::$perPageSelect[0],
			"per_page_select" => self::$perPageSelect,
            "fields" => $this->_array_fields,
			"data_list" => $this->data_list
        ];
		$this->buildRowActions();
		$paginator = $this->buildPaginator();
		$this->ctrl->setVar('_paginator',$paginator);
		
		
		
    }

	/**
	 * display list
	 *
	 * @return void
	 */
	function display(): void{
		if ($this->ctrl instanceof TabAdminInterface) {
            $this->container_layout = '@core/layouts/tab/base.htm';
        }
		$this->ctrl->setVar('container_layout',$this->container_layout);
		$this->ctrl->setVar('template_data',$this->template_data);
		$this->ctrl->output($this->html_template);
    }

	/**
     * Action row callback
     *
     * @param Closure $function
     * @return static
     */
    function onAction(\Closure $function): static{
        $this->action_functions[] = $function;
        return $this;
    }

	/**
	 * Callback on sort
	 *
	 * @param \Closure $function
	 * @return static
	 */
    public function onSort(\Closure $function): static{
        $this->data_source->onSort($function);
        return $this;
    }

	

	/**
	 * Callback on search
	 *
	 * @param \Closure $function
	 * @return static
	 */
    public function onSearch(\Closure $function): static{
        $this->data_source->onSearch($function);
        return $this;
    }


   

	function runActions(): void{
		
		$action = _var('action_list');
		$id = _var('id_row_list');
		if( $action ){
			foreach($this->action_functions as $function){
				call_user_func($function,$action,$id);
			}
			
		}
	}


	/**
	 * Create new ListCore
	 *
	 * @param string $id
	 * @param Controller $ctrl
	 * @return static
	 */
	public static function create(string $id, Controller $ctrl): static{
		$obj = new static($id,$ctrl);
        return $obj;
    }


	/**
	 * build paginator
	 *
	 * @return Paginator
	 */
	function buildPaginator(): Paginator{
		$totalItems = $this->template_data['total_items'];
		
		$itemsPerPage = $this->template_data['per_page'];
		$currentPage = _var('pageID');
		//$urlPattern = '/foo/page/(:num)';
		$urlPattern = $this->ctrl->getUrlCurrent();
		$urlPattern = preg_replace("/&pageID=([0-9]+)/",'',$urlPattern);
		
		$urlPattern .= "&pageID=(:num)";
	
		$paginator = new Paginator($totalItems, $itemsPerPage, $currentPage,$urlPattern);
		$paginator->setMaxPagesToShow($this->maxPagesToShow);
		return $paginator;
	}
}