<?php
use Catalogo\{Category, Product};
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Pagination\Paginator;
use Marion\Traits\ApiResponse;

class ApiController extends \Api\Controllers\Controller{
    use ApiResponse;

    
    function init($options = array())
    {
        parent::init($options);
        Paginator::currentPathResolver(function(){
            $url = $_SERVER['REQUEST_URI'];
            if( preg_match('/\?page=([0-9+])/',$url) ){
                $url = preg_replace('/\?page=([0-9+])/','',$url);
            }
            if( preg_match('/\&page=([0-9+])/',$url) ){
                $url = preg_replace('/\&page=([0-9+])/','',$url);
            }
            return $url;
        });
    }

    /**
     * @OA\Get(
     *   path="/api/v1/catalog/categories",
     *   summary="Get all categories",
     *   security={{"apiKey": {}}, { "bearerAuth":{} }},
     *   tags={"Catalogo"},
     *   @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Parametro in ISO 639-1 <https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes>",
     *         @OA\Schema(
     *             type="string",
     *             default="it"
     *         ),
     *   ),
     *   @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="page of pagination",
     *         @OA\Schema(
     *             type="number"
     *         )
     *   ),
     *   @OA\Parameter(
     *         name="size",
     *         in="query",
     *         description="size of pagination. (If size -1 return all items)",
     *         @OA\Schema(
     *             type="number"
     *         )
     *   ),
     *   tags={"Catalogo"},
     *   @OA\Response(
     *     response=200,
     *     description="success",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="code",
     *                         type="integer",
     *                         description="The response code"
     *                     ),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         description="The response data",
     *                         @OA\Property(
     *                              property="data",
     *                              description="Items",
     *                              type="array",
     *                              @OA\Items
     *                          ),
     *                     ),
     *                     example={
     *                         "code": 200,
     *                         "data": {
     *                              "current_page": 1,
     *                              "last_page": 1,
     *                              "total": 10,
     *                              "per_page": 15,
     *                              "data": {
     *                                  {
     *                                      "id": 1,
     *                                      "parent_id": 0,
     *                                      "name": "Scarpe",
     *                                      "online": 1,
     *                                      "order_view": 10,
     *                                  },
     *                                  {
     *                                      "id": 2,
     *                                      "parent_id": 1,
     *                                      "name": "Classiche",
     *                                      "online": 1,
     *                                      "order_view": 11,
     *                                  }
     *                              }
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *  )
     * )
     */
   
    function getCategories(){
        $page = _var('page');
        if( !$page ) $page = 1;
        $size = _var('size');
        if( !$size ) $size = 15;

       
        $query = DB::table('product_categories')
            ->leftJoin('product_category_langs','product_category_langs.product_category_id','=','product_categories.id')
            ->where('product_category_langs.lang',_MARION_LANG_);

        if( $size == -1){
            $tot = $query->count('product_categories.id');
            $size = $tot;
        }
        $categories = $query->paginate($size,[
                'product_categories.id',
                'product_categories.parent_id',
                'product_category_langs.name',
                'product_categories.online',
                'product_categories.order_view',
            ],'page',$page);
        $this->successResponse($categories);
    }

