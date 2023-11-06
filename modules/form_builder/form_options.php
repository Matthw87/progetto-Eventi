<?php

$database = _obj('Database');

$tipi_select = Form::getTipi();
$array_tipi = array();
foreach( $tipi_select as $v){
	$array_tipi[0] = 'Seleziona...';
	$array_tipi[$v['codice']] = $v['etichetta'];
}


$campi_globali_select = $database->select('c.*,f.nome as nome_form','form_campo as c join form as f on f.codice=c.form',"c.globale=1 order by f.nome");
if( okArray($campi_globali_select) ){
	foreach($campi_globali_select as $v){
		$campi_globali[$v['nome_form']][$v['codice']]=$v['campo'];
		$campi_globali_form[]=$v['codice'];
	}

}


//gruppi form
$gruppi_form = $database->select('*','form_gruppo');
$select_gruppi_form = array("Seleziona...");
if(okArray($gruppi_form)){
	foreach($gruppi_form as $v){
		$select_gruppi_form[$v['codice']] = $v['nome'];
	}
}


$type_select = Form::getType();
$array_type = array();
$array_type[0] = 'Seleziona...';
foreach( $type_select as $v){
	$array_type[$v['codice']] = $v['etichetta'];
}

$typeTextarea = Form::getTipoTextArea();
//debugga($typeTextarea);exit;
$array_typeTextArea = array();
$array_typeTextArea[0] = 'Seleziona...';
foreach( $typeTextarea as $v){
	$array_typeTextArea[$v['codice']] = $v['etichetta'];
}

$typeData = Form::getTipoData();
//debugga($typeTextarea);exit;
$array_typeData = array();
$array_typeData[0] = 'Seleziona...';
foreach( $typeData as $v){
	$array_typeData[$v['codice']] = $v['etichetta'];
}

$typeTimestamp = Form::getTipoTimestamp();
//debugga($typeTextarea);exit;
$array_typeTimestamp = array();
$array_typeTimestamp[0] = 'Seleziona...';
foreach( $typeTimestamp as $v){
	$array_typeTimestamp[$v['codice']] = $v['etichetta'];
}

$typeTime = Form::getTipoTime();
//debugga($typeTextarea);exit;
$array_typeTime = array();
$array_typeTime[0] = 'Seleziona...';
foreach( $typeTime as $v){
	$array_typeTime[$v['codice']] = $v['etichetta'];
}

$typeFile = Form::getTipoFile();
//debugga($typeTextarea);exit;
$array_typeFile = array();
$array_typeFile[0] = 'Seleziona...';
foreach( $typeFile as $v){
	$array_typeFile[$v['codice']] = $v['etichetta'];
}


$mimeType = File::mimeTypeSupportati();
$mime_exts = array();
foreach($mimeType as $v){
	$mime_exts[] = $v;
}

//debugga($mimeType);exit;
$template->estensioni_consentite = $mimeType;


$image_conf = getConfig('image','options');
$resize_image = $image_conf['resize'];
foreach($resize_image as $v){
	$array_resize_image[$v] = $v;
}


$campi_form =array(
	'codice' => array(
        'campo' => 'codice',
        'type' => 'text',
        'default' => '',
        'obbligatorio' => 'f',
        'etichetta' => 'codice form',
    ),
	'nome' => array(
        'campo' => 'nome',
        'type' => 'text',
        'default' => '',
        'lunghezzamin' => 2,
        //'postfunction' => 'strtolower',
        'obbligatorio' => 't',
        'etichetta' => 'Nome form',
    ),
    'captcha'=>array(
        'campo'=>'captcha',
        'type'=>'radio',
        /*'origine_dati' => 'php',
        'function_php' => 'array_type',*/
        'options' => array(0,1),
        'obbligatorio'=>'t',
        'default'=> 0,
        'etichetta'=>'captcha'
    ),
    'submit'=>array(
        'campo'=>'submit',
        'type'=>'submit',
        'default'=>'Salva',
    ),
);

