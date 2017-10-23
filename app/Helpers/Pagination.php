<?php 
namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Input;

class Pagination
{
    const DEFAULT_OFFSET = 0;
    const DEFAULT_LIMIT  = 10;
    
    /**
     * This function is used to format the pagination data with links to current page, 
     * next page, previous page and last page.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Pagination\LengthAwarePaginator $data
     * @return array $pagination
     */
    public static function formatPagination($request, $data, $query_string_params = array())
    {
        $pagination = array();
        $hostname = config('settings.HOSTNAME');

        $resource = strstr($_SERVER['REQUEST_URI'], '?')
                    ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))
                    : $_SERVER['REQUEST_URI'];

        $total_records = $data->total();

        if($total_records > 0)
        {
            $first_page = 1;
            $per_page = $data->perPage();
            $total_pages = $data->lastPage();
            $current_page = $data->currentPage();
            $previous_page = $current_page - 1;
            $next_page = $current_page + 1;
            $records_per_page = '';

            if(isset($query_string_params['page']))
            {
                unset($query_string_params['page']);
            }

            if(!isset($query_string_params['records_per_page']) 
                || empty($query_string_params['records_per_page']))
            {
                $records_per_page = '&records_per_page='.$per_page;
                unset($query_string_params['records_per_page']);
            }

            $query_string = http_build_query($query_string_params);
            
            $route = $hostname.$resource.'?'.$query_string.$records_per_page.'&page=';

            $pagination = array(
                'total_records' => $total_records,
                'records_per_page' => $data->perPage(),
                'total_pages' => $total_pages,
                'page' => $current_page,
                'links' => array(
                    array('self' => $route.$current_page),
                    array('first' => $route.$first_page),
                    array('last' => $route.$total_pages),
                )
            );

            if($previous_page > 0)
            {
                array_push($pagination['links'], array('previous' => $route.$previous_page));
            }

            if($next_page < $total_pages)
            {
                array_push($pagination['links'], array('next' => $route.$next_page));
            }
        }

        return $pagination;
    }

    /**
     * Function to create pagination object manually
     *
     * @param $response
     * @param $records_per_page
     * @return LengthAwarePaginator
     */
    public static function manualPaginate($response, $records_per_page)
    {
        $slice = array_slice($response, $records_per_page * ((Input::get('page') ? Input::get('page') : 1) - 1), $records_per_page);
        return new LengthAwarePaginator($slice, count($response), $records_per_page);
    }

    /**
     * Function to create simple basic pagination
     *
     * @param $query
     * @param $records_per_page
     * @return Object
     */
    public static function simplePaginate($query ,$records_per_page)
    {
        $page = Input::get('page', 1) - 1;
        return $query->skip($page * $records_per_page)->take($records_per_page)->get();
    }

    /**
     * Function to pagination OP's response
     * @param  \Illuminate\Http\Request $request
     * @param  array $data
     * @param  array $parameters
     * @return array $response
     */
    public static function paginateOpResponse($request ,$data, $parameters, $links_required = true)
    {
        $response = [
            'pagination'    => [],
            'response'      => []
        ];

        $resource = strstr($_SERVER['REQUEST_URI'], '?')
                    ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))
                    : $_SERVER['REQUEST_URI'];

        $hostname = config('settings.HOSTNAME');
        $dataset = $data;

        if(isset($dataset['count']))
        {
            unset($dataset['count']);
        }
        
        if(isset($dataset['link']))
        {
            unset($dataset['link']);
        }
        
        $response['response'] = $dataset;

        unset($dataset);

        $totalRecords = isset($data['count']['total']) ? (integer)$data['count']['total'] : count($response['response']);

        if(isset($parameters['limit']))
        {
            unset($parameters['limit']);
        }

        $parameters['records_per_page'] = !isset($parameters['records_per_page']) || empty($parameters['records_per_page']) 
            ? 10 : (integer)$parameters['records_per_page'];

        $parameters['page'] = !isset($parameters['page']) || empty($parameters['page']) 
            ? 1 : (integer)$parameters['page'];

        $totalPages = (integer)(ceil( $totalRecords / $parameters['records_per_page'] ));

        $self = $hostname.$resource.'?'.http_build_query($parameters);
        $first = $hostname.$resource.'?'.http_build_query( array_merge($parameters, ['page' => 1]) );
        $last = $hostname.$resource.'?'.http_build_query( array_merge($parameters, ['page' => $totalPages == 0 ? 1 : $totalPages]) );

        $response['pagination'] = [
            'total_records'     => $totalRecords,
            'page'              => isset($parameters['page']) ? $parameters['page'] : '',
            'records_per_page'  => $parameters['records_per_page'],
            'total_pages'       => $totalPages == 0 ? 1 : $totalPages,
            'links'             => [
                ['self'  => isset($self) ? $self : ''],
                ['first' => $first],
                ['last'  => $last]
            ]
        ];
        
        unset($data);
        return $response;
    }

    /**
     * This function is used to show pagination for next and previous link for OP endpoint
     *
     * @param       $request
     * @param       $data
     * @param array $links
     * @param array $query_string_params
     * @return array
     */
    public static function customPagination($request, $data, $links = array(), $query_string_params = array())
    {
        $pagination = array();
        $hostname = config('settings.HOSTNAME');

        $resource = strstr($_SERVER['REQUEST_URI'], '?')
                    ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))
                    : $_SERVER['REQUEST_URI'];
        
        $total_records = count($data);
        $page = isset($query_string_params['page']) ? $query_string_params['page'] : 1;      
       
        if($total_records > 0)
        {            
            if(isset($query_string_params['page']))
            {
                unset($query_string_params['page']);
            }            

            $query_string = http_build_query($query_string_params);
            
            $route = $hostname.$resource.'?'.$query_string.'&page=';

            if(empty($links))
            {
                return array();
            }

            foreach ($links as $link)
            {                
               if($link['rel'] == 'next')
               {
                   $next_page = substr($link['href'], strrpos($link['href'], '/') + 1);
                   array_push($pagination, array('next' => $route.$next_page));
               }
               if($link['rel'] == 'prev')
               {
                   $previous_page = substr($link['href'], strrpos($link['href'], '/') + 1);
                   array_push($pagination, array('previous' => $route.$previous_page));
               }               
            }            
        }

        return $pagination;
    }

    /**
     * Computes for the offset of the pagination
     *
     * @param null $offset
     * @param      $recordsPerPage
     * @return null
     */
    public static function getOffset($offset = null, $recordsPerPage = 10)
    {
        if(! empty($offset) && $offset != 1)
        {
            return ($offset -1) * $recordsPerPage;
        }

        return 0;
    }

    /**
     * Computes for the limit of the pagination
     *
     * @param null $limit
     * @return int|null
     */
    public static function getLimit($limit = null)
    {
        if(! empty($limit))
        {
            return (int)$limit;
        }

        return 10;
    }

    /**
     * Gets the limit and offset of a given $parameters
     *
     * returns the $parameter being passed
     *
     * @param $parameters
     * @param $max_records_per_page
     *
     * @return array
     */
    public static function getOPLimitAndOffset($parameters, $max_records_per_page = 100)
    {
        $page = (! empty( $parameters['page']) ) ?  $parameters['page'] : 1;
        $parameters['limit'] = !empty($parameters['records_per_page']) ? $parameters['records_per_page'] > $max_records_per_page ? $max_records_per_page : $parameters['records_per_page'] : self::DEFAULT_LIMIT;
        $parameters['offset'] = ($page - 1) * $parameters['limit'];

        $parameters = array_map('trim', $parameters);

        return $parameters;
    }
}