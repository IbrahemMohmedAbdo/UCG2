<?php
namespace App\Services;


use App\Models\User;
use App\Models\Order;
use App\Models\Wallet;
use Illuminate\Support\Facades\Http;

class ThirdPartyApiService
{
    private $baseUrl = 'https://api.sabil.ly/v1/user/access/';
    private $token;
    public $username;
    private $status = true;

    public function __construct($authInfo)
    {
        $this->authenticateUser($authInfo);
    }

    private function authenticateUser($authInfo)
    {
        try {
            $response = Http::post($this->baseUrl, $authInfo);
            $response->throw();

            $resData = $response->json();

            $this->token = $resData['data']['token']['live'];
           $this->username = $resData['data']['user']['userId'];

            $this->status = true;

        } catch (\Exception $e) {
            // Log or handle authentication error
            $this->status = false;
        }
    }



	 public function createOrder($orderData)
    {
        if (!$this->status) {
            // Handle the case where authentication failed
            return ['error' => 'Authentication failed'];
        }

        try {
            $responseOrder = $this->sendApiRequest('post', 'https://api.sabil.ly/v1/orders/' . $this->username . '/', $orderData);
            $responseData = $this->handleApiResponse($responseOrder);

            return $responseData;
        } catch (\Exception $e) {
            // Log or handle the API request error
            return ['error' => $e->getMessage()];
        }
    }


    // public function getOrders()
    // {


    //     if (!$this->status) {
    //         // Handle the case where authentication failed
    //         return ['error' => 'Authentication failed'];
    //     }

    //     try {
    //         $responseOrders = $this->sendApiRequest('get', 'https://api.sabil.ly/v2/orders/' . $this->username . '/');
    //       //  https://api.sabil.ly/v2/orders/archived/:userId/
    //         $responseOrdersArchived = $this->sendApiRequest('get', ' https://api.sabil.ly/v2/orders/archived/' . $this->username . '/');

    //         $responseData1 = $this->handleApiResponse($responseOrders);
    //         $responseData2 = $this->handleApiResponse($responseOrdersArchived);
    //         $this->updateLocalOrderStatuses($responseData1);
    //         return $responseData1;
    //     } catch (\Exception $e) {

    //         // Log or handle the API request error
    //         return ['error' => $e->getMessage()];
    //     }
    // }

