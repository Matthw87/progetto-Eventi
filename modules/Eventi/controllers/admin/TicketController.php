<?php

use Eventi\Event;
use Eventi\Ticket;
use Marion\Controllers\ListAdminController;
use Marion\Support\Form\FormHelper;
use Marion\Support\ListWrapper\DataSource;
use Marion\Support\ListWrapper\ListHelper;

class TicketController extends ListAdminController{


    function displayList()
    {
        //codice lista
        $this->setTitle('Acquista qui il tuo biglietto');
        $this->setMenu('event_status');


        if( _var('created') ){
            $this->displayMessage('Acquistato con successo');
        }

        if( _var('updated') ){
            $this->displayMessage('Biglietto modificato con successo');
        }



        $dataSource = new DataSource('tickets');
        $dataSource->queryBuilder()->select([
            'tickets.id',
            'tickets.user',
            'tickets.ticket_numbers'
        ]);

        $fields = [
            [
                'name' => 'ID',
                'field_value' => 'id',
            ],
            [
                'name' => 'Nome prenotatore',
                'field_value' => 'user',
            ],

            [
                'name' => 'Biglietti acquistati',
                'field_value' => 'ticket_numbers',
            ]
            
        ];


        ListHelper::create('Biglietti',$this)
            ->enableBulkActions(false)
            ->addEditActionRowButton()
            ->addCopyActionRowButton()
            ->addDeleteActionRowButton()
            ->onDelete(function($id){
				if( $id ){
                    $obj = Ticket::withId($id);
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


    

    function displayForm()
    {
        //tirolo della pagina
        $this->setTitle('Aggiungi biglietto');

        //impostare la voce di menu selezionata
        $this->setMenu('event_status');


        $eventi = Event::prepareQuery()->get();
        $eventi_select = [];
        foreach($eventi as $e){
            $eventi_select[$e->id] = $e->get('name');
        }


        $fields = [
            'id' => [
                'type' => 'hidden',
                'label' => 'Id'
            ],
            'user' => [
                'type' => 'text',
                'label' => 'Intestatario',
                'validation' => 'required',
            ],

            'ticket_numbers' => [
                'type' => 'number',
                'label' => 'numero di biglietti',
                'validation' => ['required', 'integer'], 
            ],
            'event_id' => [
                'type' => 'select',
                'label' => 'Evento',
                'options' => $eventi_select,
                'validation' => ['required', 'integer'], 
            ]
        ];
        
        $action = $this->getAction();
        

        //form
        FormHelper::create('ticket',$this)
            ->layoutFile(_MARION_MODULE_DIR_."eventi/templates/admin/forms/ticket.xml")
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
                    $obj = Ticket::withId($data['id']);
                }else{
                    $obj = Ticket::create();
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