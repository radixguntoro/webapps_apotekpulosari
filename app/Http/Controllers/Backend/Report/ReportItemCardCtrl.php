<?php

namespace App\Http\Controllers\Backend\Report;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Http\Controllers\Controller;
use App\Model\ModelReportItemCard;
use App\Model\ModelTrSalesRegular;
use App\Model\ModelTrSalesNetto;
use App\Model\ModelTrSalesCredit;
use App\Model\ModelTrSalesRecipe;
use App\Model\ModelTrSalesMix;
use App\Model\ModelTrSalesLab;
use App\Model\ModelTrPurchases;
use App\Model\ModelReturns;
use App\Model\ModelReturnTrPurchases;
use App\Model\ModelReturnTrSales;
use App\Model\ModelReturnTrSalesNetto;
use App\Model\ModelStockOpname;
use App\Model\ModelStockAdjustments;
use App\Model\ModelStockAdjustmentsTrPurchases;
use App\Model\ModelStockAdjustmentsTrSalesNetto;
use App\Model\ModelStockAdjustmentsTrSales;
use App\Model\ModelItems;
use DB;

class ReportItemCardCtrl extends Controller
{
    public function index()
    {
        return view('layouts.adminLayout');
    }

    public function readHistoryStockList(Request $request)
    {
        $medicine_id = $request->get('medicine_id');
        $date_start = $request->get('date_start');
        $date_end = $request->get('date_end');

        $data_so_last = ModelStockOpname::where('medicines_items_id', $medicine_id)->where(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d")'), '<', $date_start)->orderBy('created_at', 'desc')->first();
        $date_so = $data_so_last == null ? '' : date('Y-m-d', strtotime($data_so_last->created_at));

        $stock_item_last = ModelReportItemCard::readLogQty($medicine_id, $date_so, date('Y-m-d',strtotime($date_start . "-1 days")));
        
        for ($i = 0; $i < count($stock_item_last); $i++) {
            $stock_item_last[$i]->name = $stock_item_last[$i]->name;
            $stock_item_last[$i]->cashier = Str::title($stock_item_last[$i]->cashier);
            $stock_item_last[$i]->status = $stock_item_last[$i]->status;
            $stock_item_last[$i]->qty = $stock_item_last[$i]->qty;
            $stock_item_last[$i]->date = $stock_item_last[$i]->date;
            $stock_item_last[$i]->title_id = $stock_item_last[$i]->title;
            $stock_item_last[$i]->title_name = $this->setTitle($stock_item_last[$i]->title);
            if($i == 0) {
                if($stock_item_last[$i]->status == 'stock_opname') {
                    $stock_item_last[$i]->first_stock = $stock_item_last[$i]->qty;
                    $stock_item_last[$i]->stock_out = 0;
                    $stock_item_last[$i]->stock_in = $stock_item_last[$i]->qty;
                    $stock_item_last[$i]->last_stock =  $stock_item_last[$i]->qty;
                } else if($stock_item_last[$i]->status == 'stock_out') {
                    $stock_item_last[$i]->first_stock = $stock_item_last[$i]->qty;
                    $stock_item_last[$i]->stock_out = $stock_item_last[$i]->qty;
                    $stock_item_last[$i]->stock_in = 0;
                    $stock_item_last[$i]->last_stock =  $stock_item_last[$i]->first_stock - $stock_item_last[$i]->qty;
                } else {
                    $stock_item_last[$i]->first_stock = $stock_item_last[$i]->qty;
                    $stock_item_last[$i]->stock_out = 0;
                    $stock_item_last[$i]->stock_in = $stock_item_last[$i]->qty;
                    $stock_item_last[$i]->last_stock =  $stock_item_last[$i]->first_stock + $stock_item_last[$i]->qty;
                }
            } else {
                if($stock_item_last[$i]->status == 'stock_opname') {
                    $stock_item_last[$i]->first_stock = $stock_item_last[$i]->qty;
                    $stock_item_last[$i]->stock_out = 0;
                    $stock_item_last[$i]->stock_in = $stock_item_last[$i]->qty;
                    $stock_item_last[$i]->last_stock =  $stock_item_last[$i]->qty;
                } else if($stock_item_last[$i]->status == 'stock_out') {
                    $stock_item_last[$i]->first_stock = $stock_item_last[$i-1]->last_stock;
                    $stock_item_last[$i]->stock_out = $stock_item_last[$i]->qty;
                    $stock_item_last[$i]->stock_in = 0;
                    $stock_item_last[$i]->last_stock =  $stock_item_last[$i]->first_stock - $stock_item_last[$i]->qty;
                } else {
                    $stock_item_last[$i]->first_stock = $stock_item_last[$i-1]->last_stock;
                    $stock_item_last[$i]->stock_out = 0;
                    $stock_item_last[$i]->stock_in = $stock_item_last[$i]->qty;
                    $stock_item_last[$i]->last_stock =  $stock_item_last[$i]->first_stock + $stock_item_last[$i]->qty;
                }
            }
        }

        $stock_item = ModelReportItemCard::readLogQty($medicine_id, $date_start, $date_end);
        // return response()->json($stock_item);
        for ($i = 0; $i < count($stock_item); $i++) {
            $stock_item[$i]->name = $stock_item[$i]->name;
            $stock_item[$i]->cashier = Str::title($stock_item[$i]->cashier);
            $stock_item[$i]->status = $stock_item[$i]->status;
            $stock_item[$i]->qty = $stock_item[$i]->qty;
            $stock_item[$i]->date = $stock_item[$i]->date;
            $stock_item[$i]->title_id = $stock_item[$i]->title;
            $stock_item[$i]->title_name = $this->setTitle($stock_item[$i]->title);
            if($i == 0) {
                if($stock_item[$i]->status == 'stock_opname') {
                    $stock_item[$i]->first_stock = $stock_item[$i]->qty;
                    $stock_item[$i]->stock_out = 0;
                    $stock_item[$i]->stock_in = 0;
                    $stock_item[$i]->last_stock =  $stock_item[$i]->qty;
                } else if ($stock_item[$i]->status == 'stock_out') {
                    $stock_item[$i]->first_stock = count($stock_item_last) > 0 ? $stock_item_last[count($stock_item_last)-1]->last_stock : $stock_item[$i]->qty;
                    $stock_item[$i]->stock_out = $stock_item[$i]->qty;
                    $stock_item[$i]->stock_in = 0;
                    $stock_item[$i]->last_stock =  $stock_item[$i]->first_stock - $stock_item[$i]->qty;
                } else {
                    $stock_item[$i]->first_stock = count($stock_item_last) > 0 ? $stock_item_last[count($stock_item_last)-1]->last_stock : $stock_item[$i]->qty;
                    $stock_item[$i]->stock_out = 0;
                    $stock_item[$i]->stock_in = $stock_item[$i]->qty;
                    $stock_item[$i]->last_stock = count($stock_item_last) > 0 ? $stock_item[$i]->qty + $stock_item[$i]->first_stock : $stock_item[$i]->qty;
                }
            } else {
                if($stock_item[$i]->status == 'stock_opname') {
                    $stock_item[$i]->first_stock = $stock_item[$i]->qty;
                    $stock_item[$i]->stock_out = 0;
                    $stock_item[$i]->stock_in = 0;
                    $stock_item[$i]->last_stock =  $stock_item[$i]->qty;
                } else if($stock_item[$i]->status == 'stock_out') {
                    $stock_item[$i]->first_stock = $stock_item[$i-1]->last_stock;
                    $stock_item[$i]->stock_out = $stock_item[$i]->qty;
                    $stock_item[$i]->stock_in = 0;
                    $stock_item[$i]->last_stock =  $stock_item[$i]->first_stock - $stock_item[$i]->qty;
                } else {
                    $stock_item[$i]->first_stock = $stock_item[$i-1]->last_stock;
                    $stock_item[$i]->stock_out = 0;
                    $stock_item[$i]->stock_in = $stock_item[$i]->qty;
                    $stock_item[$i]->last_stock =  $stock_item[$i]->first_stock + $stock_item[$i]->qty;
                }
            }
        }
        
        return response()->json($stock_item);
    }

    public function readHistoryStockDetail(Request $request)
    {
        $transaction_id = $request->get('transaction_id');
        $title_id = $request->get('title_id');
        
        switch($title_id) {
            case 'sales_regular':
                $title_id = $this->readDataSalesRegular($transaction_id);
                break;
            case 'sales_netto':
                $title_id = $this->readDataSalesNetto($transaction_id);
                break;
            case 'sales_credit':
                $title_id = $this->readDataSalesCredit($transaction_id);
                break;
            case 'sales_mix':
                $title_id = $this->readDataSalesMix($transaction_id);
                break;
            case 'sales_recipe':
                $title_id = $this->readDataSalesRecipe($transaction_id);
                break;
            case 'sales_lab':
                $title_id = $this->readDataSalesLab($transaction_id);
                break;
            case 'purchase':
                $title_id = $this->readDataPurchase($transaction_id);
                break;
            case 'ret_purchase':
                $title_id = $this->readDataReturnPurchase($transaction_id);
                break;
            case 'ret_netto':
                $title_id = $this->readDataReturnNetto($transaction_id);
                break;
            case 'ret_sales':
                $title_id = $this->readDataReturnSales($transaction_id);
                break;
            case 'stock_opname':
                $title_id = $this->readDataStockOpname($transaction_id);
                break;
            case 'stock_adjustments_purchase':
                $title_id = $this->readDataStockAdjustmentsPurchase($transaction_id);
                break;
            case 'stock_adjustments_sales_netto':
                $title_id = $this->readDataStockAdjustmentsSalesNetto($transaction_id);
                break;
            case 'stock_adjustments_incoming_goods':
                $title_id = $this->readDataStockAdjustmentsSales($transaction_id);
                break;
            case 'stock_adjustments_exit_goods':
                $title_id = $this->readDataStockAdjustmentsSales($transaction_id);
                break;
        }

        $data = $title_id;
        
        return response()->json($data);
    }

    public function readDataSalesRegular($id)
    {
        $data = ModelTrSalesRegular::find($id);
        $details = [];
        
        foreach ($data->trSalesRegularDetails as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $val_detail->qty;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['subtotal'] = $val_detail->subtotal;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
            $details[$k_detail]['categoryName'] = $val_detail->medicine->item->category->name;
        }

        $data->personName = '';
        $data->id = $data->trSales->transactions_id;
        $data->invoiceNumber = $data->trSales->invoice_number;
        $data->total = $data->trSales->transaction->total;
        $data->discount = $data->trSales->transaction->discount;
        $data->grandTotal = $data->trSales->transaction->grand_total;
        $data->payment = $data->payment;
        $data->balance = $data->balance;
        $data->codesId = $data->trSales->transaction->codes_id;
        $data->date = date('d-m-Y', strtotime($data->trSales->transaction->created_at));
        $data->time = date('H:i:s', strtotime($data->trSales->transaction->created_at));
        $data->cashierId = $data->trSales->transaction->cashier->id;
        $data->cashierName = Str::title($data->trSales->transaction->cashier->name);
        $data->qtyTotal = count($details);
        $data->details = $details;

        return $data;
    }

    public function readDataSalesNetto($id)
    {
        $data = ModelTrSalesNetto::with('customer')->find($id);
        $details = [];
        
        foreach ($data->trSalesNettoDetails as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $val_detail->qty_input;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['ppn'] = $val_detail->ppn;
            $details[$k_detail]['subtotal'] = (($val_detail->price - ($val_detail->price * $val_detail->discount)) + (($val_detail->price - ($val_detail->price * $val_detail->discount)) * $val_detail->ppn)) * $val_detail->qty_input;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
            $details[$k_detail]['categoryName'] = $val_detail->medicine->item->category->name;
        }

        $data->personName = $data->customer->name;
        $data->id = $data->trSales->transactions_id;
        $data->invoiceNumber = $data->trSales->invoice_number;
        $data->total = $data->trSales->transaction->total;
        $data->discount = $data->trSales->transaction->discount;
        $data->grandTotal = $data->trSales->transaction->grand_total;
        $data->payment = $data->payment;
        $data->balance = $data->balance;
        $data->codesId = $data->trSales->transaction->codes_id;
        $data->date = date('d-m-Y', strtotime($data->trSales->transaction->created_at));
        $data->time = date('H:i:s', strtotime($data->trSales->transaction->created_at));
        $data->cashierId = $data->trSales->transaction->cashier->id;
        $data->cashierName = Str::title($data->trSales->transaction->cashier->name);
        $data->qtyTotal = count($details);
        $data->details = $details;

        return $data;
    }

    public function readDataSalesCredit($id)
    {
        $data = ModelTrSalesCredit::with('customer')->find($id);
        $details = [];
        
        foreach ($data->trSalesCreditDetails as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $val_detail->qty;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['subtotal'] = $val_detail->subtotal;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
            $details[$k_detail]['categoryName'] = $val_detail->medicine->item->category->name;
        }

        $data->personName = $data->customer->name;
        $data->id = $data->trSales->transactions_id;
        $data->invoiceNumber = $data->trSales->invoice_number;
        $data->total = $data->trSales->transaction->total;
        $data->discount = $data->trSales->transaction->discount;
        $data->grandTotal = $data->trSales->transaction->grand_total;
        $data->payment = $data->payment;
        $data->balance = $data->balance;
        $data->codesId = $data->trSales->transaction->codes_id;
        $data->date = date('d-m-Y', strtotime($data->trSales->transaction->created_at));
        $data->time = date('H:i:s', strtotime($data->trSales->transaction->created_at));
        $data->cashierId = $data->trSales->transaction->cashier->id;
        $data->cashierName = Str::title($data->trSales->transaction->cashier->name);
        $data->qtyTotal = count($details);
        $data->details = $details;

        return $data;
    }

    public function readDataSalesRecipe($id)
    {
        $data = ModelTrSalesRecipe::find($id);
        $details = [];
        
        foreach ($data->trSalesRecipeMedicines as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $val_detail->qty;
            $details[$k_detail]['discount'] = $val_detail->discount == null ? '0.00' : $val_detail->discount;
            $details[$k_detail]['subtotal'] = $val_detail->subtotal;
            $details[$k_detail]['status'] = $val_detail->status;
            $details[$k_detail]['medicineId'] = $val_detail->id;
            $details[$k_detail]['medicineName'] = $val_detail->name;
            $details[$k_detail]['categoryName'] = $val_detail->status == 'mix' ? 'Racik' : 'Non Racik';
        }

        $data->personName = '';
        $data->id = $data->trSales->transactions_id;
        $data->invoiceNumber = $data->trSales->invoice_number;
        $data->total = $data->trSales->transaction->total;
        $data->discount = $data->trSales->transaction->discount;
        $data->grandTotal = $data->trSales->transaction->grand_total;
        $data->payment = $data->payment;
        $data->balance = $data->balance;
        $data->codesId = $data->trSales->transaction->codes_id;
        $data->date = date('d-m-Y', strtotime($data->trSales->transaction->created_at));
        $data->time = date('H:i:s', strtotime($data->trSales->transaction->created_at));
        $data->cashierId = $data->trSales->transaction->cashier->id;
        $data->cashierName = Str::title($data->trSales->transaction->cashier->name);
        $data->qtyTotal = count($details);
        $data->details = $details;

        return $data;
    }

    public function readDataSalesMix($id)
    {
        $data = ModelTrSalesMix::find($id);
        $details = [];
        
        foreach ($data->trSalesMixMedicines as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $val_detail->qty;
            $details[$k_detail]['discount'] = $val_detail->discount == null ? '0.00' : $val_detail->discount;
            $details[$k_detail]['subtotal'] = $val_detail->subtotal;
            $details[$k_detail]['status'] = 'mix';
            $details[$k_detail]['medicineId'] = $val_detail->id;
            $details[$k_detail]['medicineName'] = $val_detail->name;
            $details[$k_detail]['categoryName'] = 'Racik';
        }

        $data->personName = '';
        $data->id = $data->trSales->transactions_id;
        $data->invoiceNumber = $data->trSales->invoice_number;
        $data->total = $data->trSales->transaction->total;
        $data->discount = $data->trSales->transaction->discount;
        $data->grandTotal = $data->trSales->transaction->grand_total;
        $data->payment = $data->payment;
        $data->balance = $data->balance;
        $data->codesId = $data->trSales->transaction->codes_id;
        $data->date = date('d-m-Y', strtotime($data->trSales->transaction->created_at));
        $data->time = date('H:i:s', strtotime($data->trSales->transaction->created_at));
        $data->cashierId = $data->trSales->transaction->cashier->id;
        $data->cashierName = Str::title($data->trSales->transaction->cashier->name);
        $data->qtyTotal = count($details);
        $data->details = $details;

        return $data;
    }

    public function readDataSalesLab($id)
    {
        $data = ModelTrSalesLab::find($id);
        $details = [];
        
        foreach ($data->trSalesLabDetails as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $val_detail->qty;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['subtotal'] = $val_detail->subtotal;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
            $details[$k_detail]['categoryName'] = $val_detail->medicine->item->category->name;
        }

        $data->personName = '';
        $data->id = $data->trSales->transactions_id;
        $data->invoiceNumber = $data->trSales->invoice_number;
        $data->total = $data->trSales->transaction->total;
        $data->discount = $data->trSales->transaction->discount;
        $data->grandTotal = $data->trSales->transaction->grand_total;
        $data->payment = $data->payment;
        $data->balance = $data->balance;
        $data->codesId = $data->trSales->transaction->codes_id;
        $data->date = date('d-m-Y', strtotime($data->trSales->transaction->created_at));
        $data->time = date('H:i:s', strtotime($data->trSales->transaction->created_at));
        $data->cashierId = $data->trSales->transaction->cashier->id;
        $data->cashierName = Str::title($data->trSales->transaction->cashier->name);
        $data->qtyTotal = count($details);
        $data->details = $details;

        return $data;
    }

    public function readDataPurchase($id)
    {
        $data = ModelTrPurchases::with('supplier')->find($id);
        $details = [];
        
        foreach ($data->trPurchaseDetails as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $val_detail->qty;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['subtotal'] = ($val_detail->price - ($val_detail->price * $val_detail->discount)) * $val_detail->qty;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
            $details[$k_detail]['categoryName'] = $val_detail->medicine->item->category->name;
        }

        $data->personName = $data->supplier->name;
        $data->id = $data->transactions_id;
        $data->invoiceNumber = $data->invoice_number;
        $data->total = $data->transaction->total;
        $data->discount = $data->transaction->discount;
        $data->grandTotal = $data->transaction->grand_total;
        $data->payment = $data->payment;
        $data->balance = $data->balance;
        $data->codesId = $data->transaction->codes_id;
        $data->date = date('d-m-Y', strtotime($data->transaction->created_at));
        $data->time = date('H:i:s', strtotime($data->transaction->created_at));
        $data->cashierId = $data->transaction->cashier->id;
        $data->cashierName = Str::title($data->transaction->cashier->name);
        $data->qtyTotal = count($details);
        $data->details = $details;

        return $data;
    }

    public function readDataReturnPurchase($id)
    {
        $data = ModelReturnTrPurchases::with('return', 'trPurchase', 'trPurchaseDetails')->find($id);
        // dd($data);
        $details = [];
        $tmp_details = [];
        
        $tmp_details = Arr::prepend($details, $data->trPurchaseDetails);
        // return response()->json($details);

        foreach ($tmp_details as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $data->qty;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['subtotal'] = ($val_detail->price - ($val_detail->price * $val_detail->discount)) * $data->qty;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
            $details[$k_detail]['categoryName'] = $val_detail->medicine->item->category->name;
        }

        $data->personName = '';
        $data->id = $data->returns_id;
        $data->invoiceNumber = $data->returns_id;
        $data->total = $data->trPurchase->transaction->total;
        $data->discount = $data->trPurchase->transaction->discount;
        $data->grandTotal = $data->trPurchase->transaction->grand_total;
        $data->payment = $data->payment == null ? 0 : $data->payment;
        $data->balance = $data->balance == null ? 0 : $data->payment;
        $data->codesId = $data->return->codes_id;
        $data->date = date('d-m-Y', strtotime($data->return->created_at));
        $data->time = date('H:i:s', strtotime($data->return->created_at));
        $data->cashierId = $data->return->cashier->id;
        $data->cashierName = Str::title($data->return->cashier->name);
        $data->qtyTotal = count($details);
        $data->details = $details;
        
        return $data;
    }
    
    public function readDataReturnNetto($id)
    {
        $data = ModelReturnTrSalesNetto::with('return', 'trSalesNetto', 'trSalesNettoDetails')->find($id);
        // dd($data);
        $details = [];
        $tmp_details = [];
        
        $tmp_details = Arr::prepend($details, $data->trSalesNettoDetails);
        // return response()->json($details);

        foreach ($tmp_details as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $data->qty;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['subtotal'] = ($val_detail->price - ($val_detail->price * $val_detail->discount)) * $data->qty;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
            $details[$k_detail]['categoryName'] = $val_detail->medicine->item->category->name;
        }

        $data->personName = '';
        $data->id = $data->returns_id;
        $data->invoiceNumber = $data->returns_id;
        $data->total = $data->trSalesNetto->trSales->transaction->total;
        $data->discount = $data->trSalesNetto->trSales->transaction->discount;
        $data->grandTotal = $data->trSalesNetto->trSales->transaction->grand_total;
        $data->payment = $data->payment == null ? 0 : $data->payment;
        $data->balance = $data->balance == null ? 0 : $data->payment;
        $data->codesId = $data->return->codes_id;
        $data->date = date('d-m-Y', strtotime($data->return->created_at));
        $data->time = date('H:i:s', strtotime($data->return->created_at));
        $data->cashierId = $data->return->cashier->id;
        $data->cashierName = Str::title($data->return->cashier->name);
        $data->qtyTotal = count($details);
        $data->details = $details;
        
        return $data;
    }

    public function readDataReturnSales($id)
    {
        $data = ModelReturnTrSales::with('return', 'medicine')->find($id);
        // dd($data);
        $details = [];
        $tmp_details = [];
        
        $tmp_details = Arr::prepend($details, $data);
        // return response()->json($tmp_details);

        foreach ($tmp_details as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->returns_id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $data->qty;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['subtotal'] = ($val_detail->price - ($val_detail->price * $val_detail->discount)) * $data->qty;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
            $details[$k_detail]['categoryName'] = $val_detail->medicine->item->category->name;
        }

        $data->personName = '';
        $data->id = $data->returns_id;
        $data->invoiceNumber = $data->returns_id;
        $data->total = $data->price * $data->qty;
        $data->discount = $data->discount;
        $data->grandTotal = ($data->price - ($data->price * $data->discount)) * $data->qty;
        $data->payment = 0;
        $data->balance = 0;
        $data->codesId = $data->return->codes_id;
        $data->date = date('d-m-Y', strtotime($data->return->created_at));
        $data->time = date('H:i:s', strtotime($data->return->created_at));
        $data->cashierId = $data->return->cashier->id;
        $data->cashierName = Str::title($data->return->cashier->name);
        $data->qtyTotal = count($details);
        $data->details = $details;
        
        return $data;
    }

    public function readDataStockOpname($id)
    {
        $data = ModelStockOpname::where('id', $id)->with('medicine', 'person')->first();

        return $data;
    }

    public function readDataStockAdjustmentsPurchase($id)
    {
        $data = ModelStockAdjustmentsTrPurchases::with('stockAdjustments', 'trPurchase', 'trPurchaseDetails')->find($id);
        // dd($data);
        $details = [];
        $tmp_details = [];
        
        $tmp_details = Arr::prepend($details, $data->trPurchaseDetails);
        // return response()->json($details);

        foreach ($tmp_details as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $data->qty;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['subtotal'] = ($val_detail->price - ($val_detail->price * $val_detail->discount)) * $data->qty;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
            $details[$k_detail]['categoryName'] = $val_detail->medicine->item->category->name;
        }

        $data->personName = '';
        $data->id = $data->stock_adjustments_id;
        $data->invoiceNumber = $data->stock_adjustments_id;
        $data->total = $data->trPurchase->transaction->total;
        $data->discount = $data->trPurchase->transaction->discount;
        $data->grandTotal = $data->trPurchase->transaction->grand_total;
        $data->payment = $data->payment == null ? 0 : $data->payment;
        $data->balance = $data->balance == null ? 0 : $data->payment;
        $data->codesId = $data->stockAdjustments->codes_id;
        $data->date = date('d-m-Y', strtotime($data->stockAdjustments->created_at));
        $data->time = date('H:i:s', strtotime($data->stockAdjustments->created_at));
        $data->cashierId = $data->stockAdjustments->cashier->id;
        $data->cashierName = Str::title($data->stockAdjustments->cashier->name);
        $data->qtyTotal = count($details);
        $data->details = $details;
        
        return $data;
    }

    public function readDataStockAdjustmentsSalesNetto($id)
    {
        $data = ModelStockAdjustmentsTrSalesNetto::with('stockAdjustments', 'trSalesNetto', 'trSalesNettoDetails')->find($id);
        // dd($data);
        $details = [];
        $tmp_details = [];
        
        $tmp_details = Arr::prepend($details, $data->trSalesNettoDetails);

        foreach ($tmp_details as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $data->qty;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['subtotal'] = ($val_detail->price - ($val_detail->price * $val_detail->discount)) * $data->qty;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
            $details[$k_detail]['categoryName'] = $val_detail->medicine->item->category->name;
        }

        $data->personName = '';
        $data->id = $data->stock_adjustments_id;
        $data->invoiceNumber = $data->stock_adjustments_id;
        $data->total = $data->trSalesNetto->trSales->transaction->total;
        $data->discount = $data->trSalesNetto->trSales->transaction->discount;
        $data->grandTotal = $data->trSalesNetto->trSales->transaction->grand_total;
        $data->payment = $data->payment == null ? 0 : $data->payment;
        $data->balance = $data->balance == null ? 0 : $data->payment;
        $data->codesId = $data->stockAdjustments->codes_id;
        $data->date = date('d-m-Y', strtotime($data->stockAdjustments->created_at));
        $data->time = date('H:i:s', strtotime($data->stockAdjustments->created_at));
        $data->cashierId = $data->stockAdjustments->cashier->id;
        $data->cashierName = Str::title($data->stockAdjustments->cashier->name);
        $data->qtyTotal = count($details);
        $data->details = $details;
        
        return $data;
    }

    public function readDataStockAdjustmentsSales($id)
    {
        $data = ModelStockAdjustmentsTrSales::with('stockAdjustments', 'medicine')->find($id);
        // dd($data);
        $details = [];
        $tmp_details = [];
        
        $tmp_details = Arr::prepend($details, $data);

        foreach ($tmp_details as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $data->qty;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['subtotal'] = ($val_detail->price - ($val_detail->price * $val_detail->discount)) * $data->qty;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
            $details[$k_detail]['categoryName'] = $val_detail->medicine->item->category->name;
        }

        $data->personName = '';
        $data->id = $data->stock_adjustments_id;
        $data->invoiceNumber = $data->stock_adjustments_id;
        $data->total = $data->price * $data->qty;
        $data->discount = $data->discount;
        $data->grandTotal = ($data->price - ($data->price * $data->discount)) * $data->qty;
        $data->payment = 0;
        $data->balance = 0;
        $data->codesId = $data->stockAdjustments->codes_id;
        $data->date = date('d-m-Y', strtotime($data->stockAdjustments->created_at));
        $data->time = date('H:i:s', strtotime($data->stockAdjustments->created_at));
        $data->cashierId = $data->stockAdjustments->cashier->id;
        $data->cashierName = Str::title($data->stockAdjustments->cashier->name);
        $data->qtyTotal = count($details);
        $data->details = $details;
        
        return $data;
    }

    public function setTitle($title) 
    {
        switch($title) {
            case 'sales_regular':
                $title = 'Penj. Regular';
                break;
            case 'sales_netto':
                $title = 'Penj. Netto';
                break;
            case 'sales_credit':
                $title = 'Penj. Kredit';
                break;
            case 'sales_mix':
                $title = 'Penj. Racik';
                break;
            case 'sales_recipe':
                $title = 'Penj. Resep';
                break;
            case 'sales_lab':
                $title = 'Penj. Lab';
                break;
            case 'purchase':
                $title = 'Pembelian';
                break;
            case 'ret_purchase':
                $title = 'Ret. Pembelian';
                break;
            case 'ret_netto':
                $title = 'Ret. Netto';
                break;
            case 'ret_sales':
                $title = 'Ret. Penjualan';
                break;
            case 'stock_opname':
                $title = 'Stok Opname';
                break;
            case 'stock_adjustments_purchase':
                $title = 'Penyesuaian Stok';
                break;
            case 'stock_adjustments_sales_netto':
                $title = 'Penyesuaian Stok';
                break;
            case 'stock_adjustments_incoming_goods':
                $title = 'Barang Masuk';
                break;
            case 'stock_adjustments_exit_goods':
                $title = 'Barang Keluar';
                break;
        }
        return $title;
    }

    public function readItemStockList(Request $request)
    {
        $date_start = $request->get('date_start');
        $sort_stock = $request->get('sort_stock');
        $sort_name = $request->get('sort_name');
        $category = explode(',', trim($request->get('category')));
        
        $items = ModelItems::select(
                    'items.id', 
                    'items.name',
                    'items.status',
                    'c.name as category',
                    'u.name as unit',
                    DB::raw('(
                        SELECT so.stock_in_physic
                        FROM stock_opnames as so 
                        WHERE so.medicines_items_id = items.id
                        AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                        ORDER BY so.created_at desc
                        LIMIT 1
                    ) as qty_stock_opname'),
                    DB::raw('(
                        SELECT created_at 
                        FROM stock_opnames as so 
                        WHERE so.medicines_items_id = items.id
                        AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                        ORDER BY so.created_at desc
                        LIMIT 1
                    ) as so_date'),
                    // DB::raw('
                    //     (
                    //         SELECT MAX(t.created_at)
                    //         FROM tr_sales_regular_details tsrd 
                    //         join transactions t on t.id = tsrd.tr_sales_regular_id
                    //         WHERE tsrd.medicines_items_id = items.id
                    //         AND t.deleted_at is null
                    //         AND t.created_at BETWEEN (
                    //             SELECT created_at 
                    //             FROM stock_opnames as so 
                    //             WHERE so.medicines_items_id = items.id
                    // ORDER BY so.created_at
                    // LIMIT 1
                    //         ) AND "'.$date_start.' 23:59:59"
                    //     ) as sales_regular_date
                    // '),
                    DB::raw('
                        (
                            SELECT COALESCE(SUM(rtp.qty), 0)
                            FROM returns_tr_purchases rtp 
                            join tr_purchase_details tpd on tpd.id = rtp.tr_purchase_details_id
                            join returns r on r.id = rtp.returns_id
                            join transactions t on t.id = rtp.tr_purchases_transactions_id
                            WHERE tpd.medicines_items_id = items.id
                            AND t.deleted_at is null
                            AND r.created_at BETWEEN (
                                SELECT created_at 
                                FROM stock_opnames as so 
                                WHERE so.medicines_items_id = items.id
                                AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                                ORDER BY so.created_at desc
                                LIMIT 1
                            ) AND "'.$date_start.' 23:59:59"
                        ) as qty_ret_purchases
                    '),
                    DB::raw('
                        (
                            SELECT COALESCE(SUM(rtsn.qty), 0)
                            FROM returns_tr_sales_netto rtsn 
                            join tr_sales_netto_details tsnd on tsnd.id = rtsn.tr_sales_details_id
                            join returns r on r.id = rtsn.returns_id
                            join transactions t on t.id = rtsn.tr_sales_transactions_id
                            WHERE tsnd.medicines_items_id = items.id
                            AND t.deleted_at is null
                            AND r.created_at BETWEEN (
                                SELECT created_at 
                                FROM stock_opnames as so 
                                WHERE so.medicines_items_id = items.id
                                AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                                ORDER BY so.created_at desc
                                LIMIT 1
                            ) AND "'.$date_start.' 23:59:59"
                        ) as qty_ret_sales_netto
                    '),
                    DB::raw('
                        (
                            SELECT COALESCE(SUM(rts.qty), 0)
                            FROM returns_tr_sales rts 
                            join returns r on r.id = rts.returns_id
                            WHERE rts.medicines_items_id = items.id
                            AND r.created_at BETWEEN (
                                SELECT created_at 
                                FROM stock_opnames as so 
                                WHERE so.medicines_items_id = items.id
                                AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                                ORDER BY so.created_at desc
                                LIMIT 1
                            ) AND "'.$date_start.' 23:59:59"
                        ) as qty_ret_sales
                    '),
                    DB::raw('
                        (
                            SELECT COALESCE(SUM(tpd.qty), 0)
                            FROM stock_adjustments_tr_purchases satp 
                            join tr_purchase_details tpd on tpd.id = satp.tr_purchase_details_id
                            join stock_adjustments sa on sa.id = satp.stock_adjustments_id
                            join transactions t on t.id = satp.tr_purchases_transactions_id
                            WHERE tpd.medicines_items_id = items.id
                            AND t.deleted_at is null
                            AND sa.created_at BETWEEN (
                                SELECT created_at 
                                FROM stock_opnames as so 
                                WHERE so.medicines_items_id = items.id
                                AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                                ORDER BY so.created_at desc
                                LIMIT 1
                            ) AND "'.$date_start.' 23:59:59"
                        ) as qty_sa_purchases
                    '),
                    DB::raw('
                        (
                            SELECT COALESCE(SUM(tsnd.qty), 0)
                            FROM stock_adjustments_tr_sales_netto satsn 
                            join tr_sales_netto_details tsnd on tsnd.id = satsn.tr_sales_transactions_id
                            join stock_adjustments sa on sa.id = satsn.stock_adjustments_id
                            join transactions t on t.id = satsn.tr_sales_transactions_id
                            WHERE tsnd.medicines_items_id = items.id
                            AND t.deleted_at is null
                            AND sa.created_at BETWEEN (
                                SELECT created_at 
                                FROM stock_opnames as so 
                                WHERE so.medicines_items_id = items.id
                                AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                                ORDER BY so.created_at desc
                                LIMIT 1
                            ) AND "'.$date_start.' 23:59:59"
                        ) as qty_sa_sales_netto
                    '),
                    DB::raw('
                        (
                            SELECT COALESCE(SUM(sats.qty), 0)
                            FROM stock_adjustments_tr_sales sats 
                            join stock_adjustments r on r.id = sats.stock_adjustments_id
                            WHERE sats.medicines_items_id = items.id
                            AND r.created_at BETWEEN (
                                SELECT created_at 
                                FROM stock_opnames as so 
                                WHERE so.medicines_items_id = items.id
                                AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                                ORDER BY so.created_at desc
                                LIMIT 1
                            ) AND "'.$date_start.' 23:59:59"
                        ) as qty_sa_sales
                    '),
                    DB::raw('
                        (
                            SELECT COALESCE(SUM(tsrd.qty), 0)
                            FROM tr_sales_regular_details tsrd 
                            join transactions t on t.id = tsrd.tr_sales_regular_id
                            WHERE tsrd.medicines_items_id = items.id
                            AND t.deleted_at is null
                            AND t.created_at BETWEEN (
                                SELECT created_at 
                                FROM stock_opnames as so 
                                WHERE so.medicines_items_id = items.id
                                AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                                ORDER BY so.created_at desc
                                LIMIT 1
                            ) AND "'.$date_start.' 23:59:59"
                        ) as qty_sales_regular
                    '),
                    DB::raw('
                        (
                            SELECT COALESCE(SUM(tsmd.qty), 0)
                            FROM tr_sales_mix_details tsmd 
                            join tr_sales_mix_medicines tsmm on tsmm.id = tsmd.tr_sales_mix_medicines_id
                            join transactions t on t.id = tsmm.tr_sales_mix_id
                            WHERE tsmd.medicines_items_id = items.id
                            AND t.deleted_at is null
                            AND t.created_at BETWEEN (
                                SELECT created_at 
                                FROM stock_opnames as so 
                                WHERE so.medicines_items_id = items.id
                                AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                                ORDER BY so.created_at desc
                                LIMIT 1
                            ) AND "'.$date_start.' 23:59:59"
                        ) as qty_sales_mix
                    '),
                    DB::raw('
                        (
                            SELECT COALESCE(SUM(tsrd.qty), 0)
                            FROM tr_sales_recipe_details tsrd 
                            join tr_sales_recipe_medicines tsrm on tsrm.id = tsrd.tr_sales_recipe_medicines_id
                            join transactions t on t.id = tsrm.tr_sales_recipe_id
                            WHERE tsrd.medicines_items_id = items.id
                            AND t.deleted_at is null
                            AND t.created_at BETWEEN (
                                SELECT created_at 
                                FROM stock_opnames as so 
                                WHERE so.medicines_items_id = items.id
                                AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                                ORDER BY so.created_at desc
                                LIMIT 1
                            ) AND "'.$date_start.' 23:59:59"
                        ) as qty_sales_recipe
                    '),
                    DB::raw('
                        (
                            SELECT COALESCE(SUM(tsld.qty), 0)
                            FROM tr_sales_lab_details tsld 
                            join transactions t on t.id = tsld.tr_sales_lab_id
                            WHERE tsld.medicines_items_id = items.id
                            AND t.deleted_at is null
                            AND t.created_at BETWEEN (
                                SELECT created_at 
                                FROM stock_opnames as so 
                                WHERE so.medicines_items_id = items.id
                                AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                                ORDER BY so.created_at desc
                                LIMIT 1
                            ) AND "'.$date_start.' 23:59:59"
                        ) as qty_sales_lab
                    '),
                    DB::raw('
                        (
                            SELECT COALESCE(SUM(tsnd.qty), 0)
                            FROM tr_sales_netto_details tsnd 
                            join transactions t on t.id = tsnd.tr_sales_netto_id
                            WHERE tsnd.medicines_items_id = items.id
                            AND t.deleted_at is null
                            AND t.created_at BETWEEN (
                                SELECT created_at 
                                FROM stock_opnames as so 
                                WHERE so.medicines_items_id = items.id
                                AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                                ORDER BY so.created_at desc
                                LIMIT 1
                            ) AND "'.$date_start.' 23:59:59"
                        ) as qty_sales_netto
                    '),
                    DB::raw('
                        (
                            SELECT COALESCE(SUM(tscd.qty), 0)
                            FROM tr_sales_credit_details tscd 
                            join transactions t on t.id = tscd.tr_sales_credit_id
                            WHERE tscd.medicines_items_id = items.id
                            AND t.deleted_at is null
                            AND t.created_at BETWEEN (
                                SELECT created_at 
                                FROM stock_opnames as so 
                                WHERE so.medicines_items_id = items.id
                                AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                                ORDER BY so.created_at desc
                                LIMIT 1
                            ) AND "'.$date_start.' 23:59:59"
                        ) as qty_sales_credit
                    '),
                    DB::raw('
                        (
                            SELECT COALESCE(SUM(tpd.qty), 0)
                            FROM tr_purchase_details tpd 
                            join transactions t on t.id = tpd.tr_purchases_transactions_id
                            WHERE tpd.medicines_items_id = items.id
                            AND t.deleted_at is null
                            AND t.created_at BETWEEN (
                                SELECT created_at 
                                FROM stock_opnames as so 
                                WHERE so.medicines_items_id = items.id
                                AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
                                ORDER BY so.created_at desc
                                LIMIT 1
                            ) AND "'.$date_start.' 23:59:59"
                        ) as qty_purchases
                    ')
                )
                ->with('medicines')
                ->join('medicines as m', 'm.items_id', '=', 'items.id')
                ->join('categories as c', 'c.id', '=', 'items.categories_id')
                ->join('units as u', 'u.id', '=', 'm.units_id')
                ->whereNull('items.deleted_at')
                ->whereNotIn('items.id', ['2012001318', '2012100242', '2012100041', '2012001072', '2012001152', '2012100281'])
                ->where('items.status', 'active')
                ->whereNotIn('items.categories_id', $category)
                // ->where('id', '2012000615')
                ->orderBy('items.name', 'asc')
                ->get();

        $data = [];
        $total_asset = 0;

        foreach ($items as $key => $value) {
            $total_qty_sales = $value->qty_sales_regular + $value->qty_sales_mix + $value->qty_sales_recipe + $value->qty_sales_lab + $value->qty_sales_netto + $value->qty_sales_credit;
            $total_qty_purchases = $value->qty_purchases;
            $total_qty_so = $value->qty_stock_opname;
            $total_qty_stock_adjustments = $value->qty_sa_purchases + $value->qty_sa_sales_netto + $value->qty_sa_sales;
            $total_qty_ret_purchases = $value->qty_ret_purchases;
            $total_qty_ret_sales = $value->qty_ret_sales + $value->qty_ret_sales_netto;
            $total_asset += (($total_qty_so + $total_qty_purchases + $total_qty_ret_sales + $total_qty_stock_adjustments) - ($total_qty_ret_purchases + $total_qty_sales)) * $value->medicines->medicineDetails->price_sell;

            $data = collect($data);
            $data->push([
                "number" => $key + 1,
                "id" => $value->id,
                "name" => $value->name,
                "category" => $value->category,
                "unit" => $value->unit,
                "status" => $value->status,
                "stock" => ($total_qty_so + $total_qty_purchases + $total_qty_ret_sales + $total_qty_stock_adjustments) - ($total_qty_ret_purchases + $total_qty_sales),
                "price" => $value->medicines->medicineDetails->price_sell,
                "subtotal" => (($total_qty_so + $total_qty_purchases + $total_qty_ret_sales + $total_qty_stock_adjustments) - ($total_qty_ret_purchases + $total_qty_sales)) * $value->medicines->medicineDetails->price_sell
            ]);
            $data->all();
        }

        $data = [
            "data" => $data,
            "total_asset" => $total_asset,
            "total" => count($data),
        ];

        // return response()->json($items);

        return response()->json($data);
    }

    public function readStockOutList(Request $request) 
    {
        $date_start = $request->get('date_start');
        $date_end = $request->get('date_end');
        $category = $request->get('category') != '' ? explode(',', trim($request->get('category'))) : '';
        $medicine_id = $request->get('medicine_id');

        $items = ModelStockAdjustmentsTrSales::select(
            'sa.id as id',
            'stock_adjustments_tr_sales.note as note_id',
            DB::raw('
                CASE
                WHEN stock_adjustments_tr_sales.note = 1 THEN "Salah Input Data"
                WHEN stock_adjustments_tr_sales.note = 2 THEN "Barang Kadaluarsa"
                ELSE "Barang Rusak"
                END
                as note
            '),
            'i.id as sku',
            'i.name as name',
            'c.name as category',
            'stock_adjustments_tr_sales.qty as qty',
            'stock_adjustments_tr_sales.price as price',
            DB::raw('(stock_adjustments_tr_sales.qty * stock_adjustments_tr_sales.price) as subtotal'),
            'sa.created_at',
            'p.name as cashier_name'
        )
        ->join('stock_adjustments as sa', 'sa.id', '=', 'stock_adjustments_tr_sales.stock_adjustments_id')
        ->join('persons as p', 'p.id', '=', 'sa.users_persons_id')
        ->join('medicines as m', 'm.items_id', '=', 'stock_adjustments_tr_sales.medicines_items_id')
        ->join('items as i', 'i.id', '=', 'stock_adjustments_tr_sales.medicines_items_id')
        ->join('categories as c', 'c.id', '=', 'i.categories_id')
        ->where('stock_adjustments_tr_sales.qty', '<', 0)
        ->where(function($q) use ($date_start, $date_end) {
            if(!empty($date_start) && !empty($date_end)) {
                $q->whereBetween(DB::raw('DATE_FORMAT(sa.created_at, "%Y-%m-%d")'), [$date_start, $date_end]);
            }
        })
        ->where(function($q) use ($category) {
            if(!empty($category)) {
                $q->whereNotIn('c.id', $category);
            }
        })
        ->orderBy('sa.created_at', 'desc')
        ->get();

        $total = 0;
        $total_items = 0;
        foreach ($items as $key => $value) {
            $total += $value->subtotal;
            $total_items += $value->qty;
        }

        $data = [
            'data' => $items,
            'total' => $total,
            'total_items' => $total_items,
        ];

        return $data;
    }

    public function readStockInList(Request $request) 
    {
        $date_start = $request->get('date_start');
        $date_end = $request->get('date_end');
        $category = $request->get('category') != '' ? explode(',', trim($request->get('category'))) : '';
        $medicine_id = $request->get('medicine_id');

        $items = ModelStockAdjustmentsTrSales::select(
            'sa.id as id',
            'stock_adjustments_tr_sales.note as note_id',
            DB::raw('
                CASE
                WHEN stock_adjustments_tr_sales.note = 1 THEN "Salah Input Data"
                WHEN stock_adjustments_tr_sales.note = 2 THEN "Barang Kadaluarsa"
                ELSE "Barang Rusak"
                END
                as note
            '),
            'i.id as sku',
            'i.name as name',
            'c.name as category',
            'stock_adjustments_tr_sales.qty as qty',
            'stock_adjustments_tr_sales.price as price',
            DB::raw('(stock_adjustments_tr_sales.qty * stock_adjustments_tr_sales.price) as subtotal'),
            'sa.created_at',
            'p.name as cashier_name'
        )
        ->join('stock_adjustments as sa', 'sa.id', '=', 'stock_adjustments_tr_sales.stock_adjustments_id')
        ->join('persons as p', 'p.id', '=', 'sa.users_persons_id')
        ->join('medicines as m', 'm.items_id', '=', 'stock_adjustments_tr_sales.medicines_items_id')
        ->join('items as i', 'i.id', '=', 'stock_adjustments_tr_sales.medicines_items_id')
        ->join('categories as c', 'c.id', '=', 'i.categories_id')
        ->where('stock_adjustments_tr_sales.qty', '>=', 0)
        ->where(function($q) use ($date_start, $date_end) {
            if(!empty($date_start) && !empty($date_end)) {
                $q->whereBetween(DB::raw('DATE_FORMAT(sa.created_at, "%Y-%m-%d")'), [$date_start, $date_end]);
            }
        })
        ->where(function($q) use ($category) {
            if(!empty($category)) {
                $q->whereNotIn('c.id', $category);
            }
        })
        ->orderBy('sa.created_at', 'desc')
        ->get();

        $total = 0;
        $total_items = 0;
        foreach ($items as $key => $value) {
            $total += $value->subtotal;
            $total_items += $value->qty;
        }

        $data = [
            'data' => $items,
            'total' => $total,
            'total_items' => $total_items,
        ];

        return $data;
    }
}
