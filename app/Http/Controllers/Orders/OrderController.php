<?php

namespace App\Http\Controllers\Orders;

use App\Models\User;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\Product;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Http\Request;
use App\Response\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\OrderResource;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Resources\OneOrderResource;
use App\Http\Resources\CreateOrderResource;
use App\Http\Resources\StatusOrderResource;
use App\Models\PromoCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Services\ThirdPartyApiService;
use Illuminate\Pagination\LengthAwarePaginator;


class OrderController extends Controller
{
    public function getOrders(Request $request)
    {
        $user = auth()->user();

        // Fetch orders from the third-party API
        $thirdPartyApiResult = $this->fetchOrdersFromThirdPartyApi($user);
       $thirdPartyApiResult2 = $this->fetchArchivedOrdersFromThirdPartyApi($user);

        // Build local orders query
        $ordersQuery = $this->buildLocalOrdersQuery($user, $request);

        // Apply filters to the local orders query
        $this->applyFiltersToOrdersQuery($ordersQuery, $user, $request);

        // Paginate the local orders
        $perPage = $request->input('per_page', 5); // Adjust the per_page value as needed
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // Retrieve orders from the local database with pagination
        $orders = $ordersQuery->paginate($perPage, ['*'], 'page', $currentPage);

        // Return API response
        return $this->prepareApiResponse($orders);
    }


    protected function fetchOrdersFromThirdPartyApi($user)
    {
        $authInfo = [
            'username' => $user->shipping_username,
            'password' => $user->shipping_password,
            'type' => 'Authorization',
        ];

        $apiService = new ThirdPartyApiService($authInfo);
        return $apiService->getOrders();
    }
    protected function fetchArchivedOrdersFromThirdPartyApi($user)
    {
        $authInfo = [
            'username' => $user->shipping_username,
            'password' => $user->shipping_password,
            'type' => 'Authorization',
        ];

        $apiService = new ThirdPartyApiService($authInfo);
        return $apiService->getArchivedOrders();
    }



    protected function buildLocalOrdersQuery($user, $request)
    {
        return Order::with('products')->where('appKey', 539)->latest();
    }

    protected function applyFiltersToOrdersQuery($query, $user, $request)
    {
        $shipmentType = $request->input('shippment_type');

        if ($shipmentType !== null) {
            $this->applyShipmentTypeFilter($query, $shipmentType);
        }

        if ($user->type == 'driver') {
            $this->applyDriverFilters($query, $user);
        } elseif ($user->type == 'adminCG' || $user->type =='accountant' || $user->type =='storeManager') {
            // No additional filters for adminCG, accountant, or storeManager
        } else {
            $query->where('representative_id', $user->id);
        }
    }

    protected function applyShipmentTypeFilter($query, $shipmentType)
    {
        $query->where('shippment_type', $shipmentType);
    }

    protected function applyDriverFilters($query, $user)
    {
        $query->where(function ($innerQuery) use ($user) {
            $innerQuery->where('driver_id', $user->id)->orWhere('status', 'Packed');
        });
    }

    protected function prepareApiResponse($orders)
    {
        if ($orders->isEmpty()) {
            return (new ApiResponse(200, __('Orders not found for the authenticated user.'), ['orders' => []]))->send();
        }

        return (new ApiResponse(200, __('List of orders for the authenticated user'), [
            'orders' => OneOrderResource::collection($orders),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ]))->send();
    }









    public function getOrder($id)
{

    $order = Order::where('appKey',539)->find($id);

    if (!$order) {
        return (new ApiResponse(200, __('Id not found'), ['orders'=>[]]))->send();
    }
    return (new ApiResponse(200, __('order by id'), ['order' => new  OrderResource($order)]))->send();
}





