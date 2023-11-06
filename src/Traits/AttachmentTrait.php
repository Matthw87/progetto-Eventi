<?php
namespace Marion\Traits;
use Marion\Core\Attachment;
trait AttachmentTrait{

	//azzera il vettore contenente gli allegati
	function clearAttachments(){
		$this->attachments = array();
		
	}


	//rimuove tutti gli allegati
	function deleteAllAttachments(){
		if( okArray($this->attachments) ){
			foreach($this->attachments as $id){
				$attach = Attachment::withId($id);
				if( is_object($attach) ){
					$attach->delete();
				}
			}
		}
		$this->clearAttachments();
	}


	//rimuove un allegato ad uno specificato indice
	function deleteAttachAtIndex($index){
		$id = $this->attachments[$index];
		if( $id ){
			$attach = Attachment::withId($id);
			if( is_object($attach) ){
				$attach->delete();
			}
			unset($this->attachments[$index]);
		}
	}

	//setta l'array degli allegati con un array di identificaitvi di allegati
	function setAttachemntsFromArray($array=array()){
		$this->attachments = $array;
	}





	function hasAttachments(){
		if( property_exists( $this, 'attachments') ){
			return okArray($this->attachments);
		}
		return false;
	}
}


	




?>