<?php
/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2019. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Models;

use App\Models\Currency;
use App\Models\Filterable;
use App\Utils\Traits\MakesDates;
use App\Utils\Traits\MakesHash;
use App\Utils\Traits\NumberFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Invoice extends BaseModel
{
    use MakesHash;
    use SoftDeletes;
    use Filterable;
    use NumberFormatter;
    use MakesDates;

    protected $hidden = [
        'id',
        'private_notes'
    ];

    protected $appends = [
        'hashed_id'
    ];

    protected $fillable = [
        'invoice_number',
        'discount',
        'po_number',
        'invoice_date',
        'due_date',
        'terms',
        'public_notes',
        'private_notes',
        'invoice_type_id',
        'tax_name1',
        'tax_rate1',
        'tax_name2',
        'tax_rate2',
        'is_amount_discount',
        'invoice_footer',
        'partial',
        'partial_due_date',
        'custom_value1',
        'custom_value2',
        'custom_value3',
        'custom_value4',
        'custom_taxes1',
        'custom_taxes2',
        'custom_text_value1',
        'custom_text_value2',
        'line_items',
        'settings',
        'client_id',
        'footer',
    ];

    protected $casts = [
        'settings' => 'object',
        'line_items' => 'object'
    ];

    protected $with = [
        'company',
        'client',
    ];

    const STATUS_DRAFT = 1;
    const STATUS_SENT = 2;
    const STATUS_PARTIAL = 3;
    const STATUS_PAID = 4;
    const STATUS_CANCELLED = 5;

    const STATUS_OVERDUE = -1;
    const STATUS_UNPAID = -2;
    const STATUS_REVERSED = -3;

    public function getHashedIdAttribute()
    {
        return $this->encodePrimaryKey($this->id);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invitations()
    {
        return $this->hasMany(InvoiceInvitation::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function payments()
    {
        return $this->morphToMany(Payment::class, 'paymentable');
    }

    public function company_ledger()
    {
        return $this->morphMany(CompanyLedger::class, 'company_ledgerable');
    }

    /* ---------------- */
    /* Settings getters */
    /* ---------------- */

    /**
     * If True, prevents an invoice from being 
     * modified once it has been marked as sent
     * 
     * @return boolean isLocked
     */
    public function isLocked() : bool
    {
        return $this->client->getMergedSettings()->lock_sent_invoices;
    }

    /**
     * Gets the currency from the settings object.
     *
     * @return     Eloquent Model  The currency.
     */
    public function getCurrency()
    {
        return Currency::find($this->settings->currency_id);
    }


    /**
     * Determines if invoice overdue.
     *
     * @param      float    $balance   The balance
     * @param      date.    $due_date  The due date
     *
     * @return     boolean  True if overdue, False otherwise.
     */
    public static function isOverdue($balance, $due_date)
    {
        if (! $this->formatValue($balance,2) > 0 || ! $due_date) {
            return false;
        }

        // it isn't considered overdue until the end of the day
        return strtotime($this->createClientDate(date(), $this->client->timezone()->name)) > (strtotime($due_date) + (60 * 60 * 24));
    }

    public function markViewed()
    {
        $this->last_viewed = Carbon::now()->format('Y-m-d H:i');
    }
    
}