     /**
     * @OA\Get(
     *   path="/api/v1/catalog/categories/{id}",
     *   summary="Get category details",
     *   security={{"apiKey": {}}, { "bearerAuth":{} }},
     *   tags={"Catalogo"},
     *   @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Parametro in ISO 639-1 <https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes>",
     *         @OA\Schema(
     *             type="string",
     *             default="it"
     *         )
     *   ),
     *   @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="category id",
     *         @OA\Schema(
     *             type="number"
     *         )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="success",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="code",
     *                         type="integer",
     *                         description="The response code"
     *                     ),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         description="The response data",
     *                         @OA\Property(
     *                              property="data",
     *                              description="Items",
     *                              type="array",
     *                              @OA\Items
     *                          ),
     *                     ),
     *                     example={
     *                         "code": 200,
     *                         "data": {
     *                              "id": 1,
     *                              "parent_id": null,
     *                              "name": "Scarpe",
     *                              "description": null,
     *                              "image_url": null,
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *  )
     * )
     */
    function getCategory(int $id){
        $category = Category::withId($id);

        $image = $category->getUrlImage();
        $data = [
            'id' => $category->id,
            'parent_id' => $category->parent_id,
            'name' => $category->get('name'),
            'description' => $category->get('description'),
            'image_url' => $image?$image:null
        ];
        $this->successResponse($data);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/catalog/categories/{id}/children",
     *   summary="Get alla active category children by parent id",
     *   security={{"apiKey": {}}, { "bearerAuth":{} }},
     *   tags={"Catalogo"},
     *   @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Parametro in ISO 639-1 <https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes>",
     *         @OA\Schema(
     *             type="string",
     *             default="it"
     *         )
     *   ),
     *   @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="category id",
     *         @OA\Schema(
     *             type="number"
     *         )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="success",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="code",
     *                         type="integer",
     *                         description="The response code"
     *                     ),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         description="The response data",
     *                         @OA\Property(
     *                              property="data",
     *                              description="Items",
     *                              type="array",
     *                              @OA\Items
     *                          ),
     *                     ),
     *                     example={
     *                         "code": 200,
     *                         "data": {
     *                              {
     *                                  "id": 1,
     *                                  "parent_id": 1,
     *                                  "name": "Classiche",
     *                                  "has_children": 0,
     *                              },
     *                              {
     *                                  "id": 3,
     *                                  "parent_id": 1,
     *                                  "name": "Moderne",
     *                                  "has_children": 1,
     *                              }
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *  )
     * )
     */
    function getCategoryChildren($id){
        $query = DB::table('product_categories')
            ->leftJoin('product_category_langs','product_category_langs.product_category_id','=','product_categories.id')
            ->where('product_category_langs.lang',_MARION_LANG_)
            ->select([
                'product_categories.id',
                'product_categories.parent_id',
                'product_category_langs.name',
                DB::raw('EXISTS(select 1 from product_categories p where p.parent_id = product_categories.id) has_children')
                //'product_categories.online',
                //'product_categories.order_view',
            ])
            ->orderBy('order_view','ASC')
            ->orderBy('name','ASC');
        $query->where('product_categories.online',true);
        if( $id > 0 ){
            $query->where('product_categories.parent_id',$id);
        }else{
            $query->whereNull('product_categories.parent_id');
        }
        $categories = $query->get()->toArray();
        $this->successResponse($categories);
    }



     /**
     * @OA\Get(
     *   path="/api/v1/catalog/tags",
     *   summary="Get product tags",
     *   security={{"apiKey": {}}, { "bearerAuth":{} }},
     *   tags={"Catalogo"},
       *   @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Parametro in ISO 639-1 <https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes>",
     *         @OA\Schema(
     *             type="string",
     *             default="it"
     *         )
     *   ),
     *   @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="page of pagination",
     *         @OA\Schema(
     *             type="number"
     *         )
     *   ),
     *   @OA\Parameter(
     *         name="size",
     *         in="query",
     *         description="size of pagination. (If size -1 return all items)",
     *         @OA\Schema(
     *             type="number"
     *         )
     *   ),
     *   tags={"Catalogo"},
     *   @OA\Response(
     *     response=200,
     *     description="success",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="code",
     *                         type="integer",
     *                         description="The response code"
     *                     ),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         description="The response data",
     *                         @OA\Property(
     *                              property="data",
     *                              description="Items",
     *                              type="array",
     *                              @OA\Items
     *                          ),
     *                     ),
     *                     example={
     *                         "code": 200,
     *                         "data": {
     *                              "current_page": 1,
     *                              "last_page": 67,
     *                              "total": 1000,
     *                              "per_page": 15,
     *                              "data": {
     *                                  {
     *                                      "id": 1,
     *                                      "name": "Special"
     *                                  },
     *                                  {
     *                                      "id": 2,
     *                                      "name": "Sale"
     *                                  }
     *                              }
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *  )
     * )
     */
   
     function getTags(){
        $page = _var('page');
        if( !$page ) $page = 1;
        $size = _var('size');
        if( !$size ) $size = 15;
        
        $query = DB::table('product_tags')
            ->leftJoin('product_tag_langs','product_tag_langs.product_tag_id','=','product_tags.id')
            ->where('product_tag_langs.lang',_MARION_LANG_)
            ->orderBy('name','ASC');

        if( $size == -1){
            $tot = $query->count('id');
            $size = $tot;
        }
            //->get()->toArray();
        $tags = $query->paginate($size,[
            'product_tags.id',
            'product_tag_langs.name'
        ],'page',$page);
        $this->successResponse($tags);
    }

     /**
     * @OA\Get(
     *   path="/api/v1/catalog/products",
     *   security={{"apiKey": {}}, { "bearerAuth":{} }},
     *   tags={"Catalogo"},
     *   summary="Get products",
     *   @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Parametro in ISO 639-1 <https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes>",
     *         @OA\Schema(
     *             type="string",
     *             default="it"
     *         )
     *   ),
     *   @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="page of pagination",
     *         @OA\Schema(
     *             type="number"
     *         )
     *   ),
     *   @OA\Parameter(
     *         name="size",
     *         in="query",
     *         description="size of pagination. (If size -1 return all items)",
     *         @OA\Schema(
     *             type="number"
     *         )
     *   ),
     *   @OA\Parameter(
     *         name="order_by",
     *         in="query",
     *         description="order by",
     *         @OA\Schema(
     *             type="string",
     *             enum={"sku", "name"}
     *         )
     *   ),
      *   @OA\Parameter(
     *         name="order_direction",
     *         in="query",
     *         description="order direction",
     *         @OA\Schema(
     *             type="string",
     *             enum={"asc", "desc"}
     *         )
     *   ),
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="search (sku, name, ean, upc)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *   ),
     *   @OA\Parameter(
     *         name="tag_id",
     *         in="query",
     *         description="tag",
     *         @OA\Schema(
     *             type="number"
     *         )
     *   ),
     *   @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="category id",
     *         @OA\Schema(
     *             type="number"
     *         )
     *   ),
     *   tags={"Catalogo"},
     *   @OA\Response(
     *     response=200,
     *     description="success",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="code",
     *                         type="integer",
     *                         description="The response code"
     *                     ),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         description="The response data",
     *                         @OA\Property(
     *                              property="data",
     *                              description="Items",
     *                              type="array",
     *                              @OA\Items
     *                          ),
     *                     ),
     *                     example={
     *                         "code": 200,
     *                         "data": {
     *                              "current_page": 1,
     *                              "last_page": 67,
     *                              "total": 1000,
     *                              "per_page": 15,
     *                              "data": {
     *                                  {
     *                                      "id": 1,
     *                                      "category_id": 1,
     *                                      "sku": "001",
     *                                      "quantity": 10,
     *                                      "name": "Bag",
     *                                      "image_url": "http://localhost/img/7/or/sku1.jpg"
     *                                  },
     *                                  {
     *                                      "id": 2,
     *                                      "category_id": 1,
     *                                      "quantity": 10,
     *                                      "sku": "003",
     *                                      "name": "T-shirt",
     *                                      "image_url": "http://localhost/img/7/or/sku1.jpg"
     *                                  }
     *                              }
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *  )
     * )
     */
   
     function getProducts(){
        $page = _var('page');
        if( !$page ) $page = 1;
        $size = _var('size');
        if( !$size ) $size = 15;
        $query = DB::table('products')
            ->leftJoin('product_langs','product_langs.product_id','=','products.id')
            ->leftJoin('product_quantities',function($join){
                $join->on('product_quantities.product_id','=','products.id');
                $join->where('warehouse_id',Warehouse::DEFAULT);
            })
            ->where('product_langs.lang',_MARION_LANG_)
            ->where('products.online',true);
        if( $category_id = _var('category_id') ){
            $query->where('product_category_id',$category_id);
        }

        if( $search = _var('search') ){
            $query->where(function($query) use($search){
                $query->where('product_langs.name','like',"%{$search}%");
                $query->orWhere('products.sku','like',"%{$search}%");
                $query->orWhere('products.ean','like',"%{$search}%");
                $query->orWhere('products.upc','like',"%{$search}%");
            });
        }

        if( $tag = _var('tag') ){
            $query->join('product_tag_associations',function($query) use($tag){
                $query->where('product_tag_associations.product_id','=',"products.id");
                $query->orWhere('product_tag_associations.product_tag_id',$tag);
            });
        }

        if( $order_by = _var('order_by') ){
            
            if( $order_direction = _var('order_direction') ){
                $order_direction = strtolower($order_direction);
            }
            if( !$order_direction ){
                $order_direction = 'asc';
            }
           
            $query->orderBy($order_by,$order_direction);
        }else{
            $query->orderBy('order_view','asc');
        }

        if( $size == -1){
            $tot = $query->count('products.id');
            $size = $tot;
        }
        $products = $query->paginate($size,[
                'products.id',
                'products.product_category_id as category_id',
                'products.sku',
                'products.images',
                'product_langs.name',
                'product_quantities.quantity'
            ],'page',$page);
        
        foreach($products as $p){
            $images = unserialize($p->images);
            unset($p->images);
            $p->image_url = null;
            if( okArray($images) ){
                $image = $images[0];
                $p->image_url = 'http://'.$_SERVER['SERVER_NAME'].$this->getBaseUrl()."img/{$image}/or/{$p->sku}.jpg";
               
            }
        }
        
        $this->successResponse($products);
    }

     /**
     * @OA\Get(
     *   path="/api/v1/catalog/products/{id}",
     *   summary="Get product details",
     *   security={{"apiKey": {}}, { "bearerAuth":{} }},
     *   tags={"Catalogo"},
     *   tags={"Catalogo"},
     *   @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="product id",
     *         @OA\Schema(
     *             type="number"
     *         )
     *   ),
     *   @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Parametro in ISO 639-1 <https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes>",
     *         @OA\Schema(
     *             type="string",
     *             default="it"
     *         )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="success",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="code",
     *                         type="integer",
     *                         description="The response code"
     *                     ),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         description="The response data",
     *                         @OA\Property(
     *                              property="data",
     *                              description="Items",
     *                              type="array",
     *                              @OA\Items
     *                          ),
     *                     ),
     *                     example={
     *                         "code": 200,
     *                         "data": {
     *                              "id": 1,
     *                              "name": "product 0",
     *                              "sku": "sku",
     *                              "ean": null,
     *                              "upc": null,
     *                              "quantity": 10,
     *                              "category": {
     *                                  "id": 1,
     *                                  "name": "Scarpe"
     *                              },
     *                              "description": "<p>Prodotto bellissimo</p>",
     *                              "image_urls": {
     *                                   "http://localhost/img/4/or/sku0_0.jpg",
     *                                   "http://localhost/img/4/or/sku0_1.jpg",
     *                                   "http://localhost/img/4/or/sku0_2.jpg",
     *                              },
     *                              "related_products":{1,2,3}
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *  )
     * )
     */
    function getProduct(int $id){
        $product = Product::withId($id);

        $_images = $product->images;
        $images = [];
        if( okArray($_images) ){
            foreach($_images as $k => $image){
                $images[] = $this->getProtocol().$_SERVER['SERVER_NAME'].$this->getBaseUrl()."img/{$image}/or/{$product->sku}_{$k}.jpg";
            }
           
        }
        $category = $product->product_category_id?Category::withId($product->product_category_id):null;
        $data = [
            'id' => $product->id,
            'name' => $product->get('name'),
            'sku' => $product->sku,
            'ean' => $product->ean,
            'upc' => $product->upc,
            'quantity' => $product->getInventory(),
            'category' => [
                'id' => $category?$category->id:null,
                'name' => $category?$category->get('name'):null,
            ],
            'description' => $product->get('description'),
            'image_urls' => $images,
            'related_products' => []
        ];
        
        $this->successResponse($data);
    }



    private function getProtocol(): string{
        if (isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
        }else {
            $protocol = 'http://';
        }
        return $protocol;
    }


}