    public function store(OrderStoreRequest  $request)
{


try {
		 \DB::beginTransaction();
      $user = auth()->user();
  $reprId= User::where('id',$request->input('representative_Id'))->first();


    $order = Order::create([
        'client_Name' => $request->input('client_name'),
        'client_Number' => $request->input('client_number'),
        'client_Location' => $request->input('client_location'),
		'client_Cuurent_Location' => $request->input('client_cuurent_location'),
		'client_City' => $request->input('client_city'),
		'client_Cuurent_City' => $request->input('client_cuurent_city'),
        'representative_Id' => $request->input('representative_Id') ?? $user->id,
		'shippment_type'=> $request->input('shippment_type'),
        'promo_code_name'=> $request->input('promo_code_name') ?? null,
        'appKey'=>539,

    ]);

		 $user->orders()->save($order);

	foreach ($request->input('products') as $index => $productData)
    {

    $productId = $productData['id'];
    $product = Product::find($productId);

    $price = $product->price;
    $originalPrice=$product->original_price;

		$quantity=$product->variations()->where('size_id', $request->input('variations')[ $index]['size_id'])
			->where('color_id', $request->input('variations')[ $index]['color_id'])->pluck('quantity')->toArray();

		$totalQuantity = $price* $request->input('variations')[ $index]['quantity'];

        $promoCodeName = $request->input('promo_code_name');
        $promoCode = PromoCode::where('name', $promoCodeName)
        ->where('valid', 0)
        ->first();

        if ($promoCode) {
            $discountAmount = $promoCode->value;
            $totalPriceAfterDiscount = ((100 - $discountAmount) / 100) * $totalQuantity;


            $order->products()->attach($productId, [
                'size_id' => $request->input('variations')[ $index]['size_id'],
                'color_id' => $request->input('variations')[ $index]['color_id'],
                'quantity' => $request->input('variations')[ $index]['quantity'],
                'total_price' => $totalQuantity,
                'total_price_after_discount'=>$totalPriceAfterDiscount,

            ]);

            // Update promoCode valid status to 1
            $promoCode->update(['valid' => 1]);

        }
        elseif($promoCode == null || $promoCode->valid == 1){

            $order->products()->attach($productId, [
                'size_id' => $request->input('variations')[ $index]['size_id'],
                'color_id' => $request->input('variations')[ $index]['color_id'],
                'quantity' => $request->input('variations')[ $index]['quantity'],
                'total_price' => $totalQuantity,
            ]);

        }

        $product->variations()
			->where('product_id',$productId)
            ->where('size_id', $request->input('variations')[ $index]['size_id'])
			->where('color_id', $request->input('variations')[ $index]['color_id'])
            ->update(['quantity' => \DB::raw($quantity[0]-($request->input('variations')[ $index]['quantity']))]);



    $productQunatityInOrder=$request->input('variations')[ $index]['quantity'];

    $invoiceData = [
        'product_id' => $product,
        'original_price' => $originalPrice,
        'quantity' => $productQunatityInOrder,
       'amount'=>$originalPrice?($productQunatityInOrder*$originalPrice):null,
    ];

    $product->supplies()->create($invoiceData);

    }

		if ($order->shippment_type == 1) {
    $authInfo = [
            'order_id' => $order->id,
            'username' => $reprId->shipping_username,
            'password' =>$reprId->shipping_password,
            'type' => 'Authorization', // or 'Authentication' based on your needs
        ];
\Illuminate\Support\Facades\Storage::put('testsrtore164.json', json_encode($authInfo));



			 $variationQuantities = collect($request->input('variations.*.quantity'));

      $ids = collect($request->input('products.*.id'));
			$products = $ids->map(function ($id) {
    return Product::find($id);
})->filter();
			$list = $products->map(function ($product, $index) use ($variationQuantities) {
    return [
        'sku' => $product->id,
        'title' => $product->name,
        'amount' => $product->price,
        'quantity' => $variationQuantities[$index],
        'isRefundable' => true,
        'size' => [
            'scale' => 'Centimeter',
            'width' => 10,
            'height' => 10,
            'length' => 10,
        ],
        'weight' => [
            'scale' => 'Kg',
            'value' => 1,
        ],
    ];
})->toArray();


			 $orderData=[
            'servicePackageId' => 'tosyl-rgaly',
            'title'=>'CG-Group',
            'pickFromDoor'=>false,
            'dropToDoor'=>false,
            'destination[from][city]'=>'جنزور 15',//$order->client_Cuurent_City,
            'destination[from][address]'=>'زناتة 10',//$order->client_Cuurent_Location,
            'destination[to][city]'=>$order->client_City,
            'destination[to][address]'=>"",
          	'products[]'=> $list,
            'receivers[0][fullName]'=>$order->client_Name,
            'receivers[0][contact]'=> $order->client_Number,
            'paymentBy'=>'Receiver',
			'productPayment'=>'Included',
            'paymentMethod'=>'Cash',
            'allowBankPayment'=>false,
            'notes'=>$order->client_Location,
            'depositDiscount'=>false
        ];


			$service = new ThirdPartyApiService($authInfo);


			if(!$service)
			{
				return response()->json(['error' => 'Failed to fetch data from the third-party API'], 500);
			}

			$dataOrder = $service->createOrder($orderData);




        if ($dataOrder && isset($dataOrder['data']['orderId']))
         {

			$order->orderId_shipping = $dataOrder['data']['orderId'];
		    $order->client_City= $request->input('client_city');
			$order->save();


            return (new ApiResponse(200, __('order'), ['order' =>$dataOrder]))->send();
        }


        return response()->json(['error' => 'Failed to fetch data from the third-party API'], 500);
   }

			 \DB::commit();
		  return (new ApiResponse(200, __('order added successfully'), ['order' => new CreateOrderResource($order)]))->send();
	}
		 catch (\Exception $e) {
                // Handle exceptions here if needed
                \DB::rollback();
                return response()->json(['error' => $e->getMessage()], 500);
            }
    }


