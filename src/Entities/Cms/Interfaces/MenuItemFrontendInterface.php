<?php
namespace Marion\Entities\Cms\Interfaces;
interface MenuItemFrontendInterface{
	


	public static function getGroupName(): string;


	public static function getUrl(array $params):string;

	public static function getPages():array;
}




?>