    public function getOrders()
    {
        if (!$this->status) {
            return ['error' => 'Authentication failed'];
        }

        try {
            $responseOrders = $this->sendApiRequest('get', 'https://api.sabil.ly/v2/orders/' . $this->username . '/');
            $responseArchivedOrders = $this->sendApiRequest('get', 'https://api.sabil.ly/v2/orders/archived/' . $this->username . '/');


            $responseData = array_merge(
                $this->handleApiResponse($responseOrders['data']['results']),
                 $this->handleApiResponse($responseArchivedOrders['data']['results'])
            );



            $this->updateLocalOrderStatuses($responseData);

            return $responseData;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    public function getArchivedOrders()
    {
        if (!$this->status) {
            return ['error' => 'Authentication failed'];
        }

        try {

            $responseArchivedOrders = $this->sendApiRequest('get', 'https://api.sabil.ly/v2/orders/archived/' . $this->username . '/');


            $responseData = $this->handleApiResponse($responseArchivedOrders);


        $this->updateLocalOrderStatuses($responseData);


            return $responseData;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }



    public function trackOrder($orderId)
    {
        if (!$this->status) {
            return ['error' => 'Authentication failed'];
        }

        try {
            $responseTimeline = $this->sendApiRequest('get', 'https://api.sabil.ly/v2/timelines/' . $orderId . '/');
            $responseData = $this->handleApiResponse($responseTimeline);
            $this->updateLocalOrderStatuses($responseData);
            // Additional logic for tracking orders if needed

            return $responseData;
        } catch (\Exception $e) {
            // Log or handle the API request error
            return ['error' => $e->getMessage()];
        }
    }

    private function sendApiRequest($method, $url, $data = [])
    {
        $request = Http::withToken($this->token);

        if ($method == 'post') {
            $response = $request->asForm()->post($url, $data);
        } elseif ($method == 'get') {
            $response = $request->get($url);
        } else {
            throw new \InvalidArgumentException('Unsupported HTTP method');
        }

        $response->throw();

        return $response->json();
    }

    private function handleApiResponse($response)
    {
        // Check if the 'data' key exists in the response
        if (isset($response['data'])) {
            // Check if the 'status' key exists and is false
            if (isset($response['data']['status']) && $response['data']['status'] === false) {
                // Log or handle the specific error case
                $errorMessage = $response['data']['messages'][0]['message'] ?? 'Unexpected API response format';
                return ['error' => 'Specific error occurred: ' . $errorMessage];
            }
        }

        // Return the response data
        return $response;
    }


    protected function updateLocalOrderStatuses($responseData)
    {
        foreach ($responseData['data']['results'] as $apiOrder) {
            $apiStatus = $apiOrder['status'];
            $localOrder = Order::where('orderId_shipping', $apiOrder['orderId'])->first();

            if ($localOrder && $localOrder->status != $apiStatus) {
                if ($localOrder->is_completed_in_api == 0 && $apiStatus == 'Completed') {
                    $this->updateWalletsForCompletedOrder($localOrder);
                    $localOrder->is_completed_in_api = 1;
                }

                $localOrder->status = $apiStatus;
                $localOrder->save();
            }
        }
    }

    protected function updateWalletsForCompletedOrder($localOrder)
    {
        $represintative_id = $localOrder->representative_id;

        $represintativeCommission = User::where('id', $represintative_id)->first();

        $totalPrice = $localOrder->products->sum(function ($product) {
            return $product->pivot->total_price;
        });

        $represintativeWallet = Wallet::with('user')->where('user_id', $represintative_id)->first();

        if ($represintativeCommission && $represintativeWallet) {
            $currentCommission = $represintativeCommission->commission;
            $currentBalance = $represintativeWallet->balance;
            $newBalance = $currentBalance - $currentCommission;
            $represintativeWallet->update(['balance' => $newBalance]);
        }
    }











	 public function qrLink($data)
    {
		     $userId=$this->username;

        if (!$this->status) {
            // Handle the case where authentication failed
            return ['error' => 'Authentication failed'];
        }


        try {
            $responseOrder = Http::asForm()
                ->withToken($this->token)
               ->post('https://api.sabil.ly/v1/references/' . $this->username . '/',$data);

            $responseOrder->throw();

            $responseData = $responseOrder->json();

            // Check if the 'data' key exists in the response
            if (isset($responseData['data'])) {
                // Check if the 'status' key exists and is false
                if (isset($responseData['data']['status']) && $responseData['data']['status'] === false) {
                    // Log or handle the specific error case
                    $errorMessage = $responseData['data']['messages'][0]['message'] ?? 'Unexpected API response format';
                    return ['error' => 'Specific error occurred: ' . $errorMessage];
                }
            }

            // Return the response data
            return $responseData;
        } catch (\Exception $e) {
            // Log or handle the API request error
            return ['error' => $e->getMessage()];
        }


    }

	 public function deleteOrder($orderId)
    {
        if (!$this->status) {
            // Handle the case where authentication failed
            return ['error' => 'Authentication failed'];
        }

        try {
            $deleteOrderUrl = 'https://api.sabil.ly/v1/orders/' . $this->username . '/' . $orderId . '/';

            $responseDeleteOrder = Http::withToken($this->token)->delete($deleteOrderUrl);

            $responseDeleteOrder->throw();

            $responseData = $responseDeleteOrder->json();

            // Check if the 'data' key exists in the response
            if (isset($responseData['data'])) {
                // Check if the 'status' key exists and is false
                if (isset($responseData['data']['status']) && $responseData['data']['status'] === false) {
                    // Log or handle the specific error case
                    $errorMessage = $responseData['data']['messages'][0]['message'] ?? 'Unexpected API response format';
                    return ['error' => 'Specific error occurred: ' . $errorMessage];
                }
            }

            // Return the response data
            return $responseData;
        } catch (\Exception $e) {
            // Log or handle the API request error
            return ['error' => $e->getMessage()];
        }
    }









}