	public function createQrLinkAction($orderId,Request $request) {

    //$user = auth()->user();

    $order=Order::where('orderId_shipping',$orderId)->first();
	$reprId= User::where('id',$order->representative_id)->first();

	$data=[
		'reference'=>$orderId,
		'code'=> $request->code,
	];
		//dd($data);
    $authInfo = [
        'order_id' => $order->id,
        'username' => $reprId->shipping_username,
        'password' => $reprId->shipping_password,
        'type' => 'Authorization',

    ];
    $service = new ThirdPartyApiService($authInfo);
    if(!$service)
    {
         return response()->json(['error' => 'Failed to fetch data from the third-party API'], 500);
    }
    $dataOrder = $service->qrLink($data);
		//dd($dataOrder['data']['code']);
    if ($dataOrder && isset ($dataOrder['data']['code'])) {
       	$order->order_shipping_code=$dataOrder['data']['code'];
		$order->status='Shipping_packed';
		$order->save();
        return (new ApiResponse(200, __('order'), ['order' =>$dataOrder]))->send();
    }
}



	public function assignDriver($order_id, $driver_id)
{



    $order = Order::where('appKey',539)->find($order_id);
    $driver = User::where('appKey', 539)
    ->whereIn('type', ['driver', 'external_driver'])
    ->find($driver_id);

    if (!$order || !$driver) {
        return response()->json(['message' => 'Order or driver not found'], 404);
    }



        $order->driver_id = $driver->id;
        $order->save();

        return response()->json(['message' => 'Order assigned to the driver']);

}


public function packed($order_id, $user_id, Request $request) {




	    $validatedData = $request->validate([
        'scan_code_id' => 'required|unique:orders,scan_code_id',

    ]);

		$user=auth()->user();
         $order = Order::find($order_id);
       $storeManager = User::where('appKey', 539)
        ->where('type',  'storeManager')
        ->find($user_id);


    if (!$order || !$storeManager) {

        return response()->json(['error' => 'Order or user not found'], 403);
    }

    $order->status = 'packed';
	$order->storeManager_id=$user_id;
	$order->scan_code_id = $validatedData['scan_code_id'];
    $order->save();

    return response()->json(['message' => 'Order status updated to Packed'], 200);


}
	public function delivering($order_id,$user_id)
{
	$user=auth()->user();


        $order = Order::where('status','packed')->where('scan_code_id',$order_id)->first();

		$driver = User::where('appKey', 539)
        ->where('type', 'driver')
        ->find($user_id);

   if (!$order || !$driver) {

        return response()->json(['error' => 'Order or user not found'], 403);
    }

        $order->status = 'delivering';
		$order->driver_id=$user_id;
        $order->save();

        return response()->json(['message' => 'Order with deriver successfully'], 200);

    }
public function delivered($order_id,$user_id)
{
	//$user=auth()->user();

     $order = Order::with('products')->whereIn('status',['delivering','postponed','cancelled'])->where('scan_code_id',$order_id)->first();

			$driver = User::where('appKey', 539)
        ->where('type', 'driver')
        ->find($user_id);

   if (!$order || !$driver) {

        return response()->json(['error' => 'Order or user not found'], 403);
    }

        $order->status = 'delivered';
		$order->driver_id=$user_id;
        $order->save();
		if( $order->save())
		{
	$represintative_id=$order->representative_id;

	$driver_id=$order->driver_id;
	$represintativeCommission=User::where('id',$represintative_id)->first();
	$totalPrice = $order->products->sum(function ($product) {
    return $product->pivot->total_price;
            });
	$driverWallet=Wallet::with('user')->where('user_id',$driver_id)->first();
	$represintativeWallet=Wallet::with('user')->where('user_id',$represintative_id)->first();

	if ($represintativeCommission && $represintativeWallet ) {
    $currentCommission = $represintativeCommission->commission;
		$currentBalance = $represintativeWallet->balance;
        $newBalance = $currentBalance + $currentCommission;
		$represintativeWallet->update(['balance' => $newBalance]);

}
	if ($driverWallet) {
    $currentBalance = $driverWallet->balance;
    $newBalance = $currentBalance + $totalPrice;

    $driverWallet->update(['balance' => $newBalance]);
}



        return response()->json(['message' => 'Order delivered successfully'], 200);
		}

    }


public function returned($order_id,$user_id)
{
	//dd($user_id);
    $user = auth()->user();

 /*
$order = Order::with('products')
    ->where('status', 'cancelled')
    ->where('scan_code_id', $order_id)
	->orWhere('order_shipping_code', $order_id)
    ->first();
*/
	$order = Order::with('products')
    ->where('status', 'cancelled')
    ->where(function ($query) use ($order_id) {
        $query->where('scan_code_id', $order_id)
              ->orWhere('order_shipping_code', $order_id);
    })
    ->first();
	$storeManager = User::where('appKey', 539)
    ->where('type', 'storeManager')
    ->find($user_id);


if (!$order ) {
        // Update the order status to 'Packed'
        return response()->json(['error' => 'Order or user not found'], 403);
    }
    try {


	\DB::beginTransaction();
		\Storage::put('line511.json', json_encode($order->products));

$quantities = $order->products()->pluck('order_product.quantity')->unique()->values()->toArray();
\Storage::put('line513.json', json_encode($quantities));

$order->products->each(function ($product, $index) use ($quantities) {
	\Storage::put('line517'.$index.'.json', json_encode($product));
    $quantityToAdd = $quantities[$index] ?? 0;


    //$product->variations->each(function ($variation) use ($quantityToAdd) {
//        $pivot = $variation->pivot;
//        $colorId = $pivot->color_id;
//        $sizeId = $pivot->size_id;
//        $newQuantity = $pivot->quantity + $quantityToAdd;
$pivot = $product->pivot;
        $existingRecord = \DB::table('product_color_size')
            ->where([
                'product_id' => $product->id,
				'color_id' => $pivot->color_id,
				'size_id' => $pivot->size_id,
            ])->first();

        if ($existingRecord) {
            \DB::table('product_color_size')
                ->where([
                	'product_id' => $product->id,
                    'color_id' => $pivot->color_id,
                    'size_id' => $pivot->size_id,
                ])
                ->increment('quantity', $quantityToAdd);
        }



    //});
});





        $order->update(['status' => 'returned', 'storeManager_id' => $user_id]);

			if( $order->save())
		{
	$represintative_id=$order->representative_id;

	$driver_id=$order->driver_id;
	$represintativeCommission=User::where('id',$represintative_id)->first();
	$totalPrice = $order->products->sum(function ($product) {
    return $product->pivot->total_price;
            });
	$driverWallet=Wallet::with('user')->where('user_id',$driver_id)->first();
	$represintativeWallet=Wallet::with('user')->where('user_id',$represintative_id)->first();

	if ($represintativeCommission && $represintativeWallet ) {
    $currentCommission = $represintativeCommission->commission;
		$currentBalance = $represintativeWallet->balance;
        $newBalance = $currentBalance - $currentCommission;
		$represintativeWallet->update(['balance' => $newBalance]);

}
	if ($driverWallet) {
    $currentBalance = $driverWallet->balance;
    $newBalance = $currentBalance - $totalPrice;

    $driverWallet->update(['balance' => $newBalance]);
}



      //  return response()->json(['message' => 'Order delivered successfully'], 200);
		}


        \DB::commit();


        return (new ApiResponse(200, 'Order returned successfully', ['order' => 'Order returned successfully']))->send();
    } catch (\Exception $e) {

        \DB::rollback();


        return (new ApiResponse(500, 'Error returning order', ['error' => $e->getMessage()]))->send();
    }
}
public function postponed(Request $request, $order_id,$user_id)
{

    $user = auth()->user();

    $order = Order::where('scan_code_id',$order_id)->first();
	$driver = User::where('appKey', 539)
        ->whereIn('type', ['driver'])
        ->find($user_id);

   if (!$order || !$driver) {

        return response()->json(['error' => 'Order or user not found'], 403);
    }


	    $order->status = 'postponed';
		$order->driver_id=$user_id;
		$order->comment=$request->input('comment');
        $order->save();





    return response()->json(['message' => 'Order postponed successfully'], 200);
}

