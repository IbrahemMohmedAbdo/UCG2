<?php

namespace App\Http\Controllers\Statistics;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function statistics()
    {
        $numberOfAllUsers = $this->numberOfAllUsers();
        $numberOfAllOrders = $this->numberOfAllOrders();
        $numberOfOrdersByStatus = $this->numberOfOrdersByStatus();
        $numberOfAllProducts = $this->numberOfAllProducts();
        $numberOfAllCategories = $this->numberOfAllCategories();
        $numberOfProductsByCategory = $this->numberOfProductsByCategory();
        // $BestSellingProducts = $this->BestSellingProducts();
        // $WorstSellingProducts = $this->WorstSellingProducts();

        return response()->json([
            'usersByTypes' => $numberOfAllUsers,
            'allOrders' => $numberOfAllOrders,
            'ordersByStatus' => $numberOfOrdersByStatus,
            'allProducts' => $numberOfAllProducts,
            'allCategories' => $numberOfAllCategories,
            'productsByCategory' => $numberOfProductsByCategory,
            // 'BestSellingProducts' => $BestSellingProducts,
            // 'WorstSellingProducts' => $WorstSellingProducts,

        ], 200);
    }


    protected function numberOfAllUsers()
    {
        $numberOfinnerRepresentatives = User::where('appKey', 539)->where('type', 'innerRepresentative')->count();
        $numberOfouterRepresentatives = User::where('appKey', 539)->where('type', 'outerRepresentative')->count();
        $numberOfdrivers = User::where('appKey', 539)->where('type', 'driver')->count();
        $numberOfaccountants = User::where('appKey', 539)->where('type', 'accountant')->count();
		$numberOfstoreManagers = User::where('appKey', 539)->where('type', 'storeManager')->count();


        return [
            'numberOfinnerRepresentatives' => $numberOfinnerRepresentatives,
            'numberOfouterRepresentatives' => $numberOfouterRepresentatives,
            'numberOfdrivers' => $numberOfdrivers,
            'numberOfaccountants' => $numberOfaccountants,
			'numberOfstoreManagers' => $numberOfstoreManagers,

        ];
    }


    protected function numberOfAllOrders()
    {
        $numberOfAllOrders = Order::where('appKey', 539)->count();

        return $numberOfAllOrders;
    }


    protected function numberOfOrdersByStatus()
    {
        $numberOfPacked = Order::where('appKey', 539)->where('status', 'packed')->count();
        $numberOfDelivered = Order::where('appKey', 539)->where('status', 'delivered')->count();
        $numberOfPending = Order::where('appKey', 539)->where('status', 'pending')->count();
        $numberOfCancelled = Order::where('appKey', 539)->where('status', 'cancelled')->count();
		$numberOfDelivering = Order::where('appKey', 539)->where('status', 'delivering')->count();
        $numberOfReturned = Order::where('appKey', 539)->where('status', 'returned')->count();
        $numberOfPostponed = Order::where('appKey', 539)->where('status', 'postponed')->count();

        return [
            'numberOfPacked' => $numberOfPacked,
            'numberOfDelivered' => $numberOfDelivered,
            'numberOfPending' => $numberOfPending,
            'numberOfCancelled' => $numberOfCancelled,
			'numberOfDelivering' => $numberOfDelivering,
            'numberOfReturned' => $numberOfReturned,
            'numberOfPostponed' => $numberOfPostponed,
        ];
    }


    protected function numberOfAllProducts()
    {
        $products = Product::where('appKey', 539)->count();

        return $products;
    }


    protected function numberOfAllCategories()
    {
        $numberOfAllCategories = Category::where('appKey', 539)->count();

        return $numberOfAllCategories;
    }


    protected function numberOfProductsByCategory()
    {
        $categories = Category::where('appKey', 539)->with('products')->get();
        $categoryProducts = [];
        foreach ($categories as $category) {
            $categoryProducts[$category->name] = $category->products->count();
        }

        return $categoryProducts;
    }


    public function BestSellingProducts()
    {
        $bestSellingProducts = Product::where('appKey', 539)->with('orders')->withCount('orders')->orderByDesc('orders_count')->take(10)->get();
        $bestSellingProductsDetails = [];
        foreach ($bestSellingProducts as $product) {
            $productName = $product->name;
            $numberOfOrders = $product->orders->count();
            $bestSellingProductsDetails[$productName] = $numberOfOrders;
        }

        return response()->json([
            'bestSellingProducts' => $bestSellingProductsDetails,
        ]);
    }


    public function WorstSellingProducts()
    {
        $worstSellingProducts = Product::where('appKey', 539)->withCount('orders')->orderBy('orders_count')->take(10)->get();
        $worstSellingProductsDetails = [];
        foreach ($worstSellingProducts as $product) {
            $productName = $product->name;
            $numberOfOrders = $product->orders->count();
            $worstSellingProductsDetails[$productName] = $numberOfOrders;
        }

        return response()->json([
            'worstSellingProducts' => $worstSellingProductsDetails,
        ]);
    }


   
    public function BestSellingCategories()
    {
        $bestSellingProducts = Product::where('appKey', 539)
            ->with('category')
            ->select('category_id', \DB::raw('COUNT(*) as product_count'))
            ->groupBy('category_id')
            ->orderByDesc('product_count')
            ->take(10)
            ->get();

        // Load the category relationship for each result
        $bestSellingProducts->load('category');

        $bestSellingProductsDetails = [];

        foreach ($bestSellingProducts as $product) {
            $categoryId = $product->category->name; // Use the correct attribute for category ID
            $numberOfProducts = $product->product_count;
            $bestSellingProductsDetails[$categoryId] = $numberOfProducts;
        }

        return response()->json([
            'bestSellingCategory' => $bestSellingProductsDetails,
        ]);
    }
	
	
	public function monthOrderStatistics(Request $request)
    {
        $monthes = [];
        for($i=(int)$request->startMonth; $i<=(int)$request->endMonth; $i++){
            $numberOfOrders = Order::where('appKey', 539)
                                ->whereMonth('created_at', $i)
                                ->count();
            $monthName = DateTime::createFromFormat('!m', $i)->format('M');
            $monthes[$monthName] = $numberOfOrders;
        };
        $monthes['totalOrders'] = array_sum($monthes);
        
        return $monthes;
    }
	
	
	public function ordersForUser()
	{
		$userID = auth()->id();
		$orders = Order::where('representative_id', $userID)
					   ->where('appKey', 539)
					   ->get();
		$ordersDetails = [
			'total' => $orders->count(),
			'packed' => 0,
			'delivered' => 0,
			'pending' => 0,
			'cancelled' => 0,
			'delivering' => 0,
            'returned' => 0,
            'postponed' => 0
		];

		foreach ($orders as $order) {
			switch ($order->status) {
				case 'Backed':
					$ordersDetails['packed'] += 1;
					break;
				case 'Delivered':
					$ordersDetails['delivered'] += 1;
					break;
				case 'Pending':
					$ordersDetails['pending'] += 1;
					break;
				case 'Cancelled':
					$ordersDetails['cancelled'] += 1;
					break;
				case 'Delivering':
					$ordersDetails['delivering'] += 1;
					break;
				case 'Returned':
					$ordersDetails['returned'] += 1;
					break;
				case 'Postponed':
					$ordersDetails['postponed'] += 1;
					break;
			}
		}

		return response()->json([
			'ordersDetails' => $ordersDetails,
		]);
	}
	
	
	public function ordersForUserType(User $user)
    {
        if($user->type == 'driver'){
            $orders = Order::where('appKey', 539)->where('driver_id', $user->id)->get();
            $ordersDetails = [
                'total' => $orders->count(),
                'packed' => 0,
                'delivered' => 0,
                'pending' => 0,
                'cancelled' => 0,
                'delivering' => 0,
                'returned' => 0,
                'postponed' => 0
            ];
    
            foreach ($orders as $order) {
                switch ($order->status) {
                    case 'Backed':
                        $ordersDetails['packed'] += 1;
                        break;
                    case 'Delivered':
                        $ordersDetails['delivered'] += 1;
                        break;
                    case 'Pending':
                        $ordersDetails['pending'] += 1;
                        break;
                    case 'Cancelled':
                        $ordersDetails['cancelled'] += 1;
                        break;
                    case 'Delivering':
                        $ordersDetails['delivering'] += 1;
                        break;
                    case 'Returned':
                        $ordersDetails['returned'] += 1;
                        break;
                    case 'Postponed':
                        $ordersDetails['postponed'] += 1;
                        break;
                }
            }
        }
		
		return response()->json([
			'ordersDetails' => $ordersDetails,
		]);
    }
	
	
	


}
