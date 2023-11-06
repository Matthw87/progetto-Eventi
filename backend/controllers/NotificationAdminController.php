<?php
use Marion\Core\Marion;
use Marion\Entities\Cms\Notification;
use Marion\Controllers\AdminController;
class NotificationAdminController extends AdminController{
	public $_auth = 'cms';
	
	
	function displayContent()
	{
		$action = $this->getAction();
		switch($action){
			case 'view_mail':
				$id = _var('id');
				$database = Marion::getDB();
				$mail_view = $database->select('*','mail_log',"id={$id}");
				
				$this->setVar('mail_view',$mail_view[0]);
				$this->output('@core/admin/notification/view_mail.htm');
			break;
		}
	}

	function displayList(){


            $id = _var('id');
            
            $limit = $this->getLimitList();
            $offset = $this->getOffsetList();
            
			$query = Notification::prepareQuery();
			$user = Marion::getUser();
            $query->where('receiver',$user->id);
            if( $id ){
                $query->where('id',$id);
            }
            $query2 = clone $query;

            if( $limit ){
				$query->limit($limit);
			}
			if( $offset ){
				$query->offset($offset);
            }
            
            $tot = $query2->getCount();
           
		     $notifications = $query->orderBy('date','DESC')->get();
           
            if(okArray($notifications)){
                
                foreach($notifications as  $v){
                    $v->view_old = $v->view;
                    $v->set(array('view'=>1))->save();
                    
                }
                
            }
            $this->setVar('list',$notifications);
            //$this->setVar('links',$pager_links);
			
			$this->output('@core/admin/notification/list.htm');
	}


	function delete(){
		$id = $this->getID();
	
		$database = Marion::getDB();
		$database->delete('footer',"id={$id}");
		$this->redirectToList(array('deleted'=>1));
		

		
	}


}

?>