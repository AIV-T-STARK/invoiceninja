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

use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyUser extends Pivot
{
    protected $guarded = ['id'];


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'settings' => 'collection',
        'permissions' => 'object',
    ];

    public function account()
    {
        return $this->hasOne(Account::class);
    }

    public function user()
    {
        return $this->hasOne(User::class)->withPivot('permissions', 'settings', 'is_admin', 'is_owner', 'is_locked');
    }

    public function company()
    {
    	return $this->hasOne(Company::class)->withPivot('permissions', 'settings', 'is_admin', 'is_owner', 'is_locked');
    }
}