$campi_campo =array(
	'codice' => array(
        'campo' => 'codice',
        'type' => 'text',
        'default' => '',
        'obbligatorio' => 'f',
        'etichetta' => 'codice campo',
    ),
    'id_form' => array(
        'campo' => 'id_form',
        'type' => 'text',
        'default' => '',
        'obbligatorio' => 't',
        'etichetta' => 'codice form',
    ),
   
	 'type'=>array(
        'campo'=>'type',
        'type'=>'select',
        /*'origine_dati' => 'php',
        'function_php' => 'array_type',*/
        'options' => $array_type,
        'obbligatorio'=>'t',
        'default'=>'0',
        'etichetta'=>'tipo html'
    ),
    'tipo'=>array(
        'campo'=>'tipo',
        'type'=>'select',
        /*'origine_dati' => 'php',
        'function_php' => 'array_type',*/
        'options' => $array_tipi,
        'obbligatorio'=>'f',
        'default'=>'0',
        'etichetta'=>'tipo'
    ),
     'ordine'=>array(
        'campo'=>'ordine',
        'type'=>'text',
        'tipo' => 'Integer',
        'obbligatorio'=>'f',
        'default'=>'',
        'etichetta'=>'ordine di controllo'
    ),
    'obbligatorio'=>array(
        'campo'=>'obbligatorio',
        'type'=>'radio',
        'options' => ['0','1'],
        'obbligatorio'=>'t',
        'default'=>'0',
        'etichetta'=>'obbligatorio',
    ),
    'valuezero'=>array(
        'campo'=>'valuezero',
        'type'=>'radio',
        'options' => ['0','1'],
        'obbligatorio'=>'f',
        'default'=>'0',
        'etichetta'=>'valore zero ammesso',
    ),
    'tipo_valori'=>array(
        'campo'=>'tipo_valori',
        'type'=>'radio',
        'options' => ['0','1'],
        'obbligatorio'=>'f',
        'default'=>'1',
        'etichetta'=>'tipo valori',
    ),
    'function_template'=>array(
        'campo'=>'function_template',
        'type'=>'text',
        'obbligatorio'=>'f',
        'etichetta'=>'funzione di template',
    ),
    /*'multilocale'=>array(
        'campo'=>'multilocale',
        'type'=>'radio',
        'options' => ['0','1'],
        'obbligatorio'=>'t',
        'default'=>'0',
        'etichetta'=>'gestione piu\' lingue',
    ),*/
    /*'multilocale'=>array(
        'campo'=>'multilocale',
        'type'=>'radio',
        'options' => ['0','1'],
        'obbligatorio'=>'t',
        'default'=>'0',
        'etichetta'=>'gestione piu\' lingue',
    ),
    'attivo'=>array(
        'campo'=>'attivo',
        'type'=>'radio',
        'options' => ['0','1'],
        'obbligatorio'=>'t',
        'default'=>'1',
        'etichetta'=>'attivo',
    ),
    'checklunghezza'=>array(
        'campo'=>'checklunghezza',
        'type'=>'radio',
        'options' => ['0','1'],
        'obbligatorio'=>'t',
        'default'=>'0',
        'etichetta'=>'controllo lunghezza',
    ),*/
    'campo' => array(
        'campo' => 'campo',
        'type' => 'text',
        'default' => '',
        'lunghezzamin' => 2,
        //'postfunction' => 'strtolower',
        'obbligatorio' => 't',
        'etichetta' => 'campo',
    ),
    'etichetta' => array(
        'campo' => 'etichetta',
        'type' => 'text',
        'default' => '',
        'lunghezzamin' => 2,
        //'prefunction' => 'strtolower',
        //'postfunction' => 'strtolower',
        'obbligatorio' => 't',
		'multilocale' => 't',
        'etichetta' => 'etichetta campo',
    ),
	'class' => array(
        'campo' => 'class',
        'type' => 'text',
        'default' => 'form-control',
        'obbligatorio' => 'f',
        'etichetta' => 'classi css',
    ),
    'tipo_textarea'=>array(
        'campo'=>'tipo_textarea',
        'type'=>'select',
        /*'origine_dati' => 'php',
        'function_php' => 'array_type',*/
        'options' => $array_typeTextArea,
        'obbligatorio'=>'f',
        'default'=>'0',
        'etichetta'=>'tipo'
    ),
    'tipo_data'=>array(
        'campo'=>'tipo_data',
        'type'=>'select',
        /*'origine_dati' => 'php',
        'function_php' => 'array_type',*/
        'options' => $array_typeData,
        'obbligatorio'=>'f',
        'default'=>'0',
        'etichetta'=>'tipo'
    ),
    'tipo_timestamp'=>array(
        'campo'=>'tipo_timestamp',
        'type'=>'select',
        /*'origine_dati' => 'php',
        'function_php' => 'array_type',*/
        'options' => $array_typeTimestamp,
        'obbligatorio'=>'f',
        'default'=>'0',
        'etichetta'=>'tipo'
    ),
    'tipo_file'=>array(
        'campo'=>'tipo_file',
        'type'=>'select',
        /*'origine_dati' => 'php',
        'function_php' => 'array_type',*/
        'options' => $array_typeFile,
        'obbligatorio'=>'f',
        'default'=>'0',
        'etichetta'=>'tipo'
    ),
	'options'=>array(
        'campo'=>'options',
        'type'=>'textarea',
        /*'origine_dati' => 'php',
        'function_php' => 'array_type',*/
        'obbligatorio'=>'f',
        'default'=>'0',
        'etichetta'=>'opzioni',
		'multilocale' => 't'
    ),
    'tipo_time'=>array(
        'campo'=>'tipo_time',
        'type'=>'select',
        /*'origine_dati' => 'php',
        'function_php' => 'array_type',*/
        'options' => $array_typeTime,
        'obbligatorio'=>'f',
        'default'=>'0',
        'etichetta'=>'tipo'
    ),
    
    /*'gettext'=>array(
        'campo'=>'gettext',
        'type'=>'radio',
        'options' => ['0','1'],
        'obbligatorio'=>'t',
        'default'=>'0',
        'etichetta'=>'gettext',
    ),*/
    'default_value' => array(
        'campo' => 'default_value',
        'type' => 'text',
        'default' => '',
        'obbligatorio' => 'f',
        'etichetta' => 'valore di default',
    ),
    'lunghezzamin' => array(
        'campo' => 'lunghezzamin',
        'type' => 'text',
        'default' => '',
        'tipo' => 'Integer',
        'obbligatorio' => 'f',
        'etichetta' => 'lunghezza minima valore',
    ),
    'lunghezzamax' => array(
        'campo' => 'lunghezzamax',
        'type' => 'text',
        'default' => '',
        'tipo' => 'Integer',
        'obbligatorio' => 'f',
        'etichetta' => 'lunghezza massima valore',
    ),
    /* 'unique_value'=>array(
        'campo'=>'unique_value',
        'type'=>'radio',
        'options' => ['0','1'],
        'obbligatorio'=>'t',
        'default'=>'0',
        'etichetta'=>'valore unico select',
    ),*/
     /*'estensioni_consentite'=>array(
        'campo'=>'estensioni_consentite',
        'type'=>'checkbox',
        'options'=>$mime_exts,
        'default'=>'', //i campi selezionati default vengono messi in un array
        'obbligatorio'=>'t',
        'postfunction' => 'serialize',
        //'prefunction' => 'unserialize',
        'etichetta'=>'tipi di file consentiti',
    ),*/
    'ext_image'=>array(
        'campo'=>'ext_image',
        'type'=>'checkbox',
        'options'=>array('gif','png','jpeg','jpg'),
        'default'=>array('gif','png','jpeg','jpg'), //i campi selezionati default vengono messi in un array
        'obbligatorio'=>'f',
        'postfunction' => 'serialize',
        'prefunction' => 'unserialize',
        'etichetta'=>'estensioni immagini',
    ),
	'resize_image'=>array(
        'campo'=>'resize_image',
        'type' => 'multiselect',
        'options'=> $array_resize_image,
        'default'=>$array_resize_image, //i campi selezionati default vengono messi in un array
        'obbligatorio'=>'f',
        'postfunction' => 'serialize',
        'prefunction' => 'unserialize',
        'etichetta'=>'resize immagini',
    ),
    'ext_attach'=>array(
        'campo'=>'ext_attach',
        'type'=>'checkbox',
        'options'=>array('gif','png','jpeg','jpg','zip','tar','doc','docx','xls','txt','rar','csv','pdf'),
        'default'=>array('gif','png','jpeg','jpg','zip','tar','doc','docx','xls','txt','rar','csv','pdf'), //i campi selezionati default vengono messi in un array
        'obbligatorio'=>'f',
        'postfunction' => 'serialize',
        'prefunction' => 'unserialize',
        'etichetta'=>'estensioni allegati',
    ),
     'number_files' => array(
        'campo' => 'number_files',
        'type' => 'text',
        'default' => '0',
        'tipo' => 'Integer',
        'obbligatorio' => 'f',
        'etichetta' => 'numer massimo di file',
    ),
	 'post_function' => array(
        'campo' => 'post_function',
        'type' => 'text',
        'default' => '',
        'postfunction' => 'strtolower',
        'obbligatorio' => 'f',
        'etichetta' => 'funzioni post function',
    ),
	 'pre_function' => array(
        'campo' => 'pre_function',
        'type' => 'text',
        'default' => '',
        'postfunction' => 'strtolower',
        'obbligatorio' => 'f',
        'etichetta' => 'funzioni pre function',
    ),
     'ifisnull'=>array(
        'campo'=>'ifisnull',
        'type'=>'radio',
        'options' => ['0','1','2'],
        'obbligatorio'=>'f',
        'default'=>'0',
        'etichetta'=>'azione se il campo è nullo',
    ),
	'dimension_resize_default'=>array(
        'campo'=>'dimension_resize_default',
        'type'=>'radio',
        'options' => ['0','1'],
        'obbligatorio'=>'f',
        'default'=>'0',
        'etichetta'=>'dimensioni resize immagini default',
    ),
	'value_ifisnull' => array(
        'campo' => 'value_ifisnull',
        'type' => 'text',
        'default' => '',
        'obbligatorio' => 'f',
        'etichetta' => 'valore se il campo è nullo',
    ),
     'submit'=>array(
        'campo'=>'submit',
        'type'=>'submit',
        'default'=>'Salva',
    ),
);



?>