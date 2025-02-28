<?php

namespace App\Http\Controllers;

use App\Services\MortgageService;
use App\Models\MortgageRequest;
use App\Models\Installment;
use Illuminate\Support\Facades\Auth;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $mortgageService;
    protected $paymentService;

    public function __construct(MortgageService $mortgageService, PaymentService $paymentService) // Perbaikan: __construct
    {
        $this->mortgageService = $mortgageService;
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $userId = Auth::id();
        $mortgages = $this->mortgageService->getUserMortgages($userId);

        return view('customer.mortgages.index', compact('mortgages'));
    }

    public function details(MortgageRequest $mortgageRequest)
    {
        $details = $this->mortgageService->getMortgageDetails($mortgageRequest);
        return view('customer.mortgages.details', $details);
    }

    public function installment_details(Installment $installment)
    {
        $installmentDetails = $this->mortgageService->getInstallmentDetails($installment);
        return view('customer.installment.index', compact('installmentDetails'));
    }

    public function installment_payment(MortgageRequest $mortgageRequest)
    {
        $paymentDetails = $this->mortgageService->getInstallmentPaymentDetails($mortgageRequest);
        return view('customer.installments.pay_installment', $paymentDetails);
    }

    public function paymentStoreMidtrans(Request $request)
    {
        try {
            $mortgageRequest = $this->mortgageService->getMortgageRequest($request->input('mortgage_request_id'));

            $snapToken = $this->paymentService->createPayment($mortgageRequest);

            return response()->json(['snap_token' => $snapToken], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Payment failed: ' . $e->getMessage()], 500);
        }
    }

    public function paymentMidtransNotification(Request $request)
    {
        try {
            $this->paymentService->processNotification();

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to process notification: ' . $e->getMessage()], 500);
        }
    }
}
