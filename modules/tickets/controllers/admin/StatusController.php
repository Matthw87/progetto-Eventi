<?php

use Marion\Controllers\ListAdminController;
use Marion\Support\Form\FormHelper;
use Marion\Support\ListWrapper\DataSource;
use Marion\Support\ListWrapper\ListHelper;
use Tickets\TicketStatus;

class StatusController extends ListAdminController{
    
    function displayList()
    {
        //codice lista
        $this->setTitle('Ticket status');
        $this->setMenu('tickets_status');


        if( _var('created') ){
            $this->displayMessage('Stato creato cin successo');
        }

        if( _var('updated') ){
            $this->displayMessage('Stato aggiornato cin successo');
        }



        $dataSource = new DataSource('ticket_status');
        $dataSource->queryBuilder()
            ->leftJoin('ticket_status_langs', 'ticket_status.id', '=', 'ticket_status_langs.ticket_status_id')
            ->where('lang', _MARION_LANG_)
            ->select(['ticket_status.id', 'ticket_status.active', 'ticket_status_langs.name']);

        $fields = [
            [
                'name' => 'ID',
                'field_value' => 'id',
            ],
            [
                'name' => 'Attivo?',
                'field_value' => 'active',
                'function_type' => 'value',
                'function' => function($value){
                    if( $value ){
                        return "SI";
                    }
                    return 'NO';
                }
            ],
            [
                'name' => 'Nome',
                'field_value' => 'name'
            ],
        ];


        ListHelper::create('tickets_status',$this)
            ->enableBulkActions(false)
            ->addEditActionRowButton()
            ->addCopyActionRowButton()
            ->addDeleteActionRowButton()
            ->onDelete(function($id){
				if( $id ){
                    $obj = TicketStatus::withId($id);
                    if( $obj ){
                        $this->displayMessage('Stato eliminato con successo');
                        $obj->delete();
                    }
                }
			})
            ->setFieldsFromArray($fields)
            ->setDataSource($dataSource)
            ->display();
    }


    function displayContent()
    {
        //codice lista
        $action = $this->getAction();
        switch( $action ){
            case 'ciao':
                echo "azione di ciao";
                break;
            default:
                echo "altri contenuti";
                break;
        }
        
    }

    function displayForm()
    {
        //tirolo della pagina
        $this->setTitle('Ticket status');

        //impostare la voce di menu selezionata
        $this->setMenu('tickets_status');


        $fields = [
            'id' => [
                'type' => 'hidden',
                'label' => 'Id stato'
            ],
            'name' => [
                'type' => 'text',
                'label' => 'Nome',
                'multilang' => 1,
                'validation' => ['required']
            ],
            'description' => [
                'type' => 'textarea',
                'label' => 'Descrizione',
                'multilang' => 1,
                'validation' => ['required']
            ],
            'active' => [
                'type' => 'switch',
                'label' => 'Attvo?'
            ],

        ];

        
        $action = $this->getAction();
        

        //form
        FormHelper::create('tickets_status',$this)
            ->layoutFile(_MARION_MODULE_DIR_."tickets/templates/admin/forms/ticket_status.xml")
            ->setFields($fields)
            ->init(function( FormHelper $form) use ($action){
                if( $action == 'edit' ){
                    if( !$form->isSubmitted() ){
                        $id = _var('id');
                        $obj = TicketStatus::withId($id);
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
                    $obj = TicketStatus::withId($data['id']);
                }else{
                    $obj = TicketStatus::create();
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