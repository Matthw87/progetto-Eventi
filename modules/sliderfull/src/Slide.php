<?php
namespace SliderFull;
use Marion\Core\Base;
use Marion\Support\Image\ImageComposed;
class Slide extends Base{
	// COSTANTI DI BASE
	const TABLE = 'sliderfull_slides'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'sliderfull_slides_langs'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'slide_id';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'lang'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault

	public $id;
	public $slider_id;
	public $image;
	public $mobile_image;

	/**
	 * return url image for desktop
	 *
	 * @param string $type
	 * @return string
	 */
	function getUrlImage(): string{
		return  _MARION_BASE_URL_."media/filemanager/".$this->image;
	}

	/**
	 * return url image for mobile
	 *
	 * @param string $type
	 * @return string
	 */
	function getMobileUrlImage(): string{

		if( $this->mobile_image ){
			return  _MARION_BASE_URL_."media/filemanager/".$this->mobile_image;
		}else{
			return $this->getUrlImage();
		}
	}



	/**
	 * Get Slider
	 *
	 * @return Slider|null
	 */
	public function getSlider(): ?Slider{
		if( $this->slider_id ){
			return Slider::withId($this->slider_id);
		}
		return null;
	}


}



?>