	public function cancel_Order(Request $request, $order_id,$user_id)
{

    $user = auth()->user();

    $order = Order::where('scan_code_id',$order_id)->first();
	$driver = User::where('appKey', 539)
        ->whereIn('type', ['driver','adminCG'])
        ->find($user_id);

   if (!$order || !$driver) {

        return response()->json(['error' => 'Order or user not found'], 403);
    }


	    $order->status = 'cancelled';
		$order->driver_id=$user_id;
		$order->comment=$request->input('comment');
        $order->save();





    return response()->json(['message' => 'Order cancelled successfully'], 200);
}



	public function delete_order($id)
	{

		try {
        // Find the order in your local database
        $order = Order::find($id);

        // If the order does not exist, return an error response
        if (!$order) {
            return (new ApiResponse(200, __('Order not found.'), []))->send();
        }

        // Check if the order has an associated order ID in the third-party API
        if ($order->orderId_shipping) {
            // Try to get the representative information
            $reprId = User::findOrFail($order->representative_id);

            // Initialize the ThirdPartyApiService with authentication information
            $authInfo = [
                'order_id' => $order->id,
                'username' => $reprId->shipping_username,
                'password' => $reprId->shipping_password,
                'type' => 'Authorization',
            ];
            $apiService = new ThirdPartyApiService($authInfo);

            // Attempt to delete the order from the third-party API
            $thirdPartyApiResult = $apiService->deleteOrder($order->orderId_shipping);

            // Check the result from the third-party API
            if (isset($thirdPartyApiResult['error'])) {
                // Log or handle the third-party API error
                return (new ApiResponse(500, __('Failed to delete order in third-party API'), []))->send();
            }
        }

        // If the order was deleted in the third-party API or it doesn't have orderId_shipping, proceed to delete it locally
        if ($order->delete()) {
            return (new ApiResponse(200, __('Order deleted successfully'), []))->send();
        } else {
            return (new ApiResponse(500, __('Failed to delete order locally'), []))->send();
        }
    } catch (\Exception $e) {
        // Log or handle the exception
        return (new ApiResponse(500, __('An error occurred while processing your request'), []))->send();
    }



	}






}
