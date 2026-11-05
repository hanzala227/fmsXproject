<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OceanBooking extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'ocean_bookings';

    protected $fillable = [
        'company_id',
        'booking_no',
        'booking_date',
        'quotation_no',
        'itn_no',
        'sales_person_id',
        'op_id',
        'carrier_bkg_no',
        'customer_id',
        'carrier_id',
        'ship_mode',
        'svc_term_from_id',
        'svc_term_to_id',
        'incoterms',
        'actual_shipper_id',
        'bill_to_id',
        'consignee_id',
        'notify_id',
        'shipping_agent',
        'hbl_agent_id',
        'forwarding_agent_id',
        'co_loader_id',
        'vessel_id',
        'voyage',
        'por_id',
        'pol_id',
        'pod_id',
        'del_id',
        'fdest_id',
        'etd',
        'eta',
        'office_id',
        'cargo_type',
        'trucker_id',
        'container_no',
        'marks',
        'description',
        'remarks',
        'pkg_qty',
        'weight_kg',
        'measure_cbm',
        'status',
        'color',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'ref_no',
        'is_hold',
        'hold_by_id',
        'is_freight_released',
        'freight_released_by_id',
        'buying_freight',
        'selling_freight',
        'customs_broker_id',
        'por_etd',
        'sales_type',
        'on_board_date',
        'lc_no',
        'lc_issue_bank',
        'lc_issue_date',
        'is_express_bl',
        'show_preferences',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'etd'          => 'date',
        'eta'          => 'date',
        'por_etd'      => 'date',
        'on_board_date'=> 'date',
        'lc_issue_date'=> 'date',
        'pkg_qty'      => 'decimal:2',
        'weight_kg'    => 'decimal:3',
        'measure_cbm'  => 'decimal:3',
        'is_hold'      => 'boolean',
        'is_freight_released' => 'boolean',
        'is_express_bl' => 'boolean',
        'show_preferences' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(TradePartner::class, 'customer_id');
    }

    public function carrier()
    {
        return $this->belongsTo(TradePartner::class, 'carrier_id');
    }

    public function vessel()
    {
        return $this->belongsTo(Vessel::class, 'vessel_id');
    }

    public function pol()
    {
        return $this->belongsTo(Port::class, 'pol_id');
    }

    public function pod()
    {
        return $this->belongsTo(Port::class, 'pod_id');
    }

    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function op()
    {
        return $this->belongsTo(User::class, 'op_id');
    }

    public function svcTermFrom()
    {
        return $this->belongsTo(ServiceTerm::class, 'svc_term_from_id');
    }

    public function svcTermTo()
    {
        return $this->belongsTo(ServiceTerm::class, 'svc_term_to_id');
    }

    public function actualShipper()
    {
        return $this->belongsTo(TradePartner::class, 'actual_shipper_id');
    }

    public function billTo()
    {
        return $this->belongsTo(TradePartner::class, 'bill_to_id');
    }

    public function consignee()
    {
        return $this->belongsTo(TradePartner::class, 'consignee_id');
    }

    public function notify()
    {
        return $this->belongsTo(TradePartner::class, 'notify_id');
    }

    public function hblAgent()
    {
        return $this->belongsTo(TradePartner::class, 'hbl_agent_id');
    }

    public function forwardingAgent()
    {
        return $this->belongsTo(TradePartner::class, 'forwarding_agent_id');
    }

    public function coLoader()
    {
        return $this->belongsTo(TradePartner::class, 'co_loader_id');
    }

    public function por()
    {
        return $this->belongsTo(Port::class, 'por_id');
    }

    public function del()
    {
        return $this->belongsTo(Port::class, 'del_id');
    }

    public function fdest()
    {
        return $this->belongsTo(Port::class, 'fdest_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function trucker()
    {
        return $this->belongsTo(TradePartner::class, 'trucker_id');
    }

    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }

    public function customsBroker()
    {
        return $this->belongsTo(TradePartner::class, 'customs_broker_id');
    }

    public function holdBy()
    {
        return $this->belongsTo(User::class, 'hold_by_id');
    }

    public function freightReleasedBy()
    {
        return $this->belongsTo(User::class, 'freight_released_by_id');
    }
}
