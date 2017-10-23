<?php

namespace App\Modules\Admin\Models\Role;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use App\Modules\Admin\Models\Role;
use Exception;
use DateTime;
use Hash;
use Input;
use DB;

class Admin extends Authenticatable
{
    use EntrustUserTrait; 
    
    /**
     * The connection associated with the model.
     *
     * @var string
     */
    protected $connection = 'AD';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'role_admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

}
