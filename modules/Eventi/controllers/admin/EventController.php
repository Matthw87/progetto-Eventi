<?php

use Eventi\Event;
use Eventi\Ticket;
use Marion\Controllers\ListAdminController;
use Marion\Support\Form\FormHelper;
use Marion\Support\ListWrapper\DataSource;
use Marion\Support\ListWrapper\ListHelper;

class EventController extends ListAdminController{


    function displayList()
    {
        //codice lista
        $this->setTitle('Eventi');
        $this->setMenu('eventi_events');


        if( _var('created') ){
            $this->displayMessage('Ecenti creato con successo');
        }

        if( _var('updated') ){
            $this->displayMessage('Evento modificato con successo');
        }



        $dataSource = new DataSource('events');
        $dataSource->queryBuilder()
            ->leftJoin('event_langs','event_langs.event_id','=','events.id')
            ->select([
            'events.id',
            'events.date',
            'event_langs.name',
            'event_langs.location',
        ]);

        $fields = [
            [
                'name' => 'ID',
                'field_value' => 'id',
            ],
            [
                'name' => 'Nome evento',
                'field_value' => 'name',
            ],

            [
                'name' => 'Location',
                'field_value' => 'location',
            ],
            [
                'name' => 'Data',
                'field_value' => 'date',
            ]
            
        ];


        ListHelper::create('Biglietti',$this)
            ->enableBulkActions(false)
            ->addEditActionRowButton()
            ->addCopyActionRowButton()
            ->addDeleteActionRowButton()
            ->onDelete(function($id){
				if( $id ){
                    $obj = Event::withId($id);
                    if( $obj ){
                        $this->displayMessage('Evento eliminato con successo');
                        $obj->delete();
                    }
                }
			})
            ->setFieldsFromArray($fields)
            ->setDataSource($dataSource)
            ->display();
    }


    

    function displayForm()
    {
        //tirolo della pagina
        $this->setTitle('Evento');

        //impostare la voce di menu selezionata
        $this->setMenu('eventi_events');


        $fields = [
            'id' => [
                'type' => 'hidden',
                'label' => 'Id'
            ],
            'name' => [
                'type' => 'text',
                'label' => 'Nome evento',
                'validation' => 'required',
                'multilang' => true
            ],
            'description' => [
                'type' => 'textarea',
                'label' => 'Descrizione evento',
                'validation' => 'required',
                'multilang' => true
            ],
            'location' => [
                'type' => 'text',
                'label' => 'Location evento',
                'validation' => 'required',
                'multilang' => true
            ],
            'date' => [
                'type' => 'date',
                'label' => 'Data evento',
                'validation' => 'required',
            ],
        ];
        
        $action = $this->getAction();
        

        //form
        FormHelper::create('ticket',$this)
            ->layoutFile(_MARION_MODULE_DIR_."eventi/templates/admin/forms/event.xml")
            ->setFields($fields)
            ->init(function( FormHelper $form) use ($action){
                if( $action == 'edit' ){
                    if( !$form->isSubmitted() ){
                        $id = _var('id');
                        $obj = $id;
                        if( $obj ){
                            $data = $obj->getDataForm();
                            $form->formData->data = $data;
                        }
                    }
                }
            })
            ->process( function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();

                if( $action == 'edit' ){
                    $obj = Event::withId($data['id']);
                }else{
                    $obj = Event::create();
                }

                $obj->set($data);
                $obj->save();

                if( $action == 'edit' ){
                    $this->redirectToList(['updated' => 1]);
                }else{
                    $this->redirectToList(['created' => 1]);
                }
                

            })->display();
    }
}