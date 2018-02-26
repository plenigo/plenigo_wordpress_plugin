<?php

namespace plenigo_plugin\settings;

require_once __DIR__ . '/WP_List_Table.php';

/**
 * Class LogTable, contains all the logic for paging and loading the log table list.
 *
 * @package plenigo_plugin\settings
 */
class LogTable extends WP_List_Table {
    /**
     * This decides how many records per page to show.
     */
    public $perPage = 10;
    public $escapeChars = true;

    /**
     * Set up a constructor that references the parent constructor. We use the parent reference to set some default configs.
     */
    function __construct() {
        //Set parent defaults
        parent::__construct(
            array(
                //singular name of the listed records
                'singular' => 'log',
                //plural name of the listed records
                'plural' => 'logs',
                //does this table support ajax?
                'ajax' => true
            )
        );
    }

    /**
     * Builder used for the log email.
     *
     * @return LogTable
     */
    public static function makeNewForLogMail() {
        $obj = new LogTable();
        $obj->perPage = 50;
        return $obj;
    }

    /**
     * Default columns to show.
     *
     * @param object $item object that has the columns
     * @param string $column_name name of the column
     *
     * @return mixed can return the column or the complete row in case the value is not there
     */
    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'date':
            case 'log':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    /**
     * Return the column names.
     *
     * @return array of column names
     */
    function get_columns() {
        return $columns = array('date' => 'Date', 'log' => 'Log');
    }

    /**
     * This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed.
     *
     * @global WPDB $wpdb
     *
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     */
    function prepare_items() {
        global $wpdb;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $currentPage = $this->get_pagenum();
        $startingCount = ($currentPage - 1) * $this->perPage;

        $tableName = $wpdb->prefix . 'plenigo_log';
        $sql = "SELECT * FROM $tableName ORDER BY creation_date DESC LIMIT {$this->perPage} OFFSET $startingCount";
        $logLines = $wpdb->get_results($sql);
        $logLinesArray = [];
        foreach ($logLines as $logLine) {
            $logData = $logLine->log;
            if ($this->escapeChars) {
                $logData = htmlspecialchars($logLine->log);
            }
            $logLinesArray[] = array('date' => $logLine->creation_date, 'log' => $logData);
        }
        $data = $logLinesArray;

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $tableName");

        $this->items = $data;

        /**
         * We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page' => $this->perPage,
                'total_pages' => ceil($total_items / $this->perPage),
            )
        );
    }

    /**
     * Generate the table navigation above or below the table.
     *
     * @param string $which
     */
    protected function display_tablenav($which) {
        //we need to override this method in order to avoid wpnonce token errors
        ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">
            <?php if ($this->has_items()) : ?>
                <div class="alignleft actions bulkactions">
                    <?php $this->bulk_actions($which); ?>
                </div>
            <?php
                endif;
                $this->extra_tablenav($which);
                $this->pagination($which);
            ?>
            <br class="clear"/>
        </div>
        <?php
    }

    /**
     * Handle an incoming ajax request (called from admin-ajax.php).
     *
     */
    function ajaxResponse() {
        $this->prepare_items();

        extract($this->_args);
        extract($this->_pagination_args, EXTR_SKIP);

        ob_start();
        if (!empty($_REQUEST['no_placeholder']))
            $this->display_rows();
        else
            $this->display_rows_or_placeholder();

        $rows = ob_get_clean();

        ob_start();
        $this->print_column_headers();
        $headers = ob_get_clean();

        ob_start();
        $this->pagination('top');
        $pagination_top = ob_get_clean();

        ob_start();
        $this->pagination('bottom');
        $pagination_bottom = ob_get_clean();

        $response = array('rows' => $rows);
        $response['pagination']['top'] = $pagination_top;
        $response['pagination']['bottom'] = $pagination_bottom;
        $response['column_headers'] = $headers;

        if (isset($total_items))
            $response['total_items_i18n'] = sprintf(_n('1 item', '%s items', $total_items), number_format_i18n($total_items));

        if (isset($total_pages)) {
            $response['total_pages'] = $total_pages;
            $response['total_pages_i18n'] = number_format_i18n($total_pages);
        }

        die(json_encode($response));
    }

    /**
     * Prepares the items and returns them as a string.
     *
     * @return string
     */
    public function toString() {
        $this->prepare_items();
        $logData = '';
        foreach ($this->items as $log) {
            $logData .= $log['log'] . PHP_EOL;
        }
        return $logData;
    }
}