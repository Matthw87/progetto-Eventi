<?php
use Marion\Controllers\ListAdminController;
use Marion\Core\Marion;
use Marion\Entities\Cms\MenuItem;
use Marion\Entities\Permission;

use Marion\Support\ListWrapper\{DataSource, SortableListHelper,ListActionRowButton};
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Support\Form\FormHelper;
class MenuBackendController extends ListAdminController{
	public $_auth = 'superadmin';


    function setMedia(){
        parent::setMedia();
		if( $this->getAction() != 'list'){
            $this->registerJS('../modules/developer/js/menu_edit.js?v=5','end');
        }
	}

    /**
     * Dissplay list
     *
     * @return void
     */
    function displayList(): void{
        $this->setMenu('developer_menu_backend');

        $this->setTitle(_translate('menu.list.title','developer'));


        if( _var('updated') ){
			$this->displayMessage(_translate('menu_admin.messages.updated','developer'));
		}
		if( _var('added') ){
			$this->displayMessage(_translate('menu_admin.messages.added','developer'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('menu_admin.messages.deleted','developer'));
		}

		$dataSource = (new DataSource('menu_items'));
		$dataSource->queryBuilder()
			->leftJoin('menu_items_langs',"menu_items_langs.menu_item_id","=","menu_items.id")
			->where('menu_items_langs.locale',_MARION_LANG_)
            ->where('menu_items.scope','frontend')
            ->orderBy('menu_items.priority');
		$dataSource->addFields(['menu_items.id','menu_items.permission','menu_items_langs.name','menu_items.active','menu_items.parent']);
		
		SortableListHelper::create('catalogo_section',$this)
			->setDataSource($dataSource)
			->setMaxDepth(4)
			->setContent(function($item){
				$content = $item->name;
                $content .= " <small style='color:orange;'>{$item->permission}</small>";
                if( !$item->active ){
                    $content .= " <span class='label label-danger'>".strtoupper(_translate('menu_admin.offline','developer'))."</span>";
                }
				return $content;
			})->addActionRowButton(
				(new ListActionRowButton('edit'))
				->setIcon('fa fa-edit')
				->setText(_translate('list.edit'))
				->setUrl(function($item){
                    return 'index.php?ctrl=MenuBackend&mod=developer&action=edit&id='.$item->id;
				})
			)->addActionRowButton(
				(new ListActionRowButton('duplicate'))
				->setIcon('fa fa-copy')
				->setText(_translate('list.duplicate'))
				->setUrl(function($item){
                    return 'index.php?ctrl=MenuBackend&mod=developer&action=duplicate&id='.$item->id;
				})
			)->addActionRowButton(
				(new ListActionRowButton('delete'))
				->setConfirm(true)
				->setConfirmMessage(function($item){
					return _translate(['menu_admin.messages.confirm_delete_message',$item->name],'developer');
				})
				->setIcon('fa fa-trash-o')
				->setText(_translate('list.delete'))
			)->onAction(function($action,$id){
				switch($action){
					case 'delete':
						$this->delete($id);
						break;
				}
			})->onChange(function($ids){
                $order = 1;
				foreach($ids as $v){
                   
                    $update = [
						'parent' => null,
						'priority' => $order
					];
					$order++;
					DB::table('menu_items')
					->where("id", $v['id'])
					->update($update);
					if( isset($v['children']) ){
						if( okArray($v['children']) ){
							foreach( $v['children'] as  $v1 ){
								$update = [
									'parent' => $v['id'],
									'priority' => $order
								]; 
								$order++;
								DB::table('menu_items')
									->where("id", $v1['id'])
									->update($update);
								if( isset($v1['children']) ){
									foreach( $v1['children'] as  $v2 ){
										$update = [
											'parent' => $v1['id'],
											'priority' => $order
										]; 
										$order++;
										DB::table('menu_items')
											->where("id", $v2['id'])
											->update($update);
										if( isset($v2['children']) ){
											foreach( $v2['children'] as  $v3 ){
												$update = [
													'parent' => $v2['id'],
													'priority' => $order
												]; 
												$order++;
												DB::table('menu_items')
													->where("id", $v3['id'])
													->update($update);
											}
										}
									}
								}
							}
						}
					}
                    

                }
			})->display();
        
    }



    	/**
	 * Display form
	 *
	 * @return void
	 */
	function displayForm()
	{

		$this->setMenu('developer_menu_backend');
		$this->setTitle('Menu item');
	
		
		$fields = [
			'id' => [
				'type' => 'hidden'
			],
            'parent' => [
				'type' => 'select',
                'label' => _translate('menu_admin.form.fields.parent','developer'),
                'options' => function(){
                    $menu = MenuItem::prepareQuery()->whereExpression("(parent is NULL OR parent = 0)")
                        ->where('active', 1)->where('locale',_MARION_LANG_)
                        ->orderBy('name')->get();
                    $toreturn[0] = _translate('general.select..');
                    if( okArray($menu) ){
                        foreach( $menu as $v){
                            $toreturn[$v->id] = $v->get('name');
                        }
                    }
                    return $toreturn;
                }
			],
            'permission' => [
				'type' => 'select',
                'label' => _translate('menu_admin.form.fields.permission','developer'),
                'options' => function(){
                    $menu = Permission::prepareQuery()->where('active',1)->where('locale','it')->orderBy('name')->get();
        
                    $toreturn = [];
                    if( okArray($menu) ){
                        foreach( $menu as $v){
                            $toreturn[$v->label] = $v->get('name');
                        }
                    }
                    return $toreturn;
                }
			],
			'url' => [
				'type' => 'text',
                'label' => _translate('menu_admin.form.fields.url','developer'),
				'validation'=> 'required|max:200',
			],
            'name' => [
				'type' => 'text',
                'label' => _translate('menu_admin.form.fields.name','developer'),
				'validation'=> 'required|max:100',
                'multilang' => true
			],
            'tag' => [
				'type' => 'text',
                'label' => _translate('menu_admin.form.fields.tag','developer'),
				'validation'=> 'required|max:50',
			],
            'active' => [
				'type' => 'switch',
                'label' => _translate('menu_admin.form.fields.active','developer')
			],
            'target_blank' => [
				'type' => 'switch',
                'label' => _translate('menu_admin.form.fields.target_blank','developer')
			],
            'icon' => [
				'type' => 'select',
                'label' => _translate('menu_admin.form.fields.icon','developer'),
                'options' => function(){
                    return $this->select_icons();
                }
			],
            'icon_image' => [
				'type' => 'text',
                'label' => _translate('menu_admin.form.fields.icon_img','developer'),
				'validation'=> 'max:100',
			],
            'priority' => [
				'type' => 'text',
                'label' => _translate('menu_admin.form.fields.priority','developer'),
				'validation'=> 'required|integer',
			],
            'show_label' => [
				'type' => 'switch',
                'label' => _translate('menu_admin.form.fields.show_label','developer')
			],
            'label_static' => [
				'type' => 'switch',
                'label' => _translate('menu_admin.form.fields.label_static','developer')
			],
            'label_text' => [
				'type' => 'text',
                'label' => _translate('menu_admin.form.fields.label_text','developer')
			],
            'label_function' => [
				'type' => 'text',
                'label' => _translate('menu_admin.form.fields.label_function','developer')
			],
		];

		//prendo l'action
		$action = $this->getAction();


       
        $form = FormHelper::create('developer_menu_admin',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'developer/templates/admin/forms/menu.xml')
            ->init(function(FormHelper $form) use ($action){
				//controllo se il form è stato sottomesso

				if( !$form->isSubmitted() ){
					
					if($action != 'add'){
						$menu =  MenuItem::withId(_var('id'));
						if($menu ){
                            $data = $menu->getDataForm();
                            if( $action == 'duplicate' ){
                                unset($data['id']);
                            }
                            if( $data['show_label'] ){
                                
                                if( $data['label_text'] ){
                                    $data['label_static'] = 1;
                                }else{
                                    $data['label_static'] = 0;
                                }
                            } 
                            
							$form->formData->data = $data;
						}

					}
				}else{
                    $data = $form->getSubmittedData();
                    if( isset($data['show_label']) && $data['show_label'] ){
                        if( isset($data['label_static']) && $data['label_static'] ){
                            $form->fields['label_text']['validation'] = 'required';
                        }else{
                            $form->fields['label_function']['validation'] = 'required';
                        }
                    }
                }
            })->process(function(FormHelper $form) use ($action){
                $data = $form->getValidatedData();
				$data['scope'] = 'frontend';


                if( $data['show_label'] ){
                    
                    if( $data['label_static'] ){
                        $data['label_type'] = 'static';
                        $data['label_function'] = null;
                    }else{
                        $data['label_type'] = 'dynamic';
                        $data['label_text'] = null;
                    }
                }else{
                    $data['label_text'] = null;
                    $data['label_function'] = null;
                }


                if( !$data['parent'] ) unset($data['parent']);
				$params = [];
                if( $action == 'edit' ){
					$params['updated'] = 1;
					$obj = MenuItem::withId($data['id']);
				}else{
					$params['added'] = 1;
					$obj = MenuItem::create();
				}
                $obj->set($data);
                $obj->save();
				if( $form->ctrl instanceof ListAdminController ){
					$form->ctrl->redirectTolist($params);
				}
			

            })->setFields($fields);

        $form->display();
	}

    /**
     * delete menu item
     *
     * @param integer $id
     * @return void
     */
    private function delete(int $id): void{
        $obj = MenuItem::withId($id);

		
        if(is_object($obj)){
            $obj->delete();
        }
        $this->displayMessage(_translate('menu_admin.messages.deleted','developer'));
        
        
    }    

    /**
     * options for icon select
     *
     * @return array
     */
    private function select_icons(): array{
        $icons = [];
        $icon[] = 'fa-glass';
        $icon[] = 'fa-music';
        $icon[] = 'fa-search';
        $icon[] = 'fa-envelope-o';
        $icon[] = 'fa-heart';
        $icon[] = 'fa-star';
        $icon[] = 'fa-star-o';
        $icon[] = 'fa-user';
        $icon[] = 'fa-film';
        $icon[] = 'fa-th-large';
        $icon[] = 'fa-th';
        $icon[] = 'fa-th-list';
        $icon[] = 'fa-check';
        $icon[] = 'fa-times';
        $icon[] = 'fa-search-plus';
        $icon[] = 'fa-search-minus';
        $icon[] = 'fa-power-off';
        $icon[] = 'fa-signal';
        $icon[] = 'fa-cog';
        $icon[] = 'fa-trash-o';
        $icon[] = 'fa-home';
        $icon[] = 'fa-file-o';
        $icon[] = 'fa-clock-o';
        $icon[] = 'fa-road';
        $icon[] = 'fa-download';
        $icon[] = 'fa-arrow-circle-o-down';
        $icon[] = 'fa-arrow-circle-o-up';
        $icon[] = 'fa-inbox';
        $icon[] = 'fa-play-circle-o';
        $icon[] = 'fa-repeat';
        $icon[] = 'fa-refresh';
        $icon[] = 'fa-list-alt';
        $icon[] = 'fa-lock';
        $icon[] = 'fa-flag';
        $icon[] = 'fa-headphones';
        $icon[] = 'fa-volume-off';
        $icon[] = 'fa-volume-down';
        $icon[] = 'fa-volume-up';
        $icon[] = 'fa-qrcode';
        $icon[] = 'fa-barcode';
        $icon[] = 'fa-tag';
        $icon[] = 'fa-tags';
        $icon[] = 'fa-book';
        $icon[] = 'fa-bookmark';
        $icon[] = 'fa-print';
        $icon[] = 'fa-camera';
        $icon[] = 'fa-font';
        $icon[] = 'fa-bold';
        $icon[] = 'fa-italic';
        $icon[] = 'fa-text-height';
        $icon[] = 'fa-text-width';
        $icon[] = 'fa-align-left';
        $icon[] = 'fa-align-center';
        $icon[] = 'fa-align-right';
        $icon[] = 'fa-align-justify';
        $icon[] = 'fa-list';
        $icon[] = 'fa-outdent';
        $icon[] = 'fa-indent';
        $icon[] = 'fa-video-camera';
        $icon[] = 'fa-picture-o';
        $icon[] = 'fa-pencil';
        $icon[] = 'fa-map-marker';
        $icon[] = 'fa-adjust';
        $icon[] = 'fa-tint';
        $icon[] = 'fa-pencil-square-o';
        $icon[] = 'fa-share-square-o';
        $icon[] = 'fa-check-square-o';
        $icon[] = 'fa-arrows';
        $icon[] = 'fa-step-backward';
        $icon[] = 'fa-fast-backward';
        $icon[] = 'fa-backward';
        $icon[] = 'fa-play';
        $icon[] = 'fa-pause';
        $icon[] = 'fa-stop';
        $icon[] = 'fa-forward';
        $icon[] = 'fa-fast-forward';
        $icon[] = 'fa-step-forward';
        $icon[] = 'fa-eject';
        $icon[] = 'fa-chevron-left';
        $icon[] = 'fa-chevron-right';
        $icon[] = 'fa-plus-circle';
        $icon[] = 'fa-minus-circle';
        $icon[] = 'fa-times-circle';
        $icon[] = 'fa-check-circle';
        $icon[] = 'fa-question-circle';
        $icon[] = 'fa-info-circle';
        $icon[] = 'fa-crosshairs';
        $icon[] = 'fa-times-circle-o';
        $icon[] = 'fa-check-circle-o';
        $icon[] = 'fa-ban';
        $icon[] = 'fa-arrow-left';
        $icon[] = 'fa-arrow-right';
        $icon[] = 'fa-arrow-up';
        $icon[] = 'fa-arrow-down';
        $icon[] = 'fa-share';
        $icon[] = 'fa-expand';
        $icon[] = 'fa-compress';
        $icon[] = 'fa-plus';
        $icon[] = 'fa-minus';
        $icon[] = 'fa-asterisk';
        $icon[] = 'fa-exclamation-circle';
        $icon[] = 'fa-gift';
        $icon[] = 'fa-leaf';
        $icon[] = 'fa-fire';
        $icon[] = 'fa-eye';
        $icon[] = 'fa-eye-slash';
        $icon[] = 'fa-exclamation-triangle';
        $icon[] = 'fa-plane';
        $icon[] = 'fa-calendar';
        $icon[] = 'fa-random';
        $icon[] = 'fa-comment';
        $icon[] = 'fa-magnet';
        $icon[] = 'fa-chevron-up';
        $icon[] = 'fa-chevron-down';
        $icon[] = 'fa-retweet';
        $icon[] = 'fa-shopping-cart';
        $icon[] = 'fa-folder';
        $icon[] = 'fa-folder-open';
        $icon[] = 'fa-arrows-v';
        $icon[] = 'fa-arrows-h';
        $icon[] = 'fa-bar-chart-o';
        $icon[] = 'fa-twitter-square';
        $icon[] = 'fa-facebook-square';
        $icon[] = 'fa-camera-retro';
        $icon[] = 'fa-key';
        $icon[] = 'fa-cogs';
        $icon[] = 'fa-comments';
        $icon[] = 'fa-thumbs-o-up';
        $icon[] = 'fa-thumbs-o-down';
        $icon[] = 'fa-star-half';
        $icon[] = 'fa-heart-o';
        $icon[] = 'fa-sign-out';
        $icon[] = 'fa-linkedin-square';
        $icon[] = 'fa-thumb-tack';
        $icon[] = 'fa-external-link';
        $icon[] = 'fa-sign-in';
        $icon[] = 'fa-trophy';
        $icon[] = 'fa-github-square';
        $icon[] = 'fa-upload';
        $icon[] = 'fa-lemon-o';
        $icon[] = 'fa-phone';
        $icon[] = 'fa-square-o';
        $icon[] = 'fa-bookmark-o';
        $icon[] = 'fa-phone-square';
        $icon[] = 'fa-twitter';
        $icon[] = 'fa-facebook';
        $icon[] = 'fa-github';
        $icon[] = 'fa-unlock';
        $icon[] = 'fa-credit-card';
        $icon[] = 'fa-rss';
        $icon[] = 'fa-hdd-o';
        $icon[] = 'fa-bullhorn';
        $icon[] = 'fa-bell';
        $icon[] = 'fa-certificate';
        $icon[] = 'fa-hand-o-right';
        $icon[] = 'fa-hand-o-left';
        $icon[] = 'fa-hand-o-up';
        $icon[] = 'fa-hand-o-down';
        $icon[] = 'fa-arrow-circle-left';
        $icon[] = 'fa-arrow-circle-right';
        $icon[] = 'fa-arrow-circle-up';
        $icon[] = 'fa-arrow-circle-down';
        $icon[] = 'fa-globe';
        $icon[] = 'fa-wrench';
        $icon[] = 'fa-tasks';
        $icon[] = 'fa-filter';
        $icon[] = 'fa-briefcase';
        $icon[] = 'fa-arrows-alt';
        $icon[] = 'fa-users';
        $icon[] = 'fa-link';
        $icon[] = 'fa-cloud';
        $icon[] = 'fa-flask';
        $icon[] = 'fa-scissors';
        $icon[] = 'fa-files-o';
        $icon[] = 'fa-paperclip';
        $icon[] = 'fa-floppy-o';
        $icon[] = 'fa-square';
        $icon[] = 'fa-bars';
        $icon[] = 'fa-list-ul';
        $icon[] = 'fa-list-ol';
        $icon[] = 'fa-strikethrough';
        $icon[] = 'fa-underline';
        $icon[] = 'fa-table';
        $icon[] = 'fa-magic';
        $icon[] = 'fa-truck';
        $icon[] = 'fa-pinterest';
        $icon[] = 'fa-pinterest-square';
        $icon[] = 'fa-google-plus-square';
        $icon[] = 'fa-google-plus';
        $icon[] = 'fa-money';
        $icon[] = 'fa-caret-down';
        $icon[] = 'fa-caret-up';
        $icon[] = 'fa-caret-left';
        $icon[] = 'fa-caret-right';
        $icon[] = 'fa-columns';
        $icon[] = 'fa-sort';
        $icon[] = 'fa-sort-asc';
        $icon[] = 'fa-sort-desc';
        $icon[] = 'fa-envelope';
        $icon[] = 'fa-linkedin';
        $icon[] = 'fa-undo';
        $icon[] = 'fa-gavel';
        $icon[] = 'fa-tachometer';
        $icon[] = 'fa-comment-o';
        $icon[] = 'fa-comments-o';
        $icon[] = 'fa-bolt';
        $icon[] = 'fa-sitemap';
        $icon[] = 'fa-umbrella';
        $icon[] = 'fa-clipboard';
        $icon[] = 'fa-lightbulb-o';
        $icon[] = 'fa-exchange';
        $icon[] = 'fa-cloud-download';
        $icon[] = 'fa-cloud-upload';
        $icon[] = 'fa-user-md';
        $icon[] = 'fa-stethoscope';
        $icon[] = 'fa-suitcase';
        $icon[] = 'fa-bell-o';
        $icon[] = 'fa-coffee';
        $icon[] = 'fa-cutlery';
        $icon[] = 'fa-file-text-o';
        $icon[] = 'fa-building-o';
        $icon[] = 'fa-hospital-o';
        $icon[] = 'fa-ambulance';
        $icon[] = 'fa-medkit';
        $icon[] = 'fa-fighter-jet';
        $icon[] = 'fa-beer';
        $icon[] = 'fa-h-square';
        $icon[] = 'fa-plus-square';
        $icon[] = 'fa-angle-double-left';
        $icon[] = 'fa-angle-double-right';
        $icon[] = 'fa-angle-double-up';
        $icon[] = 'fa-angle-double-down';
        $icon[] = 'fa-angle-left';
        $icon[] = 'fa-angle-right';
        $icon[] = 'fa-angle-up';
        $icon[] = 'fa-angle-down';
        $icon[] = 'fa-desktop';
        $icon[] = 'fa-laptop';
        $icon[] = 'fa-tablet';
        $icon[] = 'fa-mobile';
        $icon[] = 'fa-circle-o';
        $icon[] = 'fa-quote-left';
        $icon[] = 'fa-quote-right';
        $icon[] = 'fa-spinner';
        $icon[] = 'fa-circle';
        $icon[] = 'fa-reply';
        $icon[] = 'fa-github-alt';
        $icon[] = 'fa-folder-o';
        $icon[] = 'fa-folder-open-o';
        $icon[] = 'fa-smile-o';
        $icon[] = 'fa-frown-o';
        $icon[] = 'fa-meh-o';
        $icon[] = 'fa-gamepad';
        $icon[] = 'fa-keyboard-o';
        $icon[] = 'fa-flag-o';
        $icon[] = 'fa-flag-checkered';
        $icon[] = 'fa-terminal';
        $icon[] = 'fa-code';
        $icon[] = 'fa-reply-all';
        $icon[] = 'fa-mail-reply-all';
        $icon[] = 'fa-star-half-o';
        $icon[] = 'fa-location-arrow';
        $icon[] = 'fa-crop';
        $icon[] = 'fa-code-fork';
        $icon[] = 'fa-chain-broken';
        $icon[] = 'fa-question';
        $icon[] = 'fa-info';
        $icon[] = 'fa-exclamation';
        $icon[] = 'fa-superscript';
        $icon[] = 'fa-subscript';
        $icon[] = 'fa-eraser';
        $icon[] = 'fa-puzzle-piece';
        $icon[] = 'fa-microphone';
        $icon[] = 'fa-microphone-slash';
        $icon[] = 'fa-shield';
        $icon[] = 'fa-calendar-o';
        $icon[] = 'fa-fire-extinguisher';
        $icon[] = 'fa-rocket';
        $icon[] = 'fa-maxcdn';
        $icon[] = 'fa-chevron-circle-left';
        $icon[] = 'fa-chevron-circle-right';
        $icon[] = 'fa-chevron-circle-up';
        $icon[] = 'fa-chevron-circle-down';
        $icon[] = 'fa-html5';
        $icon[] = 'fa-css3';
        $icon[] = 'fa-anchor';
        $icon[] = 'fa-unlock-alt';
        $icon[] = 'fa-bullseye';
        $icon[] = 'fa-ellipsis-h';
        $icon[] = 'fa-ellipsis-v';
        $icon[] = 'fa-rss-square';
        $icon[] = 'fa-play-circle';
        $icon[] = 'fa-ticket';
        $icon[] = 'fa-minus-square';
        $icon[] = 'fa-minus-square-o';
        $icon[] = 'fa-level-up';
        $icon[] = 'fa-level-down';
        $icon[] = 'fa-check-square';
        $icon[] = 'fa-pencil-square';
        $icon[] = 'fa-external-link-square';
        $icon[] = 'fa-share-square';
        $icon[] = 'fa-compass';
        $icon[] = 'fa-caret-square-o-down';
        $icon[] = 'fa-caret-square-o-up';
        $icon[] = 'fa-caret-square-o-right';
        $icon[] = 'fa-eur';
        $icon[] = 'fa-gbp';
        $icon[] = 'fa-usd';
        $icon[] = 'fa-inr';
        $icon[] = 'fa-jpy';
        $icon[] = 'fa-rub';
        $icon[] = 'fa-krw';
        $icon[] = 'fa-btc';
        $icon[] = 'fa-file';
        $icon[] = 'fa-file-text';
        $icon[] = 'fa-sort-alpha-asc';
        $icon[] = 'fa-sort-alpha-desc';
        $icon[] = 'fa-sort-amount-asc';
        $icon[] = 'fa-sort-amount-desc';
        $icon[] = 'fa-sort-numeric-asc';
        $icon[] = 'fa-sort-numeric-desc';
        $icon[] = 'fa-thumbs-up';
        $icon[] = 'fa-thumbs-down';
        $icon[] = 'fa-youtube-square';
        $icon[] = 'fa-youtube';
        $icon[] = 'fa-xing';
        $icon[] = 'fa-xing-square';
        $icon[] = 'fa-youtube-play';
        $icon[] = 'fa-dropbox';
        $icon[] = 'fa-stack-overflow';
        $icon[] = 'fa-instagram';
        $icon[] = 'fa-flickr';
        $icon[] = 'fa-adn';
        $icon[] = 'fa-bitbucket';
        $icon[] = 'fa-bitbucket-square';
        $icon[] = 'fa-tumblr';
        $icon[] = 'fa-tumblr-square';
        $icon[] = 'fa-long-arrow-down';
        $icon[] = 'fa-long-arrow-up';
        $icon[] = 'fa-long-arrow-left';
        $icon[] = 'fa-long-arrow-right';
        $icon[] = 'fa-apple';
        $icon[] = 'fa-windows';
        $icon[] = 'fa-android';
        $icon[] = 'fa-linux';
        $icon[] = 'fa-dribbble';
        $icon[] = 'fa-skype';
        $icon[] = 'fa-foursquare';
        $icon[] = 'fa-trello';
        $icon[] = 'fa-female';
        $icon[] = 'fa-male';
        $icon[] = 'fa-gittip';
        $icon[] = 'fa-sun-o';
        $icon[] = 'fa-moon-o';
        $icon[] = 'fa-archive';
        $icon[] = 'fa-bug';
        $icon[] = 'fa-vk';
        $icon[] = 'fa-weibo';
        $icon[] = 'fa-renren';
        $icon[] = 'fa-pagelines';
        $icon[] = 'fa-stack-exchange';
        $icon[] = 'fa-arrow-circle-o-right';
        $icon[] = 'fa-arrow-circle-o-left';
        $icon[] = 'fa-caret-square-o-left';
        $icon[] = 'fa-dot-circle-o';
        $icon[] = 'fa-wheelchair';
        $icon[] = 'fa-vimeo-square';
        $icon[] = 'fa-try';
        $icon[] = 'fa-plus-square-o';
        $icon[] = 'shopping';
    
        $toreturn[0] = _translate('general.select..');
        foreach($icon as $v){
            if( $v == 'shopping'){
                $toreturn["glyph-icon flaticon-shopping80"] = $v;
            }else{
                $toreturn["fa ".$v] = $v;
            }
        }
        return $toreturn;
    }
    
    
}