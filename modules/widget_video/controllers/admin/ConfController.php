<?php
use Marion\Controllers\Controller;
use Marion\Core\Marion;
use Marion\Support\Form\Traits\FormHelper;

class ConfController extends Controller
{
	use FormHelper;
	public $_auth = 'cms';
	public $_form_control = 'widget_video';

	private $upload_files;
	private $extensions_files = array('mp4','webm');


	function init($options = array())
	{
		parent::init($options);
		$this->upload_files = media_dir('widget_video/uploads')."/";
	}

	function generateSlug($name)
	{
		$index = 1;
		$slug = strtolower(preg_replace('/_/', '-', preg_replace('/\s+/', '-', $name)));
		$newSlug = '';

		if (file_exists($this->upload_files . $slug)) {
			do {
				$newSlug = $index + '-' . $slug;
				$index++;
			} while (file_exists($this->upload_files . $newSlug));
		}

		$toReturn = ($newSlug != '') ? $newSlug : $slug;

		return $toReturn;
	}

	function saveFile($files)
	{
		$file = $files['file'];

		$slug = $this->generateSlug($file['name']);
		$tmp = $file['tmp_name'];
		$ext = strtolower(end(explode('.', $file['name'])));
		
		if (!in_array($ext,$this->extensions_files) ){
			$error = 'L\'estensione del file non è corretta';
			return $error;
		}
		if( is_writable($this->upload_files) ){
			return move_uploaded_file($tmp, $this->upload_files . $slug);
		}else{
			return "La cartella <b>uploads</b> all'interno del modulo <b>widget_video</b> non è scrivibile";
		}
	}

	function display()
	{
		$database = Marion::getDB();
		$this->id_box = _var('id_box');
		$this->setVar('id_box', _var('id_box'));
		if ($this->isSubmitted()) {
			$formdata = $this->getFormdata();

			
			$array = $this->checkDataForm($this->_form_control, $formdata);

			if ($array[0] == 'ok') {
				if ($formdata['tipo_video'] == 'carica' && $_FILES['file']['tmp_name'] != '') {
					$error = $this->saveFile($_FILES);
					if( $error != 1 ){
						$array[0] = 'nak';
						$array[1] = $error;
					}
				}
			}
			if ($array[0] == 'ok') {

				

				unset($array[0]);

				$data = array();
				foreach ($array as $k => $v) {
					if ($k != '_locale_data') {
						$data[$k] = $v;
					}
				}
				if( array_key_exists('_locale_data',$array) ){
					foreach ($array['_locale_data'] as $k => $v) {
						foreach ($v as $k1 => $v1) {
							$data[$k1][$k] = $v1;
						}
					}
				}

				if ($formdata['tipo_video'] == 'carica' && $_FILES['file']['tmp_name'] != '') {
					$slug = $this->generateSlug($_FILES['file']['name']);

					$data['url'] = $slug;
					$formdata['url'] = $slug;
				}

				$dati = serialize($data);

				$database->update('composed_page_composition_tmp', "id={$this->id_box}", array('parameters' => $dati));

				$this->displayMessage('Dati salati con successo!', 'success');
			} else {
				$this->errors[] = $array[1];
			}
			$dati = $formdata;
		} else {
			$data = $database->select('*', 'composed_page_composition_tmp', "id={$this->id_box}");
			$dati = null;
			if (okArray($data)) {
				if( isset($data[0]['parameters']) ){
					$dati = unserialize($data[0]['parameters']);
				}else{
					$dati = [];
				}
				
			}
		}
		if( isset($dati['tipo_video']) && $dati['tipo_video'] == 'carica' ){
			if( $dati['url'] ){
				if( file_exists($this->upload_files. $dati['url']) ){
					$this->setVar('file_caricato',$dati['url']);
				}
			}
		}

		$dataform = $this->getDataForm($this->_form_control, $dati);

		$this->setVar('dataform', $dataform);

		$this->output('@widget_video/admin/conf.htm');
	}
}
