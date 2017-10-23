<?php

namespace App\Modules\Admin\Models\Action\Report;

use App\Modules\Admin\Models\Admin;
use DB;
use App\Modules\Core\Models\BaseModel;

class Export extends BaseModel
{
    const STATUS_PENDING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_FAILED = 2;
    const STATUS_DONE= 3;
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const FAILED = 'failed';
    const DONE = 'done';

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
    protected $table = 'action_report_export';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Parameter list that applies to date conditions in the clause
     */
    public static $DATE_FILTERS = [
        'from_date' => ['e.created_at', '>=', '%s'],
        'to_date'   => ['e.created_at', '<=', '%s']
    ];

    /**
     * Parameter list that applies to sort order by  in the clause
     */
    public static $SORT_FILTERS = [
        'status'        => ['a.is_active', '=', '%s'],
        'date_added'    => ['e.created_at', '=', '%s']
    ];

    /**
     * Parameter list that applies to the AD Admins table
     */
    public static $EXPORTS_FILTERS = [
        'export_id'         => ['e.id', '=', '%s'],
        'admin_id'          => ['admin_id', '=', '%s'],
        'report_name'       => ['report_name', '=', '%s'],
        'status'            => ['e.is_active', '=', '%s'],
    ];

    /**
     * Maps params to filter columns. (used in cases where column names may appear on other tables)
     *
     * @var array
     */
    public static $map_params_to_filter_columns = [
        'export_id' => 'id',
        'report_from_date' => 'date',
        'report_to_date' => 'to_date',
        'status' => 'is_active',
        'date_added' => 'created_at'
    ];

    /**
     * The list of available statuses.
     *
     * @var array
     */
    public static $status_list = [
        self::PENDING => self::STATUS_PENDING,
        self::PROCESSING => self::STATUS_PROCESSING,
        self::FAILED => self::STATUS_FAILED,
        self::DONE => self::STATUS_DONE
    ];

    /**
     * Creates new export
     * .
     * @param array $data
     * @return object|bool
     */
    public function createExport($data)
    {
        $this->admin_id = $data['admin_id'];
        $this->report_name = $data['report_name'];
        $this->report_file_name = $data['report_file_name'];
        $this->is_active = self::STATUS_PENDING;
        unset($data['report_name']);
        unset($data['report_file_name']);
        $this->query_params = json_encode($data);
        $this->save();
    }

    /**
     * Sets an export's is_active to a value.
     *
     * @param int $export_id
     * @param int $value
     * @return object|bool
     */
    public static function setStatus($export_id, $value = self::STATUS_PENDING)
    {
        $export = self::find($export_id);
        $export->is_active = $value;
        
        if ($export->save())
        {
            return $export;
        }
        
        return false;
    }

    /**
     * Get an export's status.
     *
     * @param int $export_id
     * @return string
     */
    public static function getStatus($export_id)
    {
        $export = self::find($export_id);
        $status_msg = array_search($export->is_active, self::$status_list);
        return (!empty($status_msg)) ? $status_msg : self::PENDING ;
    }

    /**
     * Sets an export's details to a value.
     *
     * @return object|bool
     */
    public function setDetails($value = [])
    {
        $this->details = json_encode($value);
        
        if ($this->save())
        {
            return $this;
        }
        
        return false;
    }

    /**
     * Get Action report export using search parameters.
     *
     * @param array $search_parameters
     * @return object
     */
    public function getActionReportExport($search_parameters,$records_per_page)
    {
        $query = $this->useValues($this->queryRequestReport(), $search_parameters)
            ->useFilters(self::$EXPORTS_FILTERS)
            ->useSortFilters(self::$SORT_FILTERS)
            ->apply();
        return $query->paginate($records_per_page);

    }

    /**
     * Returns a query that selects all the relevant fields for get action report export
     * from the AD database
     *
     * @return obj Query object intended for the AD db
     */
    protected function queryRequestReport()
    {
        return DB::connection($this->connection)
            ->table('action_report_export as e')
            ->Join('admins as a', 'a.id', '=', 'e.admin_id')
            ->select(
                'e.id as id',
                'e.admin_id as admin_id',
                'a.email as email',
                'e.report_name as report_name',
                'e.report_file_name as report_file_name',
                'e.details as details',
                'e.is_active as status',
                'e.created_at as date_added'
            );
    }

    /**
      * function to get query that selects report_file_name from AD database
      * only if export id and admin id matches.
      *
      * @param array $export_id
      * @param array $admin_id
      * @return query
      */
    public function getFileName($export_id, $admin_id)
    {
        $file_name = DB::connection($this->connection)
            ->table('action_report_export')
            ->where('id', '=', $export_id )
            ->where('admin_id', '=', $admin_id )
            ->select('report_file_name')
            ->first();

        return ($file_name != null) ? $file_name : false;
    }

    /**
     * function to get report_file_name from action_report_export table 
     *
     * @return object
     */
    public function getReportFileName()
    {
        $query = $this->queryActionReports()->select('report_file_name')->get();
        return $query;
    }

    /**
     * function to delete data from database
     *
     * @return object
     */
    public function deleteRecord()
    {
        $query = $this->queryActionReports()->delete();
        return $query;
    }

    /**
     * function to get query
     *
     * @return query
     */
    public function queryActionReports()
    {
        $days = config('action_report_export.days');
        return self::where('updated_at', '<', date('Y-m-d H:i:s',strtotime('-'.$days)));
    }
}
