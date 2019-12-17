<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use DateTime;

class pu_po_estimateController extends Controller
{
  private $PlanID =0;
  private $VendorID = 0;
  
  private $Eta ;

  private $POEstimateID=0;
  private $avcwh_level=0;
  
  private $StartSellingDate;
  private $EndSellingDate;
  private $EndSellingDateOnFBA;

  private $BeginInventoryAtY4ANow = 0;

  private $BeginInventoryOnAVC_WH = 0;
  private $BeginInventoryOnFBA = 0 ;
  private $BeginInventoryOnY4A = 0 ;

  private $ForeCastOnAVC_WH = 0;
  private $ForeCastOnFBA = 0 ;
  private $ForeCastOnY4A = 0 ;


  private $SoThieu = 0 ;
  private $SuggestOrder = 0;
  private $sku ='' ;
  private $POID= 0;
  private $CurrentDate;  
  
  private $CurrentWeek,$CurrentYear ;
  private $FromWeek,$FromYear,$ToWeek,$ToYear;

 

  public  function ShowPODetail($MyPOID)
  {
   // $POs=DB::select("select pu_po_estimates.id , vendors.title , pu_po_estimates.the_week,  pu_po_estimates.the_year from pu_po_estimates INNER JOIN
   // vendors on pu_po_estimates.vendor_id = vendors.id");

   $PODetails = DB::select("select pu_po_estimate_details.sku, products.title ,GetSellType(pu_po_estimate_details.sell_type)as sell_type, 
   pu_po_estimate_details.balance,pu_po_estimate_details.balance_at_selling,
   pu_po_estimate_details.fob_price,pu_po_estimate_details.moq,pu_po_estimate_details.quantity
   from pu_po_estimate_details inner JOIN
   products on pu_po_estimate_details.sku = products.product_sku
   WHERE pu_po_estimate_details.po_estimate_id = $MyPOID ");
  return view('layouts.PU-PODetail',compact('PODetails'));     
    
  }

  public function ShowPOList()
  {
    $POs=DB::select("select pu_po_estimates.id , vendors.title , pu_po_estimates.the_week,  pu_po_estimates.the_year from pu_po_estimates INNER JOIN
    vendors on pu_po_estimates.vendor_id = vendors.id");

    $PODetails = DB::select("select pu_po_estimate_details.sku from pu_po_estimate_details where 1=0");
    return view('layouts.PU-POList',compact('POs','PODetails'));     
  }
    /*
    Đầu vào: Data Forecast của sales, Kế hoạch mua hang của PU
    Đầu ra: Tạo ra các PO estimate cho các tuần chưa tạo, mỗi tuần, mỗi vendor nếu có trong plan sẽ tạo một PO   */
    public function CreateAllPOEstimate()
    {
      // xóa toàn bộ po estimate
      DB::table('pu_po_estimates')->delete();
      DB::table('pu_po_estimate_details')->delete();

      // Load toàn bộ plan của PU những tuần chưa tạo PO estimate
      //$Plans =   DB::table('pu_plannings') ->select ('id','vendor_id','order_week','order_date','eta','end_selling_date') 
      $Plans =   DB::table('pu_plannings') 
      ->select ('id','order_week','at_year','vendor_id','eta','end_selling_date') 
      ->where ('status_id','=',1) 
      ->get();
      foreach ($Plans as $plan) {
        $this->PlanID = $plan->id;
        $this->VendorID = $plan->vendor_id;
        $this->TheWeek= $plan->order_week;
        $this->TheYear= $plan->at_year;
        $this->Eta = $plan->eta;
        $this->StartSellingDate = $this->Eta ; // Tính tồn kho cuối ngày trước ngày dự tính bán một ngày
        $this->EndSellinfDate = $plan->end_selling_date;
        $this->CreatePOForOneWeek($this->PlanID, $this->VendorID,$this->TheWeek,$this->TheYear,$this->StartSellingDate, $this->EndSellinfDate);
      }
  }

  // ------------------------------------------------------------------------------
  public function GetRangeToGetDataForecast()// xác định thời gian cần lấy data forecast
  {
    $this->CurrentDate = date("Y-m-d"); 
    $CurentWeek = date('W', strtotime($this->CurrentDate));
    $CurentYear = date('Y', strtotime($this->CurrentDate));
    $RuleLeadTime = 1;// set rule tính từ tuần hiện tại thì chỉ lấy data FC của 1 tuần sau
    $RuleLenthOfWeek = 21;// set rule độ dài tuần của data forecast là 21 tuần là tối đa 
   
    for($i=1;$i<= $RuleLeadTime;$i++){
      if ($CurentWeek == 52){
        $CurentWeek = 1;
        $CurentYear = $CurentYear+1;
      }
      else{
        $CurentWeek = $CurentWeek +1;
      }
    }    

    $this->FromWeek = $CurentWeek;
    $this->FromYear = $CurentYear;

    for($i=1;$i<=$RuleLenthOfWeek;$i++){
      if ($CurentWeek==52){
        $CurentWeek =1;
        $CurentYear++;
      }
      else{
        $CurentWeek ++;
      }
    }
    $this->ToWeek=$CurentWeek;

    $this->ToYear = $CurentYear;
  }
// ------------------------------------------------------------------------------
  public function ConvertForeCastMaster()
  {
    // xóa data forecast 
    DB::table('sal_forecasts')->delete();
  
    $ForeCasts =DB::table('product_sales_forecast_3')
    ->join('products','products.id','=','product_sales_forecast_3.product_id')
    ->select('product_sales_forecast_3.id','products.Product_sku','product_sales_forecast_3.channel')
    ->where([
    ['products.published','=',1],
    ['products.purchasing','=',1],
    ['product_sales_forecast_3.channel','>',0] // cai nay hoi khoa tai sao co chanel id = 0
    ])->get();
    
    foreach($ForeCasts as $ForeCast){
      // insert sal_forecasts
      $id= $ForeCast->id;
      
      DB::table('sal_forecasts')->insert(
      ['chanel_id' => $ForeCast->channel,'sku'=>$ForeCast->Product_sku,'forecast_id'=> $ForeCast->id]);
      }
      return redirect()->back();
  }

// ------------------------------------------------------------------------------
  public function ConvertForeCastDetail()// 
  {
    DB::table('sal_forecast_details')->delete();

    $this->GetRangeToGetDataForecast();
    $ForeCasts =DB::table('sal_forecasts')
    ->select('sal_forecasts.forecast_id','sal_forecasts.id')
    ->get();
    
    foreach($ForeCasts as $ForeCast){
      $TheForeCastMasterID = $ForeCast->id;
      $OrgID = $ForeCast->forecast_id;
      // load year
      $Years= DB::table('product_sales_forecast_detail_3')
      ->select('product_sales_forecast_detail_3.year')
      ->where([
      ['product_sales_forecast_detail_3.product_sales_forecast_id','=', $OrgID],
      ['product_sales_forecast_detail_3.year','>=',$this->FromYear],
      ['product_sales_forecast_detail_3.year','<=',$this->ToYear]
      ])->get();
      foreach( $Years as  $Year)  {
        $ForeCastDetails=DB::table('product_sales_forecast_detail_3')
        ->select('product_sales_forecast_detail_3.forecast')
        ->where([
        ['product_sales_forecast_detail_3.product_sales_forecast_id','=', $OrgID],
        ['product_sales_forecast_detail_3.year','=', $Year->year]
        ])->get();

        $MyArr =  json_decode($ForeCastDetails,true); 
        $MyArr =  json_decode($MyArr[0]['forecast'],true); 
        foreach($MyArr  as  $key=>$val){
          $TheWeek = $key ;
          $TheQuantity = $val['qty']/7;
          if ( $TheQuantity > 0){
            if( ($TheWeek >= $this->FromWeek and $TheWeek <= 52  and  $Year->year >= $this->FromYear and $Year->year <=$this->ToYear)
            or ($TheWeek <= $this->ToWeek and $TheWeek >=1  and  $Year->year >= $this->FromYear and $Year->year <=$this->ToYear)) {  

              $this->InsertToForeCastDetail($Year->year,$TheWeek,$TheQuantity,$TheForeCastMasterID);

            }
          }
        }
     }
    }
    return redirect()->back();
  }

  // -----------------------------------------------------------------------------------------------
  public function InsertToForeCastDetail($TheYear,$TheWeek,$TheQuantity,$ForeCastID)
  {
    $dto = new DateTime(); 
    $dto->setISODate($TheYear, $TheWeek);
    for($i=1;$i<=7;$i++){

      $ret[$i] = $dto->format('Y-m-d');
      $ret[$i]= date('Y-m-d',strtotime( $ret[1]. '+'.$i.'days'));
      if ($this->sku =='9E00'){
        print_f( $ret[$i]); 
      }

      DB::table('sal_forecast_details')->insert(
      ['sales_forecast_id' => $ForeCastID,'the_date'=> $ret[$i],'quantity'=>$TheQuantity]); 
    }
  }
  
  // -----------------------------------------------------------------------------------------------
  public function GetBalance( $sku, $StartSellingDate )
  {
    $Y4AStoreID = 46;
    // Get Balance in stores(AVCWH,FBA,Y4A) at now => get current date :Ldate("Y-m-d")
    /*
    $Balances = DB::select ('call PU_GetBalanceOnWH(?,?,?,?,?)',[$sku,$StartSellingDate,0,0,0]);
    foreach($Balances as $Balance){
    $this->BeginInventoryOnY4A = $Balance->BalanceY4A;
    $this->BeginInventoryOnAVC_WH = $Balance->BalanceAVCWH;
    $this->BeginInventoryOnFBA = $Balance->BalanceFBA;
    */
    //-- and warehouse_id ='. $Y4AStoreID.' AND p.published = 1
   // and p.product_sku =  '.$sku .'

    // tinh so ton kho hien tai cua y4a
    $BalanceY4As = DB::select("select sum(if(a.quantity is not null , a.quantity,0)) as  quantity
    from 
    (
    SELECT if(inv.quantity > 0, inv.quantity,0) as quantity
    FROM productinventory AS inv
    LEFT JOIN products AS p ON inv.product_id = p.id
    WHERE p.sell_type = 1  
    and p.published = 1
    and p.product_sku = '$sku'
    and warehouse_id = $Y4AStoreID 

    union
    
    SELECT sum(prdc.quantity *  if(inv.quantity >=0,inv.quantity,0)) as quantity
    FROM productinventory AS inv
    LEFT JOIN products AS p ON inv.product_id = p.id
    inner join  productcombo prdc on p.id = prdc.product_id
    inner join products as p2 on prdc.child_id = p2.id
    WHERE p.sell_type in (2,3) 
    and p.published = 1
    and p.product_sku =  '$sku'
    and warehouse_id = $Y4AStoreID 
    )a");


    foreach( $BalanceY4As as $BalanceY4A ){
      $this->BeginInventoryAtY4ANow = $BalanceY4A->quantity;
      $this->BeginInventoryOnY4A = $BalanceY4A->quantity;
      //print_r('Y4A now: '. $this->BeginInventoryOnY4A .'<br>' );
    }
  
    //  ---------------- tinh so reserved hien tai cua kho y4a -------------------------------
    $ReserY4As = DB::select("
    select sum(if(a.quantity is null , 0,a.quantity)) as quantity  FROM
    (
    SELECT sum(if(inv_r.quantity > 0,inv_r.quantity,0)) as quantity
    FROM productinventory_reserved AS inv_r
    LEFT JOIN products AS p ON inv_r.product_id = p.id
    WHERE p.published = 1 
    and p.sell_type = 1
    and p.product_sku = '$sku'
    union 
    SELECT sum(if(inv_r.quantity > 0,inv_r.quantity,0)*prdc.quantity)
    FROM productinventory_reserved AS inv_r
    LEFT JOIN products AS p ON inv_r.product_id = p.id
    inner join  productcombo prdc on p.id = prdc.product_id
    inner join products as p2 on prdc.child_id = p.id
    WHERE p.published = 1 
    and p.sell_type in(2,3) 
    and p2.product_sku = '$sku'
    )a"); 
    foreach( $ReserY4As as $ReserY4A ){
      $this->BeginInventoryOnY4A =  $this->BeginInventoryOnY4A - $ReserY4A->quantity;
      //print_r('Y4A Reser: '.$ReserY4A->quantity .'<br>' );
    }


    // ---------------- tinh so pipeline hien tai cua kho y4a -------------------------------
    $PielineY4As = DB::select("
    select sum(if(a.quantity is null,0,a.quantity)) as quantity from
    (
    select sum(if(smcondt.quantity > 0,smcondt.quantity,0)) as quantity from shipment sm inner join
    shipment_containers smcon on sm.id =smcon.shipment_id  inner JOIN
    shipment_containers_details  smcondt on smcon.id = smcondt.container_id inner JOIN
    products prd on smcondt.product_id =prd.id
    where  sm.status in (2,3) -- trang thai cua shipment
    and smcon.status in (1,8) -- trang thai cua container nu them 11 la trang thai hoan tat container
    and prd.product_sku = '$sku'
    
    union 
        
    select sum(smcondt.quantity * prdc.quantity) as quantity
    from shipment sm inner join
    shipment_containers smcon on sm.id =smcon.shipment_id  inner JOIN
    shipment_containers_details  smcondt on smcon.id = smcondt.container_id inner JOIN
    products prd on smcondt.product_id =prd.id
    inner join  productcombo prdc on prd.id = prdc.product_id
    inner join products as p2 on prdc.child_id = p2.id
    where  sm.status in (2,3) -- trang thai cua shipment
    and smcon.status in (1,8) -- trang thai cua container nu them 11 la trang thai hoan tat container
    and p2.product_sku = '$sku'
    and prd.sell_type in (2,3)  -- combo/mutil 
    )a");
    
    foreach( $PielineY4As as $PielineY4A ){
      $this->BeginInventoryOnY4A =  $this->BeginInventoryOnY4A + $PielineY4A->quantity;
      //print_r('Y4A Pipelie: '.$PielineY4A->quantity .'<br>' );
    }


    
    // ---------------- tinh so forecast cua kho y4a -------------------------------
    $ForecastY4As = DB::select("
    select sum(if( a.quantity is null,0,a.quantity )) as quantity FROM
    (
    select sum(fcdt.quantity) as quantity from sal_forecasts  fc INNER JOIN
    sal_forecast_details fcdt on fc.id = fcdt.sales_forecast_id INNER JOIN
    products p on  p.product_sku = fc.sku
    where  fcdt.the_date >=  CURDATE()
    and fcdt.the_date <= '$StartSellingDate'
    and fc.sku = '$sku'
    and fc.chanel_id not in (1,3) 
    and p.sell_type = 1  
    union
    select sum(fcdt.quantity * prdc.quantity) as quantity from sal_forecasts  fc 
    INNER JOIN sal_forecast_details fcdt on fc.id = fcdt.sales_forecast_id 
    INNER JOIN products p on fc.sku = p.product_sku  
    inner join  productcombo prdc on p.id = prdc.product_id
    inner join products as p2 on prdc.child_id = p2.id
    inner join prd_sell_types st on p.sell_type = st .id
    where  fcdt.quantity > 0
    and fcdt.the_date >=  CURDATE()
    and fcdt.the_date <= '$StartSellingDate'
    and p2.product_sku = '$sku'
    and fc.chanel_id not in (1,3) 
    and p.sell_type in (2,3) 
    ) a");
    foreach( $ForecastY4As as $ForecastY4A ){
      $this->BeginInventoryOnY4A =  $this->BeginInventoryOnY4A - $ForecastY4A->quantity;
      //print_r('Y4A Forecast: '.$ForecastY4A->quantity .'<br>' );
      //print_r('Balance Y4A: '.$this->BeginInventoryOnY4A  .'<br>' );
    }

    // --------------Tính so tồn kho hiện tại của FBA------------------
    $BalanceFBAs = DB::select("
    select sum(if(a.quantity is null,0,a.quantity)) as quantity  FROM
    (
    SELECT
    sum(
    if(afn_fulfillable_quantity is null or afn_fulfillable_quantity <0 ,0,afn_fulfillable_quantity) 
    + if( afn_reserved_quantity is null or afn_reserved_quantity <0 ,0, afn_reserved_quantity)
    + if(afn_inbound_shipped_quantity is null or afn_inbound_shipped_quantity < 0 ,0,afn_inbound_shipped_quantity) 
    + if(afn_inbound_receiving_quantity is null or afn_inbound_receiving_quantity<0 ,0,afn_inbound_receiving_quantity) 
    ) as quantity
    FROM fba_inventory AS inv
    LEFT JOIN products AS p ON inv.product_id = p.id
    WHERE p.published = 1
    and p.sell_type =1
    and p.product_sku = '$sku'
    
    union
    
    SELECT
    sum((
    if(afn_fulfillable_quantity is null or afn_fulfillable_quantity <0 ,0,afn_fulfillable_quantity) 
    + if( afn_reserved_quantity is null or afn_reserved_quantity <0 ,0, afn_reserved_quantity)
    + if(afn_inbound_shipped_quantity is null or afn_inbound_shipped_quantity < 0 ,0,afn_inbound_shipped_quantity) 
    + if(afn_inbound_receiving_quantity is null or afn_inbound_receiving_quantity<0 ,0,afn_inbound_receiving_quantity) 
    ) * prdc.quantity ) as quantity
    FROM fba_inventory AS inv
    LEFT JOIN products AS p ON inv.product_id = p.id
    inner join  productcombo prdc on p.id = prdc.product_id
    inner join products as p2 on prdc.child_id = p2.id
    
    WHERE p.published = 1
    and p.sell_type in (2,3)
    and p2.product_sku ='$sku'
    )a");

    foreach( $BalanceFBAs as $BalanceFBA ){
      $this->BeginInventoryOnFBA  =  $BalanceFBA->quantity;
      //print_r('BalanceFBA: '.$BalanceFBA->quantity .'<br>' );
    }
    
    // ---------Tìm số Forecast của FBA----------------------
    $ForeCastFBAs = DB::select("
    select sum(if(a.quantity is null,0,a.quantity)) as quantity from 
    (
    select sum(fcdt.quantity) as quantity from sal_forecasts  fc
    INNER JOIN sal_forecast_details fcdt on fc.id = fcdt.sales_forecast_id 
    INNER JOIN products prd on fc.sku = prd.product_sku
    where  prd.sell_type = 1 -- single
    and fcdt.the_date >=  CURDATE()
    and fcdt.the_date <=  '$StartSellingDate'
    and fc.sku = '$sku'
    and fc.chanel_id in (1) 
    
    union 
    
    select sum(fcdt.quantity * prdc.quantity ) as quantity from sal_forecasts  fc
    INNER JOIN sal_forecast_details fcdt on fc.id = fcdt.sales_forecast_id 
    INNER JOIN products prd on fc.sku = prd.product_sku
    INNER JOIN productcombo prdc on prd.id = prdc.product_id
    INNER JOIN products p2 on prdc.child_id = p2.id
    
    where  prd.sell_type in(2,3) -- combo/mutilple
    and fcdt.the_date  >=  CURDATE()
    and fcdt.the_date  <= '$StartSellingDate'
    and p2.product_sku =  '$sku'
    and fc.chanel_id in (1) -- FBA
    )a");
    foreach($ForeCastFBAs as $ForeCastFBA ){
      $this->BeginInventoryOnFBA  =  $this->BeginInventoryOnFBA - $ForeCastFBA->quantity ;
      //print_r('Forecast FBA: '.$ForeCastFBA->quantity .'<br>' );
    }
    
    
    // --------Tim so ton cua kenh AVC-WH ----------------
    $BalanceAVC_WHs = DB::select("
    select sum(if(a.quantity is null,0,a.quantity)) as quantity FROM

    (
    SELECT sum(inv.sellable_unit - inv.purchase_qty) as quantity
    FROM amazon_avc_inventory AS inv
    LEFT JOIN products AS p ON inv.product_id = p.id
    WHERE report_date = (SELECT MAX(report_date) FROM amazon_avc_inventory)
    and p.sell_type = 1 -- single
    and  p.product_sku  =  '$sku'
    and  inv.sellable_unit > inv.purchase_qty

    union 

    SELECT sum((inv.sellable_unit - inv.purchase_qty)* prdc.quantity) as quantity
    FROM amazon_avc_inventory AS inv
    LEFT JOIN products AS p ON inv.product_id = p.id

    inner join  productcombo prdc on p.id = prdc.product_id
    inner join products as p2 on prdc.child_id = p.id

    WHERE report_date = (SELECT MAX(report_date) FROM amazon_avc_inventory)
    and p.sell_type in(2,3) -- combo/mutilple
    and  p2.product_sku  =  '$sku'
    and  inv.sellable_unit > inv.purchase_qty
    )a"); 

    foreach($BalanceAVC_WHs as $BalanceAVC_WH ){
      $this->BeginInventoryOnAVC_WH = $BalanceAVC_WH->quantity ;
     // print_r('Balance AVC: '.$BalanceAVC_WH->quantity .'<br>' );
    }

    // --------Tìm số forecast của AVCWH ----------------
    $ForeCastsAVC_WHs = DB::select("
    select sum(if(a.quantity is null,0,a.quantity)) as quantity FROM
    (
    select sum(fcdt.quantity) as quantity from sal_forecasts  fc 
    INNER JOIN sal_forecast_details fcdt on fc.id = fcdt.sales_forecast_id 
    INNER JOIN products p on   fc.sku = p.product_sku
    where  fcdt.the_date >=  CURDATE()
    and fcdt.the_date <= '$StartSellingDate'
    and fc.sku = '$sku'
    and fc.chanel_id in (3) 
    and p.sell_type =1 

    union

    select sum(fcdt.quantity * prdc.quantity ) from sal_forecasts  fc 
    INNER JOIN sal_forecast_details fcdt on fc.id = fcdt.sales_forecast_id 
    INNER JOIN products p on   fc.sku = p.product_sku

    inner join  productcombo prdc on p.id = prdc.product_id
    inner join products as p2 on prdc.child_id = p.id

    where  fcdt.the_date >=  CURDATE()
    and fcdt.the_date <= '$StartSellingDate'
    and p2.product_sku = '$sku'
    and fc.chanel_id in (3) 
    and p.sell_type =1 
    )a");
    
    foreach($ForeCastsAVC_WHs as $ForeCastsAVC_WH ){
      $this->BeginInventoryOnAVC_WH =  $this->BeginInventoryOnAVC_WH -  $ForeCastsAVC_WH->quantity  ;
     // print_r('Balance AVC: '. $ForeCastsAVC_WH->quantity .'<br>' );
    }
   // print_r('====================================================================' .'<br>' );
}

  // -----------------------------------------------------------------------------------------------
  public function CreatePOForOneWeek($PlanID, $VendorID,$TheWeek,$TheYear)
  {
    
    $this->EndSellingDateOnFBA = date('Y-m-d',strtotime($this->EndSellingDate. '+ 14 days'));
    
    // Create PO master
   

    $this->POID =DB::table('pu_po_estimates')->insertGetId(
      ['vendor_id' =>  $VendorID,'the_week'=>$TheWeek, 'the_year' => $TheYear,'plan_id'=>$PlanID]
    );

    // Load Product List (break ra thanh sku single ) available for this vendor
   // $ProductListAvailables= DB::select ('call PU_GetProductListAvailable(?)',[$VendorID]);

    $ProductListAvailables= DB::select ("select (a.sku) as sku, a.sell_type as sell_type ,a.sell_status as sell_status,
    a.fob_price as fob_price,a.appotion_price as appotion_price ,a.moq as moq from
    (
      select products.product_sku  as sku, products.sell_type, products.sell_status, 
      products.fob_price,products.appotion_price,product_manufactures.moq from products INNER JOIN
      product_manufactures on products.id = product_manufactures.product_id INNER JOIN
      manufacturers_3 on product_manufactures.manufacture_id = manufacturers_3.id INNER JOIN
      vendors on manufacturers_3.vendor_id =vendors.id
      where manufacturers_3.vendor_id = $VendorID
      and products.published = 1
      and products.purchasing = 1
      and products.sell_type = 1
    union
      select products.product_sku  as sku, products.sell_type, products.sell_status, 
      products.fob_price,products.appotion_price ,product_manufactures.moq from products INNER JOIN
      product_manufactures on products.id = product_manufactures.product_id INNER JOIN
      manufacturers_3 on product_manufactures.manufacture_id = manufacturers_3.id INNER JOIN
      vendors on manufacturers_3.vendor_id =vendors.id
      where manufacturers_3.vendor_id = $VendorID
      and products.published = 1
      and products.purchasing = 1
      and products.sell_type in( 2,3) 
    ) a");



    foreach($ProductListAvailables as $ProductListAvailable){
      $SuggestOrder = 0;
     // $this->sku = $ProductListAvailable->sku;
      
      //Caculate Balance NUmber
      $this->GetBalance( $ProductListAvailable->sku,$this->StartSellingDate);// Kiếm 3 số tồn kho

      // Caculate Forecast number in stage [StartSellingDate,EndSellingDate]
      $avcwh_level = DB::table('sal_lois')->where('sku',$ProductListAvailable->sku)->value('avcwh_level');
      // số liệu  thể hiện số tuần cần trữ hàng theo FC
      $this->$avcwh_level= $avcwh_level;
      $this->GetForecastQuantity($ProductListAvailable->sku,$avcwh_level);
     

      $SuggestOrder = max(($this->ForeCastOnAVC_WH  - $this->BeginInventoryOnAVC_WH),0);
      $SuggestOrder += max(($this->ForeCastOnFBA  - $this->BeginInventoryOnFBA),0);
      $SuggestOrder += max(($this->ForeCastOnY4A  - $this->BeginInventoryOnY4A),0);

      // Lay toi thieu 30, lam tron va lay theo moq
      $SuggestOrder=max($SuggestOrder,30);
      $SuggestOrder=max($SuggestOrder,$ProductListAvailable->moq);
      
      $mystring = (string)$SuggestOrder;
      $myNumber = (int)substr($mystring, -1);
      if ($myNumber<>0 ){
        if($myNumber>5){
          $NewNum =10-$myNumber;
          $SuggestOrder=$SuggestOrder+$NewNum;
        }else{
          $SuggestOrder=$SuggestOrder- $myNumber;
        }
      }

      if( $SuggestOrder >=30 ){
        // insert to detail PO 
        DB::table('pu_po_estimate_details')->insert(
        ['po_estimate_id' => $this->POID, 
        'sku' => $ProductListAvailable->sku,
        'quantity'=>$SuggestOrder,
        'sell_type'=>$ProductListAvailable->sell_type,
        'sell_status'=>$ProductListAvailable->sell_status,
        'fob_price'=>$ProductListAvailable->fob_price,
        'appotion_price'=>$ProductListAvailable->appotion_price,
        'moq'=> $ProductListAvailable->moq,'quantity'=>$SuggestOrder,
        'balance'=>$this->BeginInventoryAtY4ANow,
        'balance_at_selling'=>$this->BeginInventoryOnY4A
        ] );
        $SuggestOrder = 0;
      }
 
    }// foreach list product
    
  }

  public function GetForecastQuantity($sku,$AVC_LOI)
  {
    $Y4AStoreID = 46;
    $SalesChanelFBA = 1;
    $SalesChanelAVC_WH = 3;
    $EndSellingDateOnAVC_WH = $this->EndSellingDate;
      

    if($AVC_LOI > 0){
      $EndSellingDateOnAVC_WH = date('Y-m-d',strtotime($this->EndSellingDate. '+'. $AVC_LOI *7 .'days'));
    }
     

    /*
    $Forecasts= DB::select ('call PU_GetForecastNumber(?,?,?,?,?)',[$sku,$StartSellingDate,$EndSellingDate,$Channel,0]);
    foreach($Forecasts as $Forecast){
      return $Forecast->Forecast;
    }
    */
    // ---------------- Tính số FC của kho Y4A -------------------------------
    
      $ForecastY4As = DB::select(
      "select sum(if( a.quantity is null,0,a.quantity )) as quantity FROM
      (
      select sum(fcdt.quantity) as quantity from sal_forecasts  fc INNER JOIN
      sal_forecast_details fcdt on fc.id = fcdt.sales_forecast_id INNER JOIN
      products p on  p.product_sku = fc.sku
      where  fcdt.the_date >=  '$this->StartSellingDate'
      and fcdt.the_date <= '$this->EndSellingDate'
      and fc.sku = '$sku'
      and fc.chanel_id not in ($SalesChanelFBA ,$SalesChanelAVC_WH) 
      and p.sell_type = 1  
      union
      select sum(fcdt.quantity * prdc.quantity) as quantity from sal_forecasts  fc 
      INNER JOIN sal_forecast_details fcdt on fc.id = fcdt.sales_forecast_id 
      INNER JOIN products p on fc.sku = p.product_sku  
      inner join  productcombo prdc on p.id = prdc.product_id
      inner join products as p2 on prdc.child_id = p2.id
      inner join prd_sell_types st on p.sell_type = st .id
      where  fcdt.quantity > 0
      and fcdt.the_date >=  '$this->StartSellingDate'
      and fcdt.the_date <= '$this->EndSellingDate'
      and p2.product_sku = '$sku'
      and fc.chanel_id not in ($SalesChanelFBA,$SalesChanelAVC_WH)  
      and p.sell_type in (2,3) 
      ) a");
      foreach( $ForecastY4As as $ForecastY4A ){
        $this->ForeCastOnY4A  = $ForecastY4A->quantity;
      }
    
    
    // ---------------- Tính số FC của kho FBA -------------------------------
    $ForecastFBAs = DB::select(
    " select sum(if(a.quantity is null,0,a.quantity)) as quantity from 
    (
    select sum(fcdt.quantity) as quantity from sal_forecasts  fc
    INNER JOIN sal_forecast_details fcdt on fc.id = fcdt.sales_forecast_id 
    INNER JOIN products prd on fc.sku = prd.product_sku
    where  prd.sell_type = 1 
    and fcdt.the_date >= '$this->StartSellingDate'
    and fcdt.the_date <= '$this->EndSellingDateOnFBA'
    and fc.sku = sku
    and fc.chanel_id in ($SalesChanelFBA) 

    union 

    select sum(fcdt.quantity * prdc.quantity ) as quantity from sal_forecasts  fc
    INNER JOIN sal_forecast_details fcdt on fc.id = fcdt.sales_forecast_id 
    INNER JOIN products prd on fc.sku = prd.product_sku
    INNER JOIN productcombo prdc on prd.id = prdc.product_id
    INNER JOIN products p2 on prdc.child_id = p2.id

    where  prd.sell_type in(2,3) 
    and fcdt.the_date >=  '$this->StartSellingDate'
    and fcdt.the_date <= '$this->EndSellingDateOnFBA'
    and p2.product_sku  = '$sku'
    and fc.chanel_id in ($SalesChanelFBA ) 
    )a");

    foreach( $ForecastFBAs as $ForecastFBA ){
      $this->ForeCastOnFBA   = $ForecastFBA->quantity;
    }
  

  // ---------------- Tính số FC của kho AVCWH -------------------------------
    $ForecastAVC_WHs = DB::select(  
    " select sum(if(a.quantity is null,0,a.quantity)) as quantity FROM
    (
    select sum(fcdt.quantity) as quantity from sal_forecasts  fc 
    INNER JOIN sal_forecast_details fcdt on fc.id = fcdt.sales_forecast_id 
    INNER JOIN products p on   fc.sku = p.product_sku
    where  fcdt.the_date >=  '$this->StartSellingDate'
    and fcdt.the_date <= '$EndSellingDateOnAVC_WH'
    and fc.sku = '$sku'
    and fc.chanel_id in ($SalesChanelAVC_WH ) 
    and p.sell_type =1 

    union

    select sum(fcdt.quantity * prdc.quantity ) as quantity from sal_forecasts  fc 
    INNER JOIN sal_forecast_details fcdt on fc.id = fcdt.sales_forecast_id 
    INNER JOIN products p on   fc.sku = p.product_sku

    inner join  productcombo prdc on p.id = prdc.product_id
    inner join products as p2 on prdc.child_id = p.id

    where  fcdt.the_date >= '$this->StartSellingDate'
    and fcdt.the_date <=  '$EndSellingDateOnAVC_WH'
    and  p2.product_sku = '$sku'
    and fc.chanel_id in ('$SalesChanelAVC_WH') 
    and p.sell_type =1 
    )a");
    foreach( $ForecastAVC_WHs as $ForecastAVC_WH ){
      $this->ForeCastOnAVC_WH = $ForecastAVC_WH->quantity;
    }
  
  }
}
