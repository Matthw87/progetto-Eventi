<?php
use Marion\Controllers\Elements\UrlButton;
use Marion\Controllers\ListAdminController;
use Marion\Core\Marion;
use Marion\Support\Form\FormHelper;
use Marion\Support\ListWrapper\DataSource;
use Marion\Support\ListWrapper\ListHelper;
use Eventi\EventStatus;


class StatusController extends ListAdminController{


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



        $dataSource = new DataSource('Biglietti');
        $dataSource->queryBuilder()
        ->join('Eventi', 'Biglietti.id_evento', '=', 'Eventi.id')
        ->select([
        'Biglietti.id',
        'Biglietti.nome prenotatore',
        'Biglietti.numero di biglietti',
        'Eventi.Event Name',
        'Eventi.Location',
        'Eventi.date'
        ]);

        $fields = [
            [
                'name' => 'ID',
                'field_value' => 'id',
            ],
            [
                'name' => 'Nome prenotatore',
                'field_value' => 'nome prenotatore',
            ],

            [
                'name' => 'Biglietti acquistati',
                'field_value' => 'numero di biglietti',
            ],
            
            [
                'name' => 'Evento',
                'field_value' => 'Event Name',
            ],
            [
                'name' => 'Location',
                'field_value' => 'Location',
            ],
            [
                'name' => 'Data',
                'field_value' => 'date',
            ],
            
        ];


        ListHelper::create('Biglietti',$this)
            ->enableBulkActions(false)
            ->addEditActionRowButton()
            ->addCopyActionRowButton()
            ->addDeleteActionRowButton()
            ->onDelete(function($id){
				if( $id ){
                    $obj = EventStatus::withId($id);
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


        $eventi_array = [
            '1' => 'Sum41',
            '2' => 'Ultimo',
            '3' => 'Ed Sheeran',
            '4' => 'Ariana Grande',
            '5' => 'Drake',
            '6' => 'Lady Gaga',
            '7' => 'Eminem',
            '8' => 'Katy Perry',
            '9' => 'Kendrick Lamar',
            '10' => 'Bruno Mars',
            '11' => 'Adele',
            '12' => 'Rihanna',
            '13' => 'Blink-182',
            '14' => 'Tony Tammaro',
            '15' => 'Imagine Dragons',
            '16' => 'Twenty One Pilots',
            '17' => 'Geolier',
            '18' => 'Green Day',
            '19' => 'Lazza',
            '20' => 'Sferaebbasta',
            '21' => 'Shiva',
            '22' => 'Tha Supreme',
            '23' => 'lil cdp',
            '24' => 'Trebiscott',
            '25' => 'The Weeknd',
            '26' => 'Marracash',
            '27' => 'Fedez',
            '28' => 'DPG',
            '29' => 'Shakira',
            '30' => 'Sabaku',
        ];


        $fields = [
            'id' => [
                'type' => 'hidden',
                'label' => 'Id'
            ],
            'name' => [
                'type' => 'text',
                'label' => 'Nome prenotatore',
                'validation' => 'required',

                
            ],

            'numero_di_biglietti' => [
 

                'type' => 'number',
                'label' => 'numero di biglietti',
                'validation' => ['required', 'integer'], 
            ],


            'Evento' => [
                'type' => 'select',
                'label' => 'Evento',
                'validation' => 'required',
                'options' => array_flip($eventi_array),
                ],

            ];

            
            

        
        $action = $this->getAction();
        

        //form
        FormHelper::create('Biglietti',$this)
            ->layoutFile(_MARION_MODULE_DIR_."Eventi/templates/admin/forms/event.xml")
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
                    $obj = EventStatus::withId($data['id']);
                }else{
                    $obj = EventStatus::create();
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